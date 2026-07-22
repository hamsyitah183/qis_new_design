<!-- MODAL ADD ITEM -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="addExporterModalLabel" data-en="Add Consignment" data-bm="Tambah Konsainan">
                    <i class='bx bx-package me-2'></i>Add Consignment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="row gy-4 mb-3 p-4">
                    <div class="news"></div>
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                        <label for="itemSelect" class="form-label" data-en="Item" data-bm="Item">Item <a style="color:red"> * </a></label>
                        <select class="form-select" id="itemSelect" name="itemSelect">
                        </select>
                        <small style="color:red" data-en="Item referring to the exporter's Country" data-bm="Item merujuk kepada Negara pengeksport">Item referring to the exporter's Country</small>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                        <label for="itemValue" class="form-label" data-en="Value (RM)" data-bm="Nilai (RM)">Value (RM)<a style="color:red"> * </a></label>
                        <input type="number" class="form-control" id="itemValue" name="itemValue" placeholder="RM ...">
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-12">
                        <label for="itemQuantity" class="form-label" data-en="Quantity" data-bm="Kuantiti">Quantity<a style="color:red"> * </a></label>
                        <input type="number" class="form-control" id="itemQuantity" name="itemQuantity">
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                        <label for="itemMeasure" class="form-label" data-en="Measurement Unit" data-bm="Unit Ukuran">Measurement Unit<a style="color:red"> * </a></label>
                        <select class="form-select" id="itemMeasure" name="itemMeasure">
                            <option value="" data-en="-- Select Measurement Unit --" data-bm="-- Pilih Unit Ukuran --">-- Select Measurement Unit --</option>
                            @foreach ($pubmeasure as $measure)
                                <option value="{{ $measure->cate_code }}">{{ $measure->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemPurpose" class="form-label" data-en="Purpose" data-bm="Tujuan">Purpose<a style="color:red"> * </a></label>
                        <select class="form-select" id="itemPurpose" name="itemPurpose">
                            <option value="" data-en="-- Select Purpose --" data-bm="-- Pilih Tujuan --">-- Select Purpose --</option>
                            @foreach ($pubpurpose as $purpose)
                                <option value="{{ $purpose->cate_code }}" data-description="{{ $purpose->description }}">
                                    {{ $purpose->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemUses" class="form-label" data-en="Uses" data-bm="Kegunaan">Uses<a style="color:red"> * </a></label>
                        <select class="form-select" id="itemUses" name="itemUses"></select>
                    </div>
                    <div class="row gy-4">
                        <div class="col-xl-12">
                            <div class="card-header">
                                <div class="card-title" data-en="Attachment" data-bm="Lampiran">
                                    Attachment<a style="color:red"> * </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="itemDropzone" method="post" class="dz-clickable" enctype="multipart/form-data">
                                    @csrf
                                    <div class="dz-default dz-message">
                                        <button class="dz-button p-5 border w-100 border-radius" type="button">
                                            <i class='bx bx-cloud-upload' style="font-size:22px"></i><br>
                                            <span data-en="Drop files here to upload" data-bm="Lepaskan fail di sini untuk memuat naik">Drop files here to upload</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <button id="saveBtn" type="submit" class="btn btn-primary ipa-btn-primary" data-en="Add Item" data-bm="Tambah Item">
                    <i class="bx bx-save me-1"></i> Add Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Item File Preview & Rename Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="itemFilePreviewOffcanvas" aria-labelledby="itemFilePreviewOffcanvasLabel" style="width: 70%; max-width: 900px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="itemFilePreviewOffcanvasLabel">
            <i class="bi bi-file-earmark me-2"></i> <span id="itemFileName" data-en="File Preview" data-bm="Pratonton Fail">File Preview</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 60px);">
        <div class="pd-nav flex-shrink-0">
            <ul class="nav nav-pills flex-column" id="itemFileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ifile-view-tab" data-bs-toggle="tab"
                        data-bs-target="#ifile-view" type="button" role="tab" aria-selected="true"
                        data-bs-placement="right" title="View" >
                        <i class="bi bi-eye"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ifile-details-tab" data-bs-toggle="tab"
                        data-bs-target="#ifile-details" type="button" role="tab" aria-selected="false"
                        data-bs-placement="right" title="Details" >
                        <i class="bi bi-info-circle"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ifile-edit-tab" data-bs-toggle="tab"
                        data-bs-target="#ifile-edit" type="button" role="tab" aria-selected="false"
                        data-bs-placement="right" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content flex-grow-1 p-3 overflow-auto" id="itemFileTabContent">
            <div class="tab-pane fade show active" id="ifile-view" role="tabpanel">
                <div id="itemFilePreviewContainer" class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br><span data-en="Select a file" data-bm="Pilih fail">Select a file</span></div>
                </div>
            </div>
            <div class="tab-pane fade" id="ifile-details" role="tabpanel">
                <div id="itemFileDetails" class="py-2"></div>
            </div>
            <div class="tab-pane fade" id="ifile-edit" role="tabpanel">
                <div class="p-3">
                    <div class="mb-3">
                        <label for="itemFileEditName" class="form-label" data-en="File Name" data-bm="Nama Fail">File Name</label>
                        <input type="text" class="form-control" id="itemFileEditName" data-en="Enter new file name" data-bm="Masukkan nama fail baharu" placeholder="Enter new file name">
                    </div>
                    <button type="button" class="btn btn-primary" id="itemFileSaveBtn" data-en="Save Changes" data-bm="Simpan Perubahan">
                        <i class="bi bi-check me-2"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>