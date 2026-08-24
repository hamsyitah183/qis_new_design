/**
 * consignment-actions.js
 * Bilingual workflow actions for Consignment Certificate applications.
 * All bulk actions (approve/reject/payment/print) are handled here.
 * Reapply (per‑permit) is also here.
 */

import $ from "jquery";
import Swal from "sweetalert2";
import { applyTranslations } from "../../app";
import { CONSIGNMENT_APPLICATION_FEE, money } from "./consignment1.js";

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
// Translation map – all user-facing strings
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
        en: { title: 'Reject Application?', text: 'Are you sure you want to reject this application?', confirm: 'Yes, reject it!', cancel: 'Cancel' },
        bm: { title: 'Tolak Permohonan?', text: 'Adakah anda pasti mahu menolak permohonan ini?', confirm: 'Ya, tolak!', cancel: 'Batal' }
    },
    applicationNotVerified: {
        en: { title: 'Application Not Approved!', text: 'The application has been successfully marked as not verified.' },
        bm: { title: 'Permohonan Tidak Diluluskan!', text: 'Permohonan telah berjaya ditandakan sebagai tidak disahkan.' }
    },
    acceptCertificates: {
        en: { title: 'Are you sure?', text: 'Do you want to accept all certificates for this application?', confirm: 'Yes, proceed', cancel: 'Cancel' },
        bm: { title: 'Adakah anda pasti?', text: 'Adakah anda mahu menerima semua sijil untuk permohonan ini?', confirm: 'Ya, teruskan', cancel: 'Batal' }
    },
    acceptCertificatesConfirm: {
        en: { title: 'Please Confirm Again', text: 'This action cannot be undone. Accept the certificates?', confirm: 'Yes, accept it', cancel: 'Cancel' },
        bm: { title: 'Sila Sahkan Semula', text: 'Tindakan ini tidak boleh dibatalkan. Terima sijil?', confirm: 'Ya, terima', cancel: 'Batal' }
    },
    certificatesAccepted: {
        en: { title: 'Accepted!', text: 'The certificates have been accepted.' },
        bm: { title: 'Diterima!', text: 'Sijil telah diterima.' }
    },
    rejectCertificates: {
        en: { title: 'Reject All Consignment Certificates', text: 'Please provide a reason for rejecting these certificates:', placeholder: 'Enter rejection reason...', confirm: 'Reject Certificates', cancel: 'Cancel' },
        bm: { title: 'Tolak Semua Sijil Konsainan', text: 'Sila berikan sebab untuk menolak sijil ini:', placeholder: 'Masukkan sebab penolakan...', confirm: 'Tolak Sijil', cancel: 'Batal' }
    },
    certificatesRejected: {
        en: { title: 'Rejected!', text: 'The certificates have been rejected successfully.' },
        bm: { title: 'Ditolak!', text: 'Sijil telah berjaya ditolak.' }
    },
    permitDownloadReason: {
        en: { title: 'This Certificate has been downloaded more than once', placeholder: 'Enter reason...', confirm: 'Submit', cancel: 'Cancel' },
        bm: { title: 'Sijil ini telah dimuat turun lebih daripada sekali', placeholder: 'Masukkan sebab...', confirm: 'Hantar', cancel: 'Batal' }
    },
    reasonSubmitted: {
        en: { title: 'Submitted!', text: 'The reason submitted successfully.' },
        bm: { title: 'Dihantar!', text: 'Sebab telah berjaya dihantar.' }
    },
    payNow: {
        en: { title: 'Proceed to Payment?', text: 'You are about to pay RM {amount} for this application.', confirm: 'Yes, proceed to payment', cancel: 'Cancel' },
        bm: { title: 'Teruskan ke Pembayaran?', text: 'Anda akan membayar RM {amount} untuk permohonan ini.', confirm: 'Ya, teruskan ke pembayaran', cancel: 'Batal' }
    },
    processing: { en: 'Processing...', bm: 'Memproses...' },
    loading: { en: 'Loading...', bm: 'Memuat...' },
    uploading: { en: 'Uploading...', bm: 'Memuat naik...' },
    error: { en: 'Error!', bm: 'Ralat!' },
    required: { en: 'Please fill all required fields', bm: 'Sila isi semua ruangan wajib' },
    noItem: { en: 'No item to save', bm: 'Tiada item untuk disimpan' },
    failedSave: { en: 'Failed to save certificate', bm: 'Gagal menyimpan sijil' },
    permitReapply: { en: 'Certificate Reapply!', bm: 'Permohonan Semula Sijil!' },
    reasonRequired: { en: 'Rejection reason is required', bm: 'Sebab penolakan diperlukan' },
    reasonMin5: { en: 'Rejection reason is required (min 5 characters).', bm: 'Sebab penolakan diperlukan (min 5 aksara).' },
    reasonRequired5: { en: 'Reason is required (min 5 characters).', bm: 'Sebab diperlukan (min 5 aksara).' },
    unableCheckout: { en: 'Unable to proceed to checkout.', bm: 'Tidak dapat meneruskan ke bayaran.' },
    loadingUses: { en: 'Loading uses...', bm: 'Memuat kegunaan...' },
    confirm: { en: 'Confirm', bm: 'Sahkan' },
    cancel: { en: 'Cancel', bm: 'Batal' },
    submit: { en: 'Submit', bm: 'Hantar' },
};

