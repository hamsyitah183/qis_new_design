import $ from "jquery";
import Swal from "sweetalert2";
import { fileUpload } from "./registerAction.js";

let documents = null;

// =============================================================
// Helpers
// =============================================================

function resolveFileUrl(path) {
    if (!path) return "";
    if (/^https?:\/\//i.test(path)) return path;
    const origin = window.location.origin;
    if (path.startsWith("/")) return origin + path;
    return `${origin}/storage/${path}`;
}

function formatFileSize(bytes) {
    if (!bytes) return "";
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
}

function isFileStillValid(file) {
    if (!file.valid_until) return true;
    const today = new Date().setHours(0, 0, 0, 0);
    const validUntil = new Date(file.valid_until).setHours(0, 0, 0, 0);
    return validUntil >= today;
}

function getExistingFiles(user, docName) {
    return (user.attachments || []).filter(att => att.document_type === docName);
}

function hasValidReadFile(user, docName) {
    return getExistingFiles(user, docName).some(
        file => Number(file.is_read) === 1 && isFileStillValid(file) && !file.rejected_reason
    );
}

// =============================================================
// Modals & Preview
// =============================================================

function ensureDescriptionModal() {
    if (document.getElementById("docDescriptionModal")) return;
    const html = `
        <div class="modal fade" id="docDescriptionModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="docDescriptionModalLabel">Document Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body doc-description-modal-body"></div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML("beforeend", html);
}

function showDocumentDescription(doc) {
    ensureDescriptionModal();
    const modalEl = document.getElementById("docDescriptionModal");
    document.getElementById("docDescriptionModalLabel").textContent = doc.name;
    const body = modalEl.querySelector(".doc-description-modal-body");
    body.innerHTML = doc.description || `<p class="text-muted mb-0">No description available.</p>`;
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

function viewExistingFile(url, name) {
    const fullUrl = resolveFileUrl(url);
    const modalEl = document.getElementById("fileLabelModal");
    if (!modalEl) {
        window.open(fullUrl, "_blank", "noopener,noreferrer");
        return;
    }

    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    const previewImg = document.getElementById("fileLabelPreview");
    const previewIcon = document.getElementById("filePreviewIcon");
    const pdfViewer = document.getElementById("fileLabelPdfViewer");
    const fileNameDisplay = document.getElementById("fileLabelName");
    const openBtn = document.getElementById("fileLabelOpenBtn");
    const modalTitle = document.getElementById("fileLabelModalLabel");

    if (previewImg) previewImg.classList.add("d-none");
    if (previewIcon) previewIcon.classList.add("d-none");
    if (pdfViewer) pdfViewer.style.display = "none";

    const ext = (fullUrl || "").split(".").pop().toLowerCase();

    if (["jpg", "jpeg", "png", "gif", "bmp", "svg", "webp"].includes(ext)) {
        if (previewImg) {
            previewImg.src = fullUrl;
            previewImg.classList.remove("d-none");
        }
    } else if (ext === "pdf") {
        if (pdfViewer) {
            pdfViewer.src = fullUrl;
            pdfViewer.style.display = "block";
        } else {
            if (previewIcon) {
                previewIcon.classList.remove("d-none");
                const icon = previewIcon.querySelector("i") || previewIcon;
                if (icon.tagName === "I") icon.className = "ti ti-file-text ti-5x text-muted";
                const msg = previewIcon.querySelector("p");
                if (msg) msg.textContent = "PDF preview not available. Click 'Open in New Tab' to view.";
            }
        }
    } else {
        if (previewIcon) {
            previewIcon.classList.remove("d-none");
            const icon = previewIcon.querySelector("i") || previewIcon;
            if (icon.tagName === "I") icon.className = "ti ti-file-text ti-5x text-muted";
            const msg = previewIcon.querySelector("p");
            if (msg) msg.textContent = "Preview not available. Use 'Open in New Tab' to view it.";
        }
    }

    if (fileNameDisplay) fileNameDisplay.textContent = name || "Document";
    if (openBtn) {
        openBtn.href = fullUrl;
        openBtn.download = name || "document";
        openBtn.style.display = "inline-block";
    }
    if (modalTitle) modalTitle.textContent = "File Preview";

    modal.show();
}

window.viewExistingFile = viewExistingFile;

// =============================================================
// Document List API
// =============================================================

async function listDocuments() {
    try {
        const response = await fetch("/documents/data", {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        const data = await response.json();
        documents = data.document ?? data;
        console.log("📄 Document requirements loaded:", documents);
        return documents;
    } catch (error) {
        console.error("❌ Failed to load document list:", error);
        Swal.fire({
            icon: "error",
            title: "Failed to Load Documents",
            text: "Could not fetch document requirements. Please refresh the page.",
        });
        return null;
    }
}

// =============================================================
// UI Rendering
// =============================================================

function createDocumentCard(doc, user) {
    const existingFiles = getExistingFiles(user, doc.name);
    const hasValidRead = hasValidReadFile(user, doc.name);

    const card = document.createElement("div");
    card.className = "card custom-card border shadow-sm mb-0 document-upload-section p-3 mb-2";
    card.dataset.docId = doc.id;

    // ─── Header ──────────────────────────────────────────────────────
    const header = document.createElement("div");
    header.className = "d-flex align-items-center justify-content-between doc-row-toggle";
    header.setAttribute("role", "button");
    header.setAttribute("aria-expanded", "false");
    header.dataset.docId = doc.id;

    // Build left side: icon + name
    const leftSide = document.createElement("div");
    leftSide.className = "d-flex align-items-center gap-2 min-w-0";
    leftSide.innerHTML = `
        <i class="ti ti-file-text fs-18 text-muted flex-shrink-0"></i>
        <div class="min-w-0">
            <div class="fw-semibold fs-14">${doc.name}</div>
        </div>
    `;

    // Build right side: actions + badge + toggle
    const rightSide = document.createElement("div");
    rightSide.className = "d-flex align-items-center gap-2 flex-shrink-0";

    // Details button (if description exists)
    if (doc.description) {
        const detailsBtn = document.createElement("button");
        detailsBtn.type = "button";
        detailsBtn.className = "badge rounded-pill bg-light-primary text-primary border-0 doc-details-btn d-flex align-items-center gap-1";
        detailsBtn.dataset.docId = doc.id;
        detailsBtn.innerHTML = `<i class="ti ti-info-circle fs-14"></i> Details`;
        detailsBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            const docId = this.dataset.docId;
            const doc = documents.find(d => String(d.id) === String(docId));
            if (doc) showDocumentDescription(doc);
        });
        rightSide.appendChild(detailsBtn);
    }

    // Status badge
    const badge = document.createElement("span");
    badge.className = "badge rounded-pill bg-light text-muted doc-status-badge";
    badge.dataset.docId = doc.id;
    badge.textContent = existingFiles.length > 0 ? `${existingFiles.length} file(s)` : "No files";
    rightSide.appendChild(badge);

    // Add Document button (if not fully verified)
    if (!hasValidRead) {
        const addBtn = document.createElement("button");
        addBtn.type = "button";
        addBtn.className = "btn btn-sm btn-outline-primary add-document-btn d-flex align-items-center gap-1";
        addBtn.dataset.docId = doc.id;
        addBtn.innerHTML = `<i class="ti ti-plus"></i> Add`;
        addBtn.addEventListener("click", function (e) {
            e.stopPropagation(); // prevent toggling the panel
            const docId = this.dataset.docId;
            const uploadZone = document.querySelector(`.upload-zone-wrapper[data-doc-id="${docId}"]`);
            if (!uploadZone) return;
            const isHidden = uploadZone.classList.contains("d-none");
            uploadZone.classList.toggle("d-none", !isHidden);
            this.innerHTML = isHidden ? `<i class="ti ti-x"></i> Cancel` : `<i class="ti ti-plus"></i> Add`;
        });
        rightSide.appendChild(addBtn);
    }

    // Chevron toggle
    const chevron = document.createElement("i");
    chevron.className = "ti ti-chevron-down doc-toggle-icon fs-16 text-muted";
    rightSide.appendChild(chevron);

    header.appendChild(leftSide);
    header.appendChild(rightSide);

    // ─── Panel ──────────────────────────────────────────────────────
    const panel = document.createElement("div");
    panel.className = "doc-panel d-none";
    panel.dataset.docId = doc.id;

    console.log('exisitng file', existingFiles)

    // ─── Existing files list ──────────────────────────────────────
    let existingHtml = existingFiles.length > 0
        ? `
            <div class="existing-file-list-empty text-muted fs-12 mb-2" data-doc-id="${doc.id}">
                <p class="text-primary">Previously uploaded:</p>
            </div>
            <ul class="existing-file-list-container list-unstyled d-flex flex-column gap-2 mb-3" data-doc-id="${doc.id}">
                ${existingFiles.map(file => `
                    <li class="file-list-item existing-file">
                        <i class="${file.file_path?.endsWith(".pdf") ? "ti ti-file-type-pdf" : "ti ti-photo"}"></i>
                        <div class="file-meta">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="file-name">${file.original_file_name || "Document"}</div>
                                ${Number(file.is_read) === 1 && !file.rejected_reason ? `<span class="badge bg-success-transparent fs-11 py-0 px-1"><i class="ti ti-check fs-11"></i> Read</span>` : ""}
                                ${file.rejected_reason ? `<span class="badge bg-danger fs-11 py-0 px-1">Rejected</span>` : ""}
                            </div>
                            ${file.rejected_reason ? `<div class="fs-12 mt-1 p-2 alert alert-danger"><span data-en="Reason" data-bm="Sebab"></span>${file.rejected_reason}</div>` : ""}
                            ${file.file_size ? `<div class="file-size">${formatFileSize(file.file_size)}</div>` : ""}
                            <div class="file-uploaded-date text-muted fs-12">
                                Uploaded on: ${new Date(file.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        <div class="file-actions">
                            <button class="btn btn-sm btn-icon btn-success-light btn-wave file-view-btn" 
                                    data-url="${file.file_path}" 
                                    data-name="${file.original_file_name || "Document"}" 
                                    onclick="window.viewExistingFile(this.dataset.url, this.dataset.name)">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </li>
                `).join("")}
            </ul>
        `
        : `
            <div class="existing-file-list-empty text-muted fs-12 mb-2 d-none" data-doc-id="${doc.id}">
                Previously uploaded:
            </div>
            <ul class="existing-file-list-container list-unstyled d-flex flex-column gap-2 mb-3" data-doc-id="${doc.id}"></ul>
        `;

    const lockedNotice = hasValidRead
        ? `<div class="text-success fs-12 d-flex align-items-center gap-1 mt-1 d-none">
            <i class="ti ti-circle-check"></i> Verified and valid — no action needed.
        </div>`
        : "";

    // Upload zone (hidden by default; toggled by "Add" button in header)
    const uploadZone = hasValidRead
        ? ""
        : `
            <div class="upload-zone-wrapper d-none mt-3" data-doc-id="${doc.id}">
                <div class="file-drop-area p-4 text-center border border-dashed rounded-3" style="cursor: pointer;" data-doc-id="${doc.id}">
                    <h5 class="display-3 text-muted mb-2"><i class="ti ti-folder-down"></i></h5>
                    <div class="text-muted">Drop files here or click to upload.</div>
                    <div class="text-muted fs-12 mt-1">You can select multiple files at once.</div>
                    <input type="file" class="file-input" style="display: none;" accept=".jpg,.jpeg,.png,.pdf" multiple name="attachment[${doc.id}][]" data-doc-id="${doc.id}">
                    <input type="hidden" name="document_type[${doc.id}]" value="${doc.name}">
                </div>
                <div class="file-list-empty text-center text-muted fs-13 py-2" data-doc-id="${doc.id}">
                    No files added yet.
                </div>
                <ul class="file-list-container list-unstyled d-flex flex-column gap-2 mb-0" data-doc-id="${doc.id}"></ul>
                ${doc.requires_expiry ? `
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="valid_from_${doc.id}" class="form-label">Valid From</label>
                            <input type="date" class="form-control" id="valid_from_${doc.id}" name="valid_from[${doc.id}]">
                        </div>
                        <div class="col-md-6">
                            <label for="valid_until_${doc.id}" class="form-label">Valid Until</label>
                            <input type="date" class="form-control" id="valid_until_${doc.id}" name="valid_until[${doc.id}]">
                        </div>
                    </div>
                ` : ""}
            </div>
        `;

    panel.innerHTML = `
        <div class="pt-3 mt-3 border-top border-block-start-dashed">
            ${existingHtml}
            ${lockedNotice}
            ${uploadZone}
        </div>
    `;

    // ─── Toggle panel on header click ──────────────────────────────
    header.addEventListener("click", function (e) {
        // Avoid toggling if the click originated from a button inside the header
        if (e.target.closest(".add-document-btn") || e.target.closest(".doc-details-btn")) return;
        const docId = this.dataset.docId;
        const panel = document.querySelector(`.doc-panel[data-doc-id="${docId}"]`);
        if (panel) {
            const isExpanded = this.getAttribute("aria-expanded") === "true";
            panel.classList.toggle("d-none", isExpanded);
            this.setAttribute("aria-expanded", String(!isExpanded));
        }
    });

    card.appendChild(header);
    card.appendChild(panel);
    return card;
}

function renderDocumentList(user) {
    const container = document.getElementById("document-list-container");
    if (!container) {
        console.warn("Container #document-list-container not found");
        return;
    }
    container.innerHTML = "";

    const wrapper = document.createElement("div");
    wrapper.className = "d-flex flex-column gap-2";

    documents.forEach(doc => {
        const card = createDocumentCard(doc, user);
        wrapper.appendChild(card);
    });

    container.appendChild(wrapper);

    // Bind remaining events (upload initialization, submit)
    initializeFileUploads(user);
    setupSubmitButton();
}

// =============================================================
// Upload & Submit
// =============================================================

function initializeFileUploads(user) {
    if (typeof fileUpload !== "function") {
        console.warn("fileUpload function not found; upload zones will not work.");
        return;
    }

    documents.forEach(doc => {
        const hasValidRead = hasValidReadFile(user, doc.name);
        if (!hasValidRead) {
            fileUpload(doc.id);
        }

        // Update badge counts dynamically
        const card = document.querySelector(`.document-upload-section[data-doc-id="${doc.id}"]`);
        if (!card) return;

        const badge = card.querySelector(".doc-status-badge");
        const existingList = card.querySelector(".existing-file-list-container");
        const stagedList = card.querySelector(".file-list-container");

        function updateBadge() {
            const existingCount = existingList ? existingList.children.length : 0;
            const stagedCount = stagedList ? stagedList.children.length : 0;
            const total = existingCount + stagedCount;
            if (total > 0) {
                badge.textContent = total + (total === 1 ? " file" : " files");
                badge.classList.add("has-files");
            } else {
                badge.textContent = "No files";
                badge.classList.remove("has-files");
            }
        }

        updateBadge();
        if (stagedList) {
            const observer = new MutationObserver(updateBadge);
            observer.observe(stagedList, { childList: true, subtree: true });
        }
    });
}

function setupSubmitButton() {
    const submitFooter = document.getElementById("document-submit-footer");
    if (!submitFooter) return;

    submitFooter.innerHTML = `
        <button type="button" class="btn btn-primary" id="submit-documents-btn" disabled>
            <i class="ti ti-upload me-1"></i> Submit Documents
        </button>
    `;
    submitFooter.classList.remove("d-none");

    const submitBtn = document.getElementById("submit-documents-btn");

    function checkStagedFiles() {
        let hasFiles = false;
        document.querySelectorAll(".file-input").forEach(input => {
            if (input.files && input.files.length > 0) hasFiles = true;
        });
        submitBtn.disabled = !hasFiles;
    }

    document.querySelectorAll(".file-list-container").forEach(list => {
        const observer = new MutationObserver(checkStagedFiles);
        observer.observe(list, { childList: true, subtree: true });
    });

    submitBtn.addEventListener("click", async function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting...`;

        try {
            const formData = new FormData();
            let hasFiles = false;

            document.querySelectorAll(".file-input").forEach(input => {
                const docId = input.dataset.docId;
                if (input.files && input.files.length > 0) {
                    Array.from(input.files).forEach(file => {
                        formData.append(`attachment[${docId}][]`, file);
                        hasFiles = true;
                    });
                    const docTypeInput = document.querySelector(`input[name="document_type[${docId}]"]`);
                    if (docTypeInput) formData.append(`document_type[${docId}]`, docTypeInput.value);
                    const validFrom = document.querySelector(`input[name="valid_from[${docId}]"]`);
                    if (validFrom?.value) formData.append(`valid_from[${docId}]`, validFrom.value);
                    const validUntil = document.querySelector(`input[name="valid_until[${docId}]"]`);
                    if (validUntil?.value) formData.append(`valid_until[${docId}]`, validUntil.value);
                }
            });

            if (!hasFiles) throw new Error("No files selected to upload.");

            const response = await fetch("/public/upload-verification", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || "Failed to upload documents.");

            await Swal.fire({
                icon: "success",
                title: "Success",
                text: data.message || "Documents uploaded successfully.",
                confirmButtonText: "OK",
                timer: 2000,
            });

            window.location.reload();
        } catch (error) {
            console.error("Upload error:", error);
            Swal.fire({
                icon: "error",
                title: "Upload Failed",
                text: error.message || "An unexpected error occurred.",
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="ti ti-upload me-1"></i> Submit Documents`;
        }
    });
}

// =============================================================
// Public API
// =============================================================

export async function userDocument(user) {
    await listDocuments();
    renderDocumentList(user);
}