async function public_user_list() {
    // 🔹 Lazy-load heavy libraries
    const [{ default: DataTable }, { default: Swal }] = await Promise.all([
        import("datatables.net-dt"),
        import("sweetalert2"),
    ]);

    /**
     * ✅ Initialize DataTable
     */
    async function data_table_init() {
        const [
            { default: DataTable },
            _bs5, // Bootstrap 5 integration
            _responsive, // Responsive extension
            _buttons, // Buttons extension
            _buttonsHtml5, // HTML5 export buttons
            _buttonsPrint, // Print button
        ] = await Promise.all([
            import("datatables.net-bs5"), // Core + Bootstrap 5 styling
            import("datatables.net-responsive-bs5"), // Responsive
            import("datatables.net-buttons-bs5"), // Buttons styling
            import("datatables.net-buttons/js/buttons.html5.mjs"), // CSV, Excel, PDF
            import("datatables.net-buttons/js/buttons.print.mjs"), // Print
        ]);

        // Import styles
        await Promise.all([
            import("datatables.net-bs5/css/dataTables.bootstrap5.min.css"),
            // import(
            //     "datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css"
            // ),
            import("datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css"),
        ]);
        new DataTable("#publicUsersTable", {
            processing: true,
            serverSide: true,
            ajax: "/internal/user_public/list/data",
            columns: [
                { data: "fullname", name: "fullname" },
                { data: "account_type", name: "account_type" },
                { data: "email", name: "email" },
                { data: "phone_number", name: "phone_number" },
                { data: "created_at", name: "created_at" },
                {
                    data: "doa_verified",
                    name: "doa_verified",
                    orderable: false,
                    searchable: false,
                },
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
    function open_public_user_modal(mode = "add", userId = null) {
        const isAdd = mode === "add";
        const isView = mode === "view";
        const isEdit = mode === "edit";

        const title = isAdd
            ? "Add Public User"
            : isView
            ? "View Public User"
            : "Edit Public User";
        $("#publicUserModalLabel").text(title);

        // Reset form + validation
        $("#publicUserForm")[0].reset();
        $(".form-control").removeClass("is-invalid");
        $(".invalid-feedback").text("");

        // Toggle footer
        if (isView) $("#publicUserModal .modal-footer").hide();
        else $("#publicUserModal .modal-footer").show();

        // Readonly in view mode
        $("#publicUserForm input, #publicUserForm select").prop(
            "readonly",
            isView
        );
        $("#publicUserForm select").prop("disabled", isView);

        if (isAdd) {
            $("#userUuid").val("");
            new bootstrap.Modal(
                document.getElementById("publicUserModal")
            ).show();
            return;
        }

        if(isEdit) {
            $("#email").prop('readonly',isEdit )
        }

        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: `/internal/user_public/user/data/${userId}`,
            type: "GET",
            success: function (response) {
                const user = response.user;
                $("#userUuid").val(user.uuid);
                $("#fullname").val(user.fullname);
                $("#no_ic").val(user.no_ic);
                $("#email").val(user.email);
                $("#account_type").val(user.account_type);
                $("#phone_number").val(user.phone_number);
                $("#office_number").val(user.office_number || "");
                $("#address_1").val(user.address_1);
                $("#address_2").val(user.address_2 || "");
                $("#postcode").val(user.postcode);
                $("#district").val(user.district);
                $("#state").val(user.state);

                Swal.close();
                new bootstrap.Modal(
                    document.getElementById("publicUserModal")
                ).show();
            },
            error: function () {
                Swal.fire("Error", "Unable to load user details", "error");
            },
        });
    }

    /**
     * ✅ Handle Add + Edit form submission
     */
    function handle_public_user_submit() {
        $(document)
            .off("submit", "#publicUserForm")
            .on("submit", "#publicUserForm", function (e) {
                e.preventDefault();
                $(".form-control").removeClass("is-invalid");
                $(".invalid-feedback").text("");

                const formData = $(this).serialize();
                const userUuid = $("#userUuid").val();
                const isEdit = Boolean(userUuid);

                Swal.fire({
                    title: isEdit ? "Updating user..." : "Saving user...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: "/internal/user_public/save",
                    method: "POST",
                    data: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (response) {
                        console.log('response detal', response)
                        Swal.fire({
                            icon: "success",
                            title: isEdit ? "User Updated!" : "User Added!",
                            text: response.message,
                        });

                        const modalEl =
                            document.getElementById("publicUserModal");
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();

                        $("#publicUsersTable").DataTable().ajax.reload();
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
    function delete_public_user() {
        $(document).on("click", ".deletePublicUser", function (e) {
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
                        url: `/internal/user_public/delete/${userId}`,
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
                            $("#publicUsersTable").DataTable().ajax.reload();
                        },
                        error: function () {
                            Swal.fire(
                                "Error!",
                                "Failed to delete user.",
                                "error"
                            );
                        },
                    });
                }
            });
        });
    }

    /**
     * ✅ Initialize Handlers
     */
    data_table_init();
    handle_public_user_submit();

    // Add
    $(document).on("click", ".addPublicUser-modal", (e) => {
        e.preventDefault();
        open_public_user_modal("add");
    });

    // View
    $(document).on("click", ".viewPublicUser-modal", function (e) {
        e.preventDefault();
        open_public_user_modal("view", $(this).data("id"));
    });

    // Edit
    $(document).on("click", ".editPublicUser-modal", function (e) {
        e.preventDefault();
        open_public_user_modal("edit", $(this).data("id"));
    });

    delete_public_user();

    // Reset modal on close
    $("#publicUserModal").on("hidden.bs.modal", function () {
        $("#publicUserModalLabel").text("Add Public User");
        $("#publicUserForm")[0].reset();
        $("#publicUserForm input, #publicUserForm select").prop(
            "readonly",
            false
        );
        $("#publicUserForm select").prop("disabled", false);
        $(".form-control").removeClass("is-invalid");
        $(".invalid-feedback").text("");
        $("#publicUserModal .modal-footer").show();
    });
}

/**
 * ✅ Only load this feature when needed (lazy)
 */
document.addEventListener("DOMContentLoaded", () => {
    const tableEl = document.querySelector("#publicUsersTable");
    if (tableEl) public_user_list();
});
