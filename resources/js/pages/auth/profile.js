import $ from "jquery";
import Swal from "sweetalert2";
import { notifyUser } from "../../app";
import select2 from "select2";

import { userDocument } from "./listDocument.js";
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

let user = null;

// ---- Separate fetch function ----
export async function fetchUserData() {
    const response = await $.ajax({
        url: `/data`,
        type: "GET",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    return response; // { user: {...}, type: 'public'|'internal' }
}

// ---- Helper: escape HTML to prevent XSS ----
function escapeHtml(unsafe) {
    if (!unsafe) return "";
    return unsafe.replace(/[&<>"]/g, function (m) {
        if (m === "&") return "&amp;";
        if (m === "<") return "&lt;";
        if (m === ">") return "&gt;";
        if (m === '"') return "&quot;";
        return m;
    });
}

// ---- PIC edit row management ----
let editPicRowCount = 0;

function addEditPICRow(name = "", position = "", phone = "") {
    const container = document.getElementById("editPicContainer");
    const emptyEl = document.getElementById("editPicEmpty");
    if (!container) return;

    const row = document.createElement("div");
    row.className = "row g-3 align-items-end pic-row mb-2";
    row.dataset.index = editPicRowCount++;
    row.innerHTML = `
        <div class="col-4">
            <label class="form-label" data-en="Name" data-bm="Nama">Name</label>
            <input type="text" class="form-control form-control-sm" name="pic_name[]" value="${escapeHtml(name)}" placeholder="e.g. Ahmad Bin Ali">
        </div>
        <div class="col-3">
            <label class="form-label" data-en="Position" data-bm="Jawatan">Position</label>
            <input type="text" class="form-control form-control-sm" name="pic_position[]" value="${escapeHtml(position)}" placeholder="Manager">
        </div>
        <div class="col-3">
            <label class="form-label" data-en="Phone" data-bm="Telefon">Phone</label>
            <input type="text" class="form-control form-control-sm" name="pic_phone[]" value="${escapeHtml(phone)}" placeholder="0123456789">
        </div>
        <div class="col-2 text-end">
            <button type="button" class="btn btn-sm btn-danger-light remove-edit-pic-row" data-en="Remove" data-bm="Buang">
                <i class="ti ti-x"></i>
            </button>
        </div>
    `;
    container.insertBefore(row, emptyEl);
    emptyEl.style.display = "none";

    if (typeof applyTranslations === "function") {
        applyTranslations(row);
    }
}

// ---- Updated loadProfile ----
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
        const response = await fetchUserData();

        user = response.user;
        user["type"] = response.type;
        fillTheData(user, response.type);

        if (user.type === "public") {
            publicUserAddUpdate(user);
            await userDocument(user);
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
    $("#account_type").val(user.account_type);

    if (type === "internal") {
        $(".email").prop("readonly", true);
        $(".ic").prop("readonly", true);
    }

    $(".mainFullName").html(badgeVerification);

    // ─── Persons In Charge (display) ─────────────────────────
    // ─── Persons In Charge (display) ─────────────────────────
    const picListBody = document.getElementById("profilePicListBody");
    if (picListBody) {
        picListBody.innerHTML = "";
        if (
            user.account_type === "company" &&
            user.person_in_charge &&
            user.person_in_charge.length
        ) {
            user.person_in_charge.forEach((pic) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                <td>${escapeHtml(pic.name)}</td>
                <td>${escapeHtml(pic.position)}</td>
                <td>${escapeHtml(pic.phone)}</td>
            `;
                picListBody.appendChild(tr);
            });
        } else {
            const tr = document.createElement("tr");
            tr.innerHTML = `
            <td colspan="3" class="text-center text-muted" data-en="No persons in charge" data-bm="Tiada orang bertanggungjawab">
                No persons in charge
            </td>
        `;
            picListBody.appendChild(tr);
        }
    }

    // ─── Persons In Charge (edit rows) ──────────────────────
    const editContainer = document.getElementById("editPicContainer");
    const editEmpty = document.getElementById("editPicEmpty");
    if (editContainer) {
        // Clear existing rows (keep empty placeholder)
        editContainer.querySelectorAll(".pic-row").forEach((el) => el.remove());
        editPicRowCount = 0;

        if (
            user.account_type === "company" &&
            user.person_in_charge &&
            user.person_in_charge.length
        ) {
            user.person_in_charge.forEach((pic) => {
                addEditPICRow(pic.name, pic.position, pic.phone);
            });
            if (editEmpty) editEmpty.style.display = "none";
        } else {
            if (editEmpty) editEmpty.style.display = "block";
        }
    }
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

    if (user.postcode) {
        postcodeSelect.val(user.postcode).trigger("change");
    }
}

$(document).on("click", ".uploadAgain", function () {
    $(".hasImage").css("display", "none");
    $(".hasNoImage").css("display", "block");

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
    const malaysiaOffset = 8 * 60;
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

// ---- Document ready ----
$(document).ready(function () {
    loadProfile();
    editProfile();
    changePassword();

    // ─── Edit PIC: Add button ──────────────────────────────
    $(document).on("click", "#editAddPICBtn", function (e) {
        e.preventDefault();
        addEditPICRow();
    });

    // ─── Edit PIC: Remove button ───────────────────────────
    $(document).on("click", ".remove-edit-pic-row", function (e) {
        e.preventDefault();
        const row = $(this).closest(".pic-row");
        row.remove();
        if ($("#editPicContainer .pic-row").length === 0) {
            $("#editPicEmpty").show();
        }
    });

    // State change event listeners
    $(document).on("change", ".state", function () {
        const stateId = $(this).val();
        fetchDistricts(stateId);
    });

    $(document).on("change", ".district", function () {
        const districtId = $(this).val();
        fetchPostcodes(districtId);
    });
});

// ---- Address dropdown helpers ----
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
        $(this).attr("aria-expanded", "false");
        $panel.addClass("d-none");
    } else {
        $(this).attr("aria-expanded", "true");
        $panel.removeClass("d-none");
    }
});

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
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
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
                        setTimeout(() => loadProfile(), 1500);
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            xhr.responseJSON?.message ||
                            "Failed to delete document.",
                    });
                },
            });
        }
    });
});
