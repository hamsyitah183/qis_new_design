<div class="border rounded-3 p-3">
    @php
        $approved = $user['user']->approved ?? null;
        $attachments = $user['user']->attachments ?? collect();
        $verifiedDOA = $approved->doa_verified ?? null;
    @endphp

    <div class="hasImage" style="display:none;">
        <!-- Verification Status -->
        <div class="mb-4">
            <div class="mt-3 status"></div>
            <div class="fs-13 reason"></div>

            <div class="mt-3">
                <span class="fs-12">
                    <span class="fw-bold" data-en="Submitted on :" data-bm="Dihantar pada :">Submitted on :</span>
                    <span class="submittedVerification text-muted">
                        {{ $approved->created_at ?? 'N/A' }}
                    </span>
                </span>
                <br>
                <span class="fs-12">
                    <span class="fw-bold" data-en="View By :" data-bm="Dilihat Oleh :">View By :</span>
                    <span class="approvedBy text-muted">
                        {{ $approved->approver->name ?? 'N/A' }}
                    </span>
                    <span class="approvedDate text-muted">
                        {{ $approved->doa_approved_time ?? '' }}
                    </span>
                </span>
            </div>

            <div class="rejectedBtn p-1 d-flex justify-content-end align-items-end mt-3"></div>
        </div>

        <!-- Documents List Section -->
        <div class="mt-4 pt-3 border-top border-block-start-dashed">
            <div class="mb-3">
                <h6 class="fw-semibold mb-2" data-en="Your Uploaded Documents" data-bm="Dokumen Yang Dimuat Naik">Your Uploaded Documents</h6>
            </div>

            @if ($attachments->isNotEmpty())
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach ($attachments as $attachment)
                        <div class="attachment-list-item d-flex align-items-center gap-2 border rounded-3 p-3" style="background: rgba(var(--primary-rgb, 13, 110, 253), 0.02);">
                            <i class="{{ str_ends_with(strtolower($attachment->file_path), '.pdf') ? 'ti ti-file-type-pdf' : 'ti ti-photo' }} fs-20 text-primary flex-shrink-0"></i>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold fs-13 text-truncate">
                                    {{ $attachment->document_type ?? 'Document' }}
                                </div>
                                <div class="text-muted fs-11 text-truncate">
                                    {{ $attachment->original_file_name }}
                                </div>
                                @if ($attachment->valid_until)
                                    <div class="text-muted fs-11">
                                        <span data-en="Valid until:" data-bm="Sah sehingga:">Valid until:</span>
                                        {{ \Carbon\Carbon::parse($attachment->valid_until)->format('d M Y') }}
                                    </div>
                                @endif
                                <div class="text-muted fs-11 mt-1">
                                    <span data-en="Uploaded:" data-bm="Dimuat naik:">Uploaded:</span>
                                    {{ \Carbon\Carbon::parse($attachment->created_at)->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <a href="{{ asset($attachment->file_path) }}" target="_blank" rel="noopener"
                                   class="btn btn-sm btn-icon btn-primary-light" title="View" data-en="View" data-bm="Lihat">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-icon btn-danger-light delete-attachment"
                                   data-attachment-id="{{ $attachment->id }}" title="Delete" data-en="Delete" data-bm="Padam">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Add More Documents Section -->
                <div class="card custom-card border mt-3 mb-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between doc-row-toggle" role="button"
                            aria-expanded="false" data-doc-id="add-more">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-plus fs-18 text-primary flex-shrink-0"></i>
                                <div>
                                    <div class="fw-semibold fs-14" data-en="Add More Documents" data-bm="Tambah Lebih Banyak Dokumen">Add More Documents</div>
                                    <div class="text-muted fs-12" data-en="Upload additional supporting documents" data-bm="Muat naik dokumen sokongan tambahan">Upload additional supporting documents</div>
                                </div>
                            </div>
                            <i class="ti ti-chevron-down doc-toggle-icon fs-16 text-muted"></i>
                        </div>

                        <!-- Upload panel, hidden by default -->
                        <div class="doc-panel d-none" data-doc-id="add-more">
                            <div class="pt-3 mt-3 border-top border-block-start-dashed">
                                <form class="dropzone" id="addMoreDropzone" enctype="multipart/form-data">
                                    <div class="dz-message p-4 text-center">
                                        <h5 class="display-3 text-muted mb-2">
                                            <i class="ti ti-folder-down"></i>
                                        </h5>
                                        <div class="text-muted" data-en="Drop files here or click to upload." data-bm="Lepaskan fail di sini atau klik untuk muat naik.">
                                            Drop files here or click to upload.
                                        </div>
                                        <div class="text-muted fs-12 mt-1" data-en="Supported formats: PDF, JPG, PNG" data-bm="Format yang disokong: PDF, JPG, PNG">
                                            Supported formats: PDF, JPG, PNG
                                        </div>
                                    </div>
                                </form>

                                <!-- File list for newly staged files -->
                                <ul class="file-list-container list-unstyled d-flex flex-column gap-2 mt-3 mb-0"
                                    data-doc-id="add-more"></ul>

                                <div class="text-end mt-3">
                                    <button id="uploadMoreBtn" class="btn btn-primary" type="button" data-en="Upload Files" data-bm="Muat Naik Fail">
                                        <i class="ti ti-upload me-2"></i>
                                        <span data-en="Upload Files" data-bm="Muat Naik Fail">Upload Files</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card custom-card border mb-0">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="ti ti-folder-open fs-32 text-muted op-5"></i>
                        </div>
                        <div class="text-muted fs-13" data-en="No documents uploaded yet." data-bm="Belum ada dokumen dimuat naik.">
                            No documents uploaded yet.
                        </div>
                        <p class="text-muted fs-12 mt-1" data-en="Upload your verification documents to complete the process." data-bm="Muat naik dokumen pengesahan anda untuk menyelesaikan proses.">
                            Upload your verification documents to complete the process.
                        </p>

                        <button type="button" class="btn btn-primary btn-sm mt-3 doc-row-toggle"
                            role="button" aria-expanded="false" data-doc-id="initial-upload">
                            <i class="ti ti-upload me-2"></i>
                            <span data-en="Upload Documents" data-bm="Muat Naik Dokumen">Upload Documents</span>
                        </button>
                    </div>
                </div>

                <div class="doc-panel d-none mt-3" data-doc-id="initial-upload">
                    <form class="dropzone" id="verificationDropzone" enctype="multipart/form-data">
                        <div class="dz-message p-4 text-center">
                            <h5 class="display-3 text-muted mb-2">
                                <i class="ti ti-folder-down"></i>
                            </h5>
                            <div class="text-muted" data-en="Drop your verification file here or click to upload." data-bm="Jatuhkan fail pengesahan anda di sini atau klik untuk memuat naik.">
                                Drop your verification file here or click to upload.
                            </div>
                        </div>
                    </form>

                    <div class="text-end mt-3">
                        <button id="uploadBtn" class="btn btn-primary" type="button" data-en="Upload File" data-bm="Muat Naik Fail">
                            <i class="ti ti-upload me-2"></i>
                            <span data-en="Upload File" data-bm="Muat Naik Fail">Upload File</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>



    <!-- Empty State (no verification submitted yet) -->
    <div class="hasNoImage" style="display: none;">
        <div class="card custom-card border mb-0">
            <div class="card-body p-5 text-center">
                <div class="mb-3">
                    <i class="ti ti-file-alert fs-40 text-warning op-7"></i>
                </div>
                <h5 class="fw-semibold mb-2" data-en="No Verification Documents" data-bm="Tiada Dokumen Pengesahan">No Verification Documents</h5>
                <p class="text-muted fs-13 mb-4" data-en="You haven't submitted any verification documents yet. Please upload your documents to start the verification process." data-bm="Anda belum mengemukakan dokumen pengesahan. Sila muat naik dokumen anda untuk memulakan proses pengesahan.">
                    You haven't submitted any verification documents yet. Please upload your documents to start the verification process.
                </p>

                <form class="dropzone" id="verificationDropzone" enctype="multipart/form-data">
                    <div class="dz-message p-5 text-center">
                        <h5 class="display-3 text-muted mb-2">
                            <i class="ti ti-folder-down"></i>
                        </h5>
                        <div class="text-muted" data-en="Drop your verification file here or click to upload." data-bm="Jatuhkan fail pengesahan anda di sini atau klik untuk memuat naik.">
                            Drop your verification file here or click to upload.
                        </div>
                    </div>
                </form>

                <div class="text-end mt-4">
                    <button id="uploadBtn" class="btn btn-primary" type="button" data-en="Upload File" data-bm="Muat Naik Fail">
                        <i class="ti ti-upload me-2"></i>
                        <span data-en="Upload File" data-bm="Muat Naik Fail">Upload File</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .attachment-list-item i {
        font-size: 20px;
    }

    .doc-row-toggle {
        cursor: pointer;
    }

    .doc-row-toggle .doc-toggle-icon {
        transition: transform .2s ease;
    }

    .doc-row-toggle[aria-expanded="true"] .doc-toggle-icon {
        transform: rotate(180deg);
    }

    .file-drop-area.is-dragover {
        border-color: rgb(var(--primary-rgb)) !important;
        background: rgba(var(--primary-rgb), .05);
    }
</style>