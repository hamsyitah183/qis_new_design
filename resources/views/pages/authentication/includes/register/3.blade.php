<div class="tab-pane fade border-0 p-0" id="shipped-tab-pane" role="tabpanel" aria-labelledby="shipped-tab-pane"
    tabindex="0">
    <div class="p-3">
        <p class="mb-1 fw-semibold text-muted op-5 fs-20">03</p>
        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
            <div data-en="Upload Attachment" data-bm="Muat Naik Lampiran">Upload Attachment</div>
        </div>
        <p class="text-muted fs-13 mb-3" data-en="Add one or more supporting documents (e.g. IC front/back, business registration). Accepted formats: JPG, PNG, PDF."
            data-bm="Tambah satu atau lebih dokumen sokongan (cth. IC depan/belakang, pendaftaran perniagaan). Format diterima: JPG, PNG, PDF.">
            Add one or more supporting documents (e.g. IC front/back, business registration). Accepted formats: JPG,
            PNG, PDF.
        </p>
    </div>

    <div class="row gy-3 px-3">
        <div class="card custom-card card-style-6 border shadow-sm mb-0">
            <!-- Drag & Drop area -->
            <div id="fileDropArea" class="p-4 text-center border border-dashed rounded-3" style="cursor: pointer;">
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

                <!-- Hidden multi-file input; kept name="attachment[]" so FormData collects every added file -->
                <input type="file" id="fileInput" style="display: none;" accept=".jpg,.jpeg,.png,.pdf" multiple
                    name="attachment[]">
            </div>
        </div>

        <!-- Selected file list -->
        <div id="fileListEmpty" class="text-center text-muted fs-13 py-2" data-en="No files added yet."
            data-bm="Belum ada fail ditambah.">
            No files added yet.
        </div>

        <ul id="fileListContainer" class="list-unstyled d-flex flex-column gap-2 mb-0"></ul>
    </div>


    <div class="p-3 border-top border-block-start-dashed d-flex justify-content-between mt-3">
        <button class="btn btn-auth-secondary" id="backToDetailsTab" type="button">
            <i class="ri-arrow-left-line me-2 align-middle"></i>
            <span data-en="Back" data-bm="Kembali">Back</span>
        </button>

        <button class="btn btn-auth-primary" id="finishRegistrationBtn" type="button">
            <span data-en="Submit" data-bm="Hantar">Submit</span>
        </button>
    </div>
</div>

<style>
    #fileDropArea.is-dragover {
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
</style>

<script>
    (function () {
        var dropArea = document.getElementById('fileDropArea');
        var fileInput = document.getElementById('fileInput');
        var listContainer = document.getElementById('fileListContainer');
        var emptyState = document.getElementById('fileListEmpty');

        var MAX_FILES = 10;
        var MAX_SIZE_MB = 10;

        // Holds the actual File objects currently selected, in order.
        var selectedFiles = [];

        function formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function iconFor(name) {
            var ext = name.split('.').pop().toLowerCase();
            if (ext === 'pdf') return 'ti ti-file-type-pdf';
            return 'ti ti-photo';
        }

        // Rebuilds the hidden <input type="file"> FileList from selectedFiles
        // using DataTransfer, so the array stays the source of truth and the
        // actual form submission (FormData) always matches what's rendered.
        function syncInput() {
            var dt = new DataTransfer();
            selectedFiles.forEach(function (file) {
                dt.items.add(file);
            });
            fileInput.files = dt.files;
        }

        function render() {
            listContainer.innerHTML = '';
            emptyState.style.display = selectedFiles.length ? 'none' : 'block';

            selectedFiles.forEach(function (file, index) {
                var li = document.createElement('li');
                li.className = 'file-list-item';
                li.innerHTML =
                    '<i class="' + iconFor(file.name) + '"></i>' +
                    '<div class="file-meta">' +
                    '<div class="file-name">' + file.name + '</div>' +
                    '<div class="file-size">' + formatSize(file.size) + '</div>' +
                    '</div>' +
                    '<button type="button" class="file-remove" aria-label="Remove">&times;</button>';

                li.querySelector('.file-remove').addEventListener('click', function () {
                    selectedFiles.splice(index, 1);
                    syncInput();
                    render();
                });

                listContainer.appendChild(li);
            });
        }

        function addFiles(fileList) {
            var incoming = Array.prototype.slice.call(fileList);

            incoming.forEach(function (file) {
                if (selectedFiles.length >= MAX_FILES) return;

                if (file.size > MAX_SIZE_MB * 1024 * 1024) {
                    alert(file.name + ' exceeds the ' + MAX_SIZE_MB + 'MB limit.');
                    return;
                }

                // Skip exact duplicates (same name + size)
                var isDuplicate = selectedFiles.some(function (f) {
                    return f.name === file.name && f.size === file.size;
                });
                if (isDuplicate) return;

                selectedFiles.push(file);
            });

            syncInput();
            render();
        }

        dropArea.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function (e) {
            addFiles(e.target.files);
            // reset so selecting the exact same file again still fires 'change'
            fileInput.value = '';
        });

        ['dragenter', 'dragover'].forEach(function (evt) {
            dropArea.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropArea.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (evt) {
            dropArea.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropArea.classList.remove('is-dragover');
            });
        });

        dropArea.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files) {
                addFiles(e.dataTransfer.files);
            }
        });

        render();
    })();
</script>