function getText(key, lang = null) {
    const l = lang || getLang();
    const [topKey, subKey] = key.split('.');
    const entry = t[topKey];
    if (!entry) return key;

    if (!subKey) return entry[l] ?? entry.en ?? key;

    const langObj = entry[l] || entry.en;
    if (!langObj) return key;
    return langObj[subKey] ?? '';
}

function swalTitle(key) { return getText(key + '.title') || getText(key); }
function swalText(key) { return getText(key + '.text') || ''; }
function swalConfirm(key) { return getText(key + '.confirm') || getText('confirm'); }
function swalCancel(key) { return getText(key + '.cancel') || getText('cancel'); }

// ---------------------------------------------------------------
// Common helpers
// ---------------------------------------------------------------

function applicationId() {
    return window.APPLICATION_ID || window.ImportPermitView?.getApplication()?.application_id;
}

function csrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

function reload() {
    return window.ImportPermitView?.reload();
}

// ---------------------------------------------------------------
// Application-level clerk review actions
// (POST /consignment/verify/{applicationId})
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
                url: `/consignment/verify/${applicationId()}`,
                method: 'POST',
                data: { _token: csrfToken(), accepted: 1 },
                success: function () {
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
                    Swal.fire({ icon: 'error', title: getText('error'), text: err.responseJSON?.message || 'Something went wrong.' });
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
                url: `/consignment/verify/${applicationId()}`,
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
                    Swal.fire({ icon: 'error', title: getText('error'), text: err.responseJSON?.message || 'Something went wrong.' });
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
                url: `/consignment/verify/${applicationId()}`,
                method: 'POST',
                data: { _token: csrfToken(), verified: 1 },
                success: function () {
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
                    Swal.fire({ icon: 'error', title: getText('error'), text: err.responseJSON?.message || 'Something went wrong.' });
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
                url: `/consignment/verify/${applicationId()}`,
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
                    Swal.fire({ icon: 'error', title: getText('error'), text: err.responseJSON?.message || 'Something went wrong.' });
                },
            });
        });
    });
}

// ---------------------------------------------------------------
// Bulk certificate actions (POST /internal/consignment/{applicationId})
// ---------------------------------------------------------------

