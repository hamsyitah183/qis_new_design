import { notifyUser, showToast, applyTranslations } from "../../../app";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    loading: { en: 'Loading...', bm: 'Memuat...' },
    fetchUserWait: { en: 'Please wait while we fetch the user details.', bm: 'Sila tunggu sementara kami mengambil butiran pengguna.' },
    processWait: { en: 'Please wait while we process your request.', bm: 'Sila tunggu sementara kami memproses permintaan anda.' },
    dataError: { en: 'Data Processing Error', bm: 'Ralat Pemprosesan Data' },
    dataErrorMsg: { en: 'An error occurred while processing the user data. Please try again.', bm: 'Ralat berlaku semasa memproses data pengguna. Sila cuba lagi.' },
    unexpectedError: { en: 'Unexpected Error', bm: 'Ralat Tidak Dijangka' },
    unexpectedErrorMsg: { en: 'An unexpected error occurred. Please try again.', bm: 'Ralat tidak dijangka berlaku. Sila cuba lagi.' },
    updatingUser: { en: 'Updating user...', bm: 'Mengemas kini pengguna...' },
    savingUser: { en: 'Saving user...', bm: 'Menyimpan pengguna...' },
    userUpdated: { en: 'User Updated!', bm: 'Pengguna Dikemas kini!' },
    userAdded: { en: 'User Added!', bm: 'Pengguna Ditambah!' },
    userUpdatedMsg: { en: 'User has been updated successfully.', bm: 'Pengguna telah berjaya dikemas kini.' },
    userAddedMsg: { en: 'User has been added successfully.', bm: 'Pengguna telah berjaya ditambah.' },
    validationError: { en: 'Validation Error', bm: 'Ralat Pengesahan' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    cannotUndo: { en: 'This action cannot be undone!', bm: 'Tindakan ini tidak dapat dipulihkan!' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    userDeletedMsg: { en: 'User deleted successfully.', bm: 'Pengguna berjaya dipadam.' },
    error: { en: 'Error!', bm: 'Ralat!' },
    deleteFailed: { en: 'Failed to delete user.', bm: 'Gagal memadam pengguna.' },
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}


import { autoInitFilterSelect2 } from "../../../utils/select2Utils";

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
            { data: "branch", name: "branch" },
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

    // Init Select2 on all static filter selects (those with class 'select2')
    autoInitFilterSelect2();
}

async function getSwal() {
    const { default: Swal } = await import("sweetalert2");
    return Swal;
}

/**
 * ✅ Open modal for Add / Edit / View
 */
