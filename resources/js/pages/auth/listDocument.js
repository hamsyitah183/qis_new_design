import $ from "jquery";
import Swal from "sweetalert2";
import { fileUpload } from "./registerAction.js";

let documents = null;

// ---- Shared modal preview for existing files ----
function viewExistingFile(url, name) {
    const modalEl = document.getElementById("fileLabelModal");
    if (!modalEl) {
        // Fallback: open in new tab if modal not found
        window.open(url, "_blank", "noopener,noreferrer");
        return;
    }

    // Get or create Bootstrap Modal instance
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

    // Get modal elements
    const previewImg = document.getElementById("fileLabelPreview");
    const previewIcon = document.getElementById("filePreviewIcon");
    const pdfViewer = document.getElementById("fileLabelPdfViewer");
    const fileNameDisplay = document.getElementById("fileLabelName");
    const openBtn = document.getElementById("fileLabelOpenBtn");
    const modalTitle = document.getElementById("fileLabelModalLabel");

    // Reset all viewers
    if (previewImg) previewImg.style.display = "none";
    if (pdfViewer) pdfViewer.style.display = "none";
    if (previewIcon) previewIcon.style.display = "none";

    // Determine file extension
    const ext = (url || "").split(".").pop().toLowerCase();

    // Show appropriate viewer
    if (["jpg", "jpeg", "png", "gif", "bmp", "svg", "webp"].includes(ext)) {
        if (previewImg) {
            previewImg.src = url;
            previewImg.style.display = "block";
        }
    } else if (ext === "pdf") {
        if (pdfViewer) {
            pdfViewer.src = url;
            pdfViewer.style.display = "block";
        } else {
            // Fallback: show icon
            if (previewIcon) {
                previewIcon.style.display = "block";
                const icon = previewIcon.querySelector("i") || previewIcon;
                if (icon.tagName === "I") icon.className = "ti ti-file-type-pdf ti-5x text-muted";
                const msg = previewIcon.querySelector("p");
                if (msg) msg.textContent = "PDF preview not available. Click 'Open in New Tab' to view.";
            }
        }
    } else {
        // Generic file fallback
        if (previewIcon) {
            previewIcon.style.display = "block";
            const icon = previewIcon.querySelector("i") || previewIcon;
            if (icon.tagName === "I") icon.className = "ti ti-file-text ti-5x text-muted";
            const msg = previewIcon.querySelector("p");
            if (msg) msg.textContent = "Preview not available. Use 'Open in New Tab' to view it.";
        }
    }

    if (fileNameDisplay) fileNameDisplay.textContent = name || "Document";
    if (openBtn) {
        openBtn.href = url;
        openBtn.download = name || "document";
        openBtn.style.display = "inline-block";
    }
    if (modalTitle) modalTitle.textContent = "File Preview";

    modal.show();
}

// Attach to window so it can be called from inline onclick
window.viewExistingFile = viewExistingFile;