function acceptCertificates() {
    $(document).off('click', '.accept').on('click', '.accept', function (e) {
        e.preventDefault();
        const id = $(this).data('application');

        Swal.fire({
            title: swalTitle('acceptCertificates'),
            text: swalText('acceptCertificates'),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: swalConfirm('acceptCertificates'),
            cancelButtonText: swalCancel('acceptCertificates'),
        }).then((firstResult) => {
            if (!firstResult.isConfirmed) return;

            Swal.fire({
                title: swalTitle('acceptCertificatesConfirm'),
                text: swalText('acceptCertificatesConfirm'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: swalConfirm('acceptCertificatesConfirm'),
                cancelButtonText: swalCancel('acceptCertificatesConfirm'),
            }).then((secondResult) => {
                if (!secondResult.isConfirmed) return;

                Swal.fire({ title: getText('processing'), allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: `/internal/consignment/${id}`,
                    method: 'POST',
                    data: { _token: csrfToken(), accepted: 1 },
                    success: function () {
                        Swal.close();
                        Swal.fire({
                            icon: 'success',
                            title: swalTitle('certificatesAccepted'),
                            text: swalText('certificatesAccepted'),
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.close();
                        Swal.fire({ icon: 'error', title: getText('error'), text: err.responseJSON?.message || 'Something went wrong.' });
                    },
                });
            });
        });
    });
}

function rejectCertificates() {
    $(document).off('click', '.reject').on('click', '.reject', function (e) {
        e.preventDefault();
        const id = $(this).data('application');

        const placeholder = getText('rejectCertificates.placeholder');

        Swal.fire({
            title: swalTitle('rejectCertificates'),
            text: swalText('rejectCertificates'),
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: placeholder,
            showCancelButton: true,
            confirmButtonText: swalConfirm('rejectCertificates'),
            cancelButtonText: swalCancel('rejectCertificates'),
            inputValidator: (value) => {
                if (!value || value.trim().length < 5) return getText('reasonMin5');
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: getText('processing'), allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: `/internal/consignment/${id}`,
                method: 'POST',
                data: { _token: csrfToken(), rejected: 1, reason: result.value },
                success: function () {
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: swalTitle('certificatesRejected'),
                        text: swalText('certificatesRejected'),
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: getText('error'), text: err.responseJSON?.message || 'Something went wrong.' });
                },
            });
        });
    });
}

// ---------------------------------------------------------------
// Application-level certificate download
// (POST /permit/print { type: 'Consignment', permit_number: applicationId })
// ---------------------------------------------------------------

function generatePermit() {
    $(document).off('click', '.generatePermit').on('click', '.generatePermit', function (e) {
        e.preventDefault();
        const id = $(this).data('permit');

        $.ajax({
            url: `/permit/print`,
            method: 'POST',
            data: { _token: csrfToken(), type: 'Consignment', permit_number: id },
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
                            if (!value || value.trim().length < 5) return getText('reasonRequired5');
                        },
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        Swal.fire({ title: getText('processing'), allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                        $.ajax({
                            url: `/permit/print`,
                            method: 'POST',
                            data: { _token: csrfToken(), type: 'Consignment', permit_number: id, reason: result.value },
                            success: function () {
                                Swal.close();
                                Swal.fire({
                                    icon: 'success',
                                    title: swalTitle('reasonSubmitted'),
                                    text: swalText('reasonSubmitted'),
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                                reload();
                                window.open(`/consignment/generate/${id}`, '_blank');
                            },
                            error: function (err) {
                                Swal.close();
                                Swal.fire({ icon: 'error', title: getText('error'), text: err.responseJSON?.message || 'Something went wrong.' });
                            },
                        });
                    });
                } else {
                    window.open(`/consignment/generate/${id}`, '_blank');
                }
            },
            error: function (err) {
                Swal.fire({ icon: 'error', title: getText('error'), text: err.responseJSON?.message || 'Something went wrong.' });
            },
        });
    });
}

// ---------------------------------------------------------------
// Bulk Payment – sends all pending permits in one transaction
// Flat fee of RM 10 per application (not per permit)
// ---------------------------------------------------------------

