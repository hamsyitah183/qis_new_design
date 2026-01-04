import { notifyUser, showToast } from "../../../app";

/**
 * ✅ Lazy Initialize DataTable for Internal Users
 */
let internalListTable;

async function data_table_init() {
    const [
        { default: DataTable },
        _bs5,
        _responsive,
        _buttons,
        _buttonsHtml5,
        _buttonsPrint,
    ] = await Promise.all([
        import("datatables.net-bs5"),
        import("datatables.net-responsive-bs5"),
        import("datatables.net-buttons-bs5"),
        import("datatables.net-buttons/js/buttons.html5.mjs"),
        import("datatables.net-buttons/js/buttons.print.mjs"),
    ]);

    await Promise.all([
        import("datatables.net-bs5/css/dataTables.bootstrap5.min.css"),
        import("datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css"),
    ]);

    internalListTable = new DataTable("#internalUsersTable", {
        processing: true,
        serverSide: true,
        ajax: "/internal/user_internal/list/data",
        columns: [
            { data: "fullname", name: "fullname" },
            { data: "email", name: "email" },
            { data: "phone_number", name: "phone_number" },
            { data: "position", name: "position" },
            { data: "role", name: "role", orderable: false, searchable: false },
            { data: "office", name: "office" },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
            },
        ],
        responsive: true,
        pageLength: 10,
    });
}

async function getSwal() {
    const { default: Swal } = await import("sweetalert2");
    return Swal;
}

/**
 * ✅ Open modal for Add / Edit / View
 */
async function open_internal_user_modal(mode = "add", userId = null) {
    const Swal = await getSwal();
    const isAdd = mode === "add";
    const isView = mode === "view";
    const isEdit = mode === "edit";

    const title = isAdd
        ? "Add Internal User"
        : isView
        ? "View Internal User"
        : "Edit Internal User";
    $("#internalUserModalLabel").text(title);
    $("#internalUserForm")[0].reset();
    $(".form-control").removeClass("is-invalid");
    $(".invalid-feedback").text("");

    $("#internalUserForm input, #internalUserForm select").prop(
        "readonly",
        isView
    );
    $("#internalUserForm select").prop("disabled", isView);
    if (isView) $("#internalUserModal .modal-footer").hide();
    else $("#internalUserModal .modal-footer").show();

    if (isAdd) {
        $("#userUuid").val("");
        new bootstrap.Modal("#internalUserModal").show();
        return;
    }

    if (isEdit) {
        $("#email").prop("readonly", true);
    }

    Swal.fire({
        title: "Loading...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: `/internal/user_internal/user/data/${userId}`,
        type: "GET",
        success: function (response) {
            const user = response.user;
            $("#userUuid").val(user.uuid);
            $("#fullname").val(user.fullname);
            $("#email").val(user.email);
            $("#phone_number").val(user.phone_number);
            $("#position").val(user.position || "");
            $("#office").val(user.office || "");
            $("#no_ic").val(user.no_ic || "");
            $("#no_ic").prop("readonly", true);
            Swal.close();
            new bootstrap.Modal("#internalUserModal").show();
        },
        error: function () {
            Swal.fire("Error", "Unable to load user details", "error");
        },
    });
}

/**
 * ✅ Handle Add + Edit form submission (bind once!)
 */
function handle_internal_user_submit() {
    $(document).on("submit", "#internalUserForm", async function (e) {
        e.preventDefault();
        const Swal = await import("sweetalert2").then((m) => m.default);

        $(".form-control").removeClass("is-invalid");
        $(".invalid-feedback").text("");

        const formData = $(this).serialize();
        const uuid = $("#userUuid").val();
        const isEdit = Boolean(uuid);

        Swal.fire({
            title: isEdit ? "Updating user..." : "Saving user...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: `/internal/user_internal/save`,
            method: "POST",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: isEdit ? "User Updated!" : "User Added!",
                    text: response.message,
                });
                bootstrap.Modal.getInstance("#internalUserModal").hide();
                internalListTable.ajax.reload();
            },
            error: function (xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach((key) => {
                        const input = $(`#${key}`);
                        input.addClass("is-invalid");
                        $(`#error-${key}`).text(errors[key][0]);
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Failed!",
                        text: "Something went wrong while saving the user.",
                    });
                }
            },
        });
    });
}

/**
 * ✅ Delete handler
 */
async function delete_internal_user() {
    const Swal = await getSwal();
    $(document).on("click", ".deleteBtn", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "This action cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/internal/user_internal/delete/${userId}`,
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function () {
                        Swal.fire(
                            "Deleted!",
                            "User deleted successfully.",
                            "success"
                        );
                        internalListTable.ajax.reload();
                    },
                    error: function () {
                        Swal.fire("Error!", "Failed to delete user.", "error");
                    },
                });
            }
        });
    });
}

/**
 * ✅ Initialize everything
 */
async function internal_user_list() {
    await data_table_init();
    handle_internal_user_submit(); // bind once
    await delete_internal_user();

    $(document).on("click", ".addInternalUser-modal", (e) => {
        e.preventDefault();
        open_internal_user_modal("add");
    });
    $(document).on("click", ".viewInternalUser-modal", (e) => {
        e.preventDefault();
        open_internal_user_modal("view", $(e.currentTarget).data("id"));
    });
    $(document).on("click", ".editInternalUser-modal", (e) => {
        e.preventDefault();
        open_internal_user_modal("edit", $(e.currentTarget).data("id"));
    });

    $("#internalUserModal").on("hidden.bs.modal", function () {
        $("#internalUserModalLabel").text("Add Internal User");
        $("#internalUserForm")[0].reset();
        $("#internalUserForm input, #internalUserForm select").prop(
            "readonly",
            false
        );
        $("#internalUserForm select").prop("disabled", false);
        $(".form-control").removeClass("is-invalid");
        $(".invalid-feedback").text("");
        $("#internalUserModal .modal-footer").show();
    });

    console.log("user id", userId);
    setTimeout(() => {
        window.Echo.channel("internal-user-added").listen(
            "InternalUserAdded",
            (e) => {
                console.log(e.message);
            }
        );

        window.Echo.private(`internal-user-edited.${userId}`).listen(
            ".InternalUserEdited",
            (e) => {
                console.log("✅ YOU were edited:", e.message);
                showToast(`${e.message} (by ${e.editor})`);
                notifyUser(e.message, e.editor);
                
            
            }
        );

        window.Echo.channel("internal-user-deleted").listen(
            "InternalUserDeleted",
            (e) => {
                console.log(e.message);
            }
        );
    }, 100);
}

// ✅ Initialize on page load
internal_user_list();
