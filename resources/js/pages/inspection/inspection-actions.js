/**
 * inspection-actions.js
 * Workflow actions for Inspection Certificate applications — split out of
 * the single inspection_detail.js to match the 3-file pattern used by
 * Import Permit (importPermitActions.js) and Consignment
 * (consignment-actions.js).
 *
 * Endpoints/payloads are carried over as-is from the legacy
 * inspection_detail.js (not guessed):
 *   - POST /internal/inspection/{id}/status   { status }
 *   - POST /public/inspection/{id}/status     { status }   (verify uses the public guard — kept as-is)
 *   - POST /internal/inspection_item/{id}/accept | reject   (id = application id, bulk)
 *   - POST /permit/print { type: 'Inspection', permit_number }, then GET /inspection/generate/{id}
 *   - POST /public/save-inspection/{permitId}
 *   - POST /payment/signed-url { type: 'inspection', application_id: APPLICATION.id }  (numeric PK, not the string application_id)
 */

import $ from "jquery";
import Swal from "sweetalert2";
import Dropzone from "dropzone";
import "dropzone/dist/dropzone.css";
import { PERMITS, APPLICATION, INSPECTION_PERMIT_FEE, INSPECTION_PRINT_TYPE, money, escapeHtml } from "./inspection1.js";

Dropzone.autoDiscover = false;

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

// ─── Application-level status transitions ──────────────────────────

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
                url: `/internal/inspection/${applicationId()}/status`,
                method: 'POST',
                data: { _token: csrfToken(), status: 'Clerk Verified' },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Application Accepted!',
                        text: res.message || 'The application has been successfully accepted.',
                        showConfirmButton: false,
                        position: 'center',
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
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
                url: `/internal/inspection/${applicationId()}/status`,
                method: 'POST',
                data: { _token: csrfToken(), status: 'Rejected', reason: result.value },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Application Rejected!',
                        text: 'The application has been rejected.',
                        showConfirmButton: false,
                        timer: 2000,
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
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
                url: `/public/inspection/${applicationId()}/status`,
                method: 'POST',
                data: { _token: csrfToken(), status: 'Clerk review in-progress' },
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
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
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
                url: `/internal/inspection/${applicationId()}/status`,
                method: 'POST',
                data: { _token: csrfToken(), status: 'Rejected' },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Application Not Approved!',
                        text: 'The application has been successfully marked as not verified.',
                        showConfirmButton: false,
                        position: 'center',
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                },
            });
        });
    });
}

// ─── Bulk item accept/reject ────────────────────────────────────────

