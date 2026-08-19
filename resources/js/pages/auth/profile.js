import $ from "jquery";
import Swal from "sweetalert2";
import { notifyUser } from "../../app";
import select2 from "select2";
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

let user = null;

export async function loadProfile() {
    $(".uploadAgain").hide();
    console.log("call the load profile function");
    const allInputs = document.querySelectorAll(
        "input, select, button, textarea",
    );
    allInputs.forEach((el) => (el.disabled = true));

    Swal.fire({
        title: "Loading Profile...",
        text: "Please wait a moment.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const response = await $.ajax({
            url: `/data`,
            type: "GET",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        user = response.user; // important: populate global user
        user["type"] = response.type;
        fillTheData(user, response.type);

        if (user.type === "public") {
            publicUserAddUpdate(user);
        }

        initAddressDropdowns(user);

        Swal.close();
    } catch (error) {
        console.error("Error loading profile:", error);
        Swal.fire({
            icon: "error",
            title: "Failed to Load",
            text: "Failed to load profile data. Please try again.",
        });
    } finally {
        allInputs.forEach((el) => (el.disabled = false));
    }
}

function fillTheData(user, type) {
    console.log("user id", user);
    const fullAddress =
        user.office ??
        user.address_1 + (user.address_2 ? `, ${user.address_2}` : "");
    let badgeVerification = "";
    $(".type").val(type);
    $(".uuid").val(user.uuid);
    $("#uploadBtn").attr("data-id", user.uuid);
    $(".fullname").val(user.fullname).text(user.fullname);
    $(".address").val(fullAddress).text(fullAddress);
    $(".phone_number")
        .val(user.phone ?? user.phone_number)
        .text(user.phone ?? user.phone_number);
    $(".email").val(user.email).text(user.email);
    $(".ic")
        .val(user.no_ic ?? user.ic)
        .text(user.ic ?? user.no_ic)
        .prop("readonly", true);
    $(".position").val(user.position).text(user.position);

    // Set branch for internal users (display-only on profile)
    if (type === "internal") {
        $(".branch").text(user.branch || "");
    }
    $(".address_1").val(user.address_1);
    $(".address_2").val(user.address_2);
    $(".office_number").val(user.office_number);
    $(".state").val(user.state);
    // Fetch districts if state is selected
    // if (user.state) {
    //     fetchDistricts(user.state, user.district, (resolvedDistrictId) => {
    //         if (resolvedDistrictId) {
    //             fetchPostcodes(resolvedDistrictId, user.postcode);
    //         }
    //     });
    // }

    // $(".district").val(user.district);
    //$(".state").val(user.state);
    $("#account_type").val(user.account_type);

    if (type === "internal") {
        $(".email").prop("readonly", true);
        $(".ic").prop("readonly", true);
    }

    if (type === "public") {
        const approved = user.approved ?? {}; // ✅ Fallback to empty object
        const attachments = user.attachments ?? []; // ✅ New: multiple documents

        // 🔹 Handle DOA verification badge
        let badgeVerification = `<span class="badge bg-dark-transparent ms-1" title="Not verified by DOA">
                Not Verified by DOA
            </span>`;
        if (approved.doa_verified) {
            badgeVerification = `
            <span class="badge bg-success-transparent ms-1" title="Verified by DOA">
                Verified by DOA
            </span>`;
        } else {
            badgeVerification = `
            <span class="badge bg-dark-transparent ms-1" title="Not verified by DOA">
                Not Verified by DOA
            </span>`;
        }

        // 🔹 Handle attachments (now a list, one per document type)
        const container = $("#imgLink");
        container.empty();

        if (attachments.length > 0) {
            attachments.forEach((attachment) => {
                const fileUrl = attachment.file_path
                    ? `/${attachment.file_path}`.replace("//", "/")
                    : (attachment.file_url ?? null);
                const ext = (attachment.original_file_name || fileUrl || "")
                    .split(".")
                    .pop()
                    .toLowerCase();
                const iconClass =
                    ext === "pdf" ? "ti ti-file-type-pdf" : "ti ti-photo";
                const validUntil = attachment.valid_until
                    ? `<div class="text-muted fs-11">Valid until: ${formatTime(
                          attachment.valid_until,
                      )}</div>`
                    : "";

                container.append(`
                    <div class="attachment-list-item d-flex align-items-center gap-2 border rounded-3 p-2 mb-2">
                        <i class="${iconClass} fs-20 text-primary flex-shrink-0"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold fs-13 text-truncate">
                                ${attachment.document_type ?? "Document"}
                            </div>
                            <div class="text-muted fs-11 text-truncate">
                                ${attachment.original_file_name ?? ""}
                            </div>
                            ${validUntil}
                        </div>
                        ${
                            fileUrl
                                ? `<a href="${fileUrl}" target="_blank" rel="noopener" class="btn btn-sm btn-icon btn-primary-light flex-shrink-0" title="View">
                                    <i class="ti ti-eye"></i>
                                   </a>`
                                : ""
                        }
                    </div>
                `);
            });

            $(".hasImage").css("display", "block");
            $(".hasNoImage").css("display", "none");

            if (approved.status?.toLowerCase().includes("rejected")) {
                $(".rejectedBtn").html(`
                    <div class = "btn btn-sm btn-warning uploadAgain">
                        Upload Again
                    </div>
                `);
            }
        } else {
            container.append("<p>No attachment uploaded yet.</p>");
            $(".hasImage").css("display", "none");
            $(".hasNoImage").css("display", "block");
        }

        // 🔹 Dates and approver info
        $(".submittedVerification").text(
            approved.updated_at ? formatTime(approved.updated_at) : "N/A",
        );

        $(".approvedBy").text(approved.approver?.fullname ?? "N/A");

        $(".approvedDate").text(
            approved.doa_approved_time
                ? `on (${formatTime(approved.doa_approved_time)})`
                : "",
        );

        // 🔹 Status display
        let statusText = "";
        let reason = "";

        if (approved.status?.toLowerCase().includes("waiting")) {
            statusText = `
            <div class="alert alert-warning" role="alert">
                <i class="ti ti-alert-circle me-2 fs-16"></i>
                ${approved.status}
            </div>`;
        } else if (approved.status?.toLowerCase().includes("approved")) {
            statusText = `
            <div class="alert alert-success" role="alert">
                <i class="ti ti-rosette-discount-check me-2 fs-16"></i>
                ${approved.status}
            </div>`;
        } else if (approved.status?.toLowerCase().includes("rejected")) {
            statusText = `
            <div class="alert alert-danger" role="alert">
                <i class="ti ti-rosette-discount-x me-2 fs-16"></i>
                ${approved.status}
            </div>`;

            reason = `
                <div class = "me-2 mt-2 border rounded-3 p-3">
                <span class = "fw-bold">Reason: </span>
                <span class = "text-muted">${approved.reason}</span>
               </div>`;
        } else {
            statusText = `
            <div class="alert alert-secondary" role="alert">
                No verification status available.
            </div>`;
        }

        $(".status").html(statusText);
        $(".reason").html(reason);
    }

    $(".mainFullName").html(badgeVerification);
}

async function initAddressDropdowns(user) {
    console.log("initadd", user);

    Swal.fire({
        title: "Loading address...",
        text: "Please wait",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        // ✅ Await so errors are catchable
        await loadStates(user);
    } catch (error) {
        console.error("Failed to load address dropdowns:", error);

        Swal.fire({
            icon: "error",
            title: "Failed to load address",
            text: "Please try again later",
        });
        return;
    }

    // ✅ Close only after everything succeeds
    Swal.close();
}

async function loadStates(user) {
    const res = await fetch("/get_states");
    const states = await res.json();

    const stateSelect = $(".state");
    stateSelect.empty().append('<option value="">Select State</option>');

    let selectedStateId = null;

    states.forEach((s) => {
        stateSelect.append(`<option value="${s.id}">${s.name}</option>`);

        if (user.state === s.name) {
            selectedStateId = s.id;
        }
    });

    // ✅ FORCE value selection
    if (selectedStateId) {
        stateSelect.val(selectedStateId).trigger("change");
        loadDistricts(selectedStateId, user);
    }
}

async function loadDistricts(stateId, user) {
    if (!stateId) return;

    const res = await fetch(`/get_districts/${stateId}`);
    const districts = await res.json();

    const districtSelect = $(".district");
    districtSelect.empty().append('<option value="">Select District</option>');

    let selectedDistrictId = null;

    districts.forEach((d) => {
        districtSelect.append(`<option value="${d.id}">${d.name}</option>`);

        if (user.district === d.name) {
            selectedDistrictId = d.id;
        }
    });

    if (selectedDistrictId) {
        districtSelect.val(selectedDistrictId).trigger("change");
        await loadPostcodes(selectedDistrictId, user);
    }
}

async function loadPostcodes(districtId, user) {
    if (!districtId) return;

    const res = await fetch(`/get_postcodes/${districtId}`);
    const postcodes = await res.json();

    const postcodeSelect = $(".postcode");
    postcodeSelect.empty().append('<option value="">Select Postcode</option>');

    postcodes.forEach((p) => {
        postcodeSelect.append(`<option value="${p.value}">${p.value}</option>`);
    });

    // ✅ Force selection
    if (user.postcode) {
        postcodeSelect.val(user.postcode).trigger("change");
    }
}

$(document).on("click", ".uploadAgain", function () {
    // Hide the container showing existing image
    $(".hasImage").css("display", "none");

    // Show the container for uploading new image
    $(".hasNoImage").css("display", "block");

    // Optional: clear the previous Dropzone files if needed
    if (typeof verificationDropzone !== "undefined") {
        verificationDropzone.removeAllFiles(true);
    }
});

function editProfile() {
    $("#edit-profile-tab-pane")
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();

            if (!user) {
                Swal.fire({
                    icon: "info",
                    title: "Please wait...",
                    text: "Profile is still loading.",
                });
                return;
            }

            let role = window.authUser?.roles[0]?.name || "";

            const $form = $(this);
            const formData = new FormData(this);

            formData.append("role", role);

            const allInputs = $form.find("input, select, button, textarea");
            allInputs.prop("disabled", true);
            $form.find(".is-invalid").removeClass("is-invalid");
            $form.find(".invalid-feedback").remove();

            const newEmail = $form.find(".email").val().trim();

            const submitForm = () => {
                Swal.fire({
                    title: "Updating...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: $form.attr("action") || "/data",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                        Accept: "application/json",
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1200,
                        });
                        loadProfile();
                    },
                    error: function (xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            for (const key in errors) {
                                const $input = $form.find(`[name="${key}"]`);
                                if ($input.length) {
                                    $input.addClass("is-invalid");
                                    $input.after(
                                        `<div class="invalid-feedback">${errors[key][0]}</div>`,
                                    );
                                }
                            }
                            Swal.fire({
                                icon: "error",
                                title: "Validation Failed",
                                text: "Please check your input fields.",
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    xhr.responseJSON?.message ||
                                    "Something went wrong. Please try again.",
                            });
                        }
                    },
                    complete: function () {
                        allInputs.prop("disabled", false);
                    },
                });
            };

            // Email changed?
            if (user.email.trim() !== newEmail && user.type == "public") {
                Swal.fire({
                    icon: "info",
                    title: "Change your email?",
                    text: "Changing your email may cause your verification to be retracted and require re-approval.",
                    showCancelButton: true,
                    confirmButtonText: "Continue",
                    cancelButtonText: "Cancel",
                }).then((result) => {
                    if (result.isConfirmed) submitForm();
                    else allInputs.prop("disabled", false);
                });
            } else {
                submitForm();
            }
        });
}

