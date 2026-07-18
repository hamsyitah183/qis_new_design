@extends('pages.front')

@push('scripts')
    @vite(['resources/js/pages/auth/login.js'])
@endpush

@push('style')
    <style>
        .login-page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), .08), rgba(var(--primary-tint2-rgb), .08));
            /* TODO: swap for your background image later, e.g.*/
            background: url('{{ asset('images/background.jpg') }}') center/cover no-repeat;
        }

        .login-card {
            position: relative;
            width: 100%;
            max-width: 960px;
            background: var(--custom-white);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .12);
            display: flex;
            min-height: 560px;
        }

        /* Left visual panel */
        .login-card-visual {
            flex: 1 1 42%;
            position: relative;
            background: linear-gradient(160deg, rgb(var(--primary-rgb)) 0%, rgb(var(--primary-tint2-rgb)) 100%);
            /* TODO: swap for background-image: url('...') center/cover no-repeat; */
            flex-direction: column;
            justify-content: space-between;
            padding: 40px;
            color: #fff;
        }

        .login-card-visual .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            letter-spacing: .5px;
            font-size: 18px;
        }

        .login-card-visual .brand img {
            height: 36px;
        }

        .login-card-visual .visual-caption h5 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .login-card-visual .visual-caption > p {
            color: rgba(255, 255, 255, .85);
            margin-bottom: 0;
        }

        /* Explanation list of application types */
        .visual-app-list {
            list-style: none;
            padding: 0;
            margin: 22px 0 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .visual-app-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .visual-app-list li i {
            font-size: 20px;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 10px;
            padding: 8px;
            line-height: 1;
            flex-shrink: 0;
        }

        .visual-app-list li strong {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 2px;
        }

        .visual-app-list li span {
            display: block;
            font-size: 12.5px;
            color: rgba(255, 255, 255, .8);
            line-height: 1.4;
        }

        /* Right form panel */
        .login-card-form {
            flex: 1 1 58%;
            padding: 48px;
            display: flex;
            align-items: center;
        }

        .login-form-inner {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
        }

        .login-form-inner h4 {
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--default-text-color);
        }

        .login-form-inner .subtitle {
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        .login-form-inner .form-label {
            font-weight: 500;
            color: var(--default-text-color);
        }

        .login-form-inner .form-control,
        .login-form-inner .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid var(--input-border);
            background: var(--form-control-bg);
        }

        .login-form-inner .form-control:focus,
        .login-form-inner .form-select:focus {
            border-color: rgb(var(--primary-rgb));
            box-shadow: 0 0 0 .2rem rgba(var(--primary-rgb), .15);
        }

        .forgot-link {
            color: var(--text-muted);
            text-decoration: underline;
            font-size: 14px;
        }

        .btn-login-primary {
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            background: rgb(var(--primary-rgb));
            border: none;
            color: #fff;
            transition: background .2s ease;
        }

        .btn-login-primary:hover {
            background: rgb(var(--primary-tint1-rgb));
            color: #fff;
        }

        /* Button to move to register page */
        .btn-register-outline {
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            background: transparent;
            border: 1.5px solid rgb(var(--primary-rgb));
            color: rgb(var(--primary-rgb));
            transition: all .2s ease;
            display: block;
            text-align: center;
            text-decoration: none;
        }

        .btn-register-outline:hover {
            background: rgba(var(--primary-rgb), .08);
            color: rgb(var(--primary-rgb));
        }

        /* Language toggle */
        .lang-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 5;
            display: flex;
            background: var(--gray-2);
            border-radius: 999px;
            padding: 4px;
            gap: 2px;
        }

        .lang-btn {
            border: none;
            background: transparent;
            padding: 5px 12px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 999px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all .2s ease;
        }

        .lang-btn.active {
            background: rgb(var(--primary-rgb));
            color: #fff;
        }

        /* Responsive breakpoints */
        @media (max-width: 991.98px) {
            .login-card {
                flex-direction: column;
                min-height: auto;
            }

            .login-card-visual {
                display: none;
            }

            .login-card-form {
                padding: 32px 24px;
            }
        }

        @media (max-width: 575.98px) {
            .login-page-wrapper {
                padding: 0;
            }

            .login-card {
                border-radius: 0;
                min-height: 100vh;
                box-shadow: none;
            }

            .login-card-form {
                padding: 28px 20px;
            }

            .lang-toggle {
                top: 14px;
                right: 14px;
            }
        }

        @media (min-width: 992px) {
            .login-card-visual {
                display: flex;
            }
        }
    </style>
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