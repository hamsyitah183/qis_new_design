import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import { fetchVerificationCount, formatTime } from "../../../app";

let verificationTable = null;

// ---------- Offcanvas state ----------
let vdAttachments = [];
let vdCurrentIndex = 0;
let vdOffcanvas = null;
let vdUser = null; // stores the public_user object

// ---------- Helpers ----------
function escapeHtml(text) {
    if (!text) return '—';
    return String(text).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

function formatFileSize(bytes) {
    if (!bytes) return '—';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function showLoader(text = "Loading...") {
    Swal.fire({
        title: text,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });
}

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

// ---------- Offcanvas init ----------
function initVdOffcanvas() {
    const el = document.getElementById('verificationOffcanvas');
    if (el && !vdOffcanvas) {
        vdOffcanvas = new bootstrap.Offcanvas(el, { backdrop: true, keyboard: true });
    }
}

// ---------- Render attachment in offcanvas ----------
function renderVdAttachment() {
    const file = vdAttachments[vdCurrentIndex];
    if (!file) {
        $("#vdAttachmentViewer").html(
            '<div class="text-muted text-center py-5"><i class="bi bi-file-earmark-fill fs-1"></i><br>No file selected.</div>'
        );
        $("#vdAttachmentDetails").empty();
        return;
    }

    // Viewer
    const container = $("#vdAttachmentViewer");
    const path = file.file_path || '';
    const name = file.original_file_name || 'Document';

    const ext = (path || '').split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext);
    const isPdf = ext === 'pdf';

    let html = '';
    if (isImage) {
        html = `<img src="${path}" alt="${escapeHtml(name)}" class="img-fluid">`;
    } else if (isPdf) {
        html = `<iframe src="${path}" class="w-100" style="height:500px;" frameborder="0"></iframe>`;
    } else {
        html = `
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-fill fs-1 d-block mb-3 text-muted"></i>
                <p class="text-muted">Preview not available for this file type.</p>
                <a href="${path}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </div>
        `;
    }
    container.html(html);

    // Details tab
    const detailsContainer = $("#vdAttachmentDetails");
    detailsContainer.html(`
        <div class="detail-row"><span class="detail-label">User</span><span class="detail-value">${vdUser ? escapeHtml(vdUser.fullname) : '—'}</span></div>
        <div class="detail-row"><span class="detail-label">IC</span><span class="detail-value">${vdUser ? escapeHtml(vdUser.no_ic) : '—'}</span></div>
        <div class="detail-row"><span class="detail-label">File Name</span><span class="detail-value">${escapeHtml(name)}</span></div>
        <div class="detail-row"><span class="detail-label">File Type</span><span class="detail-value">${escapeHtml(file.file_type || 'Unknown')}</span></div>
        <div class="detail-row"><span class="detail-label">File Size</span><span class="detail-value">${formatFileSize(file.file_size)}</span></div>
        <div class="detail-row"><span class="detail-label">Document Type</span><span class="detail-value">${escapeHtml(file.document_type || '—')}</span></div>
        <div class="detail-row"><span class="detail-label">Uploaded At</span><span class="detail-value">${formatTime(file.created_at)}</span></div>
        ${file.valid_from ? `<div class="detail-row"><span class="detail-label">Valid From</span><span class="detail-value">${file.valid_from}</span></div>` : ''}
        ${file.valid_until ? `<div class="detail-row"><span class="detail-label">Valid Until</span><span class="detail-value">${file.valid_until}</span></div>` : ''}
    `);

    // Update counter
    const total = vdAttachments.length;
    $("#vdCounter").text(`${vdCurrentIndex + 1} / ${total}`);
    $("#vdPrevBtn, #vdNextBtn").prop('disabled', false);
    $("#vdPrevBtn").prop('disabled', vdCurrentIndex === 0);
    $("#vdNextBtn").prop('disabled', vdCurrentIndex === total - 1);
}

// ---------- Populate offcanvas with verification data ----------
function populateVerificationOffcanvas(response) {
    if (!response || !response.public_user) {
        $("#vdAttachmentViewer").html(
            '<div class="alert alert-danger">No data found</div>'
        );
        return;
    }

    const user = response.public_user;
    const approved = response.approved || {};

    // Store user for details tab
    vdUser = user;

    // Footer info
    $("#vdFullname").text(user.fullname || '—');
    $("#vdIc").text(user.no_ic || '—');
    const status = approved.status || 'Unknown';
    $("#vdStatus").text(status);

    // Attachments from the `user_attachments` array
    vdAttachments = response.user_attachments || [];
    if (!vdAttachments.length) {
        $("#vdAttachmentViewer").html(
            '<div class="text-muted text-center py-5"><i class="bi bi-file-earmark-fill fs-1"></i><br>No attachments uploaded.</div>'
        );
        $("#vdCounter").text("0 / 0");
        $("#vdPrevBtn, #vdNextBtn").prop('disabled', true);
        return;
    }

    vdCurrentIndex = 0;
    renderVdAttachment();

    // Set Accept/Reject buttons
    const userId = user.uuid;
    $("#vdAcceptBtn").data('id', userId);
    $("#vdRejectBtn").data('id', userId);
}

// ---------- Handle Accept ----------
function handleAccept(userId, tableInstance, offcanvasId = null) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to accept this verification.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, accept it!'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoader("Approving...");

            $.ajax({
                url: `/internal/verification/${userId}/save`,
                method: "POST",
                data: {
                    approved: "yes",
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Approved!",
                        text: response.message || "User verified.",
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    fetchVerificationCount();

                    if (offcanvasId) {
                        const offcanvasEl = document.getElementById(offcanvasId);
                        const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                        if (offcanvas) offcanvas.hide();
                    }

                    if (tableInstance) tableInstance.ajax.reload();
                },
                error: function (xhr) {
                    Swal.fire("Error", xhr.responseJSON?.message || "Action failed", "error");
                },
            });
        }
    });
}

