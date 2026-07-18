@extends('pages.front')

@push('scripts')
    @vite(['resources/js/pages/auth/login.js'])
@endpush

@push('style')
 
@endpush

@section('content')
    <div class="login-page-wrapper">
        <div class="login-card">

            {{-- Language toggle: visible on all breakpoints --}}
            <div class="lang-toggle">
                <button type="button" class="lang-btn active" data-lang="en">EN</button>
                <button type="button" class="lang-btn" data-lang="bm">BM</button>
            </div>

            {{-- Left visual panel: hidden on tablet/mobile, background image to be set later --}}
            <div class="login-card-visual">
                <div class="brand">
                    <img src="{{ asset('images/logo-DOA.png') }}" alt="Logo">
                    <span>QIS</span>
                </div>

                <div class="visual-caption">
                    <h5 data-en="Welcome Back" data-bm="Selamat Kembali">Welcome Back</h5>
                    <p data-en="Sign in to continue managing your applications for agricultural goods in Sabah."
                        data-bm="Log masuk untuk terus menguruskan permohonan barangan pertanian anda di Sabah.">
                        Sign in to continue managing your applications for agricultural goods in Sabah.
                    </p>

                    <ul class="visual-app-list">
                        <li>
                            <i class='bx bx-package'></i>
                            <div>
                                <strong data-en="Import Permit" data-bm="Permit Import">Import Permit</strong>
                                <span
                                    data-en="Official authorization to import regulated agricultural goods into Sabah."
                                    data-bm="Kebenaran rasmi untuk mengimport barangan pertanian terkawal ke Sabah.">
                                    Official authorization to import regulated agricultural goods into Sabah.
                                </span>
                            </div>
                        </li>
                        <li>
                            <i class='bx bx-search'></i>
                            <div>
                                <strong data-en="Inspection Certificate" data-bm="Sijil Pemeriksaan">Inspection
                                    Certificate</strong>
                                <span
                                    data-en="Required for agricultural goods not covered under the standard Import Permit list."
                                    data-bm="Diperlukan bagi barangan pertanian yang tidak disenaraikan di bawah Permit Import standard.">
                                    Required for agricultural goods not covered under the standard Import Permit list.
                                </span>
                            </div>
                        </li>
                        <li>
                            <i class='bx bx-file'></i>
                            <div>
                                <strong data-en="Consignment Certificate" data-bm="Sijil Consignment">Consignment
                                    Certificate</strong>
                                <span
                                    data-en="Export authorization dedicated to the movement of agricultural goods to Brunei."
                                    data-bm="Kebenaran eksport khusus untuk pergerakan barangan pertanian ke Brunei.">
                                    Export authorization dedicated to the movement of agricultural goods to Brunei.
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Right form panel --}}
            <div class="login-card-form">
                <div class="login-form-inner">
                    <h4 data-en="Login" data-bm="Log Masuk">Login</h4>
                    <p class="subtitle" data-en="Please enter your details to sign in"
                        data-bm="Sila masukkan butiran anda untuk log masuk">
                        Please enter your details to sign in
                    </p>

                    <form id="loginForm" method="POST" action="{{ route('login.action') }}">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-12">
                                <label for="planSelect" class="form-label" data-en="User Type"
                                    data-bm="Jenis Pengguna">User Type</label>
                                <select id="planSelect" name="userType" class="form-select xintra-select">
                                    <option value="public" data-en="Public" data-bm="Awam" selected>Public</option>
                                    <option value="internal" data-en="Internal" data-bm="Dalaman">Internal</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="signin-username" class="form-label"><span data-en="Email"
                                        data-bm="Emel">Email</span> <span class="text-primary2">*</span></label>
                                <input type="email" name="email" class="form-control" id="signin-username"
                                    data-en="Email" data-bm="Emel" data-i18n-attr="placeholder" placeholder="Email">
                            </div>

                            <div class="col-12">
                                <label for="signin-password" class="form-label d-block"><span data-en="Password"
                                        data-bm="Kata Laluan">Password</span> <span
                                        class="text-primary2">*</span></label>
                                <input type="password" name="password" class="form-control" id="signin-password"
                                    data-en="Password" data-bm="Kata Laluan" data-i18n-attr="placeholder"
                                    placeholder="Password">
                                <div class="mt-2 text-end">
                                    <a href="/forgot-password" class="forgot-link" data-en="Forgot password?"
                                        data-bm="Lupa kata laluan?">Forgot password?</a>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-login-primary" data-en="Sign In"
                                data-bm="Log Masuk">Sign In</button>
                        </div>
                    </form>

                    {{-- Button to move to register page --}}
                    <div class="d-grid mt-3">
                        <a href="/register" class="btn-register-outline" data-en="Create an Account"
                            data-bm="Cipta Akaun">Create an Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

   
@endsection