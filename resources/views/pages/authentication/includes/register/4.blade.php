<div class="tab-pane fade border-0 p-0" id="password-tab-pane" role="tabpanel" aria-labelledby="password-tab-pane"
    tabindex="0">
    <div class="p-3">
        <p class="mb-1 fw-semibold text-muted op-5 fs-20">04</p>
        <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
            <div data-en="Password" data-bm="Kata Laluan">Password</div>
        </div>
        <p class="text-muted fs-13 mb-3"
            data-en="Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character."
            data-bm="Kata laluan mesti sekurang-kurangnya 8 aksara panjang dan termasuk sekurang-kurangnya satu huruf besar, satu huruf kecil, satu nombor, dan satu aksara khas.">
            Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter,
            one number, and one special character.
        </p>
    </div>

    <div class="row">
        <!-- Password -->
        <div class="col-xl-12">
            <label class="form-label text-default"><span data-en="Password" data-bm="Kata Laluan">Password</span>
                <span class="text-primary2">*</span></label>
            <input type="password" name="password" class="form-control" data-en="Password" data-bm="Kata Laluan"
                data-i18n-attr="placeholder" placeholder="Password">
        </div>
        <div class="col-xl-12 mt-4">
            <label class="form-label text-default"><span data-en="Confirm Password" data-bm="Sahkan Kata Laluan">Confirm
                    Password</span>
                <span class="text-primary2">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" data-en="Confirm Password"
                data-bm="Sahkan Kata Laluan" data-i18n-attr="placeholder" placeholder="Confirm Password">
        </div>
    </div>

    <div class="p-3 border-top border-block-start-dashed d-flex justify-content-between mt-3">
        <button class="btn btn-auth-secondary" id="backToSummaryTab" type="button">
            <i class="ri-arrow-left-line me-2 align-middle"></i>
            <span data-en="Back" data-bm="Kembali">Back</span>
        </button>

        <button class="btn btn-auth-primary" id="finishRegistrationBtn" type="button">
            <span data-en="Submit" data-bm="Hantar">Submit</span>
        </button>
    </div>
</div>
