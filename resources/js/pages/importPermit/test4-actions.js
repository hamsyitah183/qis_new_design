/**
 * importPermitActions.js - Bilingual workflow actions for Import Permit applications
 * All Swal alerts now display plain translated text based on the user's language preference.
 */

import $ from "jquery";
import Quill from "quill";
import "quill/dist/quill.snow.css";
import Swal from "sweetalert2";
import select2 from "select2";
import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import { applyTranslations } from "../../app"; // adjust path as needed
import { APPLICATION, PERMITS } from "./test1";

window.Tagify = Tagify;

let qacQuill = null;
let qacCategoriesLoaded = false;
let qacMeasurementsLoaded = false;
// ---------------------------------------------------------------
// Helper: get current language
// ---------------------------------------------------------------

function getLang() {
    try {
        return localStorage.getItem("qis_lang") || "en";
    } catch {
        return "en";
    }
}

// ---------------------------------------------------------------
// Translation map – all user‑facing strings
// ---------------------------------------------------------------

const t = {
    acceptApplication: {
        en: {
            title: "Accept Application?",
            text: "Are you sure you want to accept this application?",
            confirm: "Yes, accept it!",
            cancel: "Cancel",
        },
        bm: {
            title: "Terima Permohonan?",
            text: "Adakah anda pasti mahu menerima permohonan ini?",
            confirm: "Ya, terima!",
            cancel: "Batal",
        },
    },
    applicationVerified: {
        en: {
            title: "Application Verified!",
            text: "The application has been successfully verified.",
        },
        bm: {
            title: "Permohonan Disahkan!",
            text: "Permohonan telah berjaya disahkan.",
        },
    },
    rejectApplication: {
        en: {
            title: "Reject Application",
            placeholder: "Enter rejection reason...",
            confirm: "Confirm",
            cancel: "Cancel",
        },
        bm: {
            title: "Tolak Permohonan",
            placeholder: "Masukkan sebab penolakan...",
            confirm: "Sahkan",
            cancel: "Batal",
        },
    },
    applicationRejected: {
        en: {
            title: "Application Rejected!",
            text: "The application has been rejected.",
        },
        bm: { title: "Permohonan Ditolak!", text: "Permohonan telah ditolak." },
    },
    verifyApplication: {
        en: {
            title: "Verify Application?",
            text: "Are you sure you want to verify this application?",
            confirm: "Yes, verify it!",
            cancel: "Cancel",
        },
        bm: {
            title: "Sahkan Permohonan?",
            text: "Adakah anda pasti mahu mengesahkan permohonan ini?",
            confirm: "Ya, sahkan!",
            cancel: "Batal",
        },
    },
    notVerifyApplication: {
        en: {
            title: "Mark as Not Verified?",
            text: "Are you sure you want to mark this application as not verified?",
            confirm: "Yes, proceed",
            cancel: "Cancel",
        },
        bm: {
            title: "Tandakan Sebagai Tidak Disahkan?",
            text: "Adakah anda pasti mahu menandakan permohonan ini sebagai tidak disahkan?",
            confirm: "Ya, teruskan",
            cancel: "Batal",
        },
    },
    applicationNotVerified: {
        en: {
            title: "Application Not Approved!",
            text: "The application has been successfully marked as not verified.",
        },
        bm: {
            title: "Permohonan Tidak Diluluskan!",
            text: "Permohonan telah berjaya ditandakan sebagai tidak disahkan.",
        },
    },
    acceptPermit: {
        en: {
            title: "Are you sure?",
            text: "Do you want to accept this permit?",
            confirm: "Yes, proceed",
            cancel: "Cancel",
        },
        bm: {
            title: "Adakah anda pasti?",
            text: "Adakah anda mahu menerima permit ini?",
            confirm: "Ya, teruskan",
            cancel: "Batal",
        },
    },
    acceptPermitConfirm: {
        en: {
            title: "Please Confirm Again",
            text: "This action cannot be undone. Accept the permit?",
            confirm: "Yes, accept it",
            cancel: "Cancel",
        },
        bm: {
            title: "Sila Sahkan Semula",
            text: "Tindakan ini tidak boleh dibatalkan. Terima permit?",
            confirm: "Ya, terima",
            cancel: "Batal",
        },
    },
    permitAccepted: {
        en: { title: "Accepted!", text: "The permit has been accepted." },
        bm: { title: "Diterima!", text: "Permit telah diterima." },
    },
    rejectPermit: {
        en: {
            title: "Reject Permit",
            placeholder: "Enter rejection reason...",
            confirm: "Reject Permit",
            cancel: "Cancel",
        },
        bm: {
            title: "Tolak Permit",
            placeholder: "Masukkan sebab penolakan...",
            confirm: "Tolak Permit",
            cancel: "Batal",
        },
    },
    permitRejected: {
        en: {
            title: "Rejected!",
            text: "The permit has been rejected successfully.",
        },
        bm: { title: "Ditolak!", text: "Permit telah berjaya ditolak." },
    },
    permitDownloadReason: {
        en: {
            title: "This Permit has been downloaded more than once",
            placeholder: "Enter reason...",
            confirm: "Submit",
            cancel: "Cancel",
        },
        bm: {
            title: "Permit ini telah dimuat turun lebih daripada sekali",
            placeholder: "Masukkan sebab...",
            confirm: "Hantar",
            cancel: "Batal",
        },
    },
    reasonSubmitted: {
        en: { title: "Submitted!", text: "The reason submitted successfully." },
        bm: { title: "Dihantar!", text: "Sebab telah berjaya dihantar." },
    },
    payNow: {
        en: {
            title: "Proceed to Payment?",
            confirm: "Yes, proceed to payment",
            cancel: "Cancel",
        },
        bm: {
            title: "Teruskan ke Pembayaran?",
            confirm: "Ya, teruskan ke pembayaran",
            cancel: "Batal",
        },
    },

    customItemsPending: {
        en: {
            title: "Custom Items Pending",
            text: "There are custom items that need to be accepted before you can accept the application. Please accept them first.",
            confirm: "OK",
        },
        bm: {
            title: "Item Khas Menunggu",
            text: "Terdapat item khas yang perlu diterima sebelum anda boleh menerima permohonan ini. Sila terima item tersebut terlebih dahulu.",
            confirm: "OK",
        },
    },

    addNewItem: {
        en: {
            title: "Item Added!",
            text: "The custom item has been added successfully.",
        },
        bm: {
            title: "Item Ditambah!",
            text: "Item khas telah berjaya ditambah.",
        },
    },
    selectExistingItem: {
        en: {
            title: "Select Existing Item",
            text: "Please select an item from the list to replace.",
        },
        bm: {
            title: "Pilih Item Sedia Ada",
            text: "Sila pilih item dari senarai untuk diganti.",
        },
    },

    processing: { en: "Processing...", bm: "Memproses..." },
    loading: { en: "Loading...", bm: "Memuat..." },
    redirecting: {
        en: "Redirecting to payment...",
        bm: "Sedang dialihkan ke pembayaran...",
    },
    uploading: { en: "Uploading...", bm: "Memuat naik..." },
    error: { en: "Error!", bm: "Ralat!" },
    required: {
        en: "Please fill all required fields",
        bm: "Sila isi semua ruangan wajib",
    },
    noItem: { en: "No item to save", bm: "Tiada item untuk disimpan" },
    failedSave: { en: "Failed to save permit", bm: "Gagal menyimpan permit" },
    permitReapply: { en: "Permit Reapply!", bm: "Permohonan Semula Permit!" },
    reasonRequired: {
        en: "Rejection reason is required",
        bm: "Sebab penolakan diperlukan",
    },
    reasonMin5: {
        en: "Rejection reason is required (min 5 characters).",
        bm: "Sebab penolakan diperlukan (min 5 aksara).",
    },
    reasonRequired5: {
        en: "Reason is required (min 5 characters).",
        bm: "Sebab diperlukan (min 5 aksara).",
    },
    unableCheckout: {
        en: "Unable to proceed to checkout.",
        bm: "Tidak dapat meneruskan ke bayaran.",
    },
    selectItem: { en: "-- Select Item --", bm: "-- Pilih Item --" },
    selectUses: { en: "-- Select Uses --", bm: "-- Pilih Kegunaan --" },
    loadingUses: { en: "Loading uses...", bm: "Memuat kegunaan..." },
    yesReject: { en: "Yes, reject it!", bm: "Ya, tolak!" },
    confirm: { en: "Confirm", bm: "Sahkan" },
    cancel: { en: "Cancel", bm: "Batal" },
    submit: { en: "Submit", bm: "Hantar" },
};

