<div class="tab-pane fade border-0 p-0" id="confirm-tab-pane" role="tabpanel" aria-labelledby="confirm-tab-pane"
    tabindex="0">
    <div class="p-3">
        <p class="mb-1 fw-semibold text-muted op-5 fs-20">02</p>
        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
            <div>Fill in your details</div>
        </div>
        <div class="row gy-3">
            <div class="col-xl-12">
                <label for="fullname" class="form-label fullnameLabel">Name  <span class="text-primary2">*</span></label>
                <input type="text" class="form-control" id="fullname" placeholder="" name="fullname">
            </div>

            <!-- Identity Number -->
            <div class="col-xl-6">
                <label class="form-label text-default icLabel">Identity Number  <span class="text-primary2">*</span></label>
                <input type="text" name="no_ic" class="form-control" id="no_ic">
            </div>

            <!-- Phone -->
            <div class="col-xl-6">

                <label class="form-label text-default phoneLabel">Phone Number  <span class="text-primary2">*</span></label>
                <div class="d-flex justify-content-between">
                    <div class="">
                        <select name="phoneNumber" id="phone_country" class = "form-control">
                            @foreach ($countryNo as $item)
                                <option value="{{  $item->start_no  }}">{{ $item->country }} ({{ $item->start_no }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-100">

                        <input type="text" name="phone_number" class="form-control" id="phone_number">
                    </div>
                </div>

            </div>

            <!-- Office number (optional) -->
            <div class="col-xl-6">
                <label class="form-label text-default officeLabel">Office Number (Optional)</label>
                <input type="text" name="office_number" class="form-control" placeholder=" (415) 555-0132">
            </div>

            <!-- Email -->
            <div class="col-xl-6">
                <label class="form-label text-default">Email  <span class="text-primary2">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="Johndoe@gmail.com"
                    id="email">
            </div>

            <!-- Password -->
            <div class="col-xl-12">
                <label class="form-label text-default">Password  <span class="text-primary2">*</span></label>
                <input type="password" name="password" class="form-control" placeholder="Password">
            </div>

            <!-- Address 1 -->
            <div class="col-xl-12">
                <label class="form-label text-default">Address 1  <span class="text-primary2">*</span></label>
                <textarea name="address_1" id="" cols="30" rows="2" class="form-control border"></textarea>
            </div>

            <!-- Address 2 (optional) -->
            <div class="col-xl-12">
                <label class="form-label text-default">Address 2 (Optional)</label>
                <textarea name="address_2" id="" cols="30" rows="2" class="form-control border"></textarea>
            </div>

            <!-- Postcode -->
            <div class="col-xl-6">
                <label class="form-label text-default">Postcode  <span class="text-primary2">*</span></label>
                <select name="postcode" class="form-control" id="postcode">
                    <option value="">Select Postcode</option>
                </select>
            </div>

            <!-- State -->
            <div class="col-xl-6">
                <label class="form-label text-default">State  <span class="text-primary2">*</span></label>
                <select name="state" class="form-control" id="state">
                    <option value="">Select State</option>
                </select>
            </div>

            <!-- District -->
            <div class="col-xl-12">
                <label class="form-label text-default">District  <span class="text-primary2">*</span></label>
                <select name="district" class="form-control" id="district" disabled>
                    <option value="">Select District</option>
                </select>
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
