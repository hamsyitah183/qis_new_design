import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
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
    errorExclamation: { en: 'Error!', bm: 'Ralat!' },
    actionFailed: { en: 'Action failed', bm: 'Tindakan gagal' },
    verifyError: { en: 'Failed to load verification info.', bm: 'Gagal memuatkan maklumat pengesahan.' },
    loadingVerification: { en: 'Loading verification...', bm: 'Memuatkan maklumat pengesahan...' },
    acceptDoc: { en: 'Accept this document?', bm: 'Terima dokumen ini?' },
    acceptDocText: { en: 'This will mark the file as reviewed and accepted.', bm: 'Ini akan menanda fail ini sebagai telah disemak dan diterima.' },
    accepted: { en: 'Accepted', bm: 'Diterima' },
    reasonRequired: { en: 'Reason is required', bm: 'Sebab diperlukan' },
    rejected: { en: 'Rejected', bm: 'Ditolak' },
    acceptAllDocs: { en: 'Accept all documents?', bm: 'Terima semua dokumen?' },
    acceptAllDocsText: { en: 'This will accept all required documents and verify the user.', bm: 'Ini akan menerima semua dokumen yang diperlukan dan mengesahkan pengguna.' },
    approved: { en: 'Approved!', bm: 'Diluluskan!' },
    userVerified: { en: 'User verified.', bm: 'Pengguna disahkan.' },
    userRejected: { en: 'User rejected.', bm: 'Pengguna ditolak.' },
    noFileSelected: { en: 'No file selected', bm: 'Tiada fail dipilih' },
    pleaseSelectFile: { en: 'Please select a file first.', bm: 'Sila pilih fail dahulu.' },
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}

let verificationTable = null;

// ---------- Offcanvas state ----------
let vdAttachments = [];
let vdCurrentIndex = 0;
let vdOffcanvas = null;
let vdUser = null;
let vdDocTypeFilter = null;

// ---------- Attachment List Offcanvas ----------
let attachmentListOffcanvas = null;
let currentListUserId = null;

// ---------- Helpers ----------
function escapeHtml(text) {
    if (!text) return "—";
    return String(text).replace(/[&<>"']/g, function (c) {
        return {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;",
        }[c];
    });
}

function formatFileSize(bytes) {
    if (!bytes) return "—";
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
}

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
    const el = document.getElementById("verificationOffcanvas");
    if (el && !vdOffcanvas) {
        vdOffcanvas = new bootstrap.Offcanvas(el, {
            backdrop: true,
            keyboard: true,
        });
    }
}

function initAttachmentListOffcanvas() {
    const el = document.getElementById("attachmentListOffcanvas");
    if (el && !attachmentListOffcanvas) {
        attachmentListOffcanvas = new bootstrap.Offcanvas(el, {
            backdrop: true,
            keyboard: true,
        });
    }
}

