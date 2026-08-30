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
    rejectDoc: { en: 'Reject this document?', bm: 'Tolak dokumen ini?' },
    reasonForRejection: { en: 'Reason for rejection', bm: 'Sebab penolakan' },
    reasonRequired: { en: 'Reason is required', bm: 'Sebab diperlukan' },
    rejected: { en: 'Rejected', bm: 'Ditolak' },
    acceptAllDocs: { en: 'Accept all documents?', bm: 'Terima semua dokumen?' },
    acceptAllDocsText: { en: 'This will accept all required documents and verify the user.', bm: 'Ini akan menerima semua dokumen yang diperlukan dan mengesahkan pengguna.' },
    approved: { en: 'Approved!', bm: 'Diluluskan!' },
    userVerified: { en: 'User verified.', bm: 'Pengguna disahkan.' },
    rejectAllDocs: { en: 'Reject user?', bm: 'Tolak pengguna?' },
    rejectAllDocsText: { en: 'This will reject the verification application.', bm: 'Ini akan menolak permohonan pengesahan.' },
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

// ---------- Render attachment in offcanvas ----------
function renderVdAttachment() {
    const file = vdAttachments[vdCurrentIndex];
    if (!file) {
        $("#vdAttachmentViewer").html(
            '<div class="text-muted text-center py-5"><i class="bi bi-file-earmark-fill fs-1"></i><br>No file selected.</div>',
        );
        $("#vdAttachmentDetails").empty();
        updateAttachmentActions(null);
        return;
    }

    const container = $("#vdAttachmentViewer");
    const path = file.file_path || "";
    const name = file.original_file_name || "Document";

    const ext = (path || "").split(".").pop().toLowerCase();
    const isImage = [
        "jpg",
        "jpeg",
        "png",
        "gif",
        "webp",
        "bmp",
        "svg",
    ].includes(ext);
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

    // ─── Update actions based on file status ───
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
        // Hide buttons
        $acceptBtn.hide();
        $rejectBtn.hide();

        // Show status badge
        if (file.rejected_reason) {
            $statusBadge
                .removeClass("bg-success bg-warning")
                .addClass("bg-danger")
                .text("Rejected")
                .show();
        } else {
            $statusBadge
                .removeClass("bg-danger bg-warning")
                .addClass("bg-success")
                .text("Reviewed")
                .show();
        }
    } else {
        // Show buttons
        $acceptBtn.show();
        $rejectBtn.show();

        // Hide badge
        $statusBadge.hide().text("");
    }
}

// ---------- Populate offcanvas ----------
function populateVerificationOffcanvas(response, filterDocType = null) {
    if (!response || !response.user) {
        $("#vdAttachmentViewer").html(
            '<div class="alert alert-danger">No user data found</div>',
        );
        return;
    }

    const user = response.user;
    const verification = response.verification || {};
    vdUser = user;

    if ($("#vdFullname").length) $("#vdFullname").text(user.fullname || "—");
    if ($("#vdIc").length) $("#vdIc").text(user.no_ic || "—");
    const status = verification.status || "Unknown";
    if ($("#vdStatus").length) $("#vdStatus").text(status);

    let allAttachments = [];
    if (
        response.attachments_grouped &&
        Array.isArray(response.attachments_grouped)
    ) {
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
        allAttachments = allAttachments.filter(
            (a) => a.document_type === filterDocType,
        );
        $("#verificationOffcanvasLabel").html(
            `<i class="bi bi-paperclip me-2"></i> ${escapeHtml(filterDocType)}`,
        );
    } else {
        $("#verificationOffcanvasLabel").html(
            `<i class="bi bi-paperclip me-2"></i> All Attachments`,
        );
    }

    vdAttachments = allAttachments;

    if (!vdAttachments.length) {
        $("#vdAttachmentViewer").html(
            '<div class="text-muted text-center py-5"><i class="bi bi-file-earmark-fill fs-1"></i><br>No attachments for this document type.</div>',
        );
        $("#vdCounter").text("0 / 0");
        $("#vdPrevBtn, #vdNextBtn").prop("disabled", true);
        $("#vdAttachmentDetails").empty();
        return;
    }

    vdCurrentIndex = 0;
    renderVdAttachment();

    const userId = user.uuid;
    // Store user ID on both buttons for potential bulk actions (if needed)
    $("#vdAcceptBtn").data("id", userId);
    $("#vdRejectBtn").data("id", userId);
}