function formatTime(timestamp) {
    const utcDate = new Date(timestamp);

    // Malaysia Time (UTC+8)
    const malaysiaOffset = 8 * 60; // minutes
    const localTime = new Date(utcDate.getTime() + malaysiaOffset * 60 * 1000);

    const options = {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    };

    const formatted = new Intl.DateTimeFormat("en-GB", options).format(
        localTime,
    );

    return formatted;
}

function changePassword() {
    $("#edit-password-tab-pane")
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();

            if (!user) {
                Swal.fire({
                    icon: "info",
                    title: "Please wait...",
                    text: "Profile is still loading.",
                });
                return;
            }

            const $form = $(this);
            const formData = new FormData(this);

            const allInputs = $form.find("input, select, button, textarea");
            allInputs.prop("disabled", true);
            $form.find(".is-invalid").removeClass("is-invalid");
            $form.find(".invalid-feedback").remove();

            // $(".type").val(type);
            // $(".uuid").val(user.uuid);

            const submitForm = () => {
                Swal.fire({
                    title: "Updating...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: $form.attr("action") || "/password",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                        Accept: "application/json",
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1200,
                        });
                    },
                    error: function (xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            for (const key in errors) {
                                const $input = $form.find(`[name="${key}"]`);
                                if ($input.length) {
                                    $input.addClass("is-invalid");
                                    $input.after(
                                        `<div class="invalid-feedback">${errors[key][0]}</div>`,
                                    );
                                }
                            }
                            Swal.fire({
                                icon: "error",
                                title: "Validation Failed",
                                text: "Please check your input fields.",
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    xhr.responseJSON?.message ||
                                    "Something went wrong. Please try again.",
                            });
                        }
                    },
                    complete: function () {
                        allInputs.prop("disabled", false);
                    },
                });
            };

            submitForm();
        });
}

