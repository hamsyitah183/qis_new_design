<!-- MODAL ADD ITEM -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header ">
                <h5 class="modal-title" id="addExporterModalLabel" data-en="Add Consignment" data-bm="Tambah Konsainan">
                    Add Consignment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Body -->
            <!-- <form id="addExporterForm"> -->
            <div class="modal-body">
                <div class="row gy-4 mb-3 p-4">

                    <div class="col-6 col-md-6 col-sm-12">
                        <label for="itemCategory" class="form-label" data-en="Category" data-bm="Kategori">Category
                        </label><a style="color:red"> * </a>
                        <select class="form-select" id="itemCategory" name="itemCategory">
                        </select>

                    </div>
                    <div class="col-6 col-md-6 col-sm-12">
                        <label for="itemSelect" class="form-label" data-en="Item Name" data-bm="Nama Item">Item Name</label> <a
                            style="color:red"> * </a>
                        <select class="form-select" id="itemSelect" name="itemSelect">
                        </select>

                    </div>

                    <div class="col-12 col-sm-12" id="customItemWrapper" style="display:none;">
                        <label for="customItemName" class="form-label" data-en="Item Name"
                            data-bm="Nama Item">Item Name</label>
                        <input type="text" class="form-control" id="customItemName" placeholder="Enter item name">
                    </div>

                    <div class="col-xl-4 col-lg-4 col-sm-12">
                        <label for="itemQuantity" class="form-label" data-en="Quantity"
                            data-bm="Kuantiti">Quantity</label><a style="color:red"> * </a>
                        <input type="number" class="form-control" id="itemQuantity" name="itemQuantity" placeholder="">
                    </div>



                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <label for="certificateNo" class="form-label" data-en="Certificate No (MyGAP or myOrganic)"
                            data-bm="No Sijil (MyGap or myOrganic)">Certificate No (MyGAP or myOrganic)</label><a
                            style="color:red"> * </a>
                        <input class="form-control" id="certificateNo" name="certificateNo" />


                    </div>
                    <div class="row gy-4">
                        <div class="col-xl-12">

                            <div class="card-header">
                                <div class="card-title" data-en="Attachment" data-bm="Lampiran">
                                    Attachment
                                </div>
                            </div>

                            <small class="text-muted attachmentInstruction"></small>

                            <div class="card-body">
                                <form id="itemDropzone" method="post" class="dz-clickable"
                                    enctype="multipart/form-data"><!--data-single="true"  -->
                                    @csrf
                                    <div class="dz-default dz-message">
                                        <button class="dz-button p-5 border w-100 border-radius" type="button"
                                            data-en="Drop files here to upload"
                                            data-bm="Jatuhkan dokumen untuk muat naik">
                                            Drop files here to upload
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <button id="saveBtn" type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i> Add Item
                </button>
            </div>
            <!-- </form> -->
        </div> <!-- end class:modal-content -->
    </div>
</div> <!-- end modal -->




<script></script>
