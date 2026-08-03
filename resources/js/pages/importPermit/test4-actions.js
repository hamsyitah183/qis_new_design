/**
 * importPermitActions.js - Bilingual workflow actions for Import Permit applications
 * All Swal alerts now display plain translated text based on the user's language preference.
 */

import $ from "jquery";
import Swal from "sweetalert2";
import { applyTranslations } from "../../app"; // adjust path as needed

// ---------------------------------------------------------------
// Helper: get current language
// ---------------------------------------------------------------

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

// ---------------------------------------------------------------
// Translation map – all user‑facing strings
// ---------------------------------------------------------------

const t = {
    acceptApplication: {
        en: { title: 'Accept Application?', text: 'Are you sure you want to accept this application?', confirm: 'Yes, accept it!', cancel: 'Cancel' },
        bm: { title: 'Terima Permohonan?', text: 'Adakah anda pasti mahu menerima permohonan ini?', confirm: 'Ya, terima!', cancel: 'Batal' }
    },
    applicationVerified: {
        en: { title: 'Application Verified!', text: 'The application has been successfully verified.' },
        bm: { title: 'Permohonan Disahkan!', text: 'Permohonan telah berjaya disahkan.' }
    },
    rejectApplication: {
        en: { title: 'Reject Application', placeholder: 'Enter rejection reason...', confirm: 'Confirm', cancel: 'Cancel' },
        bm: { title: 'Tolak Permohonan', placeholder: 'Masukkan sebab penolakan...', confirm: 'Sahkan', cancel: 'Batal' }
    },
    applicationRejected: {
        en: { title: 'Application Rejected!', text: 'The application has been rejected.' },
        bm: { title: 'Permohonan Ditolak!', text: 'Permohonan telah ditolak.' }
    },
    verifyApplication: {
        en: { title: 'Verify Application?', text: 'Are you sure you want to verify this application?', confirm: 'Yes, verify it!', cancel: 'Cancel' },
        bm: { title: 'Sahkan Permohonan?', text: 'Adakah anda pasti mahu mengesahkan permohonan ini?', confirm: 'Ya, sahkan!', cancel: 'Batal' }
    },
    notVerifyApplication: {
        en: { title: 'Mark as Not Verified?', text: 'Are you sure you want to mark this application as not verified?', confirm: 'Yes, proceed', cancel: 'Cancel' },
        bm: { title: 'Tandakan Sebagai Tidak Disahkan?', text: 'Adakah anda pasti mahu menandakan permohonan ini sebagai tidak disahkan?', confirm: 'Ya, teruskan', cancel: 'Batal' }
    },
    applicationNotVerified: {
        en: { title: 'Application Not Approved!', text: 'The application has been successfully marked as not verified.' },
        bm: { title: 'Permohonan Tidak Diluluskan!', text: 'Permohonan telah berjaya ditandakan sebagai tidak disahkan.' }
    },
    acceptPermit: {
        en: { title: 'Are you sure?', text: 'Do you want to accept this permit?', confirm: 'Yes, proceed', cancel: 'Cancel' },
        bm: { title: 'Adakah anda pasti?', text: 'Adakah anda mahu menerima permit ini?', confirm: 'Ya, teruskan', cancel: 'Batal' }
    },
    acceptPermitConfirm: {
        en: { title: 'Please Confirm Again', text: 'This action cannot be undone. Accept the permit?', confirm: 'Yes, accept it', cancel: 'Cancel' },
        bm: { title: 'Sila Sahkan Semula', text: 'Tindakan ini tidak boleh dibatalkan. Terima permit?', confirm: 'Ya, terima', cancel: 'Batal' }
    },
    permitAccepted: {
        en: { title: 'Accepted!', text: 'The permit has been accepted.' },
        bm: { title: 'Diterima!', text: 'Permit telah diterima.' }
    },
    rejectPermit: {
        en: { title: 'Reject Permit', placeholder: 'Enter rejection reason...', confirm: 'Reject Permit', cancel: 'Cancel' },
        bm: { title: 'Tolak Permit', placeholder: 'Masukkan sebab penolakan...', confirm: 'Tolak Permit', cancel: 'Batal' }
    },
    permitRejected: {
        en: { title: 'Rejected!', text: 'The permit has been rejected successfully.' },
        bm: { title: 'Ditolak!', text: 'Permit telah berjaya ditolak.' }
    },
    permitDownloadReason: {
        en: { title: 'This Permit has been downloaded more than once', placeholder: 'Enter reason...', confirm: 'Submit', cancel: 'Cancel' },
        bm: { title: 'Permit ini telah dimuat turun lebih daripada sekali', placeholder: 'Masukkan sebab...', confirm: 'Hantar', cancel: 'Batal' }
    },
    reasonSubmitted: {
        en: { title: 'Submitted!', text: 'The reason submitted successfully.' },
        bm: { title: 'Dihantar!', text: 'Sebab telah berjaya dihantar.' }
    },
    payNow: {
        en: { title: 'Proceed to Payment?', confirm: 'Yes, proceed to payment', cancel: 'Cancel' },
        bm: { title: 'Teruskan ke Pembayaran?', confirm: 'Ya, teruskan ke pembayaran', cancel: 'Batal' }
    },
    processing: { en: 'Processing...', bm: 'Memproses...' },
    loading: { en: 'Loading...', bm: 'Memuat...' },
    redirecting: { en: 'Redirecting to payment...', bm: 'Sedang dialihkan ke pembayaran...' },
    uploading: { en: 'Uploading...', bm: 'Memuat naik...' },
    error: { en: 'Error!', bm: 'Ralat!' },
    required: { en: 'Please fill all required fields', bm: 'Sila isi semua ruangan wajib' },
    noItem: { en: 'No item to save', bm: 'Tiada item untuk disimpan' },
    failedSave: { en: 'Failed to save permit', bm: 'Gagal menyimpan permit' },
    permitReapply: { en: 'Permit Reapply!', bm: 'Permohonan Semula Permit!' },
    reasonRequired: { en: 'Rejection reason is required', bm: 'Sebab penolakan diperlukan' },
    reasonMin5: { en: 'Rejection reason is required (min 5 characters).', bm: 'Sebab penolakan diperlukan (min 5 aksara).' },
    reasonRequired5: { en: 'Reason is required (min 5 characters).', bm: 'Sebab diperlukan (min 5 aksara).' },
    unableCheckout: { en: 'Unable to proceed to checkout.', bm: 'Tidak dapat meneruskan ke bayaran.' },
    selectItem: { en: '-- Select Item --', bm: '-- Pilih Item --' },
    selectUses: { en: '-- Select Uses --', bm: '-- Pilih Kegunaan --' },
    loadingUses: { en: 'Loading uses...', bm: 'Memuat kegunaan...' },
    yesReject: { en: 'Yes, reject it!', bm: 'Ya, tolak!' },
    confirm: { en: 'Confirm', bm: 'Sahkan' },
    cancel: { en: 'Cancel', bm: 'Batal' },
    submit: { en: 'Submit', bm: 'Hantar' },
};