function getText(key, lang = null) {
    const l = lang || getLang();
    const [topKey, subKey] = key.split(".");
    const entry = t[topKey];
    if (!entry) return key;

    if (!subKey) {
        return entry[l] ?? entry.en ?? key;
    }

    const langObj = entry[l] || entry.en;
    if (!langObj) return key;
    return langObj[subKey] ?? "";
}

function swalTitle(key) {
    return getText(key + ".title") || getText(key);
}

function swalText(key) {
    return getText(key + ".text") || "";
}

function swalConfirm(key) {
    return getText(key + ".confirm") || getText("confirm");
}

function swalCancel(key) {
    return getText(key + ".cancel") || getText("cancel");
}

// ---------------------------------------------------------------
// Common helpers
// ---------------------------------------------------------------

function applicationId() {
    return (
        window.APPLICATION_ID ||
        window.ImportPermitView?.getApplication()?.application_id
    );
}

function csrfToken() {
    return $('meta[name="csrf-token"]').attr("content");
}

function hidePermitActionButtons(id) {
    $(`.accept[data-permit="${id}"], .reject[data-permit="${id}"]`).remove();
}

// ---------------------------------------------------------------
// Application-level actions
// ---------------------------------------------------------------

function acceptApplication() {
    $("#acceptAppl")
        .off("click")
        .on("click", function (e) {
            e.preventDefault();

            // 1. Check for custom items
            const permits = window.ImportPermitView?.getPermits() || [];
            const hasCustom = permits.some((p) => p.isCustom === true);

            if (hasCustom) {
                // Show warning and stop
                Swal.fire({
                    icon: "warning",
                    title: swalTitle("customItemsPending"),
                    text: swalText("customItemsPending"),
                    confirmButtonText: swalConfirm("customItemsPending"),
                });
                return; // Do not proceed
            }

            // 2. No custom items – proceed with the normal confirmation
            Swal.fire({
                title: swalTitle("acceptApplication"),
                text: swalText("acceptApplication"),
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: swalConfirm("acceptApplication"),
                cancelButtonText: swalCancel("acceptApplication"),
            }).then((result) => {
                if (!result.isConfirmed) return;

                // ... existing AJAX call ...
                $.ajax({
                    url: `/application/verify/${applicationId()}`,
                    method: "POST",
                    data: { _token: csrfToken(), accepted: 1 },
                    success: function (res) {
                        Swal.fire({
                            icon: "success",
                            title: swalTitle("applicationVerified"),
                            text: swalText("applicationVerified"),
                            showConfirmButton: false,
                            position: "center",
                        });
                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: "error",
                            title: getText("error"),
                            text:
                                err.responseJSON?.message ||
                                "Something went wrong.",
                        });
                    },
                });
            });
        });
}
function adminRejectApplication() {
    $("#rejectAdminAppl")
        .off("click")
        .on("click", function (e) {
            e.preventDefault();

            const placeholder = getText("rejectApplication.placeholder");

            Swal.fire({
                title: swalTitle("rejectApplication"),
                html: `
                <p class="mb-2">${getLang() === "bm" ? "Sila berikan sebab untuk penolakan:" : "Please provide a reason for rejection:"}</p>
                <textarea id="rejectReason" class="swal2-textarea" placeholder="${placeholder}"></textarea>
            `,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: swalConfirm("rejectApplication"),
                cancelButtonText: swalCancel("rejectApplication"),
                focusConfirm: false,
                preConfirm: () => {
                    const reason =
                        document.getElementById("rejectReason").value;
                    if (!reason.trim()) {
                        Swal.showValidationMessage(getText("reasonRequired"));
                        return false;
                    }
                    return reason;
                },
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/application/verify/${applicationId()}`,
                    method: "POST",
                    data: {
                        _token: csrfToken(),
                        rejected: 1,
                        reason: result.value,
                    },
                    success: function () {
                        Swal.fire({
                            icon: "success",
                            title: swalTitle("applicationRejected"),
                            text: swalText("applicationRejected"),
                            showConfirmButton: false,
                            timer: 2000,
                        });
                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: "error",
                            title: getText("error"),
                            text:
                                err.responseJSON?.message ||
                                "Something went wrong.",
                        });
                    },
                });
            });
        });
}

function verifyApplication() {
    $("#verifyAppl")
        .off("click")
        .on("click", function (e) {
            e.preventDefault();

            Swal.fire({
                title: swalTitle("verifyApplication"),
                text: swalText("verifyApplication"),
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: swalConfirm("verifyApplication"),
                cancelButtonText: swalCancel("verifyApplication"),
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/application/verify/${applicationId()}`,
                    method: "POST",
                    data: { _token: csrfToken(), verified: 1 },
                    success: function (res) {
                        Swal.fire({
                            icon: "success",
                            title: swalTitle("applicationVerified"),
                            text: swalText("applicationVerified"),
                            showConfirmButton: false,
                            position: "center",
                        });
                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: "error",
                            title: getText("error"),
                            text:
                                err.responseJSON?.message ||
                                "Something went wrong.",
                        });
                    },
                });
            });
        });
}