export async function publicUserAddUpdate(user) {
    console.log("call public user echo function");
    console.log("public user id", user);
    setTimeout(() => {
        if (!window.Echo) {
            console.error("Echo not found");
            return;
        }

        window.Echo.private(`public-user.${user.uuid}`).listen(
            ".PublicUserEvent",
            (e) => {
                console.log("✅ Public user event:", e.message);
                notifyUser(e.message);
            },
        );
    }, 100);
}

$(document).ready(function () {
    // load profile on page load
    loadProfile();
    editProfile();
    changePassword();

    // State change event listener
    $(document).on("change", ".state", function () {
        const stateId = $(this).val();
        fetchDistricts(stateId);
    });

    $(document).on("change", ".district", function () {
        const districtId = $(this).val();
        fetchPostcodes(districtId);
    });
    // publicUserAddUpdate(user);
});

function fetchDistricts(stateId, selectedDistrict = null, callback = null) {
    const $district = $(".district");
    $district.html('<option value="">Loading...</option>');

    if (!stateId) {
        $district.html('<option value="">Select District</option>');
        if (callback) callback(null);
        return;
    }

    $.ajax({
        url: `/get_districts/${stateId}`,
        type: "GET",
        success: function (data) {
            $district
                .empty()
                .append('<option value="">Select District</option>');
            let matchedId = null;
            data.forEach((district) => {
                const isSelected =
                    selectedDistrict &&
                    (selectedDistrict == district.id ||
                        selectedDistrict == district.name);
                if (isSelected) {
                    matchedId = district.id;
                }
                const selectedAttr = isSelected ? "selected" : "";
                $district.append(
                    `<option value="${district.id}" ${selectedAttr}>${district.name}</option>`,
                );
            });

            if (callback) callback(matchedId);
        },
        error: function (err) {
            console.error("Error fetching districts", err);
            $district.html('<option value="">Error loading districts</option>');
            if (callback) callback(null);
        },
    });
}