// ---------- Render attachment in verification offcanvas ----------
function renderVdAttachment() {
    const file = vdAttachments[vdCurrentIndex];
    if (!file) {
        $("#vdAttachmentViewer").html(
            '<div class="text-muted text-center py-5"><i class="bi bi-file-earmark-fill fs-1"></i><br>No file selected.</div>'
        );
        $("#vdAttachmentDetails").empty();
        updateAttachmentActions(null);
        return;
    }

    const container = $("#vdAttachmentViewer");
    const path = file.file_path || "";
    const name = file.original_file_name || "Document";

    const ext = (path || "").split(".").pop().toLowerCase();
    const isImage = ["jpg", "jpeg", "png", "gif", "webp", "bmp", "svg"].includes(ext);
    const isPdf = ext === "pdf";

    let html = "";
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

    const detailsContainer = $("#vdAttachmentDetails");
    detailsContainer.html(`
        <div class="detail-row"><span class="detail-label">User</span><span class="detail-value">${vdUser ? escapeHtml(vdUser.fullname) : "—"}</span></div>
        <div class="detail-row"><span class="detail-label">IC</span><span class="detail-value">${vdUser ? escapeHtml(vdUser.no_ic) : "—"}</span></div>
        <div class="detail-row"><span class="detail-label">Document Type</span><span class="detail-value">${escapeHtml(file.document_type || "—")}</span></div>
        <div class="detail-row"><span class="detail-label">File Name</span><span class="detail-value">${escapeHtml(name)}</span></div>
        <div class="detail-row"><span class="detail-label">File Type</span><span class="detail-value">${escapeHtml(file.file_type || "Unknown")}</span></div>
        <div class="detail-row"><span class="detail-label">File Size</span><span class="detail-value">${formatFileSize(file.file_size)}</span></div>
        <div class="detail-row"><span class="detail-label">Uploaded At</span><span class="detail-value">${formatTime(file.created_at)}</span></div>
        ${file.valid_from ? `<div class="detail-row"><span class="detail-label">Valid From</span><span class="detail-value">${file.valid_from}</span></div>` : ""}
        ${file.valid_until ? `<div class="detail-row"><span class="detail-label">Valid Until</span><span class="detail-value">${file.valid_until}</span></div>` : ""}
        ${file.is_read ? `<div class="detail-row"><span class="detail-label">Reviewed</span><span class="detail-value">Yes</span></div>` : `<div class="detail-row"><span class="detail-label">Reviewed</span><span class="detail-value">No</span></div>`}
        ${file.rejected_reason ? `<div class="detail-row"><span class="detail-label">Rejection Reason</span><span class="detail-value text-danger">${escapeHtml(file.rejected_reason)}</span></div>` : ""}
    `);

    const total = vdAttachments.length;
    $("#vdCounter").text(`${vdCurrentIndex + 1} / ${total}`);
    $("#vdPrevBtn, #vdNextBtn").prop("disabled", false);
    $("#vdPrevBtn").prop("disabled", vdCurrentIndex === 0);
    $("#vdNextBtn").prop("disabled", vdCurrentIndex === total - 1);

    updateAttachmentActions(file);
}

function updateAttachmentActions(file) {
    const $acceptBtn = $("#vdAcceptBtn");
    const $rejectBtn = $("#vdRejectBtn");
    const $statusBadge = $("#vdStatusBadge");

    if (!file) {
        $acceptBtn.hide();
        $rejectBtn.hide();
        $statusBadge.hide().text("");
        return;
    }

    if (file.is_read) {
        $acceptBtn.hide();
        $rejectBtn.hide();
        if (file.rejected_reason) {
            $statusBadge.removeClass("bg-success bg-warning").addClass("bg-danger").text("Rejected").show();
        } else {
            $statusBadge.removeClass("bg-danger bg-warning").addClass("bg-success").text("Reviewed").show();
        }
    } else {
        $acceptBtn.show();
        $rejectBtn.show();
        $statusBadge.hide().text("");
    }
}

// ---------- Populate verification offcanvas ----------
function populateVerificationOffcanvas(response, filterDocType = null) {
    if (!response || !response.user) {
        $("#vdAttachmentViewer").html('<div class="alert alert-danger">No user data found</div>');
        return;
    }

    const user = response.user;
    vdUser = user;

    let allAttachments = [];
    if (response.attachments_grouped && Array.isArray(response.attachments_grouped)) {
        response.attachments_grouped.forEach((group) => {
            const docType = group.document_type || "Uncategorized";
            if (group.attachments && Array.isArray(group.attachments)) {
                group.attachments.forEach((att) => {
                    allAttachments.push({
                        ...att,
                        document_type: docType,
                    });
                });
            }
        });
    }

    if (filterDocType) {
        allAttachments = allAttachments.filter((a) => a.document_type === filterDocType);
        $("#verificationOffcanvasLabel").html(`<i class="bi bi-paperclip me-2"></i> ${escapeHtml(filterDocType)}`);
    } else {
        $("#verificationOffcanvasLabel").html(`<i class="bi bi-paperclip me-2"></i> All Attachments`);
    }

    vdAttachments = allAttachments;

    if (!vdAttachments.length) {
        $("#vdAttachmentViewer").html(
            '<div class="text-muted text-center py-5"><i class="bi bi-file-earmark-fill fs-1"></i><br>No attachments for this document type.</div>'
        );
        $("#vdCounter").text("0 / 0");
        $("#vdPrevBtn, #vdNextBtn").prop("disabled", true);
        $("#vdAttachmentDetails").empty();
        return;
    }

    vdCurrentIndex = 0;
    renderVdAttachment();

    const userId = user.uuid;
    $("#vdAcceptBtn").data("id", userId);
    $("#vdRejectBtn").data("id", userId);
}

