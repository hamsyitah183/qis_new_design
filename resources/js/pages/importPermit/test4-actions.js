/**
 * test4-actions.js
 * ------------------------------------------------------------------
 * Application- and permit-level workflow actions, ported from the old
 * application_detail2.js (document 19) onto the new import_permit_view
 * UI. Buttons this file listens for:
 *
 *   Application-level (server-gated, rendered in the Actions Bar):
 *     #acceptAppl, #rejectAdminAppl, #verifyAppl, #rejectAppl
 *
 *   Permit-level (rendered per-permit by test1.js's permitActionsHtml()):
 *     .accept, .reject, .reapply, .generatePermit  (data-permit attr)
 *
 * Application-level actions change which server-gated buttons should
 * render next (blade @if conditions), so those still do a full
 * window.location.reload() after success — same as the old code.
 * Permit-level actions only change data the client already renders, so
 * those call window.ImportPermitView.reload() to refresh in place
 * without a full page reload.
 */

import Dropzone from "dropzone";
import $ from "jquery";
import Swal from "sweetalert2";
import "dropzone/dist/dropzone.css";
import select2 from "select2";
select2(window.jQuery);
import "select2/dist/css/select2.min.css";

Dropzone.autoDiscover = false;

function applicationId() {
    return window.APPLICATION_ID || window.ImportPermitView?.getApplication()?.application_id;
}

function csrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

// ---------------------------------------------------------------
// Application-level actions
// ---------------------------------------------------------------

function acceptApplication() {
    $('#acceptAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Accept Application?',
            text: 'Are you sure you want to accept this application?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, accept it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/application/verify/${applicationId()}`,
                method: 'POST',
                data: { _token: csrfToken(), accepted: 1 },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Application Verified!',
                        text: res.message || 'The application has been successfully verified.',
                        showConfirmButton: false,
                        position: 'center',
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire('Error!', err.responseJSON?.message || 'Something went wrong.', 'error');
                },
            });
        });
    });
}

function adminRejectApplication() {
    $('#rejectAdminAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Reject Application',
            html: `
                <p class="mb-2">Please provide a reason for rejection:</p>
                <textarea id="rejectReason" class="swal2-textarea" placeholder="Enter rejection reason..."></textarea>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            focusConfirm: false,
            preConfirm: () => {
                const reason = document.getElementById('rejectReason').value;
                if (!reason.trim()) {
                    Swal.showValidationMessage('Rejection reason is required');
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
                    Swal.fire({ icon: 'success', title: 'Application Rejected!', text: 'The application has been rejected.', showConfirmButton: false, timer: 2000 });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire('Error!', err.responseJSON?.message || 'Something went wrong.', 'error');
                },
            });
        });
    });
}

function verifyApplication() {
    $('#verifyAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Verify Application?',
            text: 'Are you sure you want to verify this application?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, verify it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/application/verify/${applicationId()}`,
                method: 'POST',
                data: { _token: csrfToken(), verified: 1 },
                success: function (res) {
                    Swal.fire({ icon: 'success', title: 'Application Verified!', text: res.message || 'The application has been successfully verified.', showConfirmButton: false, position: 'center' });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire('Error!', err.responseJSON?.message || 'Something went wrong.', 'error');
                },
            });
        });
    });
}