function rejectApplication() {
    $("#rejectAppl")
        .off("click")
        .on("click", function (e) {
            e.preventDefault();

            Swal.fire({
                title: swalTitle("notVerifyApplication"),
                text: swalText("notVerifyApplication"),
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: swalConfirm("notVerifyApplication"),
                cancelButtonText: swalCancel("notVerifyApplication"),
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/application/verify/${applicationId()}`,
                    method: "POST",
                    data: { _token: csrfToken(), not_verified: 1 },
                    success: function () {
                        Swal.fire({
                            icon: "success",
                            title: swalTitle("applicationNotVerified"),
                            text: swalText("applicationNotVerified"),
                            showConfirmButton: false,
                            position: "center",
                        });
                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: "error",
                            title: getText("error"),
                            text:
                                err.responseJSON?.message ||
                                "Something went wrong.",
                        });
                    },
                });
            });
        });
}

// ---------------------------------------------------------------
// Permit-level actions
// ---------------------------------------------------------------

function acceptPermit() {
    $(document)
        .off("click", ".accept")
        .on("click", ".accept", function (e) {
            e.preventDefault();
            const id = $(this).data("permit");

            Swal.fire({
                title: swalTitle("acceptPermit"),
                text: swalText("acceptPermit"),
                icon: "question",
                showCancelButton: true,
                confirmButtonText: swalConfirm("acceptPermit"),
                cancelButtonText: swalCancel("acceptPermit"),
            }).then((firstResult) => {
                if (!firstResult.isConfirmed) return;

                Swal.fire({
                    title: swalTitle("acceptPermitConfirm"),
                    text: swalText("acceptPermitConfirm"),
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: swalConfirm("acceptPermitConfirm"),
                    cancelButtonText: swalCancel("acceptPermitConfirm"),
                }).then((secondResult) => {
                    if (!secondResult.isConfirmed) return;

                    Swal.fire({
                        title: getText("processing"),
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    $.ajax({
                        url: `/internal/permit/${id}`,
                        method: "POST",
                        data: { _token: csrfToken(), accepted: 1 },
                        success: function () {
                            Swal.close();
                            hidePermitActionButtons(id);
                            Swal.fire({
                                icon: "success",
                                title: swalTitle("permitAccepted"),
                                text: swalText("permitAccepted"),
                                timer: 2000,
                                showConfirmButton: false,
                            });
                            window.ImportPermitView?.reload();
                        },
                        error: function (err) {
                            Swal.close();
                            Swal.fire({
                                icon: "error",
                                title: getText("error"),
                                text:
                                    err.responseJSON?.message ||
                                    "Something went wrong.",
                            });
                        },
                    });
                });
            });
        });
}

function rejectPermit() {
    $(document)
        .off("click", ".reject")
        .on("click", ".reject", function (e) {
            e.preventDefault();
            const id = $(this).data("permit");

            const placeholder = getText("rejectPermit.placeholder");

            Swal.fire({
                title: swalTitle("rejectPermit"),
                text:
                    getText("rejectPermit.text") ||
                    "Please provide a reason for rejecting this permit:",
                icon: "warning",
                input: "textarea",
                inputPlaceholder: placeholder,
                showCancelButton: true,
                confirmButtonText: swalConfirm("rejectPermit"),
                cancelButtonText: swalCancel("rejectPermit"),
                inputValidator: (value) => {
                    if (!value || value.trim().length < 5) {
                        return getText("reasonMin5");
                    }
                },
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: getText("processing"),
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: `/internal/permit/${id}`,
                    method: "POST",
                    data: {
                        _token: csrfToken(),
                        rejected: 1,
                        reason: result.value,
                    },
                    success: function () {
                        Swal.close();
                        hidePermitActionButtons(id);
                        Swal.fire({
                            icon: "success",
                            title: swalTitle("permitRejected"),
                            text: swalText("permitRejected"),
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        window.ImportPermitView?.reload();
                    },
                    error: function (err) {
                        Swal.close();
                        Swal.fire({
                            icon: "error",
                            title: getText("error"),
                            text:
                                err.responseJSON?.message ||
                                "Something went wrong.",
                        });
                    },
                });
            });
        });
}

function generatePermit() {
    $(document)
        .off("click", ".generatePermit")
        .on("click", ".generatePermit", function (e) {
            e.preventDefault();
            const id = $(this).data("permit");

            $.ajax({
                url: `/permit/print`,
                method: "POST",
                data: {
                    _token: csrfToken(),
                    type: "Import Permit",
                    permit_number: id,
                },
                success: function (res) {
                    if (res.message === "Need Response") {
                        const placeholder = getText(
                            "permitDownloadReason.placeholder",
                        );
                        Swal.fire({
                            title: swalTitle("permitDownloadReason"),
                            text:
                                getText("permitDownloadReason.text") ||
                                "Please provide a reason for downloading it:",
                            icon: "warning",
                            input: "textarea",
                            inputPlaceholder: placeholder,
                            showCancelButton: true,
                            confirmButtonText: swalConfirm(
                                "permitDownloadReason",
                            ),
                            cancelButtonText: swalCancel(
                                "permitDownloadReason",
                            ),
                            inputValidator: (value) => {
                                if (!value || value.trim().length < 5) {
                                    return getText("reasonRequired5");
                                }
                            },
                        }).then((result) => {
                            if (!result.isConfirmed) return;

                            Swal.fire({
                                title: getText("processing"),
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading(),
                            });

                            $.ajax({
                                url: `/permit/print`,
                                method: "POST",
                                data: {
                                    _token: csrfToken(),
                                    type: "Import Permit",
                                    permit_number: id,
                                    reason: result.value,
                                },
                                success: function () {
                                    Swal.close();
                                    Swal.fire({
                                        icon: "success",
                                        title: swalTitle("reasonSubmitted"),
                                        text: swalText("reasonSubmitted"),
                                        timer: 2000,
                                        showConfirmButton: false,
                                    });
                                    window.ImportPermitView?.reload();
                                    window.open(
                                        `/permit/generate/${id}`,
                                        "_blank",
                                    );
                                },
                                error: function (err) {
                                    Swal.close();
                                    Swal.fire({
                                        icon: "error",
                                        title: getText("error"),
                                        text:
                                            err.responseJSON?.message ||
                                            "Something went wrong.",
                                    });
                                },
                            });
                        });
                    } else {
                        window.open(`/permit/generate/${id}`, "_blank");
                    }
                },
                error: function (err) {
                    Swal.fire({
                        icon: "error",
                        title: getText("error"),
                        text:
                            err.responseJSON?.message ||
                            "Something went wrong.",
                    });
                },
            });
        });
}

// ---------------------------------------------------------------
// Single-permit "Pay Now"
// ---------------------------------------------------------------

function payNowSingle() {
    $(document)
        .off("click", ".pd-pay-now")
        .on("click", ".pd-pay-now", function (e) {
            e.preventDefault();

            const id = $(this).data("permit");
            const value = $(this).data("value");
            const lang = getLang();
            const amount = Number(value).toFixed(2);

            const paymentText =
                lang === "bm"
                    ? `Anda akan membayar RM ${amount} untuk permit ini.`
                    : `You are about to pay RM ${amount} for this permit.`;

            Swal.fire({
                title: swalTitle("payNow"),
                text: paymentText,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: swalConfirm("payNow"),
                cancelButtonText: swalCancel("payNow"),
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: getText("redirecting"),
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: "/payment/signed-url",
                    method: "POST",
                    data: {
                        application_id: applicationId(),
                        permit_ids: [id],
                        total: amount,
                        type: "import_permit",
                        _token: csrfToken(),
                    },
                    success: function (res) {
                        window.location.href = res.url;
                    },
                    error: function (err) {
                        Swal.close();
                        Swal.fire({
                            icon: "error",
                            title: getText("error"),
                            text:
                                err.responseJSON?.message ||
                                getText("unableCheckout"),
                        });
                    },
                });
            });
        });
}

function generatePDF() {
    $("#printApplication").on("click", function (e) {
        e.preventDefault();

        const applicationId = $(this).data("application");

        if (!applicationId) {
            console.warn(
                "No application id found on #printApplication (expected data-application attribute).",
            );
            return;
        }

        window.open(`/import/application/${applicationId}/print`, "_blank");
    });
}

function acceptItemToList() {
    $(document).on("click", ".accept-custom", function (e) {
        e.preventDefault();
        console.log("accept item");
        const permitId = $(this).data("permit");

        // Build modal with bilingual attributes
        const modalHtml = `
            <div class="modal fade" id="acceptCustomModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" data-en="Accept Custom Item" data-bm="Terima Item Khas">
                                Accept Custom Item
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-center" data-en="How would you like to add this item?" data-bm="Bagaimana anda mahu menambah item ini?">
                                How would you like to add this item?
                            </p>
                            <div class="d-flex gap-3 justify-content-center mt-4">
                                <button class="btn btn-primary accept-custom-add" 
                                        data-permit="${permitId}"
                                        data-en="Add as New Item" 
                                        data-bm="Tambah sebagai Item Baharu">
                                    Add as New Item
                                </button>
                                <button class="btn btn-secondary accept-custom-replace" 
                                        data-permit="${permitId}"
                                        data-en="Replace from Existing List" 
                                        data-bm="Ganti dari Senarai Sedia Ada">
                                    Replace from Existing List
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" 
                                    data-bs-dismiss="modal"
                                    data-en="Cancel" 
                                    data-bm="Batal">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove any previous instance and inject
        $("#acceptCustomModal").remove();
        $("body").append(modalHtml);

        // Apply translations to the modal (for dynamic language switching)
        const modalElement = document.getElementById("acceptCustomModal");
        applyTranslations(modalElement);

        // Initialize Bootstrap modal (non‑dismissible via backdrop)
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: "static",
            keyboard: false,
        });
        modal.show();

        // ---- Handle "Add as New Item" ----
        $(modalElement)
            .find(".accept-custom-add")
            .off("click")
            .on("click", function () {
                const permitId = $(this).data("permit");
                modal.hide();

                // Find the permit's custom item name to prefill
                const permits = window.ImportPermitView?.getPermits() || [];
                const permit = permits.find(
                    (p) => String(p.id) === String(permitId),
                );
                const itemName = permit?.consignment_detail?.item_name || "";

                $("#qacPermitId").val(permitId);
                $("#qacItemName").val(itemName);
                $("#qacScientificName").val("");
                $("#qacQuanLimit").val("");

                loadQuickAddMeasurementOptions();
                initQuickAddEditor();

                const quickAddModal = bootstrap.Modal.getOrCreateInstance(
                    document.getElementById("quickAddConditionModal"),
                );

                // Remove any previous listener to avoid duplication
                quickAddModal._element.removeEventListener('shown.bs.modal', onModalShown);

                function onModalShown() {
                    quickAddModal._element.removeEventListener('shown.bs.modal', onModalShown);
                    fetchCountryList(); // now the modal is visible
                }

                quickAddModal._element.addEventListener('shown.bs.modal', onModalShown);
                quickAddModal.show();
            });

        // ---- Handle "Replace from Existing List" ----
        $(modalElement)
            .find(".accept-custom-replace")
            .off("click")
            .on("click", function () {
                const permitId = $(this).data("permit");
                modal.hide();

                const permits = window.ImportPermitView?.getPermits() || [];
                const permit = permits.find(
                    (p) => String(p.id) === String(permitId),
                );
                const itemName = permit?.consignment_detail?.item_name || "";

                $("#reiPermitId").val(permitId);
                $("#reiNewItemName").val(itemName);
                $("#reiPreview").addClass("d-none");
                $("#reiConfirmBtn").prop("disabled", true);

                initReplaceExistingSelect();

                const replaceModal = bootstrap.Modal.getOrCreateInstance(
                    document.getElementById("replaceExistingModal"),
                );
                replaceModal.show();
            });

        // Cleanup after modal is hidden
        modalElement.addEventListener("hidden.bs.modal", function () {
            $(this).remove();
        });
    });
}

