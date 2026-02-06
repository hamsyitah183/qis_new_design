<!-- MODAL ADD ITEM -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header ">
                <h5 class="modal-title" id="addExporterModalLabel">
                    Add Consignment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Body -->
            <!-- <form id="addExporterForm"> -->
            <div class="modal-body">
                <div class="row gy-4 mb-3 p-4">
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemSelect" class="form-label">Item <a style="color:red"> * </a></label>
                        <select class="form-select" id="itemSelect" name="itemSelect">
                            <!-- <option value="aa" >-- Select Item</option>
                                                                                    <option value="aasda" >aaadwd</option> -->
                        </select>
                        <small style="color:red">Item refering to the exporter's Country</small>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">

                        <label for="itemValue" class="form-label">Value (RM)<a style="color:red"> * </a></label>
                                <input type="number" class="form-control" id="itemValue" name="itemValue"
                                    placeholder="RM ...">
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemQuantity" class="form-label">Quantity<a style="color:red"> * </a></label>
                        <input type="number" class="form-control" id="itemQuantity" name="itemQuantity">
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemMeasure" class="form-label">Measurement Unit<a style="color:red"> * </a></label>
                        <select class="form-select" id="itemMeasure" name="itemMeasure">
                            <option value="">-- Select Measurement Unit --</option>
                            @foreach ($pubmeasure as $measure)
                                <option value="{{ $measure->cate_code }}">{{ $measure->description }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemPurpose" class="form-label">Purpose<a style="color:red"> * </a></label>
                        <select class="form-select" id="itemPurpose" name="itemPurpose">
                            <option value="">-- Select Purpose --</option>
                            @foreach ($pubpurpose as $purpose)
                                <option value="{{ $purpose->cate_code }}" data-description="{{ $purpose->description }}">
                                    {{ $purpose->description }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <label for="itemUses" class="form-label">Uses<a style="color:red"> * </a></label>
                        <select class="form-select" id="itemUses" name="itemUses">

                        </select>
                    </div>
                    <div class="row gy-4">
                        <div class="col-xl-12">

                            <div class="card-header">
                                <div class="card-title">
                                    Attachment<a style="color:red"> * </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="itemDropzone" method="post" class="dz-clickable"
                                    enctype="multipart/form-data"><!--data-single="true"  -->
                                    @csrf
                                    <div class="dz-default dz-message">
                                        <button class="dz-button p-5 border w-100 border-radius" type="button">
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