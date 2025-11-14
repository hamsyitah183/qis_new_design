<div class="tab-pane fade border-0 p-0" id="shipped-tab-pane" role="tabpanel" aria-labelledby="shipped-tab-pane"
    tabindex="0">
    <div class="p-3">
        <p class="mb-1 fw-semibold text-muted op-5 fs-20">03</p>
        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
            <div>Upload Attachment </div>
        </div>
    </div>
    <div class="row gy-3">
        <div class="card custom-card card-style-6 border shadow-sm mb-0">
            <!-- Drag & Drop area -->
            <div id="fileDropArea" class="p-3 text-center border border-dashed" style="cursor: pointer;">
                <div class="">
                    <h5 class="display-3 text-muted">
                        <i class="ti ti-folder-down"></i>
                    </h5>
                    <div class="text-muted">
                        Drop your verification file here or click to upload.
                    </div>

                    <!-- Hidden file input -->
                    <input type="file" id="fileInput" style="display: none;" accept=".jpg,.jpeg,.png,.pdf" name="attachment">
                    <div id="fileName" class="mt-2 fw-semibold text-dark"></div>
                </div>
            </div>
        </div>
    </div>


    <div class="p-3 border-top border-block-start-dashed d-sm-flex justify-content-between">
        <button class="btn btn-secondary" id="backToDetailsTab" type="button">
            <i class="ri-arrow-left-line me-2 align-middle"></i>
            Back
        </button>

        <button class="btn btn-primary" id="finishRegistrationBtn" type="button">
            Continue
            <i class="ri-arrow-right-line ms-2 align-middle"></i>
        </button>
    </div>
</div>