// ---------- Populate Attachment List Offcanvas ----------
function populateAttachmentListOffcanvas(response, userId) {
    const tbody = $("#attachmentListBody");
    tbody.empty();

    if (!response || !response.user) {
        tbody.html('<tr><td colspan="4" class="text-center text-danger">No user data found.</td></tr>');
        return;
    }

    const user = response.user;
    let allAttachments = [];
    if (response.attachments_grouped && Array.isArray(response.attachments_grouped)) {
        response.attachments_grouped.forEach((group) => {
            const docType = group.document_type || "Uncategorized";
            if (group.attachments && Array.isArray(group.attachments)) {
                group.attachments.forEach((att) => {
                    allAttachments.push({
                        ...att,
                        document_type: docType,
                    });
                });
            }
        });
    }

    if (!allAttachments.length) {
        tbody.html('<tr><td colspan="4" class="text-center text-muted">No attachments found.</td></tr>');
        return;
    }

    // Build table rows
    allAttachments.forEach((att) => {
        const statusBadge = att.is_read
            ? (att.rejected_reason ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-success">Reviewed</span>')
            : '<span class="badge bg-warning text-dark">Pending</span>';

        // ─── Determine which action buttons to show ────────────────
        let actionButtons = '';

        // Always show View button
        actionButtons += `
            <button class="btn btn-sm btn btn-icon btn-info-light list-view-btn" data-id="${att.id}" data-user-id="${userId}" title="View">
                <i class="bi bi-eye"></i>
            </button>
        `;

        // Show Accept & Reject only if not reviewed and not rejected
        if (!att.is_read && !att.rejected_reason) {
            actionButtons += `
                <button class="btn btn-sm btn btn-icon btn-success-light list-accept-btn" data-id="${att.id}" data-user-id="${userId}" title="Accept">
                    <i class="bi bi-check-lg"></i>
                </button>
                <button class="btn btn-sm btn btn-icon btn-danger-light list-reject-btn" data-id="${att.id}" data-user-id="${userId}" title="Reject">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;
        }

        const row = `
            <tr data-attachment-id="${att.id}">
                <td>${escapeHtml(att.original_file_name || att.file_name || "—")}</td>
                <td>${escapeHtml(att.document_type || "—")}</td>
                <td>${statusBadge}</td>
                <td>
                    <div class="d-flex gap-1">
                        ${actionButtons}
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Store user ID for later use
    currentListUserId = userId;
}

// ---------- Unified Rejection (modal) ----------
function submitRejection(userId, attachmentId, reason, tableInstance) {
    let url, data;
    const csrf = $('meta[name="csrf-token"]').attr('content');

    if (attachmentId) {
        // Per‑attachment rejection
        url = `/internal/verification/attachment/${attachmentId}/reject`;
        data = { reason: reason, _token: csrf };
    } else {
        // User‑level rejection
        url = `/internal/verification/${userId}/save`;
        data = { approved: 'no', reason: reason, _token: csrf };
    }

    showLoader('Rejecting...');

    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        success: function (response) {
            Swal.fire({
                icon: 'success',
                title: getText('rejected'),
                text: response.message || getText('userRejected'),
                timer: 2000,
                showConfirmButton: false,
            });

            // Hide the reject modal
            const modalEl = document.getElementById('rejectModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Reload DataTable
            if (tableInstance) tableInstance.ajax.reload();

            // Refresh offcanvas if open (attachment list)
            if (attachmentListOffcanvas && attachmentListOffcanvas._isShown) {
                fetchVerificationData(userId).then((res) => {
                    populateAttachmentListOffcanvas(res, userId);
                });
            }

            // Refresh verification offcanvas if open
            if (vdOffcanvas && vdOffcanvas._isShown) {
                const currentFilter = vdDocTypeFilter;
                fetchVerificationData(userId).then((res) => {
                    populateVerificationOffcanvas(res, currentFilter);
                });
            }
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: getText('error'),
                text: xhr.responseJSON?.message || getText('actionFailed'),
            });
        },
    });
}

// ---------- Accept handlers (Swal) ----------
function handleSingleAccept(attachmentId, userId) {
    Swal.fire({
        title: getText('acceptDoc'),
        text: getText('acceptDocText'),
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, accept',
    }).then((result) => {
        if (result.isConfirmed) {
            showLoader('Updating...');
            $.ajax({
                url: `/internal/verification/attachment/${attachmentId}/accept`,
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    Swal.fire({ icon: 'success', title: getText('accepted'), timer: 1500, showConfirmButton: false });
                    // Refresh the list offcanvas
                    if (attachmentListOffcanvas && attachmentListOffcanvas._isShown) {
                        fetchVerificationData(userId).then((res) => {
                            populateAttachmentListOffcanvas(res, userId);
                        });
                    }
                    // Refresh verification offcanvas if open
                    if (vdOffcanvas && vdOffcanvas._isShown) {
                        const currentFilter = vdDocTypeFilter;
                        fetchVerificationData(userId).then((res) => {
                            populateVerificationOffcanvas(res, currentFilter);
                        });
                    }
                    if (verificationTable) verificationTable.ajax.reload();
                },
                error: function (xhr) {
                    Swal.fire(getText('error'), xhr.responseJSON?.message || getText('actionFailed'), 'error');
                },
            });
        }
    });
}