// ---------- PER-ATTACHMENT ACCEPT (offcanvas) ----------
function handleSingleAccept(attachmentId, userId) {
    Swal.fire({
        title: getText("acceptDoc"),
        text: getText("acceptDocText"),
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, accept",
    }).then((result) => {
        if (result.isConfirmed) {
            showLoader("Updating...");
            $.ajax({
                url: `/internal/verification/attachment/${attachmentId}/accept`,
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: getText("accepted"),
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    // Refresh offcanvas content (keep it open)
                    const currentFilter = vdDocTypeFilter;
                    fetchVerificationData(userId).then((res) => {
                        populateVerificationOffcanvas(res, currentFilter);
                    });
                    if (verificationTable) verificationTable.ajax.reload();
                },
                error: function (xhr) {
                    Swal.fire(
                        getText("error"),
                        xhr.responseJSON?.message || getText("actionFailed"),
                        "error",
                    );
                },
            });
        }
    });
}

// ---------- PER-ATTACHMENT REJECT (offcanvas) ----------
function handleSingleReject(attachmentId, userId) {
    Swal.fire({
        title: getText("rejectDoc"),
        input: "textarea",
        inputLabel: getText("reasonForRejection"),
        inputPlaceholder: "Enter reason...",
        inputAttributes: { required: true },
        showCancelButton: true,
        confirmButtonText: "Reject",
        confirmButtonColor: "#d33",
        preConfirm: (reason) => {
            if (!reason || reason.trim() === "") {
                Swal.showValidationMessage(getText("reasonRequired"));
                return false;
            }
            return reason.trim();
        },
    }).then((result) => {
        if (result.isConfirmed) {
            const reason = result.value;
            showLoader("Updating...");
            $.ajax({
                url: `/internal/verification/attachment/${attachmentId}/reject`,
                method: "POST",
                data: {
                    reason: reason,
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: getText("rejected"),
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    // Refresh offcanvas content (keep it open)
                    const currentFilter = vdDocTypeFilter;
                    fetchVerificationData(userId).then((res) => {
                        populateVerificationOffcanvas(res, currentFilter);
                    });
                    if (verificationTable) verificationTable.ajax.reload();
                },
                error: function (xhr) {
                    Swal.fire(
                        getText("error"),
                        xhr.responseJSON?.message || getText("actionFailed"),
                        "error",
                    );
                },
            });
        }
    });
}

// ---------- BULK ACCEPT (table rows) ----------
function handleBulkAccept(userId, tableInstance) {
    Swal.fire({
        title: getText("acceptAllDocs"),
        text: getText("acceptAllDocsText"),
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, accept all!",
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
                        title: getText("approved"),
                        text: response.message || getText("userVerified"),
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    fetchVerificationCount();
                    if (tableInstance) tableInstance.ajax.reload();

                    // If offcanvas is open for this user, refresh it too
                    if (vdOffcanvas && vdOffcanvas._isShown) {
                        const currentFilter = vdDocTypeFilter;
                        fetchVerificationData(userId).then((res) => {
                            populateVerificationOffcanvas(res, currentFilter);
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire(
                        getText("error"),
                        xhr.responseJSON?.message || getText("actionFailed"),
                        "error",
                    );
                },
            });
        }
    });
}