function rejectApplication() {
    $('#rejectAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Reject Application?',
            text: 'Are you sure you want to reject this application?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reject it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/application/verify/${applicationId()}`,
                method: 'POST',
                data: { _token: csrfToken(), not_verified: 1 },
                success: function () {
                    Swal.fire({ icon: 'success', title: 'Application Not Approved!', text: 'The application has been successfully not verified.', showConfirmButton: false, position: 'center' });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire('Error!', err.responseJSON?.message || 'Something went wrong.', 'error');
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
            title: 'Are you sure?',
            text: 'Do you want to accept this permit?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
        }).then((firstResult) => {
            if (!firstResult.isConfirmed) return;

            Swal.fire({
                title: 'Please Confirm Again',
                text: 'This action cannot be undone. Accept the permit?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, accept it',
                cancelButtonText: 'Cancel',
            }).then((secondResult) => {
                if (!secondResult.isConfirmed) return;

                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: `/internal/permit/${id}`,
                    method: 'POST',
                    data: { _token: csrfToken(), accepted: 1 },
                    success: function () {
                        Swal.close();
                        Swal.fire({ icon: 'success', title: 'Accepted!', text: 'The permit has been accepted.', timer: 2000, showConfirmButton: false });
                        window.ImportPermitView?.reload();
                    },
                    error: function (err) {
                        Swal.close();
                        Swal.fire('Error!', err.responseJSON?.message || 'Something went wrong.', 'error');
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

        Swal.fire({
            title: 'Reject Permit',
            text: 'Please provide a reason for rejecting this permit:',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason...',
            showCancelButton: true,
            confirmButtonText: 'Reject Permit',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value || value.trim().length < 5) return 'Rejection reason is required (min 5 characters).';
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: `/internal/permit/${id}`,
                method: 'POST',
                data: { _token: csrfToken(), rejected: 1, reason: result.value },
                success: function () {
                    Swal.close();
                    Swal.fire({ icon: 'success', title: 'Rejected!', text: 'The permit has been rejected successfully.', timer: 2000, showConfirmButton: false });
                    window.ImportPermitView?.reload();
                },
                error: function (err) {
                    Swal.close();
                    Swal.fire('Error!', err.responseJSON?.message || 'Something went wrong.', 'error');
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
                    Swal.fire({
                        title: 'This Permit has been downloaded more than once',
                        text: 'Please provide a reason for downloading it:',
                        icon: 'warning',
                        input: 'textarea',
                        inputPlaceholder: 'Enter reason...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        inputValidator: (value) => {
                            if (!value || value.trim().length < 5) return 'Reason is required (min 5 characters).';
                        },
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                        $.ajax({
                            url: `/permit/print`,
                            method: 'POST',
                            data: { _token: csrfToken(), type: 'Import Permit', permit_number: id, reason: result.value },
                            success: function () {
                                Swal.close();
                                Swal.fire({ icon: 'success', title: 'Submitted!', text: 'The reason submitted successfully.', timer: 2000, showConfirmButton: false });
                                window.ImportPermitView?.reload();
                                window.open(`/permit/generate/${id}`, '_blank');
                            },
                            error: function (err) {
                                Swal.close();
                                Swal.fire('Error!', err.responseJSON?.message || 'Something went wrong.', 'error');
                            },
                        });
                    });
                } else {
                    window.open(`/permit/generate/${id}`, '_blank');
                }
            },
            error: function (err) {
                Swal.fire('Error!', err.responseJSON?.message || 'Something went wrong.', 'error');
            },
        });
    });
}

// ---------------------------------------------------------------
// Single-permit "Pay Now" — used by the payment CTA inside the permit
// detail offcanvas (test2.js) and the inline accordion action button
// (test1.js's permitActionsHtml()). Skips the multi-select checkout
// tab entirely for the common case of paying for just one permit.
// ---------------------------------------------------------------

