@extends('pages.front')

@push('scripts')
    @vite(['resources/js/pages/auth/login.js'])
@endpush

@section('content')
    <div class="row authentication authentication-cover-main mx-0">
        <div class="col-xxl-5 col-xl-5 col-lg-12 d-xl-block d-none px-0">
            <div class="authentication-cover overflow-hidden">
                {{-- <div class="authentication-cover-logo">
                    <a href="https://laravelui.spruko.com/xintra/index">
                        <img src="https://laravelui.spruko.com/xintra/build/assets/images/brand-logos/desktop-white.png"
                            alt="" class="authentication-brand desktop-white">
                    </a>
                </div> --}}
                <div class="aunthentication-cover-content d-flex align-items-center justify-content-center">
                    <div class="d-flex justify-content-center align-items-center flex-column">
                        <h4 class="text-center text-white">Login</h4>
                        <img src="https://qis-app.sabah.gov.my/assets/QIS-a3dc1042.gif" class="mb-2"
                            style="object-fit: contain; height: 350px;">

                        {{-- <img src="https://qis-app.sabah.gov.my/assets/preparedby-f6029c92.png" alt="" srcset="" style="height: 70px;" class="bg-white p-2 rounded-3"> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-7 col-xl-7">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-10 col-xl-9 col-lg-6 col-md-6 col-sm-8 col-12">
                    <div class="card custom-card my-auto border">
                        <div class="card-body p-5">
                            {{-- <p class="h5 mb-2 text-center">Sign In</p>
                            <img src="https://qis-app.sabah.gov.my/assets/QIS-a3dc1042.gif" class="img-fluid mb-2"
                                style="object-fit: contain; height: 200px; margin-inline: auto;"> --}}
                            <form id="loginForm" method="POST" action="{{ route('login.action') }}">
                                @csrf
                                <div class="row gy-3">
                                    <div class="col-xl-12">
                                        <label for="planSelect" class="form-label text-default">User Type</label>
                                        <select id="planSelect" name="userType" class="form-select xintra-select">
                                            <option value="public" selected>Public</option>
                                            <option value="internal">Internal</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-12">
                                        <label for="signin-username" class="form-label text-default">Email</label>
                                        <input type="email" name="email" class="form-control" id="signin-username"
                                            placeholder="Email">
                                    </div>

                                    <div class="col-xl-12 mb-2">
                                        <label for="signin-password"
                                            class="form-label text-default d-block">Password</label>
                                        <div class="position-relative">
                                            <input type="password" name="password"
                                                class="form-control" 
                                                placeholder="Password">
                                            {{-- <a href="javascript:void(0);" class="show-password-button text-muted"
                                                onclick="createpassword('signin-password', this)" id="button-addon2">
                                                <i class="ri-eye-off-line align-middle"></i>
                                            </a> --}}
                                        </div>
                                        <div class="mt-2 text-end">
                                            <a href="/forgot-password" class="fw-normal text-muted">Forgot
                                                password?</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary">Sign In</button>
                                </div>
                            </form>

                            <div class="text-center">
                                <p class="text-muted mt-3 mb-0">Doesn't have account? <a
                                        href="/register"
                                        class="text-primary">Register here</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
