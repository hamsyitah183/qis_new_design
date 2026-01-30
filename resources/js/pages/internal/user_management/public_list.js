import $ from "jquery";
import Swal from "sweetalert2";
import { fetchVerificationCount, formatTime } from "../../../app";

let publicUsersTable;

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
        publicUsersTable = new DataTable("#publicUsersTable", {
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

        if (isEdit) {
            $("#email").prop("readonly", isEdit);
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
                        console.log("response detal", response);
                        Swal.fire({
                            icon: "success",
                            title: isEdit ? "User Updated!" : "User Added!",
                            text: response.message,
                        });

                        const modalEl =
                            document.getElementById("publicUserModal");
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();

                        if (publicUsersTable) publicUsersTable.ajax.reload();
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

    // delete
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

    // verification modal
    $(document).on("click", ".badge-verification", function (e) {
        e.preventDefault();

        const id = $(this).data("id");
        $(".ic").text("");
        $("#userIC").empty();

        showLoader();

        fetchVerificationData(id)
            .then((response) => {
                Swal.close();
                handleVerificationModal(response, id);
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to load verification info.",
                });
            });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const tableEl = document.querySelector("#publicUsersTable");
    if (tableEl) public_user_list();
});

// 🔹 1. Show the loader
function showLoader(text = "Loading...") {
    Swal.fire({
        title: text,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });
}

// 🔹 2. Fetch data via AJAX (returns a Promise)
function fetchVerificationData(id) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/internal/verification/${id}`,
            method: "GET",
            success: resolve,
            error: reject,
        });
    });
}

// 🔹 3. Handle modal setup
function handleVerificationModal(response, id) {
    const modalEl = document.getElementById("verificationModal");

    console.log('response', response)

    if (!modalEl) {
        console.error("Modal element not found!");
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    // Check if public_user exists
    if (!response || !response.public_user) {
        $("#userIC").html("<p>No attachment returned.</p>");
        $(".ic").text("");
        $(".status").html("");
        $("#verificationBtn").hide();
        $("#unverificationBtn").hide();
        return;
    }

    const user = response.public_user;

    setupVerificationButton(response, id);
    $(".ic").text(user.no_ic || "");
    $('.updated_at').text(formatTime(response.updated_at))

    renderAttachment(response.verification_attachment);
    renderStatus(response.status);
    $("#verificationBtn").show();
    $("#unverificationBtn").show();

    if(response.doa_verified == 1) {
        $("#verificationBtn").hide();
        $("#unverificationBtn").hide();
    }

}

// 🔹 4. Setup Verify/Unverify button
function setupVerificationButton(response, id) {
    const btn = $("#verificationBtn");
    btn.removeClass().addClass("btn");

    if (response.doa_verified === "yes") {
        btn.addClass("btn-light btn-wave waves-effect waves-light")
            .text("Unverified User")
            .attr("data-id", id);
    } else {
        btn.addClass("btn-success").text("Verified User").attr("data-id", id);
    }

     $("#unverificationBtn").attr('data-id', id);
}

// 🔹 5. Render Attachment (image or PDF)
function renderAttachment(fileUrl) {
    const container = $("#userIC");
    container.empty();

    if (!fileUrl) {
        container.html("<p>No attachment returned.</p>");
        return;
    }

    const fileExtension = fileUrl.split(".").pop().toLowerCase();

    if (["jpg", "jpeg", "png", "gif", "webp"].includes(fileExtension)) {
        container.append(
            `<img src="/${fileUrl}" class="img-fluid" alt="Verification Attachment">`
        );
    } else if (fileExtension === "pdf") {
        container.append(
            `<iframe src="/${fileUrl}" class="w-100" style="height:500px;" frameborder="0"></iframe>`
        );
    } else {
        container.append(`<p>Unsupported file format: ${fileExtension}</p>`);
    }
}

// 🔹 6. Render status message
function renderStatus(status) {
    let statusText = "";

    if (status?.toLowerCase().includes("waiting")) {
        statusText = `
            <div class="alert alert-warning" role="alert">
                <i class="ti ti-alert-circle me-2 fs-16"></i>
                ${status}
            </div>`;
    }

    if (status?.toLowerCase().includes("approved")) {
        statusText = `
            <div class="alert alert-success" role="alert">
                <i class="ti ti-rosette-discount-check me-2 fs-16"></i>
                ${status}
            </div>`;
    }

    if (status?.toLowerCase().includes("rejected")) {
        statusText = `
            <div class="alert alert-danger" role="alert">
                <i class="ti ti-rosette-discount-check me-2 fs-16"></i>
                ${status}
            </div>`;
    }

    $(".status").html(statusText);
}

function hideLoader() {
    // ✅ Check if a Swal is open before closing it
    if (Swal.isVisible()) {
        Swal.close();
    }
}

// approve
$("#verificationBtn").on("click", function (e) {
    e.preventDefault();

    const id = $(this).data("id");
    const url = `/internal/verification/${id}/save`;

    // 🌀 Show loader
    showLoader("Approving user...");

    $.ajax({
        url: url,
        method: "POST",
        data: {
            approved: "yes",
            _token: $('meta[name="csrf-token"]').attr("content"), // ✅ Laravel CSRF token
        },
        success: function (response) {
            Swal.fire({
                icon: "success",
                title: "Approved!",
                text: "User has been successfully verified.",
                timer: 2000,
                showConfirmButton: false,
            });
            fetchVerificationCount()
            
            // Close modal after success
            const modal = bootstrap.Modal.getInstance(
                document.getElementById("verificationModal")
            );
            if (modal) modal.hide();

            if (publicUsersTable) publicUsersTable.ajax.reload();
        },
        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    xhr.responseJSON?.message ||
                    "Something went wrong while approving.",
            });
        },
    });
});

$("#unverificationBtn").on("click", function (e) {
    e.preventDefault();

    const id = $(this).data("id");
    const url = `/internal/verification/${id}/save`;

    // ✅ Close any active loader before showing Swal (avoid overlay conflict)
    hideLoader?.();

    // ✅ Hide the Bootstrap modal before opening Swal
    const modal = bootstrap.Modal.getInstance(
        document.getElementById("verificationModal")
    );
    if (modal) modal.hide();

    // ✅ Small delay to ensure modal is fully hidden before Swal opens
    setTimeout(() => {
        Swal.fire({
            title: "Unverify User",
            html: `
            <p class="mb-2">Please provide a reason for unverifying this user:</p>
            <textarea id="unverifyReason" style = "width:90%; font-size:12px; lineheight: 1.5;" 
            class="swal2-textarea" placeholder="Enter reason here..."></textarea>
        `,
            showCancelButton: true,
            confirmButtonText: "Submit",
            cancelButtonText: "Cancel",
            focusConfirm: false, // ✅ allow typing freely
            didOpen: () => {
                const textarea = document.getElementById("unverifyReason");
                if (textarea) textarea.focus(); // Auto-focus textarea
            },
            preConfirm: () => {
                const reason = document
                    .getElementById("unverifyReason")
                    .value.trim();
                if (!reason) {
                    Swal.showValidationMessage("Reason is required!");
                    return false;
                }
                return reason;
            },
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = result.value;

                // 🌀 Show loader after confirmation
                showLoader("Unverifying user...");

                $.ajax({
                    url: `/internal/verification/${id}/save`,
                    method: "POST",
                    data: {
                        approved: "no",
                        reason: reason,

                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function () {
                        Swal.fire({
                            icon: "success",
                            title: "User Unverified",
                            text: "The user has been unverified successfully.",
                            timer: 2000,
                            showConfirmButton: false,
                        });

                        if (publicUsersTable) publicUsersTable.ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text:
                                xhr.responseJSON?.message ||
                                "Something went wrong while unverifying.",
                        });
                    },
                });
            }
        });
    }, 200); // 200ms delay
});
