<div class="border rounded-3 p-3">
    <div class="d-flex flex-column gap-2" id="document-list-container">

    </div>
</div>

<div class="modal fade" id="fileLabelModal" tabindex="-1" aria-labelledby="fileLabelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileLabelModalLabel" data-en="Label your file"
                    data-bm="Berikan label pada fail">Label your file</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 align-items-center">
                    <div class="col-12">
                        <div class="border rounded p-3 text-center">
                            <img id="fileLabelPreview" src="" alt="Preview" class="img-fluid d-none" />
                            <div id="filePreviewIcon" class="d-none text-center py-5">
                                <i class="ti ti-file-text ti-5x text-muted"></i>
                                <p class="mt-3 text-muted" data-en="File preview not available"
                                    data-bm="Pratonton fail tidak tersedia">File preview not available</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <p class="mb-2"><strong data-en="Selected file:" data-bm="Fail dipilih:">Selected
                                file:</strong> <span id="fileLabelName"></span></p>
                        <label for="fileLabelInput" class="form-label" data-en="File label" data-bm="Label fail">File
                            label</label>
                        <input type="text" class="form-control" id="fileLabelInput"
                            placeholder="e.g. IC front page, IC back page">
                        <div class="form-text d-none"
                            data-en="Enter a descriptive name for this upload so you can easily identify it later."
                            data-bm="Masukkan nama deskriptif untuk muat naik ini supaya ia mudah dikenal kemudian.">
                            Enter a descriptive name for this upload so you can easily identify it later.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel"
                    data-bm="Batal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveFileLabelBtn" data-en="Save"
                    data-bm="Simpan">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
    .doc-row-toggle {
        cursor: pointer;
    }

    .doc-row-toggle .doc-toggle-icon {
        transition: transform .2s ease;
    }

    .doc-row-toggle[aria-expanded="true"] .doc-toggle-icon {
        transform: rotate(180deg);
    }

    .doc-status-badge.has-files {
        background: rgba(var(--success-rgb, 30, 195, 121), .12) !important;
        color: rgb(var(--success-rgb, 30, 195, 121)) !important;
    }

    .file-drop-area.is-dragover {
        border-color: rgb(var(--primary-rgb)) !important;
        background: rgba(var(--primary-rgb), .05);
    }

    .file-list-item {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid var(--default-border);
        border-radius: 10px;
        padding: 8px 12px;
        background: var(--default-background);
    }

    .file-list-item i {
        font-size: 20px;
        color: rgb(var(--primary-rgb));
        flex-shrink: 0;
    }

    .file-list-item .file-meta {
        flex: 1;
        min-width: 0;
    }

    .file-list-item .file-meta .file-name {
        font-size: 13px;
        font-weight: 500;
        color: var(--default-text-color);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-list-item .file-meta .file-size {
        font-size: 11.5px;
        color: var(--text-muted);
    }

    .file-list-item .file-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }

    .file-list-item.existing-file i {
        color: var(--success-color, #1ec379);
    }
</style>

<script>
    (function() {
        document.querySelectorAll('.document-upload-section').forEach(function(section) {
            var docId = section.getAttribute('data-doc-id');
            var rowToggle = section.querySelector('.doc-row-toggle');
            var panel = section.querySelector('.doc-panel[data-doc-id="' + docId + '"]');

            if (rowToggle && panel) {
                rowToggle.addEventListener('click', function() {
                    var isOpen = !panel.classList.contains('d-none');
                    panel.classList.toggle('d-none', isOpen);
                    rowToggle.setAttribute('aria-expanded', String(!isOpen));
                });
            }
        });
    })();
</script>