function payBulk() {
    $(document).off('click', '.pay-bulk').on('click', '.pay-bulk', function (e) {
        e.preventDefault();
        const applicationId = $(this).data('application');
        console.log('bulk payment');

        const permits = window.ImportPermitView?.getPermits() || [];
        const pending = permits.filter(p =>
            ['pending for payment', 'payment failed'].includes(p.status)
        );

        if (!pending.length) {
            Swal.fire({
                icon: 'info',
                title: getLang() === 'bm' ? 'Tiada permit tertunggak' : 'No pending permits',
                text: getLang() === 'bm' ? 'Tiada permit yang memerlukan pembayaran.' : 'There are no permits awaiting payment.',
            });
            return;
        }

        // ─── Flat fee per application ───────────────────────────────────
        const total = CONSIGNMENT_APPLICATION_FEE; // RM 10 flat
        const permitIds = pending.map(p => p.id);
        const amountText = money(total);

        const titleText = getText('payNow.title');
        const textTemplate = getText('payNow.text') || 'You are about to pay RM {amount} for this application.';
        const text = textTemplate.replace('{amount}', amountText);

        Swal.fire({
            title: titleText,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: getText('payNow.confirm'),
            cancelButtonText: getText('payNow.cancel'),
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: getText('processing'), allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '/payment/signed-url',
                method: 'POST',
                data: {
                    application_id: applicationId,
                    permit_ids: permitIds,
                    total: Number(total).toFixed(2),
                    type: 'consignment',
                    _token: csrfToken(),
                },
                success: function (res) {
                    window.location.href = res.url;
                },
                error: function () {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: getText('error'),
                        text: getText('unableCheckout') || 'Unable to proceed to checkout.',
                    });
                },
            });
        });
    });
}

// ---------------------------------------------------------------
// Reapply – reopens the item modal (kept from legacy wizard)
// ---------------------------------------------------------------

let itemDropzone = null;
let updateItem = null;

async function loadConsignmentSelection(selectedItemId = null, countryCode = null) {
    const $select = $('#itemSelect');

    $select.empty().append(`<option value="">${getLang() === 'bm' ? '-- Pilih Item --' : '-- Select Item --'}</option>`);

    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
    }
    $select.prop('disabled', true);

    if (!countryCode) {
        $select.prop('disabled', false);
        $select.select2({ width: '100%', dropdownParent: $('#addItemModal') });
        return;
    }

    Swal.fire({ title: getText('loading'), allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res = await fetch(`${window.baseUrl}/public/get_consignment_certificate/${countryCode}`);
        const data = await res.json();

        $select.prop('disabled', false);
        (data.data || []).forEach((row) => {
            $select.append(`<option value="${row.id}">${row.item_name}</option>`);
        });

        $select.select2({ width: '100%', allowClear: true, dropdownParent: $('#addItemModal') });

        if (selectedItemId) {
            $select.val(String(selectedItemId)).trigger('change');
        }

        Swal.close();
    } catch (e) {
        console.error('Error loading items:', e);
        $select.prop('disabled', false);
        Swal.close();
    }
}

function initItemDropzone($modal) {
    const dropzoneEl = $modal.find('#itemDropzone')[0];
    if (!dropzoneEl) return;

    if (dropzoneEl.dropzone) dropzoneEl.dropzone.destroy();

    itemDropzone = new Dropzone(dropzoneEl, {
        url: '/',
        autoProcessQueue: false,
        maxFilesize: 10,
        acceptedFiles: '.jpg,.jpeg,.png,.pdf',
        addRemoveLinks: true,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    });
}

function saveReapplyItem(permitId) {
    if (!updateItem) {
        Swal.fire('Error', getText('noItem'), 'error');
        return;
    }

    const form = document.querySelector('#wizardForm') || document.querySelector('#addItemModal form');
    const formData = new FormData(form || undefined);

    const { files, ...otherData } = updateItem;
    formData.append('items[0][data]', JSON.stringify(otherData));

    if (files && files.length > 0) {
        files.forEach((file) => {
            formData.append('files[]', file);
            formData.append('file_item_index[]', 0);
        });
    }

    Swal.fire({ title: getText('processing'), allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: '/public/save-consignment/' + permitId,
        type: 'POST',
        data: formData,
        headers: { 'X-CSRF-TOKEN': csrfToken() },
        processData: false,
        contentType: false,
        success: function () {
            Swal.fire({ icon: 'success', title: swalTitle('permitReapply'), timer: 1500, showConfirmButton: false });
            reload();
        },
        error: function () {
            Swal.fire('Error', getText('failedSave'), 'error');
        },
    });
}

