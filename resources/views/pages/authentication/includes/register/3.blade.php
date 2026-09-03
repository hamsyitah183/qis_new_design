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
                                <div class="min-w-0 d-flex align-items-center gap-1 flex-wrap">
                                    <div class="fw-semibold fs-14">{{ $doc->name }}</div>
                                    @if ($doc->description)
                                        <button type="button"
                                            class="badge rounded-pill bg-light-primary text-primary border-0 doc-details-btn d-flex align-items-center gap-1"
                                            data-doc-id="{{ $doc->id }}"
                                            data-description="{{ $doc->description }}">
                                            <i class="ti ti-info-circle fs-14"></i>
                                            <span data-en="Details" data-bm="Butiran">Details</span>
                                        </button>
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

                                    <input type="hidden" name="document_type[{{ $doc->id }}]"
                                        value="{{ $doc->name }}">
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


<script>
    (function() {
        // Toggle upload panel
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

@push('scripts')
<script>
    (function() {
        'use strict';

        // ─── Ensure the description modal exists ──────────────
        function ensureDescriptionModal() {
            if (document.getElementById('docDescriptionModal')) return;

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
            document.body.insertAdjacentHTML('beforeend', html);
        }

        // ─── Show document description modal ──────────────────
        function showDocumentDescription(docName, description) {
            ensureDescriptionModal();

            const modalEl = document.getElementById('docDescriptionModal');
            const title = document.getElementById('docDescriptionModalLabel');
            const body = modalEl.querySelector('.doc-description-modal-body');

            if (title) title.textContent = docName || 'Document Details';
            if (body) {
                body.innerHTML = description
                    ? `<div class="py-2">${description}</div>`
                    : '<p class="text-muted mb-0">No description available.</p>';
            }

            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        }

        // ─── Bind click events to all "Details" buttons ───────
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.doc-details-btn');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            // Get the document name from the row header
            const row = btn.closest('.doc-row-toggle');
            const nameEl = row ? row.querySelector('.fw-semibold') : null;
            const docName = nameEl ? nameEl.textContent.trim() : 'Document';

            const description = btn.getAttribute('data-description') || '';
            showDocumentDescription(docName, description);
        });

    })();
</script>
@endpush