function initReplaceExistingSelect() {
    
    console.log('APPLICATION country', APPLICATION.exporter.country)
    let country = APPLICATION.exporter.country;
    const $select = $('#reiSelect');

    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
    }
    $select.empty();

    $select.select2({
        dropdownParent: $('#replaceExistingModal'),
        placeholder: '-- Search item --',
        ajax: {
            url: `/internal/ip_condition/search/${country}`,
            dataType: 'json',
            delay: 250,
            data: (params) => ({ q: params.term }),
            processResults: (data) => ({ results: data.results }),
        },
        minimumInputLength: 1,
    });

    $select.off('change').on('change', function () {
        const selected = $select.select2('data')[0];
        if (!selected) return;

        $('#reiPreviewName').text(selected.text);
        $('#reiPreview').removeClass('d-none');
        $('#reiConfirmBtn').prop('disabled', false).data('selectedId', selected.id);
    });
}

$(document).on('click', '#reiConfirmBtn', function () {
    const permitId = $('#reiPermitId').val();
    const newItemName = $('#reiNewItemName').val();
    const selectedId = $(this).data('selectedId');

    if (!selectedId) return;

    Swal.fire({
        title: 'Linking...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    // 1. Add the custom name as an alias on the existing item
    $.ajax({
        url: `/internal/ip_condition/${selectedId}/add-alias`,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            alias: newItemName,
            permit_id: permitId,
        },
        success: function () {
            // 2. Link the permit to that existing condition
            $.ajax({
                url: `/internal/permit/${permitId}/link-condition`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ip_condition_id: selectedId,
                    permit_id: permitId,
                },
                success: function () {
                    Swal.close();
                    bootstrap.Modal.getInstance(document.getElementById('replaceExistingModal')).hide();
                    Swal.fire('Linked!', 'The item has been linked and the name saved as an alias.', 'success');
                    window.ImportPermitView?.reload();
                },
                error: function () {
                    Swal.close();
                    Swal.fire('Warning', 'Alias was saved but the permit could not be linked.', 'warning');
                },
            });
        },
        error: function (xhr) {
            Swal.close();
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save alias.', 'error');
        },
    });
});