function getText(key, lang = null) {
    const l = lang || getLang();
    const [topKey, subKey] = key.split('.');
    const entry = t[topKey];
    if (!entry) return key;

    if (!subKey) {
        return entry[l] ?? entry.en ?? key;
    }

    const langObj = entry[l] || entry.en;
    if (!langObj) return key;
    return langObj[subKey] ?? '';
}

function swalTitle(key) {
    return getText(key + '.title') || getText(key);
}

function swalText(key) {
    return getText(key + '.text') || '';
}

function swalConfirm(key) {
    return getText(key + '.confirm') || getText('confirm');
}

function swalCancel(key) {
    return getText(key + '.cancel') || getText('cancel');
}

// ---------------------------------------------------------------
// Common helpers
// ---------------------------------------------------------------

function applicationId() {
    return window.APPLICATION_ID || window.ImportPermitView?.getApplication()?.application_id;
}

function csrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

function hidePermitActionButtons(id) {
    $(`.accept[data-permit="${id}"], .reject[data-permit="${id}"]`).remove();
}

// ---------------------------------------------------------------
// Application-level actions
// ---------------------------------------------------------------

function acceptApplication() {
    $('#acceptAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: swalTitle('acceptApplication'),
            text: swalText('acceptApplication'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: swalConfirm('acceptApplication'),
            cancelButtonText: swalCancel('acceptApplication'),
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/application/verify/${applicationId()}`,
                method: 'POST',
                data: { _token: csrfToken(), accepted: 1 },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: swalTitle('applicationVerified'),
                        text: swalText('applicationVerified'),
                        showConfirmButton: false,
                        position: 'center',
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({
                        icon: 'error',
                        title: getText('error'),
                        text: err.responseJSON?.message || 'Something went wrong.',
                    });
                },
            });
        });
    });
}

function adminRejectApplication() {
    $('#rejectAdminAppl').off('click').on('click', function (e) {
        e.preventDefault();

        const placeholder = getText('rejectApplication.placeholder');

        Swal.fire({
            title: swalTitle('rejectApplication'),
            html: `
                <p class="mb-2">${getLang() === 'bm' ? 'Sila berikan sebab untuk penolakan:' : 'Please provide a reason for rejection:'}</p>
                <textarea id="rejectReason" class="swal2-textarea" placeholder="${placeholder}"></textarea>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: swalConfirm('rejectApplication'),
            cancelButtonText: swalCancel('rejectApplication'),
            focusConfirm: false,
            preConfirm: () => {
                const reason = document.getElementById('rejectReason').value;
                if (!reason.trim()) {
                    Swal.showValidationMessage(getText('reasonRequired'));
                    return false;
                }
                return reason;
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/application/verify/${applicationId()}`,
                method: 'POST',
                data: { _token: csrfToken(), rejected: 1, reason: result.value },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: swalTitle('applicationRejected'),
                        text: swalText('applicationRejected'),
                        showConfirmButton: false,
                        timer: 2000,
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({
                        icon: 'error',
                        title: getText('error'),
                        text: err.responseJSON?.message || 'Something went wrong.',
                    });
                },
            });
        });
    });
}

function verifyApplication() {
    $('#verifyAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: swalTitle('verifyApplication'),
            text: swalText('verifyApplication'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: swalConfirm('verifyApplication'),
            cancelButtonText: swalCancel('verifyApplication'),
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/application/verify/${applicationId()}`,
                method: 'POST',
                data: { _token: csrfToken(), verified: 1 },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: swalTitle('applicationVerified'),
                        text: swalText('applicationVerified'),
                        showConfirmButton: false,
                        position: 'center',
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({
                        icon: 'error',
                        title: getText('error'),
                        text: err.responseJSON?.message || 'Something went wrong.',
                    });
                },
            });
        });
    });
}

