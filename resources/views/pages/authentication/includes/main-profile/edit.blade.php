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
                <input type="text" class="form-control fullname" name="fullname" placeholder="Placeholder"
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
                    <input type="text" name="position" class="form-control position" placeholder="Placeholder"
                        value="">
                </div>

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">Office :</span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <input type="text" name="office" class="form-control address" placeholder="Placeholder"
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
                <input type="text" name="no_ic" class="form-control ic" placeholder="Placeholder" value="">
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
                <input type="email" name="email" class="form-control email" placeholder="Placeholder"
                    value="">
            </div>
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium">Phone :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="text" name="phone" class="form-control phone_number" placeholder="Placeholder"
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
                        placeholder="Placeholder" value="">
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

                            <span class="fw-medium">Postcode :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <input type="text" name="postcode" class="form-control postcode" placeholder="Placeholder"
                        value="">
                </div>

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium">District :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <input type="text" name="district" class="form-control district" placeholder="Placeholder"
                        value="">
                </div>

                <div class="col-xl-3">
                    <div class="lh-1">
                        <span class="fw-medium">

                            <span class="fw-medium">State :</span>

                        </span>
                    </div>
                </div>
                <div class="col-xl-9">
                    <input type="text" name="state" class="form-control state" placeholder="Placeholder"
                        value="">
                </div>
            @endif



            {{-- buttons --}}
            <div class="d-flex justify-content-end align-items-end">
                <button class="btn-sm btn-secondary border" type="submit">Update</button>
            </div>
        </div>
    </li>


</ul>
