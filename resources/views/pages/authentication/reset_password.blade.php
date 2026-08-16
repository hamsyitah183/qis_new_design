@extends('pages.front')

@push('scripts')
    @vite(['resources/js/pages/auth/reset_password.js'])
@endpush


@section('content')
    <div class="container-lg">
        <div class="row justify-content-center authentication authentication-basic align-items-center h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                <div class="card custom-card my-4">
                    <div class="card-body p-5">
                        <div class="mb-3 d-flex justify-content-center">
                            <img src="https://qis-app.sabah.gov.my/assets/QIS-a3dc1042.gif" class="img-fluid "
                                style="object-fit: contain;">
                        </div>
                        <p class="h5 mb-2 text-center" data-bm="Tetapkan Kata Laluan" data-en="Set Password">Set Password</p>
                        <form id="resetPasswordForm">
                            @csrf
                            <input type="hidden" name="email" id="email">
                            <input type="hidden" name="token" id="token">
                            <input type="hidden" name="type" id="type">

                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label class="form-label text-default" for="create-password"><span data-bm="Kata Laluan" data-en="Password">Password</span><sup
                                            class="fs-12 text-danger">*</sup></label>
                                    <div class="position-relative">
                                        <input class="form-control" name="password"
                                            id="create-password" placeholder="password" data-bm="kata laluan" data-en="password" data-i18n-attr="placeholder" type="password">
                                        {{-- <a class="show-password-button text-muted" href="javascript:void(0);"
                                            onclick="createpassword('create-password',this)">
                                            <i class="ri-eye-off-line align-middle"></i>
                                        </a> --}}
                                    </div>
                                </div>
                                <div class="col-xl-12 mb-2">
                                    <label class="form-label text-default" for="password_confirmation"><span data-bm="Sahkan Kata Laluan" data-en="Confirm Password">Confirm Password</span><sup
                                            class="fs-12 text-danger">*</sup></label>
                                    <div class="position-relative">
                                        <input class="form-control" name="password_confirmation"
                                            id="password_confirmation" placeholder="password" data-bm="kata laluan" data-en="password" data-i18n-attr="placeholder" type="password">
                                        {{-- <a class="show-password-button text-muted" href="javascript:void(0);"
                                            onclick="createpassword('create-confirmpassword',this)">
                                            <i class="ri-eye-off-line align-middle"></i>
                                        </a> --}}
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary" data-bm="Simpan Kata Laluan" data-en="Save Password">Save Password</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