// ---------- BULK REJECT (table rows) ----------
function handleBulkReject(userId, reason, tableInstance) {
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
                title: getText("rejected"),
                text: response.message || getText("userRejected"),
                timer: 2000,
                showConfirmButton: false,
            });

            // Hide the reject modal
            const modalEl = document.getElementById("rejectModal");
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            if (tableInstance) tableInstance.ajax.reload();

            // If offcanvas is open, refresh it
            if (vdOffcanvas && vdOffcanvas._isShown) {
                const currentFilter = vdDocTypeFilter;
                fetchVerificationData(userId).then((res) => {
                    populateVerificationOffcanvas(res, currentFilter);
                });
            }
        },
        error: function (xhr) {
            Swal.fire(
                getText("error"),
                xhr.responseJSON?.message || getText("actionFailed"),
                "error",
            );
        },
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
            {
                data: "status_badge",
                name: "status_badge",
                orderable: false,
                searchable: false,
            },
            {
                data: "documents",
                name: "documents",
                orderable: false,
                searchable: false,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center",
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

    // ---- View All ----
    $(document).on("click", ".view-attachment", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");
        initVdOffcanvas();

        vdAttachments = [];
        vdCurrentIndex = 0;
        vdUser = null;
        vdDocTypeFilter = null;
        $("#verificationOffcanvasLabel").html(
            '<i class="bi bi-paperclip me-2"></i> All Attachments',
        );
        if ($("#vdFullname").length) $("#vdFullname").text("—");
        if ($("#vdIc").length) $("#vdIc").text("—");
        if ($("#vdStatus").length) $("#vdStatus").text("—");
        $("#vdAttachmentViewer").html(
            '<div class="text-muted text-center py-5"><i class="bi bi-file-earmark-fill fs-1"></i><br>Loading...</div>',
        );
        $("#vdAttachmentDetails").empty();
        $("#vdCounter").text("0 / 0");
        $("#vdPrevBtn, #vdNextBtn").prop("disabled", true);

        vdOffcanvas.show();

        showLoader(getText("loadingVerification"));
        fetchVerificationData(userId)
            .then((response) => {
                Swal.close();
                populateVerificationOffcanvas(response, null);
            })
            .catch((error) => {
                console.error("Verification Load Error:", error);
                Swal.fire({
                    icon: "error",
                    title: getText("error"),
                    text:
                        error.responseText ||
                        getText("verifyError"),
                });
            });
    });

    // ---- View by Document Type ----
    $(document).on("click", ".view-doc-type", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");
        const docType = $(this).data("doc-type");
        initVdOffcanvas();

        vdAttachments = [];
        vdCurrentIndex = 0;
        vdUser = null;
        vdDocTypeFilter = docType;
        $("#verificationOffcanvasLabel").html(
            `<i class="bi bi-paperclip me-2"></i> ${escapeHtml(docType)}`,
        );
        if ($("#vdFullname").length) $("#vdFullname").text("—");
        if ($("#vdIc").length) $("#vdIc").text("—");
        if ($("#vdStatus").length) $("#vdStatus").text("—");
        $("#vdAttachmentViewer").html(
            '<div class="text-muted text-center py-5"><i class="bi bi-file-earmark-fill fs-1"></i><br>Loading...</div>',
        );
        $("#vdAttachmentDetails").empty();
        $("#vdCounter").text("0 / 0");
        $("#vdPrevBtn, #vdNextBtn").prop("disabled", true);

        vdOffcanvas.show();

        showLoader(getText("loadingVerification"));
        fetchVerificationData(userId)
            .then((response) => {
                Swal.close();
                populateVerificationOffcanvas(response, docType);
            })
            .catch((error) => {
                console.error("Verification Load Error:", error);
                Swal.fire({
                    icon: "error",
                    title: getText("error"),
                    text:
                        error.responseText ||
                        getText("verifyError"),
                });
            });
    });

    // ---- Offcanvas navigation ----
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

    // ---- PER-ATTACHMENT Accept (offcanvas) ----
    $(document).on("click", "#vdAcceptBtn", function () {
        const file = vdAttachments[vdCurrentIndex];
        if (!file) {
            Swal.fire(
                getText("noFileSelected"),
                getText("pleaseSelectFile"),
                "warning",
            );
            return;
        }
        const userId = $("#vdAcceptBtn").data("id");
        handleSingleAccept(file.id, userId);
    });

    // ---- PER-ATTACHMENT Reject (offcanvas) ----
    $(document).on("click", "#vdRejectBtn", function () {
        const file = vdAttachments[vdCurrentIndex];
        if (!file) {
            Swal.fire(
                getText("noFileSelected"),
                getText("pleaseSelectFile"),
                "warning",
            );
            return;
        }
        const userId = $("#vdRejectBtn").data("id");
        handleSingleReject(file.id, userId);
    });

    // ---- BULK Accept (table rows) ----
    $(document).on("click", ".accept-btn", function () {
        const userId = $(this).data("id");
        handleBulkAccept(userId, verificationTable);
    });

    // ---- BULK Reject (table rows) ----
    $(document).on("click", ".reject-btn", function () {
        const userId = $(this).data("id");
        $("#rejectUserUuid").val(userId);
        $("#rejectReason").val("");
        const modal = new bootstrap.Modal(
            document.getElementById("rejectModal"),
        );
        modal.show();
    });

    // ---- Reject form submit (bulk) ----
    $("#rejectForm").on("submit", function (e) {
        e.preventDefault();
        const userId = $("#rejectUserUuid").val();
        const reason = $("#rejectReason").val();
        handleBulkReject(userId, reason, verificationTable);
    });

    $("#confirmRejectBtn").on("click", function (e) {
        e.preventDefault();
        const userId = $("#rejectUserUuid").val();
        const reason = $("#rejectReason").val();
        handleBulkReject(userId, reason, verificationTable);
    });

    // ---- Legacy modal buttons ----
    $(document).on("click", "#verificationBtn", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");
        handleBulkAccept(userId, verificationTable);
    });
    $(document).on("click", "#unverificationBtn", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");
        const modalEl = document.getElementById("verificationModal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        $("#rejectUserUuid").val(userId);
        $("#rejectReason").val("");
        new bootstrap.Modal(document.getElementById("rejectModal")).show();
    });
});
