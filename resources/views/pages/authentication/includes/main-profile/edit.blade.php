<ul class="list-group list-group-flush border rounded-3">
    <li class="list-group-item p-3">
        <span class="fw-medium fs-15 d-block mb-3" data-en="Personal Info :" data-bm="Maklumat Peribadi :">Personal Info :</span>
        <div class="row gy-3 align-items-center">
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium" data-en="Name :" data-bm="Nama :">Name :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="text" class="form-control fullname" name="fullname" placeholder="Fullname" data-en="Fullname" data-bm="Nama Penuh" data-i18n-attr="placeholder"
                    value="">
            </div>
            @if ($user['type'] == 'internal')
                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium" data-en="Position :" data-bm="Jawatan :">Position :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <input type="text" name="position" class="form-control position" placeholder="Position" data-en="Position" data-bm="Jawatan" data-i18n-attr="placeholder"
                        value="">
                </div>

                <div class="col-xl-3 d-none">
                    <div class="lh-1">
                        <span class="fw-medium">Office :</span>
                    </div>
                </div>
                <div class="col-xl-9 d-none">
                    <input type="text" name="office" class="form-control address" placeholder="Office"
                        value="">
                </div>
            @else
                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium" data-en="Account Type :" data-bm="Jenis Akaun :">Account Type :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <select class="form-select" id="account_type" name="account_type" required>
                        <option value="individu" data-en="Individu" data-bm="Individu">Individu</option>
                        <option value="company" data-en="Company" data-bm="Syarikat">Company</option>
                    </select>
                </div>
            @endif

            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium" data-en="IC :" data-bm="KP :">IC :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="text" name="no_ic" class="form-control ic" placeholder="IC Number" data-en="IC Number" data-bm="Nombor KP" data-i18n-attr="placeholder" value="">
            </div>


        </div>
    </li>
    <li class="list-group-item p-3">
        <span class="fw-medium fs-15 d-block mb-3" data-en="Contact Info :" data-bm="Maklumat Perhubungan :">Contact Info :</span>
        <div class="row gy-3 align-items-center">
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium" data-en="Email :" data-bm="E-mel :">Email :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="email" name="email" class="form-control email" placeholder="Email" data-en="Email" data-bm="E-mel" data-i18n-attr="placeholder"
                    value="">
            </div>
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium" data-en="Phone :" data-bm="Telefon :">Phone :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="text" name="phone_number" class="form-control phone_number" placeholder="Phone Number" data-en="Phone Number" data-bm="Nombor Telefon" data-i18n-attr="placeholder"
                    value="">
            </div>
            @if ($user['type'] == 'public')
                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium" data-en="Office Phone Number :" data-bm="Nombor Telefon Pejabat :">Office Phone Number :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <input type="text" name="office_number" class="form-control office_number"
                        placeholder="Office Phone Number" data-en="Office Phone Number" data-bm="Nombor Telefon Pejabat" data-i18n-attr="placeholder" value="">
                </div>

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium" data-en="Address 1 :" data-bm="Alamat 1 :">Address 1 :</span>
                    </div>
                </div>
                <div class="col-xl-9">

                    <textarea name="address_1" id="" cols="30" rows="3" class="form-control border address_1"></textarea>
                </div>

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium" data-en="Address 2 :" data-bm="Alamat 2 :">Address 2 :</span>
                    </div>
                </div>
                <div class="col-xl-9">

                    <textarea name="address_2" id="" cols="30" rows="3" class="form-control border address_2"></textarea>
                </div>

                

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium" data-en="State :" data-bm="Negeri :">State :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <select name="state" class="form-select state" required>
                        <option value="" id = "state" data-en="Select State" data-bm="Pilih Negeri">Select State</option>
                        {{-- @foreach ($states as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                        @endforeach --}}
                    </select>
                </div>
                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium" data-en="District :" data-bm="Daerah :">District :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <select name="district" class="form-select district" required>
                        <option value="" id="district" data-en="Select District" data-bm="Pilih Daerah">Select District</option>
                    </select>
                </div>

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium" data-en="Postcode :" data-bm="Poskod :">Postcode :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <select name="postcode" class="form-select postcode" required>
                        <option value="" id="postcode" data-en="Select Postcode" data-bm="Pilih Poskod">Select Postcode</option>
                    </select>
                </div>

               
            @endif



            {{-- buttons --}}
            <div class="d-flex justify-content-end align-items-end">
                <button class="btn-sm btn-secondary border" type="submit" data-en="Update" data-bm="Kemaskini">Update</button>
            </div>
        </div>
    </li>


</ul>
