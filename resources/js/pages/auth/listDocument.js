import $ from "jquery";
import Swal from "sweetalert2";
// 👇 Import fileUpload from your registration module (adjust path as needed)
import { fileUpload } from "./registerAction.js";

let documents = null;

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
    const container = document.getElementById('document-list-container');
    if (!container) {
        console.warn('Container #document-list-container not found');
        return;
    }

    // Clear container
    container.innerHTML = '';

    // Create the outer wrapper
    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex flex-column gap-2';

    documents.forEach((doc) => {
        // Find existing attachments for this document type
        const existingFiles = user.attachments?.filter(
            (att) => att.document_type === doc.name
        ) || [];

        // Build the card
        const card = document.createElement('div');
        card.className = 'card custom-card border shadow-sm mb-0 document-upload-section p-3 mb-2';
        card.dataset.docId = doc.id;

        // Header
        const header = document.createElement('div');
        header.className = 'd-flex align-items-center justify-content-between doc-row-toggle';
        header.setAttribute('role', 'button');
        header.setAttribute('aria-expanded', 'false');
        header.dataset.docId = doc.id;

        header.innerHTML = `
            <div class="d-flex align-items-center gap-2 min-w-0">
                <i class="ti ti-file-text fs-18 text-muted flex-shrink-0"></i>
                <div class="min-w-0">
                    <div class="fw-semibold fs-14">${doc.name}</div>
                    ${doc.description ? `<div class="text-muted fs-12 text-truncate">${doc.description}</div>` : ''}
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge rounded-pill bg-light text-muted doc-status-badge" data-doc-id="${doc.id}">
                    ${existingFiles.length > 0 ? `${existingFiles.length} file(s)` : 'No files'}
                </span>
                <i class="ti ti-chevron-down doc-toggle-icon fs-16 text-muted"></i>
            </div>
        `;

        // Panel
        const panel = document.createElement('div');
        panel.className = 'doc-panel d-none';
        panel.dataset.docId = doc.id;

        // Existing files list – NO DELETE BUTTON
        let existingHtml = '';
        if (existingFiles.length > 0) {
            existingHtml = `
                <div class="existing-file-list-empty text-muted fs-12 mb-2" data-doc-id="${doc.id}">
                    <p class="text-primary">Previously uploaded:</p>
                </div>
                <ul class="existing-file-list-container list-unstyled d-flex flex-column gap-2 mb-3" data-doc-id="${doc.id}">
                    ${existingFiles.map((file) => `
                        <li class="file-list-item existing-file">
                            <i class="${file.file_path?.endsWith('.pdf') ? 'ti ti-file-type-pdf' : 'ti ti-photo'}"></i>
                            <div class="file-meta">
                                <div class="file-name">${file.original_file_name || 'Document'}</div>
                                ${file.file_size ? `<div class="file-size">${formatFileSize(file.file_size)}</div>` : ''}
                            </div>
                            <div class="file-actions">
                                <a class="file-view-btn" href="/${file.file_path}" target="_blank" rel="noopener">
                                    <i class="ti ti-eye"></i> View
                                </a>
                                <!-- 👇 No delete button for existing files -->
                            </div>
                        </li>
                    `).join('')}
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
        let expiryHtml = '';
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

        panel.innerHTML = `
            <div class="pt-3 mt-3 border-top border-block-start-dashed">
                ${existingHtml}
                ${dropZoneHtml}
                ${stagedHtml}
                ${expiryHtml}
            </div>
        `;

        card.appendChild(header);
        card.appendChild(panel);
        wrapper.appendChild(card);
    });

    container.appendChild(wrapper);

    // ---- Re-bind toggle events ----
    document.querySelectorAll('.doc-row-toggle').forEach((toggle) => {
        toggle.addEventListener('click', function () {
            const docId = this.dataset.docId;
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const panel = document.querySelector(`.doc-panel[data-doc-id="${docId}"]`);
            if (panel) {
                panel.classList.toggle('d-none', isExpanded);
                this.setAttribute('aria-expanded', String(!isExpanded));
            }
        });
    });

    // ---- Initialize file upload for each document ----
    // fileUpload is imported from registerAction.js and is now available
    if (typeof fileUpload === 'function') {
        documents.forEach((doc) => {
            fileUpload(doc.id);
        });
    } else {
        console.warn('fileUpload function not found; upload zones will not work.');
    }
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

export async function userDocument(user) {
    await listDocuments();
    renderDocumentList(user);
}