// ---- Document listing functions ----
async function listDocuments() {
    try {
        const response = await fetch("/documents/data", {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

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

function renderDocumentList(user) {
    const container = document.getElementById("document-list-container");
    if (!container) {
        console.warn("Container #document-list-container not found");
        return;
    }

    // Clear container
    container.innerHTML = "";

    // Create the outer wrapper
    const wrapper = document.createElement("div");
    wrapper.className = "d-flex flex-column gap-2";

    documents.forEach((doc) => {
        // Find existing attachments for this document type
        const existingFiles =
            user.attachments?.filter((att) => att.document_type === doc.name) ||
            [];

        // Build the card
        const card = document.createElement("div");
        card.className =
            "card custom-card border shadow-sm mb-0 document-upload-section p-3 mb-2";
        card.dataset.docId = doc.id;

        // Header
        const header = document.createElement("div");
        header.className =
            "d-flex align-items-center justify-content-between doc-row-toggle";
        header.setAttribute("role", "button");
        header.setAttribute("aria-expanded", "false");
        header.dataset.docId = doc.id;

        header.innerHTML = `
            <div class="d-flex align-items-center gap-2 min-w-0">
                <i class="ti ti-file-text fs-18 text-muted flex-shrink-0"></i>
                <div class="min-w-0">
                    <div class="fw-semibold fs-14">${doc.name}</div>
                    ${
                        doc.description
                            ? `<div class="text-muted fs-12 text-truncate">${doc.description}</div>`
                            : ""
                    }
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge rounded-pill bg-light text-muted doc-status-badge" data-doc-id="${doc.id}">
                    ${existingFiles.length > 0 ? `${existingFiles.length} file(s)` : "No files"}
                </span>
                <i class="ti ti-chevron-down doc-toggle-icon fs-16 text-muted"></i>
            </div>
        `;

        // Panel
        const panel = document.createElement("div");
        panel.className = "doc-panel d-none";
        panel.dataset.docId = doc.id;

        // Existing files list – NO DELETE BUTTON, uses modal preview
        let existingHtml = "";
        if (existingFiles.length > 0) {
            existingHtml = `
                <div class="existing-file-list-empty text-muted fs-12 mb-2" data-doc-id="${doc.id}">
                    <p class="text-primary">Previously uploaded:</p>
                </div>
                <ul class="existing-file-list-container list-unstyled d-flex flex-column gap-2 mb-3" data-doc-id="${doc.id}">
                    ${existingFiles
                        .map(
                            (file) => `
                        <li class="file-list-item existing-file">
                            <i class="${
                                file.file_path?.endsWith(".pdf")
                                    ? "ti ti-file-type-pdf"
                                    : "ti ti-photo"
                            }"></i>
                            <div class="file-meta">
                                <div class="file-name">${
                                    file.original_file_name || "Document"
                                }</div>
                                ${
                                    file.file_size
                                        ? `<div class="file-size">${formatFileSize(
                                              file.file_size
                                          )}</div>`
                                        : ""
                                }

                                <div class="file-uploaded-date text-muted fs-12">
                                    Uploaded on: ${new Date(
                                        file.created_at
                                    ).toLocaleDateString()}
                                </div>
                            </div>
                            <div class="file-actions">
                                <button class="btn btn-sm btn-icon btn-success-light btn-wave file-view-btn" data-url="${
                                    file.file_path
                                }" data-name="${
                                file.original_file_name || "Document"
                            }" onclick="window.viewExistingFile(this.dataset.url, this.dataset.name)">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </div>
                        </li>
                    `
                        )
                        .join("")}
                </ul>
            `;
        } else {
            existingHtml = `
                <div class="existing-file-list-empty text-muted fs-12 mb-2 d-none" data-doc-id="${doc.id}">
                    Previously uploaded:
                </div>
                <ul class="existing-file-list-container list-unstyled d-flex flex-column gap-2 mb-3" data-doc-id="${doc.id}"></ul>
            `;
        }

        // "Add Document" trigger button — reveals the dropzone/staged
        // list/expiry fields on click instead of showing them by default
        const addDocumentBtnHtml = `
            <button type="button" class="btn btn-sm btn-outline-primary add-document-btn d-flex ms-auto" data-doc-id="${doc.id}">
                <i class="ti ti-plus me-1"></i> Add Document
            </button>
        `;

        // Drag & Drop area – the dropzone
        const dropZoneHtml = `
            <div class="file-drop-area p-4 text-center border border-dashed rounded-3" style="cursor: pointer;" data-doc-id="${doc.id}">
                <h5 class="display-3 text-muted mb-2"><i class="ti ti-folder-down"></i></h5>
                <div class="text-muted">Drop files here or click to upload.</div>
                <div class="text-muted fs-12 mt-1">You can select multiple files at once.</div>
                <input type="file" class="file-input" style="display: none;" accept=".jpg,.jpeg,.png,.pdf" multiple name="attachment[${doc.id}][]" data-doc-id="${doc.id}">
                <input type="hidden" name="document_type[${doc.id}]" value="${doc.name}">
            </div>
        `;

        // New files list – staged, with REMOVE button
        const stagedHtml = `
            <div class="file-list-empty text-center text-muted fs-13 py-2" data-doc-id="${doc.id}">
                No files added yet.
            </div>
            <ul class="file-list-container list-unstyled d-flex flex-column gap-2 mb-0" data-doc-id="${doc.id}"></ul>
        `;

        // Expiry dates (if required)
        let expiryHtml = "";
        if (doc.requires_expiry) {
            expiryHtml = `
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
            `;
        }

        // Everything below the "Add Document" button stays hidden until
        // that button is clicked — same d-none pattern as the outer
        // .doc-panel, just one level deeper and scoped to the upload zone.
        const uploadZoneHtml = `
            <div class="upload-zone-wrapper d-none mt-3" data-doc-id="${doc.id}">
                ${dropZoneHtml}
                ${stagedHtml}
                ${expiryHtml}
            </div>
        `;

        panel.innerHTML = `
            <div class="pt-3 mt-3 border-top border-block-start-dashed">
                ${existingHtml}
                ${addDocumentBtnHtml}
                ${uploadZoneHtml}
            </div>
        `;

        card.appendChild(header);
        card.appendChild(panel);
        wrapper.appendChild(card);
    });

    container.appendChild(wrapper);

    // ---- Re-bind toggle events (expand/collapse the whole document card) ----
    document.querySelectorAll(".doc-row-toggle").forEach((toggle) => {
        toggle.addEventListener("click", function () {
            const docId = this.dataset.docId;
            const isExpanded = this.getAttribute("aria-expanded") === "true";
            const panel = document.querySelector(
                `.doc-panel[data-doc-id="${docId}"]`
            );
            if (panel) {
                panel.classList.toggle("d-none", isExpanded);
                this.setAttribute("aria-expanded", String(!isExpanded));
            }
        });
    });

    // ---- Re-bind "Add Document" buttons (reveal the dropzone for that doc) ----
    document.querySelectorAll(".add-document-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const docId = this.dataset.docId;
            const uploadZone = document.querySelector(
                `.upload-zone-wrapper[data-doc-id="${docId}"]`
            );
            if (!uploadZone) return;

            const isHidden = uploadZone.classList.contains("d-none");
            uploadZone.classList.toggle("d-none", !isHidden);

            this.innerHTML = isHidden
                ? `Cancel`
                : `Add Document`;
        });
    });

    // ---- Initialize file upload for each document ----
    if (typeof fileUpload === "function") {
        documents.forEach((doc) => {
            fileUpload(doc.id);

            // ---- Set up badge observer to track total files (existing + staged) ----
            const card = document.querySelector(
                `.document-upload-section[data-doc-id="${doc.id}"]`
            );
            if (card) {
                const badge = card.querySelector(".doc-status-badge");
                const existingList = card.querySelector(
                    ".existing-file-list-container"
                );
                const stagedList = card.querySelector(
                    ".file-list-container"
                );

                function updateBadge() {
                    const existingCount = existingList
                        ? existingList.children.length
                        : 0;
                    const stagedCount = stagedList
                        ? stagedList.children.length
                        : 0;
                    const total = existingCount + stagedCount;

                    if (total > 0) {
                        badge.textContent =
                            total + (total === 1 ? " file" : " files");
                        badge.classList.add("has-files");
                    } else {
                        badge.textContent = "No files";
                        badge.classList.remove("has-files");
                    }
                }

                // Initial update
                updateBadge();

                // Observe staged list changes (add/remove files)
                if (stagedList) {
                    const observer = new MutationObserver(updateBadge);
                    observer.observe(stagedList, {
                        childList: true,
                        subtree: true,
                    });
                }
            }
        });
    } else {
        console.warn("fileUpload function not found; upload zones will not work.");
    }
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
}

export async function userDocument(user) {
    await listDocuments();
    renderDocumentList(user);
}