function acceptCertificates() {
    $(document).off('click', '.accept').on('click', '.accept', function (e) {
        e.preventDefault();
        const id = $(this).data('application');

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to accept all these inspection items?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
        }).then((firstResult) => {
            if (!firstResult.isConfirmed) return;

            Swal.fire({
                title: 'Please Confirm Again',
                text: 'This action cannot be undone. Accept all the inspection items?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, accept it',
                cancelButtonText: 'Cancel',
            }).then((secondResult) => {
                if (!secondResult.isConfirmed) return;

                $.ajax({
                    url: `/internal/inspection_item/${id}/accept`,
                    method: 'POST',
                    data: { _token: csrfToken() },
                    success: function () {
                        Swal.fire('Accepted!', 'The inspection items have been accepted.', 'success');
                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
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

        Swal.fire({
            title: 'Reject Inspection Items',
            text: 'Please provide a reason for rejecting these inspection items:',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason...',
            showCancelButton: true,
            confirmButtonText: 'Reject Items',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value || value.trim().length < 5) {
                    return 'Rejection reason is required (min 5 characters).';
                }
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/internal/inspection_item/${id}/reject`,
                method: 'POST',
                data: { _token: csrfToken(), reason: result.value },
                success: function () {
                    Swal.fire('Rejected!', 'The inspection items have been rejected successfully.', 'success');
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                },
            });
        });
    });
}

// ─── Certificate download ────────────────────────────────────────────

function generatePermit() {
    $(document).off('click', '.generatePermit').on('click', '.generatePermit', function (e) {
        e.preventDefault();
        const id = $(this).data('permit');

        $.ajax({
            url: `/permit/print`,
            method: 'POST',
            data: { _token: csrfToken(), type: INSPECTION_PRINT_TYPE, permit_number: id },
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
                            data: { _token: csrfToken(), type: INSPECTION_PRINT_TYPE, permit_number: id, reason: result.value },
                            success: function () {
                                Swal.close();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Submitted!',
                                    text: 'The reason submitted successfully.',
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                                setTimeout(() => {
                                    window.open(`/inspection/generate/${id}`, '_blank');
                                }, 500);
                            },
                            error: function (err) {
                                Swal.close();
                                Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                            },
                        });
                    });
                } else {
                    window.open(`/inspection/generate/${id}`, '_blank');
                }
            },
            error: function (err) {
                Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
            },
        });
    });
}

// ─── Bulk payment ─────────────────────────────────────────────────────

function payBulk() {
    $(document).off('click', '.pay-bulk').on('click', '.pay-bulk', function (e) {
        e.preventDefault();

        const permits = window.ImportPermitView?.getPermits() || PERMITS;
        const pending = permits.filter(p => ['pending for payment', 'payment failed'].includes(p.status));

        if (!pending.length) {
            Swal.fire({
                icon: 'info',
                title: 'No pending permits',
                text: 'There are no permits awaiting payment.',
            });
            return;
        }

        const total = pending.length * INSPECTION_PERMIT_FEE;
        const permitIds = pending.map(p => p.id);
        const amountText = money(total);
        const app = window.ImportPermitView?.getApplication() || APPLICATION;

        Swal.fire({
            title: 'Proceed to Payment?',
            text: `You are about to pay RM ${amountText} for ${pending.length} permit(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed to payment',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '/payment/signed-url',
                method: 'POST',
                data: {
                    application_id: app.id,
                    permit_ids: permitIds,
                    total: Number(total).toFixed(2),
                    type: 'inspection',
                    _token: csrfToken(),
                },
                success: function (res) {
                    window.location.href = res.url;
                },
                error: function () {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Unable to proceed to checkout.' });
                },
            });
        });
    });
}

// ─── Reapply ──────────────────────────────────────────────────────────
// The legacy modal tried to pre-select #itemSelect by item *name* against
// an empty, never-populated <select> — a no-op bug. Fixed here by seeding
// the select with the existing item as its only option (the item itself
// isn't meant to change on reapply, only quantity/value/purpose/uses/
// attachments), instead of inventing an items-by-country endpoint that was
// never evidenced for Inspection.

let itemDropzone = null;
let updateItem = null;

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
        processing: function () {
            Swal.fire({
                title: 'Uploading...',
                html: 'Please wait while your file is being uploaded.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });
            groupPreview();
        },
    });

    itemDropzone.on('addedfile', function () {
        groupPreview();
    });
}

function groupPreview() {
    setTimeout(function () {
        const $dropzone = $('#itemDropzone');
        const $previews = $dropzone.find('.dz-preview');
        const $deleteBtns = $previews.find('.dz-remove');

        let $group = $dropzone.find('.dz-preview-group');
        if ($group.length === 0) {
            $group = $('<div class="dz-preview-group"></div>');
            $dropzone.find('.dz-message').after($group);
        }
        $previews.appendTo($group);

        if (itemDropzone) {
            for (const file of itemDropzone.getAcceptedFiles()) {
                if (file.type === 'application/pdf') {
                    const $preview = $(file.previewElement);
                    const $img = $preview.find('.dz-image img[data-dz-thumbnail]');
                    $img.attr('src', '/images/pdf-logo.png');
                    $img.css({ 'object-fit': 'contain', width: '100%', height: '100%' });
                }
            }
        }

        $deleteBtns.html('<i class="ti ti-trash"></i>');
        Swal.close();
    }, 100);
}

function reapply() {
    $(document).off('click', '.reapply').on('click', '.reapply', async function (e) {
        e.preventDefault();

        const id = $(this).data('permit');
        const permits = window.ImportPermitView?.getPermits() || PERMITS;
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

            const $select = $modal.find('#itemSelect');
            $select.empty();
            $select.append(`<option value="${rawDetail.item_id ?? ''}">${escapeHtml(rawDetail.item_name || permit.consignment_detail.item_name)}</option>`);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.trigger('change');
            } else {
                $select.select2({ width: '100%', dropdownParent: $modal });
            }

            $modal.find('#itemValue').val(permit.value ?? rawDetail.value);
            $modal.find('#itemQuantity').val(permit.quantity ?? rawDetail.quantity);

            $modal.find('#itemPurpose option').each(function () {
                if ($(this).data('description') === (permit.consignment_detail.purpose || rawDetail.purpose)) {
                    $(this).prop('selected', true);
                }
            });
            $modal.find('#itemPurpose').trigger('change');

            $modal.find('#itemMeasure').val(permit.unit_measurement || rawDetail.measure).trigger('change');

            const $itemUses = $modal.find('#itemUses');
            $itemUses.empty().append(`<option value="">-- Select Uses --</option>`);
            const usesValue = rawDetail.uses;
            if (usesValue) {
                $itemUses.append(`<option value="${escapeHtml(usesValue)}">${escapeHtml(usesValue)}</option>`);
                $itemUses.val(usesValue);
            }
            if ($itemUses.hasClass('select2-hidden-accessible')) {
                $itemUses.trigger('change');
            } else {
                $itemUses.select2({ width: '100%', dropdownParent: $modal });
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

        saveReapplyItem(id);

        $('#itemValue, #itemQuantity').val('');
        $('#itemSelect').val(null).trigger('change');
        $('#itemMeasure, #itemPurpose').val('').trigger('change');
        $('#itemUses').val(null).trigger('change');
        if (itemDropzone) itemDropzone.removeAllFiles(true);

        bootstrap.Modal.getInstance($modal[0])?.hide();
    });
}

function saveReapplyItem(permitId) {
    if (!updateItem) {
        Swal.fire('Error', 'No item to save', 'error');
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

    Swal.fire({ title: 'Submitting...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: '/public/save-inspection/' + permitId,
        type: 'POST',
        data: formData,
        headers: { 'X-CSRF-TOKEN': csrfToken() },
        processData: false,
        contentType: false,
        success: function () {
            Swal.fire({ icon: 'success', title: 'Permit Reapply!', timer: 1500, showConfirmButton: false });
            reload();
        },
        error: function () {
            Swal.fire('Error', 'Failed to save permit', 'error');
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

    acceptCertificates();
    rejectCertificates();
    generatePermit();
    reapply();
    payBulk();
}

document.addEventListener('DOMContentLoaded', initActions);