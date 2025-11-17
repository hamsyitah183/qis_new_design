

<div class="tab-pane fade border-0 p-0 active show" id="order-tab-pane" role="tabpanel" aria-labelledby="order-tab-pane"
    tabindex="0">
    <div class="p-3">
        <p class="mb-1 fw-semibold text-muted op-5 fs-20">01</p>
        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
            <div>Choose your account type</div>
        </div>

        <div class="row">
            <div class="col-xl-9 cursor-pointer mb-3">
                <div class="card custom-card card-style-6 border shadow-sm mb-xl-0 cursor-pointer type-element">
                    <label class="card-body p-3" for="address1">
                        <div class="d-flex gap-2">
                            <input class="form-check-input" type="radio" id="address1" name="type"
                                data-type = "individual" value="individu">

                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-3 type-div">
                            <div class="d-flex align-items-center gap-2">
                                <h6
                                    class="fs-16 mb-0 fw-semibold border border-container border-3 
                                        p-3 p-sm-2 rounded-3 d-flex justify-content-center align-items-center icon-box">
                                    <i class="bx bx-user fs-2 text-primary icon"></i>
                                </h6>

                                <span class="fs-15">Individual</span>
                            </div>


                        </div>

                    </label>
                </div>
            </div>
            <div class="col-xl-9 cursor-pointer">
                <div class="card custom-card card-style-6 border shadow-sm mb-0 cursor-pointer type-element">
                    <label class="card-body p-3" for="address2">
                        <div class="d-flex gap-2">
                            <input class="form-check-input" type="radio" id="address2" name="type"
                                data-type = "company" value="company">

                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-3 type-div">

                            <div class="d-flex align-items-center gap-2">
                                <h6
                                    class="fs-16 mb-0 fw-semibold border border-container border-3 
                                        p-3 p-sm-2 rounded-3 d-flex justify-content-center align-items-center icon-box">
                                    <i class="bx bx-buildings fs-2 mb-2 text-primary icon"></i>
                                </h6>
                                <span class="fs-15">Company</span>
                            </div>

                        </div>

                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="p-3 border-top border-block-start-dashed d-flex justify-content-between button-group align-items-center">
        <div class="text-start">
            <p class="text-muted mt-3 mb-0">Have an account? <a href="/login" class="text-primary">Sign In
                </a> here</p>
        </div>
        <button class="btn btn-primary ms-auto" id="nextToPersonalTab" type="button">
            Next
            <i class="ri-arrow-right-line ms-2 align-middle"></i>
        </button>
    </div>

</div>