// ---------- Handle Reject ----------
function handleReject(userId, reason, tableInstance, modalIdToHide = null) {
    showLoader("Rejecting...");

    $.ajax({
        url: `/internal/verification/${userId}/save`,
        method: "POST",
        data: {
            approved: "no",
            reason: reason,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            Swal.fire({
                icon: "success",
                title: "Rejected",
                text: response.message || "User rejected.",
                timer: 2000,
                showConfirmButton: false,
            });

            if (modalIdToHide) {
                const modalEl = document.getElementById(modalIdToHide);
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }

            if (tableInstance) tableInstance.ajax.reload();
            if (verificationTable) verificationTable.ajax.reload();
        },
        error: function (xhr) {
            Swal.fire("Error", xhr.responseJSON?.message || "Action failed", "error");
        },
    });
}

// ---------- Document ready ----------
$(document).ready(function () {
    // DataTable
    verificationTable = $('#verificationTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/internal/user_public/verification/data",
            data: function (d) {
                d.name = $("#filterVerifyName").val() || "";
                d.start_date = $("#filterVerifyStartDate").val() || "";
                d.end_date = $("#filterVerifyEndDate").val() || "";
            },
        },
        columns: [
            { data: 'fullname', name: 'fullname' },
            {
                data: 'verification_attachment',
                name: 'verification_attachment',
                orderable: false,
                searchable: false,
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center',
            },
        ],
    });

    // Filters
    $("#btnVerifyFilter").on("click", function (e) {
        e.preventDefault();
        verificationTable.ajax.reload();
    });
    $("#btnResetVerifyFilter").on("click", function (e) {
        e.preventDefault();
        $("#filterVerifyName").val("");
        $("#filterVerifyStartDate").val("");
        $("#filterVerifyEndDate").val("");
        verificationTable.ajax.reload();
    });

    // ---- View Attachment (opens offcanvas) ----
    $(document).on('click', '.view-attachment', function (e) {
        e.preventDefault();
        const userId = $(this).data('id');
        initVdOffcanvas();

        // Reset offcanvas
        vdAttachments = [];
        vdCurrentIndex = 0;
        vdUser = null;
        $("#vdFullname").text('—');
        $("#vdIc").text('—');
        $("#vdStatus").text('—');
        $("#vdAttachmentViewer").html(
            '<div class="text-muted text-center py-5"><i class="bi bi-file-earmark-fill fs-1"></i><br>Loading...</div>'
        );
        $("#vdAttachmentDetails").empty();
        $("#vdCounter").text('0 / 0');
        $("#vdPrevBtn, #vdNextBtn").prop('disabled', true);

        vdOffcanvas.show();

        showLoader("Loading verification...");
        fetchVerificationData(userId)
            .then((response) => {
                Swal.close();
                populateVerificationOffcanvas(response);
            })
            .catch((error) => {
                console.error("Verification Load Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.responseText || "Failed to load verification info.",
                });
            });
    });

    // ---- Offcanvas navigation ----
    $(document).on('click', '#vdPrevBtn', function () {
        if (vdCurrentIndex > 0) {
            vdCurrentIndex--;
            renderVdAttachment();
        }
    });
    $(document).on('click', '#vdNextBtn', function () {
        if (vdCurrentIndex < vdAttachments.length - 1) {
            vdCurrentIndex++;
            renderVdAttachment();
        }
    });

    // ---- Offcanvas Accept ----
    $(document).on('click', '#vdAcceptBtn', function () {
        const userId = $(this).data('id');
        if (!userId) return;
        handleAccept(userId, verificationTable, 'verificationOffcanvas');
    });

    // ---- Offcanvas Reject ----
    $(document).on('click', '#vdRejectBtn', function () {
        const userId = $(this).data('id');
        if (!userId) return;

        // Close offcanvas
        if (vdOffcanvas) vdOffcanvas.hide();

        // Open reject modal
        $('#rejectUserUuid').val(userId);
        $('#rejectReason').val('');
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        rejectModal.show();
    });

    // ---- Table Accept / Reject ----
    $(document).on('click', '.accept-btn', function () {
        const userId = $(this).data('id');
        handleAccept(userId, verificationTable);
    });

    $(document).on('click', '.reject-btn', function () {
        const userId = $(this).data('id');
        $('#rejectUserUuid').val(userId);
        $('#rejectReason').val('');
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    });

    // ---- Reject form submit ----
    $('#rejectForm').on('submit', function (e) {
        e.preventDefault();
        const userId = $('#rejectUserUuid').val();
        const reason = $('#rejectReason').val();
        handleReject(userId, reason, verificationTable, 'rejectModal');
    });

    // ---- Confirm Reject button (fallback) ----
    $('#confirmRejectBtn').on('click', function (e) {
        e.preventDefault();
        const userId = $('#rejectUserUuid').val();
        const reason = $('#rejectReason').val();
        handleReject(userId, reason, verificationTable, 'rejectModal');
    });

    // ---- Legacy modal buttons (kept for compatibility) ----
    $(document).on('click', '#verificationBtn', function (e) {
        e.preventDefault();
        const userId = $(this).data('id');
        handleAccept(userId, verificationTable, 'verificationModal');
    });
    $(document).on('click', '#unverificationBtn', function (e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const modalEl = document.getElementById('verificationModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        $('#rejectUserUuid').val(userId);
        $('#rejectReason').val('');
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    });
});