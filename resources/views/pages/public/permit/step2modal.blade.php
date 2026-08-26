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
                    <!-- Alert container for limit warnings -->
                    <div class="news"></div>

                    <!-- Item Select -->
                    <div class="col-xl-6 col-sm-12">
                        <label for="itemSelect" class="form-label" data-en="Item" data-bm="Item">Item <a
                                style="color:red"> * </a></label>
                        <select class="form-select" id="itemSelect" name="itemSelect">
                            <!-- Options populated by JS, including "Others" -->
                        </select>
                        <small style="color:red" data-en="Item referring to the exporter's Country"
                            data-bm="Item merujuk kepada Negara pengeksport">Item referring to the exporter's
                            Country</small>
                    </div>

                    <!-- Custom Item Name (hidden by default, shown when "Others" selected) -->
                    <div class="col-xl-6 col-sm-12" id="customItemWrapper" style="display:none;">
                        <label for="customItemName" class="form-label" data-en="Custom Item Name"
                            data-bm="Nama Item Tersuai">
                            Custom Item Name <a style="color:red"> * </a>
                        </label>
                        <input type="text" class="form-control" id="customItemName" name="customItemName"
                            placeholder="Enter custom item name" data-en="Enter custom item name"
                            data-bm="Masukkan nama item tersuai">
                    </div>


                    <!-- Value -->
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemValue" class="form-label" data-en="Value (RM)" data-bm="Nilai (RM)">Value (RM)<a
                                style="color:red"> * </a></label>
                        <input type="number" class="form-control" id="itemValue" name="itemValue" placeholder="RM ...">
                    </div>

                    <!-- Quantity -->
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                        <label for="itemQuantity" class="form-label" data-en="Quantity" data-bm="Kuantiti">Quantity<a
                                style="color:red"> * </a></label>
                        <input type="number" class="form-control" id="itemQuantity" name="itemQuantity">
                    </div>

                    <!-- Measurement Unit -->
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12">
                        <label for="itemMeasure" class="form-label" data-en="Measurement Unit"
                            data-bm="Unit Ukuran">Measurement Unit<a style="color:red"> * </a></label>
                        <select class="form-select" id="itemMeasure" name="itemMeasure">
                            <option value="" data-en="-- Select Measurement Unit --"
                                data-bm="-- Pilih Unit Ukuran --">-- Select Measurement Unit --</option>
                            @foreach ($pubmeasure as $measure)
                                <option value="{{ $measure->cate_code }}">{{ $measure->description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Purpose -->
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemPurpose" class="form-label" data-en="Purpose" data-bm="Tujuan">Purpose<a
                                style="color:red"> * </a></label>
                        <select class="form-select" id="itemPurpose" name="itemPurpose">
                            <option value="" data-en="-- Select Purpose --" data-bm="-- Pilih Tujuan --">-- Select
                                Purpose --</option>
                            @foreach ($pubpurpose as $purpose)
                                <option value="{{ $purpose->cate_code }}"
                                    data-description="{{ $purpose->description }}">
                                    {{ $purpose->description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Uses -->
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemUses" class="form-label" data-en="Uses" data-bm="Kegunaan">Uses<a
                                style="color:red"> * </a></label>
                        <select class="form-select" id="itemUses" name="itemUses"></select>
                    </div>

                    <!-- Attachment Dropzone -->
                    <div class="row gy-4">
                        <div class="col-xl-12">
                            <div class="card-header">
                                <div class="card-title" data-en="Attachment" data-bm="Lampiran">
                                    Attachment<a style="color:red"> * </a>
                                </div>
                            </div>
                            <!-- Attachment instruction (hidden initially) -->
                            <div class="col-12 attachmentInstruction" style="display:none; color:red; font-size: 12px;"></div>

                            <div class="card-body">
                                <form id="itemDropzone" method="post" class="dz-clickable"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="dz-default dz-message">
                                        <button class="dz-button p-5 border w-100 border-radius" type="button">
                                            <i class='bx bx-cloud-upload' style="font-size:22px"></i><br>
                                            <span data-en="Drop files here to upload"
                                                data-bm="Lepaskan fail di sini untuk memuat naik">Drop files here to
                                                upload</span>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel"
                    data-bm="Batal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <button id="saveBtn" type="submit" class="btn btn-primary ipa-btn-primary" data-en="Add Item"
                    data-bm="Tambah Item">
                    <i class="bx bx-save me-1"></i> Add Item
                </button>
            </div>
        </div>
    </div>
</div>