function payNowSingle() {
    $(document).off('click', '.pd-pay-now').on('click', '.pd-pay-now', function (e) {
        e.preventDefault();

        const id = $(this).data('permit');
        const value = $(this).data('value');

        Swal.fire({
            title: 'Proceed to Payment?',
            text: `You are about to pay RM ${Number(value).toFixed(2)} for this permit.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed to payment',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Redirecting to payment...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '/payment/signed-url',
                method: 'POST',
                data: {
                    application_id: applicationId(),
                    permit_ids: [id],
                    total: Number(value).toFixed(2),
                    type: 'import_permit',
                    _token: csrfToken(),
                },
                success: function (res) {
                    window.location.href = res.url;
                },
                error: function (err) {
                    Swal.close();
                    Swal.fire('Error!', err.responseJSON?.message || 'Unable to proceed to checkout.', 'error');
                },
            });
        });
    });
}

// ---------------------------------------------------------------
// Reapply flow (opens #addItemModal, prefilled with the rejected
// permit's details)
// ---------------------------------------------------------------

let itemDropzone = null;
let updateItem = null;

function itemConsigment($modal) {
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
        processing: function () {
            Swal.fire({ title: 'Uploading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            groupPreview();
        },
    });

    itemDropzone.on('addedfile', function () { groupPreview(); });
}

function groupPreview() {
    Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    setTimeout(function () {
        const $dropzone = $('#itemDropzone');
        const $previews = $dropzone.find('.dz-preview');
        let $group = $dropzone.find('.dz-preview-group');
        if (!$group.length) {
            $group = $('<div class="dz-preview-group"></div>');
            $dropzone.find('.dz-message').after($group);
        }
        $previews.appendTo($group);

        for (const file of itemDropzone.getAcceptedFiles()) {
            if (file.type === 'application/pdf') {
                const $img = $(file.previewElement).find('.dz-image img[data-dz-thumbnail]');
                $img.attr('src', '/images/pdf-logo.png').css({ 'object-fit': 'contain', width: '100%', height: '100%' });
            }
        }

        $dropzone.find('.dz-remove').html('<i class="ti ti-trash"></i>');
        Swal.close();
    }, 100);
}

function resetAddItemModal() {
    $('#itemValue').val('');
    $('#itemQuantity').val('');
    $('#itemSelect').val(null).trigger('change');
    $('#itemMeasure').val('').trigger('change');
    $('#itemPurpose').val('').trigger('change');
    $('#itemUses').val(null).trigger('change');
    if (itemDropzone) itemDropzone.removeAllFiles(true);
}

async function loadConsignmentSelection(selectedItemId = null) {
    const countryCode = $('#expcountryCode').val() || window.ImportPermitView?.getApplication()?.exporter?.country;
    const $select = $('#itemSelect');
    if (!countryCode) return;

    $select.empty().append('<option value="">-- Select Item --</option>');
    if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
    $select.prop('disabled', true);

    Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res = await fetch(`/public/get_consignment/${countryCode}`);
        const data = await res.json();
        $select.prop('disabled', false);

        data.forEach((row) => $select.append(`<option value="${row.id}">${row.entry_display}</option>`));

        $select.select2({ width: '100%', placeholder: '-- Select Item --', allowClear: true, dropdownParent: $('#addItemModal') });

        if (selectedItemId) $select.val(String(selectedItemId)).trigger('change');

        Swal.close();
    } catch (e) {
        $select.prop('disabled', false);
        Swal.close();
    }
}

function reapply() {
    $(document).off('click', '.reapply').on('click', '.reapply', async function (e) {
        e.preventDefault();

        const id = $(this).data('permit');
        const permits = window.ImportPermitView?.getPermits() || [];
        const permit = permits.find((p) => p.id == id);
        if (!permit) return;

        $('#saveBtn').data('id', id).attr('data-id', id);

        const detail = permit._raw?.consignment_detail || {};

        await loadConsignmentSelection(detail.item_id);

        const modalEl = document.getElementById('addItemModal');
        const modal = new bootstrap.Modal(modalEl);

        modalEl.addEventListener('shown.bs.modal', async () => {
            const $modal = $(modalEl);
            itemConsigment($modal);

            $modal.find('#itemValue').val(detail.value);
            $modal.find('#itemQuantity').val(detail.quantity);

            $modal.find('#itemPurpose option').each(function () {
                if ($(this).data('description') === detail.purpose) $(this).prop('selected', true);
            });
            $modal.find('#itemPurpose').trigger('change');

            $modal.find('#itemMeasure').val(detail.measure).trigger('change');

            const itemId = $('#itemSelect').val();
            if (itemId) {
                const $itemUses = $modal.find('#itemUses');
                $itemUses.empty().append('<option value="">-- Select Uses --</option>');

                try {
                    Swal.fire({ title: 'Loading uses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    const res = await fetch(`/public/consignment_uses/${itemId}`);
                    const data = await res.json();
                    let uses = [...new Set(data.data ?? [])];
                    uses.forEach((row) => $itemUses.append(`<option value="${row}">${row}</option>`));

                    if ($itemUses.hasClass('select2-hidden-accessible')) {
                        $itemUses.trigger('change');
                    } else {
                        $itemUses.select2({ width: '100%', placeholder: '-- Select Uses --', allowClear: true, dropdownParent: $modal });
                    }

                    if (detail.uses) $itemUses.val(detail.uses).trigger('change');

                    Swal.close();
                } catch (err) {
                    Swal.close();
                }
            }

            saveConsignmentAttachment();
        }, { once: true });

        modal.show();
    });
}

function saveConsignmentAttachment() {
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
            Swal.fire('Error', 'Please fill all required fields', 'error');
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

        saveApplication(id);
        resetAddItemModal();
        bootstrap.Modal.getInstance($modal[0])?.hide();
    });
}

function saveApplication(permitId) {
    if (!updateItem) {
        Swal.fire('Error', 'No item to save', 'error');
        return;
    }

    const { files, ...otherData } = updateItem;
    const formData = new FormData();
    formData.append('items[0][data]', JSON.stringify(otherData));

    if (files && files.length > 0) {
        files.forEach((file) => {
            formData.append('files[]', file);
            formData.append('file_item_index[]', 0);
        });
    }

    Swal.fire({ title: 'Submitting...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: '/public/save-permit/' + permitId,
        type: 'POST',
        data: formData,
        headers: { 'X-CSRF-TOKEN': csrfToken() },
        processData: false,
        contentType: false,
        success: function () {
            Swal.fire({ icon: 'success', title: 'Permit Reapply!', timer: 1500, showConfirmButton: false });
            window.ImportPermitView?.reload();
        },
        error: function () {
            Swal.fire('Error', 'Failed to save permit', 'error');
        },
    });
}

// ---------------------------------------------------------------
// Wire everything up once the DOM (and test1.js's initial render)
// is ready.
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
    reapply();
}

document.addEventListener('DOMContentLoaded', initActions);