function fetchPostcodes(districtId, selectedPostcode = null) {
    const $postcode = $(".postcode");
    $postcode.html('<option value="">Loading...</option>');

    if (!districtId) {
        $postcode.html('<option value="">Select Postcode</option>');
        return;
    }

    $.ajax({
        url: `/get_postcodes/${districtId}`,
        type: "GET",
        success: function (data) {
            $postcode
                .empty()
                .append('<option value="">Select Postcode</option>');
            data.forEach((postcode) => {
                const isSelected =
                    selectedPostcode &&
                    (selectedPostcode == postcode.id ||
                        selectedPostcode == postcode.value)
                        ? "selected"
                        : "";
                $postcode.append(
                    `<option value="${postcode.value}" ${isSelected}>${postcode.value}</option>`,
                );
            });
        },
        error: function (err) {
            console.error("Error fetching postcodes", err);
            $postcode.html('<option value="">Error loading postcodes</option>');
        },
    });
}

// ============================================================================
// Document List Management - Expandable Sections & Delete Functionality
// ============================================================================

$(document).on("click", ".doc-row-toggle", function () {
    const docId = $(this).data("doc-id");
    const isExpanded = $(this).attr("aria-expanded") === "true";
    const $panel = $(`.doc-panel[data-doc-id="${docId}"]`);

    if (isExpanded) {
        // Collapse
        $(this).attr("aria-expanded", "false");
        $panel.addClass("d-none");
    } else {
        // Expand
        $(this).attr("aria-expanded", "true");
        $panel.removeClass("d-none");
    }
});

// Delete attachment
$(document).on("click", ".delete-attachment", function (e) {
    e.preventDefault();
    const attachmentId = $(this).data("attachment-id");
    const $item = $(this).closest(".attachment-list-item");

    Swal.fire({
        icon: "warning",
        title: "Delete Document?",
        text: "This action cannot be undone.",
        showCancelButton: true,
        confirmButtonText: "Delete",
        confirmButtonColor: "#dc3545",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/attachments/${attachmentId}`,
                type: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    Accept: "application/json",
                },
                success: function (response) {
                    $item.fadeOut(300, function () {
                        $(this).remove();
                        Swal.fire({
                            icon: "success",
                            title: "Deleted",
                            text: "Document has been removed.",
                            showConfirmButton: false,
                            timer: 1200,
                        });
                        // Reload profile to refresh attachments list
                        setTimeout(() => loadProfile(), 1500);
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: xhr.responseJSON?.message || "Failed to delete document.",
                    });
                },
            });
        }
    });
});
