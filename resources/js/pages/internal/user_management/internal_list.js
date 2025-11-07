import DataTable from "datatables.net-dt";
// import "datatables.net-dt/css/dataTables.dataTables.css";

import Swal from "sweetalert2";

/**
 * ✅ Initialize DataTable for Internal Users
 */
function data_table_init() {
    const table = new DataTable("#internalUsersTable", {
        processing: true,
        serverSide: true,
        ajax: "/internal/user_internal/list/data",
        columns: [
            { data: "name", name: "name" },
            { data: "email", name: "email" },
            { data: "phone", name: "phone" },
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
    });
}

/**
 * ✅ Open modal for Add / Edit / View
 */
function open_internal_user_modal(mode = "add", userId = null) {
    const isAdd = mode === "add";
    const isView = mode === "view";
    const isEdit = mode === "edit";

    // 🔹 Modal title
    const title = isAdd
        ? "Add Internal User"
        : isView
        ? "View Internal User"
        : "Edit Internal User";
    $("#internalUserModalLabel").text(title);

    // 🔹 Reset form
    $("#internalUserForm")[0].reset();
    $(".form-control").removeClass("is-invalid");
    $(".invalid-feedback").text("");

    // 🔹 Footer visibility
    if (isView) $("#internalUserModal .modal-footer").hide();
    else $("#internalUserModal .modal-footer").show();

    // 🔹 Readonly toggle
    $("#internalUserForm input, #internalUserForm select").prop(
        "readonly",
        isView
    );
    $("#internalUserForm select").prop("disabled", isView);

    // 🔹 Add Mode → open empty modal
    if (isAdd) {
        $("#userUuid").val("");
        const modal = new bootstrap.Modal(
            document.getElementById("internalUserModal")
        );
        modal.show();
        return;
    }

    // 🔹 View/Edit → Fetch user data
    Swal.fire({
        title: "Loading...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: `/internal/user_internal/user/data/${userId}`,
        type: "GET",
        success: function (user) {
            $("#userUuid").val(user.uuid);
            $("#fullname").val(user.name);
            $("#email").val(user.email);
            $("#phone_number").val(user.phone);
            $("#office_number").val(user.office || "");
            $("#position").val(user.position || "");
            $("#no_ic").val(user.no_ic || "");

            Swal.close();
            const modal = new bootstrap.Modal(
                document.getElementById("internalUserModal")
            );
            modal.show();
        },
        error: function () {
            Swal.fire("Error", "Unable to load user details", "error");
        },
    });
}

/**
 * ✅ Handle Add + Edit form submission
 */
function handle_internal_user_submit() {
    $(document)
        .off("submit", "#internalUserForm")
        .on("submit", "#internalUserForm", function (e) {
            e.preventDefault();

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
                url: `/internal/internal-users/${uuid}`,
                method: isEdit ? "PUT" : "POST",
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: isEdit ? "User Updated!" : "User Added!",
                        text: response.message,
                    });

                    const modalEl = document.getElementById("internalUserModal");
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();

                    $("#internalUsersTable").DataTable().ajax.reload();
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
function delete_internal_user() {
    $(document).on("click", ".deleteInternalUser", function (e) {
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
                    url: `/internal/internal-users/${userId}`,
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
                        $("#internalUsersTable").DataTable().ajax.reload();
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
 * ✅ Initialize all handlers
 */
function internal_user_list() {
    data_table_init();
    handle_internal_user_submit();

    // Add
    $(document).on("click", ".addInternalUser-modal", function (e) {
        e.preventDefault();
        open_internal_user_modal("add");
    });

    // View
    $(document).on("click", ".viewInternalUser-modal", function (e) {
        e.preventDefault();
        open_internal_user_modal("view", $(this).data("id"));
    });

    // Edit
    $(document).on("click", ".editInternalUser-modal", function (e) {
        e.preventDefault();
        open_internal_user_modal("edit", $(this).data("id"));
    });

    delete_internal_user();

    // Reset modal on close
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
}

// ✅ Initialize on page load
internal_user_list();
