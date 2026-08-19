<div class="tab-pane fade border-0 p-0" id="shipped-tab-pane" role="tabpanel" aria-labelledby="shipped-tab-pane"
    tabindex="0">
    <div class="p-3">
        <p class="mb-1 fw-semibold text-muted op-5 fs-20">03</p>
        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
            <div data-en="Upload Supporting Documents" data-bm="Muat Naik Dokumen Sokongan">Upload Supporting Documents
            </div>
        </div>
        <p class="text-muted fs-13 mb-3" data-en="Click on a document below to open its upload area."
            data-bm="Klik pada dokumen di bawah untuk membuka zon muat naik.">
            Click on a document below to open its upload area.
        </p>
    </div>

    <div class="px-3">
        <div class="d-flex flex-column gap-2">
            @foreach ($documents as $doc)
                <div class="card custom-card border shadow-sm mb-0 document-upload-section"
                    data-doc-id="{{ $doc->id }}">
                    <div class="card-body p-3">

                        <!-- Row header (always visible, click to expand) -->
                        <div class="d-flex align-items-center justify-content-between doc-row-toggle" role="button"
                            aria-expanded="false" data-doc-id="{{ $doc->id }}">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <i class="ti ti-file-text fs-18 text-muted flex-shrink-0"></i>
                                <div class="min-w-0">
                                    <div class="fw-semibold fs-14">{{ $doc->name }}</div>
                                    @if ($doc->description)
                                        <div class="text-muted fs-12 text-truncate">{{ $doc->description }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="badge rounded-pill bg-light text-muted doc-status-badge"
                                    data-doc-id="{{ $doc->id }}">
                                    <span data-en="No files" data-bm="Tiada fail">No files</span>
                                </span>
                                <i class="ti ti-chevron-down doc-toggle-icon fs-16 text-muted"></i>
                            </div>
                        </div>

                        <!-- Upload panel, hidden by default via our own class (not Bootstrap's .collapse) -->
                        <div class="doc-panel d-none" data-doc-id="{{ $doc->id }}">
                            <div class="pt-3 mt-3 border-top border-block-start-dashed">

                                <!-- Already uploaded files (fetched from server) -->
                                <div class="existing-file-list-empty text-muted fs-12 mb-2 d-none"
                                    data-doc-id="{{ $doc->id }}" data-en="Previously uploaded:"
                                    data-bm="Dimuat naik sebelum ini:">
                                    Previously uploaded:
                                </div>
                                <ul class="existing-file-list-container list-unstyled d-flex flex-column gap-2 mb-3"
                                    data-doc-id="{{ $doc->id }}"></ul>

                                <!-- Drag & Drop area -->
                                <div class="file-drop-area p-4 text-center border border-dashed rounded-3"
                                    style="cursor: pointer;" data-doc-id="{{ $doc->id }}">
                                    <h5 class="display-3 text-muted mb-2">
                                        <i class="ti ti-folder-down"></i>
                                    </h5>
                                    <div class="text-muted" data-en="Drop files here or click to upload."
                                        data-bm="Lepaskan fail di sini atau klik untuk muat naik.">
                                        Drop files here or click to upload.
                                    </div>
                                    <div class="text-muted fs-12 mt-1" data-en="You can select multiple files at once."
                                        data-bm="Anda boleh memilih beberapa fail sekaligus.">
                                        You can select multiple files at once.
                                    </div>
                                    <input type="file" class="file-input" style="display: none;"
                                        accept=".jpg,.jpeg,.png,.pdf" multiple name="attachment[{{ $doc->id }}][]"
                                        data-doc-id="{{ $doc->id }}">

                                     <input type="hidden" name="document_type[{{ $doc->id }}]" value="{{ $doc->name }}">
                                </div>

                                <!-- File list for newly staged files (not yet uploaded) -->
                                <div class="file-list-empty text-center text-muted fs-13 py-2"
                                    data-doc-id="{{ $doc->id }}" data-en="No files added yet."
                                    data-bm="Belum ada fail ditambah.">
                                    No files added yet.
                                </div>
                                <ul class="file-list-container list-unstyled d-flex flex-column gap-2 mb-0"
                                    data-doc-id="{{ $doc->id }}"></ul>

                                <!-- Expiry date fields (only if required) -->
                                @if ($doc->requires_expiry)
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="valid_from_{{ $doc->id }}" class="form-label"
                                                data-en="Valid From" data-bm="Berkuat kuasa dari">Valid From</label>
                                            <input type="date" class="form-control"
                                                id="valid_from_{{ $doc->id }}"
                                                name="valid_from[{{ $doc->id }}]">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="valid_until_{{ $doc->id }}" class="form-label"
                                                data-en="Valid Until" data-bm="Berkuat kuasa sehingga">Valid
                                                Until</label>
                                            <input type="date" class="form-control"
                                                id="valid_until_{{ $doc->id }}"
                                                name="valid_until[{{ $doc->id }}]">
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="p-3 border-top border-block-start-dashed d-flex justify-content-between mt-3">
        <button class="btn btn-auth-secondary" id="backToDetailsTab" type="button">
            <i class="ri-arrow-left-line me-2 align-middle"></i>
            <span data-en="Back" data-bm="Kembali">Back</span>
        </button>
        <button class="btn btn-auth-primary" id="nextToPasswordTab" type="button">
            <span data-en="Next" data-bm="Seterusnya">Next</span>
            <i class="ri-arrow-right-line ms-2 align-middle"></i>
        </button>
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

    .file-list-item .file-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid var(--default-border);
        background: transparent;
        color: rgb(var(--primary-rgb));
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        border-radius: 6px;
        padding: 4px 8px;
        white-space: nowrap;
    }

    .file-list-item .file-view-btn:hover {
        background: rgba(var(--primary-rgb), .08);
    }

    .file-list-item .file-remove {
        border: none;
        background: transparent;
        color: var(--text-muted);
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
        padding: 2px 6px;
        flex-shrink: 0;
    }

    .file-list-item .file-remove:hover {
        color: var(--danger-color, #fb4242);
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