function loadQuickAddMeasurementOptions() {
    if (qacMeasurementsLoaded) return;
    const $select = $("#qacQuanUnit");
    $select.empty().append('<option value="">-- Select Unit --</option>');

    $.get("/measurement", function (data) {
        (data.unit || []).forEach((item) => {
            $select.append(
                `<option value="${item.id}">${item.cate_code}</option>`,
            );
        });
        qacMeasurementsLoaded = true;
    });
}

function initQuickAddEditor() {
    const modalBody = document.querySelector('#quickAddConditionModal .modal-body');
    const oldWrapper = document.getElementById('qacEditorWrapper');

    if (oldWrapper) {
        oldWrapper.remove(); // removes toolbar + editor together
    }

    // Rebuild the wrapper + editor div fresh
    const wrapper = document.createElement('div');
    wrapper.id = 'qacEditorWrapper';
    wrapper.innerHTML = `<div id="qacConditionEditor" style="min-height:120px;"></div>`;
    modalBody.querySelector('.col-xl-12:last-child').prepend(wrapper);
    // adjust selector to wherever your editor column actually is

    qacQuill = new Quill('#qacConditionEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['clean'],
            ],
        },
    });
}


// ---------------------------------------------------------------
// FIXED: fetchCountryList with dropdownParent set to modal body
// ---------------------------------------------------------------
function fetchCountryList() {
    const $select = $("#countrySelect");
    const url = $select.data("route");

    // Destroy any existing Select2 instance
    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
    }
    $select.empty();

    return $.ajax({
        url,
        type: "GET",
        dataType: "json",
        cache: false,
        success: (response) => {
            const data = response.data || [];

            console.log('country data', data)

            // Optional: placeholder option
            $select.append('<option value="">-- Select Countries --</option>');

            // Add options dynamically
            data.forEach((country) => {
                // country.value = code, country.name = full name
                $select.append(
                    `<option value="${country.value}">${country.name} (${country.value})</option>`
                );
            });

            // Initialize Select2 – attach dropdown inside modal body for proper scrolling
            $select.select2({
                width: "100%",
                placeholder: "-- Select Countries --",
                allowClear: true,
                multiple: true,
                dropdownParent: $('#quickAddConditionModal .modal-body'), // FIX: dropdown scrolls with modal
                matcher: function (params, data) {
                    // If no search term, return all data
                    if ($.trim(params.term) === "") {
                        return data;
                    }

                    // Search by both name or value
                    const term = params.term.toLowerCase();
                    if (
                        data.text.toLowerCase().includes(term) ||
                        data.id.toLowerCase().includes(term)
                    ) {
                        return data;
                    }

                    // Return null if not matched
                    return null;
                },
            });
        },
        error: (xhr) => {
            console.error("Failed to load countries:", xhr.responseText);
            Swal.fire({
                icon: "error",
                title: "Failed to load countries",
                text: "Please check your connection.",
            });
        },
    });
}