function reapply() {
    $(document).off('click', '.reapply').on('click', '.reapply', async function (e) {
        e.preventDefault();

        const id = $(this).data('permit');
        const permits = window.ImportPermitView?.getPermits() || [];
        const permit = permits.find((p) => p.id == id);
        if (!permit) {
            console.warn('Permit not found!');
            return;
        }

        const rawDetail = permit._raw?.consignment_detail || {};

        $('#saveBtn').data('id', id).attr('data-id', id);

        const modalEl = document.getElementById('addItemModal');
        const modal = new bootstrap.Modal(modalEl);

        modalEl.addEventListener('shown.bs.modal', async () => {
            const $modal = $(modalEl);
            initItemDropzone($modal);

            const countryCode = $('#expcountryCode').val();
            await loadConsignmentSelection(rawDetail.item_id, countryCode);

            $modal.find('#itemValue').val(rawDetail.value);
            $modal.find('#itemQuantity').val(rawDetail.quantity);

            $modal.find('#itemPurpose option').each(function () {
                if ($(this).data('description') === rawDetail.purpose) {
                    $(this).prop('selected', true);
                }
            });
            $modal.find('#itemPurpose').trigger('change');

            $modal.find('#itemMeasure').val(rawDetail.measure).trigger('change');

            const itemId = $('#itemSelect').val();
            if (itemId) {
                const $itemUses = $modal.find('#itemUses');
                $itemUses.empty().append(`<option value="">${getLang() === 'bm' ? '-- Pilih Kegunaan --' : '-- Select Uses --'}</option>`);

                try {
                    Swal.fire({ title: getText('loadingUses'), allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                    const res = await fetch(`/public/consignment_uses/${itemId}`);
                    const data = await res.json();
                    const uses = [...new Set(data.data ?? [])];

                    uses.forEach((row) => $itemUses.append(`<option value="${row}">${row}</option>`));

                    if ($itemUses.hasClass('select2-hidden-accessible')) {
                        $itemUses.trigger('change');
                    } else {
                        $itemUses.select2({ width: '100%', allowClear: true, dropdownParent: $modal });
                    }

                    if (rawDetail.uses) $itemUses.val(rawDetail.uses).trigger('change');

                    Swal.close();
                } catch (err) {
                    console.error('Failed to load uses:', err);
                    Swal.close();
                }
            }

            wireReapplySave();
        }, { once: true });

        modal.show();
    });
}

function wireReapplySave() {
    $(document).off('click', '#saveBtn').on('click', '#saveBtn', function (e) {
        e.preventDefault();

        const $modal = $('#addItemModal');
        const id = $(this).data('id');

        const itemSelectValue = $modal.find('#itemSelect').val();
        const itemSelectText = $modal.find('#itemSelect option:selected').text();
        const itemValue = $modal.find('#itemValue').val().trim();
        const itemQuantity = $modal.find('#itemQuantity').val().trim();
        const itemMeasure = $modal.find('#itemMeasure').val();
        const itemPurpose = $modal.find('#itemPurpose option:selected').text();
        const itemUsesValue = $modal.find('#itemUses').val();

        if (!itemSelectValue || !itemValue || !itemQuantity || !itemMeasure || !itemPurpose || !itemUsesValue) {
            Swal.fire('Error', getText('required'), 'error');
            return;
        }

        const files = itemDropzone?.getAcceptedFiles() || [];

        updateItem = {
            item_id: itemSelectValue,
            item_name: itemSelectText,
            value: itemValue,
            quantity: itemQuantity,
            measure: itemMeasure,
            purpose: itemPurpose,
            uses: itemUsesValue,
            files,
        };

        saveReapplyItem(id);

        $('#itemValue, #itemQuantity').val('');
        $('#itemSelect').val(null).trigger('change');
        $('#itemMeasure, #itemPurpose').val('').trigger('change');
        $('#itemUses').val(null).trigger('change');
        if (itemDropzone) itemDropzone.removeAllFiles(true);

        bootstrap.Modal.getInstance($modal[0])?.hide();
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

    acceptCertificates();
    rejectCertificates();
    generatePermit();
    reapply();
    payBulk(); // <-- added
}

document.addEventListener('DOMContentLoaded', initActions);

export { getText, swalTitle, swalText, swalConfirm, swalCancel };