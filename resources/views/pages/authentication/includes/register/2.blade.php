<div class="tab-pane fade border-0 p-0" id="confirm-tab-pane" role="tabpanel" aria-labelledby="confirm-tab-pane"
    tabindex="0">
    <div class="p-3">
        <p class="mb-1 fw-semibold text-muted op-5 fs-20">02</p>
        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
            <div>Fill in your details</div>
        </div>
        <div class="row gy-3">
            <div class="col-xl-12">
                <label for="fullname" class="form-label fullnameLabel">Name</label>
                <input type="text" class="form-control" id="fullname" placeholder="" name="fullname">
            </div>

            <!-- Identity Number -->
            <div class="col-xl-6">
                <label class="form-label text-default icLabel">Identity Number</label>
                <input type="text" name="no_ic" class="form-control" id="no_ic">
            </div>

            <!-- Phone -->
            <div class="col-xl-6">
                <label class="form-label text-default phoneLabel">Phone Number</label>
                <input type="text" name="phone_number" class="form-control" id="phone_number">
            </div>

            <!-- Office number (optional) -->
            <div class="col-xl-6">
                <label class="form-label text-default officeLabel">Office Number (Optional)</label>
                <input type="text" name="office_number" class="form-control" placeholder=" (415) 555-0132">
            </div>

            <!-- Email -->
            <div class="col-xl-6">
                <label class="form-label text-default">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Johndoe@gmail.com" id="email">
            </div>

            <!-- Password -->
            <div class="col-xl-12">
                <label class="form-label text-default">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password">
            </div>

            <!-- Address 1 -->
            <div class="col-xl-12">
                <label class="form-label text-default">Address 1</label>
                <textarea name="address_1" id="" cols="30" rows="2" class="form-control border"></textarea>
            </div>

            <!-- Address 2 (optional) -->
            <div class="col-xl-12">
                <label class="form-label text-default">Address 2 (Optional)</label>
                <textarea name="address_2" id="" cols="30" rows="2" class="form-control border"></textarea>
            </div>

            <!-- Postcode -->
            <div class="col-xl-6">
                <label class="form-label text-default">Postcode</label>
                <input type="text" name="postcode" class="form-control" placeholder=" 89657">
            </div>

            <!-- District -->
            <div class="col-xl-6">
                <label class="form-label text-default">District</label>
                <input type="text" name="district" class="form-control" placeholder=" Kota Kinabalu">
            </div>

            <!-- State -->
            <div class="col-xl-12">
                <label class="form-label text-default">State</label>
                <input type="text" name="state" class="form-control" placeholder=" Sabah">
            </div>
        </div>
    </div>
    <div class="p-3 border-top border-block-start-dashed d-flex justify-content-between">
        <button class="btn btn-secondary" id="backToAccountTab" type="button">
            <i class="ri-arrow-left-line me-2 align-middle"></i>
            Back
        </button>

        <button class="btn btn-primary" id="nextToSummaryTab" type="button">
            Next
            <i class="ri-arrow-right-line ms-2 align-middle"></i>
        </button>
    </div>

</div>