// ---------------------------------------------------------------
// Wire everything up
// ---------------------------------------------------------------

function initActions() {
    acceptApplication();
    adminRejectApplication();
    verifyApplication();
    rejectApplication();

    acceptItemToList();

    acceptPermit();
    rejectPermit();
    generatePermit();
    payNowSingle();

    generatePDF();

    // fetchCountryList() is no longer called here – it's triggered after modal shown

    $(document).on("click", "#qacSaveBtn", function () {
        const permitId = $("#qacPermitId").val();
        const itemName = $("#qacItemName").val().trim();

        if (!itemName) {
            Swal.fire("Error", "Item name is required.", "error");
            return;
        }

        const conditionHtml = qacQuill ? qacQuill.root.innerHTML : "";

        Swal.fire({
            title: "Saving...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

       

        const selectedCountries = $("#countrySelect").val() || [];
        const countryTagData = selectedCountries.map((val) => ({ value: val }));
        let countryTag = JSON.stringify(countryTagData)
     

        $.ajax({
            url: "/internal/ip_condition/quick-add",
            method: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                item_name: itemName,
                scientific_name: $("#qacScientificName").val(),
                category: $("#qacCategory").val(),
                quantity_limit: $("#qacQuanLimit").val(),
                measurement_unit: $("#qacQuanUnit").val(),
                condition_html: conditionHtml,
                permit_id: permitId,
                country: countryTag,
                permitId: permitId,
                application_type: APPLICATION.type,
             
            },
            success: function (res) {
                // Link the newly created condition to the permit
                $.ajax({
                    url: `/internal/permit/${permitId}/link-condition`,
                    method: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ip_condition_id: res.id,
                    },
                    success: function () {
                        Swal.close();
                        bootstrap.Modal.getInstance(
                            document.getElementById("quickAddConditionModal"),
                        ).hide();
                        Swal.fire(
                            "Saved!",
                            "New item added and linked to the permit.",
                            "success",
                        );
                        window.ImportPermitView?.reload();
                    },
                    error: function () {
                        Swal.close();
                        Swal.fire(
                            "Warning",
                            "Item was created but could not be linked to the permit.",
                            "warning",
                        );
                    },
                });
            },
            error: function (xhr) {
                Swal.close();
                Swal.fire(
                    "Error",
                    xhr.responseJSON?.message || "Failed to save item.",
                    "error",
                );
            },
        });
    });
}

document.addEventListener("DOMContentLoaded", initActions);

// Export for use in other modules if needed
export { getText, swalTitle, swalText, swalConfirm, swalCancel };