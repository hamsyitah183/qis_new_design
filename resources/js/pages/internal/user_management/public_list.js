import $ from "jquery";
import Swal from "sweetalert2";
import { fetchVerificationCount, formatTime, applyTranslations } from "../../../app";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    loading: { en: 'Loading...', bm: 'Memuat...' },
    error: { en: 'Error', bm: 'Ralat' },
    errorMsg: { en: 'Error!', bm: 'Ralat!' },
    unableToLoad: { en: 'Unable to load user details', bm: 'Tidak dapat memuatkan butiran pengguna' },
    updatingUser: { en: 'Updating user...', bm: 'Mengemas kini pengguna...' },
    savingUser: { en: 'Saving user...', bm: 'Menyimpan pengguna...' },
    userUpdated: { en: 'User Updated!', bm: 'Pengguna Dikemas kini!' },
    userAdded: { en: 'User Added!', bm: 'Pengguna Ditambah!' },
    failed: { en: 'Failed!', bm: 'Gagal!' },
    saveFailed: { en: 'Something went wrong while saving the user.', bm: 'Sesuatu yang tidak kena berlaku semasa menyimpan pengguna.' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    cannotUndo: { en: 'This action cannot be undone!', bm: 'Tindakan ini tidak dapat dipulihkan!' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    deleteSuccess: { en: 'User deleted successfully.', bm: 'Pengguna berjaya dipadam.' },
    deleteFailed: { en: 'Failed to delete user.', bm: 'Gagal memadam pengguna.' },
    verifyError: { en: 'Failed to load verification info.', bm: 'Gagal memuatkan maklumat pengesahan.' },
    approved: { en: 'Approved!', bm: 'Diluluskan!' },
    verifySuccess: { en: 'User has been successfully verified.', bm: 'Pengguna telah berjaya disahkan.' },
    unverifyUser: { en: 'Unverify User', bm: 'Tarik Balik Pengesahan Pengguna' },
    unverifyReasonPrompt: { en: 'Please provide a reason for unverifying this user:', bm: 'Sila nyatakan sebab menarik balik pengesahan pengguna ini:' },
    enterReason: { en: 'Enter reason here...', bm: 'Masukkan sebab di sini...' },
    submit: { en: 'Submit', bm: 'Hantar' },
    reasonRequired: { en: 'Reason is required!', bm: 'Sebab diperlukan!' },
    userUnverified: { en: 'User Unverified', bm: 'Pengesahan Pengguna Ditarik Balik' },
    unverifySuccess: { en: 'The user has been unverified successfully.', bm: 'Pengesahan pengguna telah berjaya ditarik balik.' },
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}

import { autoInitFilterSelect2 } from "../../../utils/select2Utils";

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
                    visible: false,
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

        // Init Select2 on all static filter selects (those with class 'select2')
        autoInitFilterSelect2();
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
            // Load states for new user
            fetchStatesModal().then((states) => {
                const lang = localStorage.getItem("qis_lang") || "en";
                const selectStateText = lang === "bm" ? "Pilih Negeri" : "Select State";
                $(".state-modal").empty().append(`<option value="" data-en="Select State" data-bm="Pilih Negeri">${selectStateText}</option>`);
                states.forEach(state => {
                    $(".state-modal").append(`<option value="${state.id}">${state.name}</option>`);
                });
            });
            new bootstrap.Modal(
                document.getElementById("publicUserModal")
            ).show();
            return;
        }

        if (isEdit) {
            $("#email").prop("readonly", isEdit);
        }

        Swal.fire({
            title: getText("loading"),
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                applyTranslations(Swal.getHtmlContainer());
            },
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

                // Load location dropdowns in sequence
                fetchStatesModal().then((states) => {
                    const lang = localStorage.getItem("qis_lang") || "en";
                    const selectStateText = lang === "bm" ? "Pilih Negeri" : "Select State";
                    $(".state-modal").empty().append(`<option value="" data-en="Select State" data-bm="Pilih Negeri">${selectStateText}</option>`);
                    states.forEach(state => {
                        const isSelected = user.state && (user.state == state.id || user.state == state.name);
                        $(".state-modal").append(`<option value="${state.id}" ${isSelected ? 'selected' : ''}>${state.name}</option>`);
                    });

                    // Get selected state ID
                    const selectedStateId = $(".state-modal").val();
                    if (selectedStateId) {
                        fetchDistrictsModal(selectedStateId, user.district, (resolvedDistrictId) => {
                            if (resolvedDistrictId) {
                                fetchPostcodesModal(resolvedDistrictId, user.postcode);
                            }
                        });
                    }
                });

                Swal.close();
                new bootstrap.Modal(
                    document.getElementById("publicUserModal")
                ).show();
            },
            error: function () {
                Swal.fire(getText("error"), getText("unableToLoad"), "error");
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
                    title: isEdit ? getText("updatingUser") : getText("savingUser"),
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        applyTranslations(Swal.getHtmlContainer());
                    },
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
                            title: isEdit ? getText("userUpdated") : getText("userAdded"),
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
                                title: getText("failed"),
                                text: getText("saveFailed"),
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
                        url: `/internal/user_public/delete/${userId}`,
                        type: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                        },
                        success: function () {
                            Swal.fire(
                                getText("deleted"),
                                getText("deleteSuccess"),
                                "success"
                            ).then(() => { $("#publicUsersTable").DataTable().ajax.reload(); });
                        },
                        error: function () {
                            Swal.fire(
                                getText("errorMsg"),
                                getText("deleteFailed"),
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

    // ========== Filter Functionality ==========
    
    // Apply Filter Button
    $(document).on("click", "#applyFilterBtn", function (e) {
        e.preventDefault();
        
        const filters = {
            account_type: [].concat($("#filterAccountType").val() || []).join(","),
            email_verification: [].concat($("#filterEmailVerification").val() || []).join(","),
            account_verification: [].concat($("#filterAccountVerification").val() || []).join(","),
            sort_by: $("#filterTime").val() // keeping sort as single value string
        };

        // Update DataTable AJAX URL with filter parameters
        const url = new URL("/internal/user_public/list/data", window.location.origin);
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                url.searchParams.append(key, filters[key]);
            }
        });

        publicUsersTable.ajax.url(url.toString()).load();
    });

    // Reset Filter Button
    $(document).on("click", "#resetFilterBtn", function (e) {
        e.preventDefault();
        
        // Reset all filter dropdowns
        $("#filterAccountType").val(null).trigger("change");
        $("#filterEmailVerification").val(null).trigger("change");
        $("#filterAccountVerification").val(null).trigger("change");
        $("#filterTime").val(null).trigger("change");

        // Reset DataTable to default URL
        publicUsersTable.ajax.url("/internal/user_public/list/data").load();
    });

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
                    title: getText("error"),
                    text: getText("verifyError"),
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
        title: text === "Loading..." ? getText("loading") : text,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            applyTranslations(Swal.getHtmlContainer());
        },
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

    if (response.doa_verified == 1) {
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
                title: getText("approved"),
                text: getText("verifySuccess"),
                timer: 2000,
                showConfirmButton: false,
            }).then(() => {
                fetchVerificationCount()
                
                // Close modal after success
                const modal = bootstrap.Modal.getInstance(
                    document.getElementById("verificationModal")
                );
                if (modal) modal.hide();

                if (publicUsersTable) publicUsersTable.ajax.reload();
            });
        },
        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: getText("error"),
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
            title: getText("unverifyUser"),
            html: `
            <p class="mb-2">${getText("unverifyReasonPrompt")}</p>
            <textarea id="unverifyReason" style="width:90%; font-size:12px; line-height: 1.5;" 
            class="swal2-textarea" placeholder="${getText("enterReason")}"></textarea>
        `,
            showCancelButton: true,
            confirmButtonText: getText("submit"),
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
                    Swal.showValidationMessage(getText("reasonRequired"));
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
                            title: getText("userUnverified"),
                            text: getText("unverifySuccess"),
                            timer: 2000,
                            showConfirmButton: false,
                        }).then(() => {
                            if (publicUsersTable) publicUsersTable.ajax.reload();
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: "error",
                            title: getText("error"),
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

// ========== Location Dropdown Helpers ==========

function fetchStatesModal() {
    return $.ajax({
        url: "/get_states",
        type: "GET"
    });
}

function fetchDistrictsModal(stateId, selectedDistrict = null, callback = null) {
    $(".district-modal").html('<option value="">Loading...</option>');

    Swal.fire({
        title: getText("loading"),
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            applyTranslations(Swal.getHtmlContainer());
        },
    });

    $.ajax({
        url: `/get_districts/${stateId}`,
        type: "GET",
        success: function (data) {
            const lang = localStorage.getItem("qis_lang") || "en";
            const selectDistText = lang === "bm" ? "Pilih Daerah" : "Select District";
            $(".district-modal").empty().append(`<option value="" data-en="Select District" data-bm="Pilih Daerah">${selectDistText}</option>`);
            let matchedId = null;

            data.forEach(district => {
                const isSelected = selectedDistrict && (selectedDistrict == district.id || selectedDistrict == district.name);
                if (isSelected) {
                    matchedId = district.id;
                }
                $(".district-modal").append(`<option value="${district.id}" ${isSelected ? 'selected' : ''}>${district.name}</option>`);
            });

            if (callback) callback(matchedId);

            Swal.close();
        },
        error: function (err) {
            console.error("Error fetching districts", err);
            $(".district-modal").html('<option value="">Error loading districts</option>');
        }
    });
}

function fetchPostcodesModal(districtId, selectedPostcode = null) {
    $(".postcode-modal").html('<option value="">Loading...</option>');

    if (!districtId) {
        const lang = localStorage.getItem("qis_lang") || "en";
        const selectPostcodeText = lang === "bm" ? "Pilih Poskod" : "Select Postcode";
        $(".postcode-modal").html(`<option value="" data-en="Select Postcode" data-bm="Pilih Poskod">${selectPostcodeText}</option>`);
        return;
    }

    Swal.fire({
        title: getText("loading"),
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            applyTranslations(Swal.getHtmlContainer());
        },
    });

    $.ajax({
        url: `/get_postcodes/${districtId}`,
        type: "GET",
        success: function (data) {
            const lang = localStorage.getItem("qis_lang") || "en";
            const selectPostcodeText = lang === "bm" ? "Pilih Poskod" : "Select Postcode";
            $(".postcode-modal").empty().append(`<option value="" data-en="Select Postcode" data-bm="Pilih Poskod">${selectPostcodeText}</option>`);
            data.forEach(postcode => {
                const isSelected = selectedPostcode && (selectedPostcode == postcode.id || selectedPostcode == postcode.value) ? 'selected' : '';
                $(".postcode-modal").append(`<option value="${postcode.value}" ${isSelected}>${postcode.value}</option>`);
            });

            Swal.close();
        },
        error: function (err) {
            console.error("Error fetching postcodes", err);
            $(".postcode-modal").html('<option value="">Error loading postcodes</option>');
        }
    });
}

// Handle State change in modal
$(document).on("change", ".state-modal", function () {
    const stateId = $(this).val();
    $(".district-modal").html('<option value="">Select District</option>');
    $(".postcode-modal").html('<option value="">Select Postcode</option>');

    if (stateId) {
        fetchDistrictsModal(stateId);
    }
});

// Handle District change in modal
$(document).on("change", ".district-modal", function () {
    const districtId = $(this).val();
    $(".postcode-modal").html('<option value="">Select Postcode</option>');

    if (districtId) {
        fetchPostcodesModal(districtId);
    }
});
