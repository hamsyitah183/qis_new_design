<div class="tab-pane fade border-0 p-0 active show" id="order-tab-pane" role="tabpanel" aria-labelledby="order-tab-pane"
    tabindex="0">
    <div class="p-3">
       
        <div class="row p-2 pt-3">

            {{-- Import Permit --}}
            <div class="col-xl-4 cursor-pointer mb-3">
                <div class="card custom-card card-style-6 border shadow-sm type-element">
                    <label class="card-body p-3" for="type-import">
                        <input class="form-check-input d-none" type="radio" id="type-import" name="type"
                            value="import-permit" data-type="import-permit">

                        <div class="d-flex justify-content-center mb-3 type-div">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <h6 class="icon-box fs-16 fw-semibold border border-3 p-3 rounded-3">
                                    <i class="bx bx-package fs-2 text-primary icon"></i>
                                </h6>
                                <span class="fs-15 fw-bold">Import Permit</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Inspection Certificate --}}
            <div class="col-xl-4 cursor-pointer mb-3">
                <div class="card custom-card card-style-6 border shadow-sm type-element">
                    <label class="card-body p-3" for="type-inspection">
                        <input class="form-check-input d-none" type="radio" id="type-inspection" name="type"
                            value="inspection_certificate" data-type="inspection_certificate">

                        <div class="d-flex justify-content-center mb-3 type-div">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <h6 class="icon-box fs-16 fw-semibold border border-3 p-3 rounded-3">
                                    <i class="bx bx-search fs-2 text-primary icon"></i>
                                </h6>
                                <span class="fs-15 fw-bold">Inspection Certificate</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Consignment Certificate --}}
            <div class="col-xl-4 cursor-pointer mb-3">
                <div class="card custom-card card-style-6 border shadow-sm type-element">
                    <label class="card-body p-3" for="type-consignment">
                        <input class="form-check-input d-none" type="radio" id="type-consignment" name="type"
                            value="consignment" data-type="consignment">

                        <div class="d-flex justify-content-center mb-3 type-div">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <h6 class="icon-box fs-16 fw-semibold border border-3 p-3 rounded-3">
                                    <i class="bx bx-file fs-2 text-primary icon"></i>
                                </h6>
                                <span class="fs-15 fw-bold">Consignment Certificate</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

        </div>

    </div>

    <div
        class="p-3 border-top border-block-start-dashed d-flex justify-content-between button-group align-items-center">

        <button class="btn btn-primary ms-auto" id="nextToPersonalTab" type="button">
            Next
            <i class="ri-arrow-right-line ms-2 align-middle"></i>
        </button>
    </div>

</div>
