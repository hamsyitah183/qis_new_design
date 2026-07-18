<div class="tab-pane fade border-0 p-0" id="confirm-tab-pane" role="tabpanel" aria-labelledby="confirm-tab-pane"
    tabindex="0">
    <div class="p-3">
        <p class="mb-1 fw-semibold text-muted op-5 fs-20">02</p>
        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
            <div data-en="Fill in your details" data-bm="Isi butiran anda">Fill in your details</div>
        </div>
        <div class="row gy-3">
            <div class="col-xl-12">
                <label for="fullname" class="form-label fullnameLabel"><span data-en="Name"
                        data-bm="Nama">Name</span> <span class="text-primary2">*</span></label>
                <input type="text" class="form-control" id="fullname" placeholder="" name="fullname">
            </div>

            <!-- Identity Number -->
            <div class="col-xl-6">
                <label class="form-label text-default icLabel"><span data-en="Identity Number"
                        data-bm="Nombor Kad Pengenalan">Identity Number</span> <span
                        class="text-primary2">*</span></label>
                <input type="text" name="no_ic" class="form-control" id="no_ic">
            </div>

            <!-- Phone -->
            <div class="col-xl-6">
                <label class="form-label text-default phoneLabel"><span data-en="Phone Number"
                        data-bm="Nombor Telefon">Phone Number</span> <span class="text-primary2">*</span></label>
                <div class="d-flex justify-content-between">
                    <div class="">
                        <select name="phoneNumber" id="phone_country" class="form-control">
                            @foreach ($countryNo as $item)
                                <option value="{{ $item->start_no }}">{{ $item->country }} ({{ $item->start_no }})
                                </option>
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
                <label class="form-label text-default officeLabel"><span data-en="Office Number (Optional)"
                        data-bm="Nombor Pejabat (Pilihan)">Office Number (Optional)</span></label>
                <input type="text" name="office_number" class="form-control" data-en="(415) 555-0132"
                    data-bm="(415) 555-0132" data-i18n-attr="placeholder" placeholder="(415) 555-0132">
            </div>

            <!-- Email -->
            <div class="col-xl-6">
                <label class="form-label text-default"><span data-en="Email" data-bm="Emel">Email</span> <span
                        class="text-primary2">*</span></label>
                <input type="email" name="email" class="form-control" data-en="Johndoe@gmail.com"
                    data-bm="Johndoe@gmail.com" data-i18n-attr="placeholder" placeholder="Johndoe@gmail.com"
                    id="email">
            </div>

            <!-- Password -->
            <div class="col-xl-12">
                <label class="form-label text-default"><span data-en="Password" data-bm="Kata Laluan">Password</span>
                    <span class="text-primary2">*</span></label>
                <input type="password" name="password" class="form-control" data-en="Password" data-bm="Kata Laluan"
                    data-i18n-attr="placeholder" placeholder="Password">
            </div>

            <!-- Address 1 -->
            <div class="col-xl-12">
                <label class="form-label text-default"><span data-en="Address 1" data-bm="Alamat 1">Address 1</span>
                    <span class="text-primary2">*</span></label>
                <textarea name="address_1" id="" cols="30" rows="2" class="form-control border"></textarea>
            </div>

          
            <!-- State -->
            <div class="col-xl-6">
                <label class="form-label text-default"><span data-en="State" data-bm="Negeri">State</span> <span
                        class="text-primary2">*</span></label>
                <select name="state" class="form-control state-register" id="state">
                    <option value="" data-en="Select State" data-bm="Pilih Negeri">Select State</option>
                </select>
            </div>

            <!-- District -->
            <div class="col-xl-6">
                <label class="form-label text-default"><span data-en="District"
                        data-bm="Daerah">District</span> <span class="text-primary2">*</span></label>
                <select name="district" class="form-control district-register" id="district" disabled>
                    <option value="" data-en="Select District" data-bm="Pilih Daerah">Select District</option>
                </select>
            </div>

            <!-- Postcode -->
            <div class="col-xl-12">
                <label class="form-label text-default"><span data-en="Postcode"
                        data-bm="Poskod">Postcode</span> <span class="text-primary2">*</span></label>
                <select name="postcode" class="form-control postcode-register" id="postcode">
                    <option value="" data-en="Select Postcode" data-bm="Pilih Poskod">Select Postcode</option>
                </select>
            </div>
        </div>
    </div>
    <div class="p-3 border-top border-block-start-dashed d-flex justify-content-between">
        <button class="btn btn-auth-secondary" id="backToAccountTab" type="button">
            <i class="ri-arrow-left-line me-2 align-middle"></i>
            <span data-en="Back" data-bm="Kembali">Back</span>
        </button>

        <button class="btn btn-auth-primary" id="nextToSummaryTab" type="button">
            <span data-en="Next" data-bm="Seterusnya">Next</span>
            <i class="ri-arrow-right-line ms-2 align-middle"></i>
        </button>
    </div>

</div>