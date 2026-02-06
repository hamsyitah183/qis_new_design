<ul class="list-group list-group-flush border rounded-3">
    <li class="list-group-item p-3">
        <span class="fw-medium fs-15 d-block mb-3">Password</span>
        <div class="row gy-3 align-items-center">

            {{-- Old Password --}}
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium">Old Password :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="password" class="form-control" name="old_password" placeholder="Enter old password"
                    required>
            </div>

            {{-- New Password --}}
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium">New Password :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="password" class="form-control" name="new_password" placeholder="Enter new password"
                    required>
            </div>

            {{-- Confirm Password --}}
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium">Confirm Password :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="password" class="form-control" name="new_password_confirmation"
                    placeholder="Confirm new password" required>
            </div>


            <div class="d-flex justify-content-end align-items-end">
                <button class="btn btn-secondary btn-sm mt-3 border">Update</button>
            </div>
        </div>
    </li>
</ul>