async function open_internal_user_modal(mode = "add", userId = null) {
    try {
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
            isView,
        );
        $("#internalUserForm select").prop("disabled", isView);
        
        if (isView) {
            $("#internalUserModal .modal-footer").hide();
        } else {
            $("#internalUserModal .modal-footer").show();
        }

        if (isAdd) {
            $("#userUuid").val("");
            new bootstrap.Modal("#internalUserModal").show();
            return;
        }

        if (isEdit) {
            $("#email").prop("readonly", true);
        }

        // Show loading
        Swal.fire({
            title: getText("loading"),
            text: getText("fetchUserWait"),
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                applyTranslations(Swal.getHtmlContainer());
            },
        });

        // Make AJAX request
        $.ajax({
            url: `/internal/user_internal/user/data/${userId}`,
            type: "GET",
            dataType: "json",
            timeout: 30000, // 30 second timeout
            success: function (response) {
                try {
                    // Validate response
                    if (!response || !response.user) {
                        throw new Error("Invalid response format");
                    }

                    const user = response.user;
                    const currentUser = window.authUser || {};

                    // Extract role names
                    const userRoles = currentUser.roles || [];
                    const roleNames = userRoles.map((role) => role.name);
                    const isSuperAdmin = roleNames.includes("superadmin");

                    console.log("Current User:", currentUser);
                    console.log("Role Names:", roleNames);
                    console.log("Is Super Admin:", isSuperAdmin);

                    // Populate form fields
                    $("#userUuid").val(user.uuid || "");
                    $("#fullname").val(user.fullname || "");
                    $("#email").val(user.email || "");
                    $("#phone_number").val(user.phone_number || "");
                    $("#position").val(user.position || "");
                    $("#office").val(user.office || "");
                    $("#no_ic").val(user.no_ic || "");

                    // Handle no_ic readonly for non-superadmin in edit mode
                    if (isEdit) {
                        if (!isSuperAdmin) {
                            $("#no_ic")
                                .prop("readonly", true)
                                .addClass("bg-light")
                                .attr("title", "Only Super Admin can edit this field")
                                .css("cursor", "not-allowed");
                        } else {
                            $("#no_ic")
                                .prop("readonly", false)
                                .removeClass("bg-light")
                                .removeAttr("title")
                                .css("cursor", "default");
                        }
                    }

                    // Populate branch if field exists
                    if ($("#branch").length) {
                        $("#branch").val(user.branch || "");
                    }

                    // Populate role
                    const role = user.roles && user.roles.length 
                        ? user.roles[0].name 
                        : "";
                    $("#role").val(role);

                    // Close loading and show modal
                    Swal.close();
                    new bootstrap.Modal("#internalUserModal").show();
                    
                } catch (error) {
                    console.error("Error processing user data:", error);
                    Swal.close();
                    Swal.fire({
                        icon: "error",
                        title: getText("dataError"),
                        text: getText("dataErrorMsg"),
                        confirmButtonText: "OK"
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error Details:", {
                    xhr: xhr,
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                Swal.close();
                
                // Determine error message based on response
                let errorTitle = "Error";
                let errorMessage = "Unable to load user details. Please try again.";
                let errorIcon = "error";
                
                if (xhr.status === 0) {
                    errorTitle = "Connection Error";
                    errorMessage = "Unable to connect to the server. Please check your internet connection.";
                } else if (xhr.status === 404) {
                    errorTitle = "User Not Found";
                    errorMessage = "The requested user could not be found. They may have been deleted.";
                } else if (xhr.status === 403) {
                    errorTitle = "Access Denied";
                    errorMessage = "You do not have permission to view this user's details.";
                } else if (xhr.status === 401) {
                    errorTitle = "Session Expired";
                    errorMessage = "Your session has expired. Please refresh the page and try again.";
                } else if (xhr.status === 500) {
                    errorTitle = "Server Error";
                    errorMessage = "An internal server error occurred. Please contact support.";
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                // Show error with retry option
                Swal.fire({
                    icon: errorIcon,
                    title: errorTitle,
                    text: errorMessage,
                    confirmButtonText: "OK",
                    showCancelButton: true,
                    cancelButtonText: "Retry",
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        // Retry loading
                        open_internal_user_modal(mode, userId);
                    }
                });
            },
        });
        
    } catch (error) {
        console.error("Function error:", error);
        const Swal = await getSwal();
        Swal.fire({
            icon: "error",
            title: getText("unexpectedError"),
            text: getText("unexpectedErrorMsg"),
            confirmButtonText: "OK"
        });
    }
}

/**
 * ✅ Handle Add + Edit form submission (bind once!)
 */
function handle_internal_user_submit() {
    $(document).on("submit", "#internalUserForm", async function (e) {
        e.preventDefault();
        
        try {
            const Swal = await import("sweetalert2").then((m) => m.default);

            $(".form-control").removeClass("is-invalid");
            $(".invalid-feedback").text("");

            const formData = $(this).serialize();
            const uuid = $("#userUuid").val();
            const isEdit = Boolean(uuid);

            // Show loading
            Swal.fire({
                title: isEdit ? getText("updatingUser") : getText("savingUser"),
                text: getText("processWait"),
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    applyTranslations(Swal.getHtmlContainer());
                },
            });

            $.ajax({
                url: `/internal/user_internal/save`,
                method: "POST",
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                timeout: 30000, // 30 second timeout
                success: function (response) {
                    Swal.close();
                    
                    // Show success message
                    Swal.fire({
                        icon: "success",
                        title: isEdit ? getText("userUpdated") : getText("userAdded"),
                        text: response.message || (isEdit ? getText("userUpdatedMsg") : getText("userAddedMsg")),
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: true,
                    }).then(() => {
                        // Close modal and reload table
                        const modal = bootstrap.Modal.getInstance("#internalUserModal");
                        if (modal) {
                            modal.hide();
                        }
                        if (typeof internalListTable !== 'undefined' && internalListTable) {
                            internalListTable.ajax.reload();
                        } else {
                            // Fallback: reload page if table not found
                            location.reload();
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", {
                        xhr: xhr,
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    
                    Swal.close();

                    // Handle validation errors (422)
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessage = "Please fix the following errors:";
                        
                        // Show validation errors on form
                        Object.keys(errors).forEach((key) => {
                            const input = $(`#${key}`);
                            input.addClass("is-invalid");
                            $(`#error-${key}`).text(errors[key][0]);
                            errorMessage += `\n• ${errors[key][0]}`;
                        });

                        // Show validation error in Swal
                        Swal.fire({
                            icon: "warning",
                            title: getText("validationError"),
                            html: errorMessage.replace(/\n/g, '<br>'),
                            confirmButtonText: "OK"
                        });
                        return;
                    }

                    // Handle other errors
                    let errorTitle = "Error";
                    let errorMessage = "Something went wrong while saving the user.";
                    let errorIcon = "error";

                    if (xhr.status === 0) {
                        errorTitle = "Connection Error";
                        errorMessage = "Unable to connect to the server. Please check your internet connection and try again.";
                    } else if (xhr.status === 403) {
                        errorTitle = "Access Denied";
                        errorMessage = "You do not have permission to perform this action.";
                    } else if (xhr.status === 401) {
                        errorTitle = "Session Expired";
                        errorMessage = "Your session has expired. Please refresh the page and try again.";
                    } else if (xhr.status === 409) {
                        errorTitle = "Conflict";
                        errorMessage = "A user with this email or IC number already exists.";
                    } else if (xhr.status === 500) {
                        errorTitle = "Server Error";
                        errorMessage = "An internal server error occurred. Please contact support.";
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }

                    // Show error with retry option
                    Swal.fire({
                        icon: errorIcon,
                        title: errorTitle,
                        text: errorMessage,
                        confirmButtonText: "Try Again",
                        showCancelButton: true,
                        cancelButtonText: "Cancel",
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Retry submission
                            $(document).find("#internalUserForm").trigger("submit");
                        }
                    });
                }
            });
            
        } catch (error) {
            console.error("Function error:", error);
            const Swal = await import("sweetalert2").then((m) => m.default);
            Swal.fire({
                icon: "error",
                title: getText("unexpectedError"),
                text: getText("unexpectedErrorMsg"),
                confirmButtonText: "OK"
            });
        }
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
            title: getText("areYouSure"),
            text: getText("cannotUndo"),
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
                            "content",
                        ),
                    },
                    success: function () {
                        Swal.fire(
                            getText("deleted"),
                            getText("userDeletedMsg"),
                            "success"
                        ).then(() => {
                            internalListTable.ajax.reload();
                        });
                    },
                    error: function () {
                        Swal.fire(getText("error"), getText("deleteFailed"), "error");
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
            false,
        );
        $("#internalUserForm select").prop("disabled", false);
        $(".form-control").removeClass("is-invalid");
        $(".invalid-feedback").text("");
        $("#internalUserModal .modal-footer").show();
    });

    // ========== Filter Functionality ==========
    
    // Apply Filter Button
    $(document).on("click", "#applyFilterBtn", function (e) {
        e.preventDefault();
        
        const filters = {
            role: [].concat($("#filter_role").val() || []).join(","),
            branch: [].concat($("#filter_branch").val() || []).join(",")
        };

        // Update DataTable AJAX URL with filter parameters
        const url = new URL("/internal/user_internal/list/data", window.location.origin);
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                url.searchParams.append(key, filters[key]);
            }
        });

        internalListTable.ajax.url(url.toString()).load();
    });

    // Reset Filter Button
    $(document).on("click", "#resetFilterBtn", function (e) {
        e.preventDefault();
        
        // Reset all filter dropdowns
        $("#filter_role").val("").trigger("change");
        $("#filter_branch").val("").trigger("change");

        // Reset DataTable to default URL
        internalListTable.ajax.url("/internal/user_internal/list/data").load();
    });

    console.log("user id", userId);
    setTimeout(() => {
        window.Echo.channel("internal-user-added").listen(
            "InternalUserAdded",
            (e) => {
                console.log(e.message);
            },
        );

        window.Echo.private(`internal-user-edited.${userId}`).listen(
            ".InternalUserEdited",
            (e) => {
                console.log("✅ YOU were edited:", e.message);
                showToast(`${e.message} (by ${e.editor})`);
                notifyUser(e.message, e.editor);
            },
        );

        window.Echo.channel("internal-user-deleted").listen(
            "InternalUserDeleted",
            (e) => {
                console.log(e.message);
            },
        );
    }, 100);
}

// ✅ Initialize on page load
internal_user_list();
