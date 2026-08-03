<ul class="list-group list-group-flush border rounded-3">
    <li class="list-group-item p-3">
        <span class="fw-medium fs-15 d-block mb-3" data-en="Password" data-bm="Kata Laluan">Password</span>
        <div class="row gy-3 align-items-center">

            {{-- Old Password --}}
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium" data-en="Old Password :" data-bm="Kata Laluan Lama :">Old Password :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="password" class="form-control" name="old_password" placeholder="Enter old password" data-en="Enter old password" data-bm="Masukkan kata laluan lama" data-i18n-attr="placeholder"
                    required>
            </div>

            {{-- New Password --}}
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium" data-en="New Password :" data-bm="Kata Laluan Baru :">New Password :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="password" class="form-control" name="new_password" placeholder="Enter new password" data-en="Enter new password" data-bm="Masukkan kata laluan baru" data-i18n-attr="placeholder"
                    required>
            </div>

            {{-- Confirm Password --}}
            <div class="col-xl-3">
                <div class="lh-1">
                    <span class="fw-medium" data-en="Confirm Password :" data-bm="Sahkan Kata Laluan :">Confirm Password :</span>
                </div>
            </div>
            <div class="col-xl-9">
                <input type="password" class="form-control" name="new_password_confirmation"
                    placeholder="Confirm new password" data-en="Confirm new password" data-bm="Sahkan kata laluan baru" data-i18n-attr="placeholder" required>
            </div>


            <div class="d-flex justify-content-end align-items-end">
                <button class="btn btn-secondary btn-sm mt-3 border" data-en="Update" data-bm="Kemaskini">Update</button>
            </div>
        </div>
    </li>
</ul>