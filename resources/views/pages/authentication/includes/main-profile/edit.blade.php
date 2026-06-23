<ul class="list-group list-group-flush border rounded-3">
    <li class="list-group-item p-3">
        <span class="fw-medium fs-15 d-block mb-3">Personal Info :</span>
        <div class="row gy-3 align-items-center">
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium">Name :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="text" class="form-control fullname" name="fullname" placeholder="Fullname"
                    value="">
            </div>
            @if ($user['type'] == 'internal')
                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium">Position :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <input type="text" name="position" class="form-control position" placeholder="Position"
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

                            <span class="fw-medium">Account Type :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <select class="form-select" id="account_type" name="account_type" required>
                        <option value="individu">Individu</option>
                        <option value="company">Company</option>
                    </select>
                </div>
            @endif

            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium">IC :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="text" name="no_ic" class="form-control ic" placeholder="IC Number" value="">
            </div>


        </div>
    </li>
    <li class="list-group-item p-3">
        <span class="fw-medium fs-15 d-block mb-3">Contact Info :</span>
        <div class="row gy-3 align-items-center">
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium">Email :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="email" name="email" class="form-control email" placeholder="Email"
                    value="">
            </div>
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium">Phone :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="text" name="phone_number" class="form-control phone_number" placeholder="Phone Number"
                    value="">
            </div>
            @if ($user['type'] == 'public')
                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium">Office Phone Number :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <input type="text" name="office_number" class="form-control office_number"
                        placeholder="Office Phone Number" value="">
                </div>

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">Address 1 :</span>
                    </div>
                </div>
                <div class="col-xl-9">

                    <textarea name="address_1" id="" cols="30" rows="3" class="form-control border address_1"></textarea>
                </div>

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">Address 2 :</span>
                    </div>
                </div>
                <div class="col-xl-9">

                    <textarea name="address_2" id="" cols="30" rows="3" class="form-control border address_2"></textarea>
                </div>

                

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium">State :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <select name="state" class="form-select state" required>
                        <option value="" id = "state">Select State</option>
                        {{-- @foreach ($states as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                        @endforeach --}}
                    </select>
                </div>
                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium">District :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <select name="district" class="form-select district" required>
                        <option value="" id="district">Select District</option>
                    </select>
                </div>

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium">Postcode :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <select name="postcode" class="form-select postcode" required>
                        <option value="" id="postcode">Select Postcode</option>
                    </select>
                </div>

               
            @endif



            {{-- buttons --}}
            <div class="d-flex justify-content-end align-items-end">
                <button class="btn-sm btn-secondary border" type="submit">Update</button>
            </div>
        </div>
    </li>


</ul>
