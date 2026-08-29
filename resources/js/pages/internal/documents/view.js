import jQuery from "jquery";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import "datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css";

const $ = jQuery;
window.$ = window.jQuery = jQuery;

// ─── Ensure the file preview modal exists (inject once) ────────
function ensureAttachmentPreviewModal() {
    if (document.getElementById("attachmentPreviewModal")) return;

    const modalHtml = `
        <div class="modal fade" id="attachmentPreviewModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attachmentPreviewModalLabel">File Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="attachmentPreviewImg" src="" class="img-fluid rounded d-none" alt="Preview">
                        <iframe id="attachmentPreviewPdf" src="" class="w-100 d-none" style="height: 70vh; border: none;"></iframe>
                        <div id="attachmentPreviewFallback" class="d-none py-5">
                            <i class="ti ti-file-text ti-5x text-muted"></i>
                            <p class="text-muted mt-3 mb-0">Preview not available for this file type.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span id="attachmentPreviewName" class="me-auto text-muted fs-13"></span>
                        <a id="attachmentPreviewDownload" href="#" class="btn btn-primary btn-sm" download>
                            <i class="ti ti-download"></i> Download
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML("beforeend", modalHtml);
}

// ─── Ensure the rejection reason modal exists (inject once) ────
function ensureRejectReasonModal() {
    if (document.getElementById("rejectReasonModal")) return;

    const modalHtml = `
        <div class="modal fade" id="rejectReasonModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-alert-circle text-danger me-1"></i> Rejection Reason
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="rejectReasonText" class="mb-0"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML("beforeend", modalHtml);
}

function viewAttachment(url, name) {
    ensureAttachmentPreviewModal();

    const modalEl = document.getElementById("attachmentPreviewModal");
    const titleEl = document.getElementById("attachmentPreviewModalLabel");
    const imgEl = document.getElementById("attachmentPreviewImg");
    const pdfEl = document.getElementById("attachmentPreviewPdf");
    const fallbackEl = document.getElementById("attachmentPreviewFallback");
    const nameEl = document.getElementById("attachmentPreviewName");
    const downloadEl = document.getElementById("attachmentPreviewDownload");

    imgEl.classList.add("d-none");
    pdfEl.classList.add("d-none");
    fallbackEl.classList.add("d-none");
    imgEl.src = "";
    pdfEl.src = "";

    const ext = (url || "").split(".").pop().toLowerCase().split("?")[0];

    if (["jpg", "jpeg", "png", "gif", "bmp", "svg", "webp"].includes(ext)) {
        imgEl.src = url;
        imgEl.classList.remove("d-none");
    } else if (ext === "pdf") {
        pdfEl.src = url;
        pdfEl.classList.remove("d-none");
    } else {
        fallbackEl.classList.remove("d-none");
    }

    titleEl.textContent = name || "File Preview";
    nameEl.textContent = name || "";
    downloadEl.href = url;
    downloadEl.download = name || "";

    const modal =
        bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

function viewRejectReason(reason) {
    ensureRejectReasonModal();

    document.getElementById("rejectReasonText").textContent = reason;

    const modalEl = document.getElementById("rejectReasonModal");
    const modal =
        bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

window.viewAttachment = viewAttachment;

function resolveAttachmentUrl(filePath) {
    if (!filePath) return "";

    // Already a full URL
    if (/^https?:\/\//i.test(filePath)) {
        return filePath;
    }

    // Already starts with /storage/... — just prepend the origin
    if (filePath.startsWith("/storage/")) {
        return window.baseUrl + filePath;
    }

    // Already starts with / but not /storage — prepend origin only
    if (filePath.startsWith("/")) {
        return window.baseUrl + filePath;
    }

    // Bare relative path like "user_attachments/xxxx.pdf"
    return `${window.baseUrl}/storage/${filePath}`;
}

$(document).ready(function () {
    const documentName = window.documentName;

    const table = $("#attachmentTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: `${window.baseUrl}/internal/documents/${window.documentId}/attachments/data`,
        columns: [
            { data: "id", name: "id", visible: false },
            { data: "user_name", name: "user_name" },
            { data: "original_file_name", name: "original_file_name" },
            { data: "file_type", name: "file_type" },
            { data: "file_size_formatted", name: "file_size" },
            { data: "valid_from_formatted", name: "valid_from" },
            { data: "valid_until_formatted", name: "valid_until" },
            { data: "created_at", name: "created_at" },
            {
                data: "is_read_badge",
                name: "is_read",
                orderable: false,
                searchable: false,
            },
            {
                data: "rejected_reason_button",
                name: "rejected_reason",
                orderable: false,
                searchable: false,
            },
            {
                data: "id",
                name: "action",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    const fileUrl = resolveAttachmentUrl(row.file_path);
                    const fileName = (
                        row.original_file_name || "Document"
                    ).replace(/'/g, "\\'");
                    return `
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-info" onclick="window.viewAttachment('${fileUrl}', '${fileName}')">
                    <i class="ti ti-eye"></i> View
                </button>
                <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-primary">
                    <i class="ti ti-download"></i> Download
                </a>
            </div>
        `;
                },
            },
        ],
        columnDefs: [
            {
                targets: 7,
                render: function (data) {
                    return data ? new Date(data).toLocaleString("en-GB") : "—";
                },
            },
        ],
    });

    // ─── Rejection reason button clicks (event delegation, DataTables re-renders rows) ───
    $("#attachmentTable").on("click", ".view-reject-reason-btn", function () {
        const reason = $(this).data("reason");
        viewRejectReason(reason);
    });
});
