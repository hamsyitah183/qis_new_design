<div class="card custom-card overflow-hidden border">
    <div class="card-body">
        <ul class="nav nav-tabs tab-style-6 mb-3 p-0" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link w-100 text-start active" id="profile-about-tab" data-bs-toggle="tab"
                    data-bs-target="#profile-about-tab-pane" type="button" role="tab"
                    aria-controls="profile-about-tab-pane" aria-selected="true" data-en = "Profile" data-bm = "Profil">Profile</button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link w-100 text-start" id="edit-profile-tab" data-bs-toggle="tab"
                    data-bs-target="#edit-profile-tab-pane" type="button" role="tab"
                    aria-controls="edit-profile-tab-pane" aria-selected="false" tabindex="-1"  data-en = "Edit Profile" data-bm = "Sunting Profil">Edit Profile</button>
            </li>

            @if ($user['type'] === 'public')
                <li class="nav-item" role="presentation">
                    <button class="nav-link w-100 text-start" id="edit-verification-tab" data-bs-toggle="tab"
                        data-bs-target="#edit-verification-tab-pane" type="button" role="tab"
                        aria-controls="edit-verification-tab-pane" aria-selected="false" tabindex="-1">
                        <span data-en="Verification" data-bm="Senarai Dokumen">List Documents</span>
                       
                        @if (authUser()['user']->approved?->verification_attachment == null)
                            <i class="ri-alert-line text-warning ms-1"></i>
                        @endif
                    </button>
                </li>
            @endif


            <li class="nav-item" role="presentation">
                <button class="nav-link w-100 text-start" id="edit-password-tab" data-bs-toggle="tab"
                    data-bs-target="#edit-password-tab-pane" type="button" role="tab"
                    aria-controls="edit-password-tab-pane" aria-selected="false" tabindex="-1" data-en="Change Password" data-bm="Tukar Kata Laluan">Change Password</button>
            </li>



        </ul>
        <div class="tab-content" id="profile-tabs">
            <div class="tab-pane show active p-0 border-0" id="profile-about-tab-pane" role="tabpanel"
                aria-labelledby="profile-about-tab" tabindex="0">
                @include('pages.authentication.includes.main-profile.about')
            </div>




            <form class="tab-pane p-0 border-0" id="edit-profile-tab-pane" role="tabpanel"
                aria-labelledby="edit-profile-tab" tabindex="0">
                <input type="hidden" name="type" class="type">
                <input type="hidden" name="uuid" class="uuid">
                @include('pages.authentication.includes.main-profile.edit')
            </form>


            @if ($user['type'] == 'public')
                <div class="tab-pane p-0 border-0" id="edit-verification-tab-pane" role="tabpanel"
                    aria-labelledby="edit-verification-tab" tabindex="0">
                    @include('pages.authentication.includes.main-profile.verification')
                </div>
            @endif

            <form class="tab-pane p-0 border-0" id="edit-password-tab-pane" role="tabpanel"
                aria-labelledby="edit-password-tab" tabindex="0">
                <input type="hidden" name="type" class="type">
                <input type="hidden" name="uuid" class="uuid">
                @include('pages.authentication.includes.main-profile.password')
            </form>

        </div>
    </div>
</div>