function handleBulkAccept(userId, tableInstance) {
    Swal.fire({
        title: getText('acceptAllDocs'),
        text: getText('acceptAllDocsText'),
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, accept all!',
    }).then((result) => {
        if (result.isConfirmed) {
            showLoader('Approving...');
            $.ajax({
                url: `/internal/verification/${userId}/save`,
                method: 'POST',
                data: { approved: 'yes', _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: getText('approved'),
                        text: response.message || getText('userVerified'),
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    fetchVerificationCount();
                    if (tableInstance) tableInstance.ajax.reload();
                    if (vdOffcanvas && vdOffcanvas._isShown) {
                        const currentFilter = vdDocTypeFilter;
                        fetchVerificationData(userId).then((res) => {
                            populateVerificationOffcanvas(res, currentFilter);
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire(getText('error'), xhr.responseJSON?.message || getText('actionFailed'), 'error');
                },
            });
        }
    });
}

// ---------- Document ready ----------
$(document).ready(function () {
    verificationTable = $("#verificationTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({
                    header: function (row) {
                        var data = row.data();
                        return "Details for " + data.fullname;
                    },
                }),
                renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                    tableClass: "table table-bordered",
                }),
            },
        },
        ajax: {
            url: "/internal/user_public/verification/data",
            data: function (d) {
                d.name = $("#filterVerifyName").val() || "";
                d.start_date = $("#filterVerifyStartDate").val() || "";
                d.end_date = $("#filterVerifyEndDate").val() || "";
            },
        },
        columns: [
            { data: "fullname", name: "fullname" },
            { data: "email", name: "email" },
            { data: "status_badge", name: "status_badge", orderable: false, searchable: false },
            { data: "documents", name: "documents", orderable: false, searchable: false },
            { data: "action",  visible: false, name: "action", orderable: false, searchable: false, className: "text-center" },
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

    // ---- View Documents (opens the list offcanvas) ----
    $(document).on("click", ".view-documents-btn", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");
        initAttachmentListOffcanvas();

        // Show loading
        $("#attachmentListBody").html('<tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>');
        attachmentListOffcanvas.show();

        fetchVerificationData(userId)
            .then((response) => {
                populateAttachmentListOffcanvas(response, userId);
            })
            .catch((error) => {
                console.error("Error loading attachments:", error);
                $("#attachmentListBody").html('<tr><td colspan="4" class="text-center text-danger">Failed to load attachments.</td></tr>');
            });
    });

    // ---- List offcanvas: View button (opens verification offcanvas) ----
    $(document).on("click", ".list-view-btn", function (e) {
        e.preventDefault();
        const attachmentId = $(this).data("id");
        const userId = $(this).data("user-id");

        // Find the attachment in the full list (we already have vdAttachments from previous fetch)
        // We'll fetch fresh data to ensure we have the full list
        fetchVerificationData(userId).then((response) => {
            if (!response || !response.user) return;

            let allAttachments = [];
            if (response.attachments_grouped && Array.isArray(response.attachments_grouped)) {
                response.attachments_grouped.forEach((group) => {
                    const docType = group.document_type || "Uncategorized";
                    if (group.attachments && Array.isArray(group.attachments)) {
                        group.attachments.forEach((att) => {
                            allAttachments.push({
                                ...att,
                                document_type: docType,
                            });
                        });
                    }
                });
            }

            const file = allAttachments.find(a => a.id == attachmentId);
            if (!file) {
                Swal.fire(getText('error'), "File not found.", "error");
                return;
            }

            // Now populate the verification offcanvas with just this file
            vdUser = response.user;
            vdAttachments = [file];
            vdCurrentIndex = 0;
            initVdOffcanvas();
            // Set filter to null because we show a single file
            vdDocTypeFilter = null;
            $("#verificationOffcanvasLabel").html(`<i class="bi bi-paperclip me-2"></i> ${escapeHtml(file.document_type || "File")}`);
            renderVdAttachment();
            const userIdForButtons = response.user.uuid;
            $("#vdAcceptBtn").data("id", userIdForButtons);
            $("#vdRejectBtn").data("id", userIdForButtons);
            // Show the offcanvas
            if (vdOffcanvas) vdOffcanvas.show();
        });
    });

    // ---- List offcanvas: Accept button ----
    $(document).on("click", ".list-accept-btn", function (e) {
        e.preventDefault();
        const attachmentId = $(this).data("id");
        const userId = $(this).data("user-id");
        handleSingleAccept(attachmentId, userId);
    });

    // ---- List offcanvas: Reject button (opens reject modal) ----
    $(document).on("click", ".list-reject-btn", function (e) {
        e.preventDefault();
        const attachmentId = $(this).data("id");
        const userId = $(this).data("user-id");
        $('#rejectUserUuid').val(userId);
        $('#rejectAttachmentId').val(attachmentId);
        $('#rejectReason').val('');
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    });

    // ---- Existing: Offcanvas navigation (verificationOffcanvas) ----
    $(document).on("click", "#vdPrevBtn", function () {
        if (vdCurrentIndex > 0) {
            vdCurrentIndex--;
            renderVdAttachment();
        }
    });
    $(document).on("click", "#vdNextBtn", function () {
        if (vdCurrentIndex < vdAttachments.length - 1) {
            vdCurrentIndex++;
            renderVdAttachment();
        }
    });

    // ---- Offcanvas Accept (verificationOffcanvas) ----
    $(document).on("click", "#vdAcceptBtn", function () {
        const file = vdAttachments[vdCurrentIndex];
        if (!file) {
            Swal.fire(getText("noFileSelected"), getText("pleaseSelectFile"), "warning");
            return;
        }
        const userId = $(this).data("id");
        handleSingleAccept(file.id, userId);
    });

    // ---- Offcanvas Reject (verificationOffcanvas) – opens reject modal ----
    $(document).on("click", "#vdRejectBtn", function () {
        const file = vdAttachments[vdCurrentIndex];
        if (!file) {
            Swal.fire(getText("noFileSelected"), getText("pleaseSelectFile"), "warning");
            return;
        }
        const userId = $(this).data("id");
        $('#rejectUserUuid').val(userId);
        $('#rejectAttachmentId').val(file.id);
        $('#rejectReason').val('');
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    });

    // ---- Table row Accept (bulk) ----
    $(document).on("click", ".accept-btn", function () {
        const userId = $(this).data("id");
        handleBulkAccept(userId, verificationTable);
    });

    // ---- Table row Reject (bulk) – opens reject modal ----
    $(document).on("click", ".reject-btn", function () {
        const userId = $(this).data("id");
        $('#rejectUserUuid').val(userId);
        $('#rejectAttachmentId').val('');
        $('#rejectReason').val('');
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    });

    // ---- Reject form submission (unified) ----
    $('#confirmRejectBtn').on('click', function (e) {
        e.preventDefault();
        const userId = $('#rejectUserUuid').val();
        const attachmentId = $('#rejectAttachmentId').val();
        const reason = $('#rejectReason').val().trim();

        if (!reason) {
            Swal.fire({
                icon: 'warning',
                title: getText('error'),
                text: getText('reasonRequired'),
            });
            return;
        }

        submitRejection(userId, attachmentId, reason, verificationTable);
    });

    // ---- Legacy modal buttons (if any) ----
    $(document).on("click", "#verificationBtn", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");
        handleBulkAccept(userId, verificationTable);
    });
    $(document).on("click", "#unverificationBtn", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");
        $('#rejectUserUuid').val(userId);
        $('#rejectAttachmentId').val('');
        $('#rejectReason').val('');
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    });
});