function rejectApplication() {
    $('#rejectAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: swalTitle('notVerifyApplication'),
            text: swalText('notVerifyApplication'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: swalConfirm('notVerifyApplication'),
            cancelButtonText: swalCancel('notVerifyApplication'),
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/application/verify/${applicationId()}`,
                method: 'POST',
                data: { _token: csrfToken(), not_verified: 1 },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: swalTitle('applicationNotVerified'),
                        text: swalText('applicationNotVerified'),
                        showConfirmButton: false,
                        position: 'center',
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({
                        icon: 'error',
                        title: getText('error'),
                        text: err.responseJSON?.message || 'Something went wrong.',
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
    $(document).off('click', '.accept').on('click', '.accept', function (e) {
        e.preventDefault();
        const id = $(this).data('permit');

        Swal.fire({
            title: swalTitle('acceptPermit'),
            text: swalText('acceptPermit'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: swalConfirm('acceptPermit'),
            cancelButtonText: swalCancel('acceptPermit'),
        }).then((firstResult) => {
            if (!firstResult.isConfirmed) return;

            Swal.fire({
                title: swalTitle('acceptPermitConfirm'),
                text: swalText('acceptPermitConfirm'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: swalConfirm('acceptPermitConfirm'),
                cancelButtonText: swalCancel('acceptPermitConfirm'),
            }).then((secondResult) => {
                if (!secondResult.isConfirmed) return;

                Swal.fire({
                    title: getText('processing'),
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: `/internal/permit/${id}`,
                    method: 'POST',
                    data: { _token: csrfToken(), accepted: 1 },
                    success: function () {
                        Swal.close();
                        hidePermitActionButtons(id);
                        Swal.fire({
                            icon: 'success',
                            title: swalTitle('permitAccepted'),
                            text: swalText('permitAccepted'),
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        window.ImportPermitView?.reload();
                    },
                    error: function (err) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: getText('error'),
                            text: err.responseJSON?.message || 'Something went wrong.',
                        });
                    },
                });
            });
        });
    });
}

function rejectPermit() {
    $(document).off('click', '.reject').on('click', '.reject', function (e) {
        e.preventDefault();
        const id = $(this).data('permit');

        const placeholder = getText('rejectPermit.placeholder');

        Swal.fire({
            title: swalTitle('rejectPermit'),
            text: getText('rejectPermit.text') || 'Please provide a reason for rejecting this permit:',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: placeholder,
            showCancelButton: true,
            confirmButtonText: swalConfirm('rejectPermit'),
            cancelButtonText: swalCancel('rejectPermit'),
            inputValidator: (value) => {
                if (!value || value.trim().length < 5) {
                    return getText('reasonMin5');
                }
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: getText('processing'),
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                url: `/internal/permit/${id}`,
                method: 'POST',
                data: { _token: csrfToken(), rejected: 1, reason: result.value },
                success: function () {
                    Swal.close();
                    hidePermitActionButtons(id);
                    Swal.fire({
                        icon: 'success',
                        title: swalTitle('permitRejected'),
                        text: swalText('permitRejected'),
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    window.ImportPermitView?.reload();
                },
                error: function (err) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: getText('error'),
                        text: err.responseJSON?.message || 'Something went wrong.',
                    });
                },
            });
        });
    });
}

function generatePermit() {
    $(document).off('click', '.generatePermit').on('click', '.generatePermit', function (e) {
        e.preventDefault();
        const id = $(this).data('permit');

        $.ajax({
            url: `/permit/print`,
            method: 'POST',
            data: { _token: csrfToken(), type: 'Import Permit', permit_number: id },
            success: function (res) {
                if (res.message === 'Need Response') {
                    const placeholder = getText('permitDownloadReason.placeholder');
                    Swal.fire({
                        title: swalTitle('permitDownloadReason'),
                        text: getText('permitDownloadReason.text') || 'Please provide a reason for downloading it:',
                        icon: 'warning',
                        input: 'textarea',
                        inputPlaceholder: placeholder,
                        showCancelButton: true,
                        confirmButtonText: swalConfirm('permitDownloadReason'),
                        cancelButtonText: swalCancel('permitDownloadReason'),
                        inputValidator: (value) => {
                            if (!value || value.trim().length < 5) {
                                return getText('reasonRequired5');
                            }
                        },
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        Swal.fire({
                            title: getText('processing'),
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading(),
                        });

                        $.ajax({
                            url: `/permit/print`,
                            method: 'POST',
                            data: { _token: csrfToken(), type: 'Import Permit', permit_number: id, reason: result.value },
                            success: function () {
                                Swal.close();
                                Swal.fire({
                                    icon: 'success',
                                    title: swalTitle('reasonSubmitted'),
                                    text: swalText('reasonSubmitted'),
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                                window.ImportPermitView?.reload();
                                window.open(`/permit/generate/${id}`, '_blank');
                            },
                            error: function (err) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: getText('error'),
                                    text: err.responseJSON?.message || 'Something went wrong.',
                                });
                            },
                        });
                    });
                } else {
                    window.open(`/permit/generate/${id}`, '_blank');
                }
            },
            error: function (err) {
                Swal.fire({
                    icon: 'error',
                    title: getText('error'),
                    text: err.responseJSON?.message || 'Something went wrong.',
                });
            },
        });
    });
}

// ---------------------------------------------------------------
// Single-permit "Pay Now"
// ---------------------------------------------------------------

function payNowSingle() {
    $(document).off('click', '.pd-pay-now').on('click', '.pd-pay-now', function (e) {
        e.preventDefault();

        const id = $(this).data('permit');
        const value = $(this).data('value');
        const lang = getLang();
        const amount = Number(value).toFixed(2);

        const paymentText = lang === 'bm'
            ? `Anda akan membayar RM ${amount} untuk permit ini.`
            : `You are about to pay RM ${amount} for this permit.`;

        Swal.fire({
            title: swalTitle('payNow'),
            text: paymentText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: swalConfirm('payNow'),
            cancelButtonText: swalCancel('payNow'),
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: getText('redirecting'),
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                url: '/payment/signed-url',
                method: 'POST',
                data: {
                    application_id: applicationId(),
                    permit_ids: [id],
                    total: amount,
                    type: 'import_permit',
                    _token: csrfToken(),
                },
                success: function (res) {
                    window.location.href = res.url;
                },
                error: function (err) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: getText('error'),
                        text: err.responseJSON?.message || getText('unableCheckout'),
                    });
                },
            });
        });
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

    acceptPermit();
    rejectPermit();
    generatePermit();
    payNowSingle();
}

document.addEventListener('DOMContentLoaded', initActions);

// Export for use in other modules if needed
export { getText, swalTitle, swalText, swalConfirm, swalCancel };