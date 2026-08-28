@extends('pages.front')

@push('scripts')
    @vite(['resources/js/pages/auth/forgot_password.js'])
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

        /* Explanation / steps list */
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

        .login-form-inner .form-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(var(--primary-rgb), .1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }

        .login-form-inner .form-icon i {
            font-size: 26px;
            color: rgb(var(--primary-rgb));
        }

        .login-form-inner h4 {
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--default-text-color);
            text-align: center;
        }

        .login-form-inner .subtitle {
            color: var(--text-muted);
            margin-bottom: 24px;
            text-align: center;
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

        .signin-hint {
            margin-top: 20px;
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
        }

        .signin-hint a {
            color: rgb(var(--primary-rgb));
            font-weight: 600;
        }

        #emailSent {
            border-radius: 12px;
            border: 1px solid rgba(var(--primary-rgb), .3);
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
                    <img src="{{ asset('images/Logo_DOA.png') }}" alt="Logo">
                    <span>QIS</span>
                </div>

                <div class="visual-caption">
                    <h5 data-en="Forgot Your Password?" data-bm="Lupa Kata Laluan Anda?">Forgot Your Password?</h5>
                    <p data-en="No worries, it happens. Follow these steps to get back into your account."
                        data-bm="Jangan risau, ini perkara biasa. Ikuti langkah berikut untuk kembali ke akaun anda.">
                        No worries, it happens. Follow these steps to get back into your account.
                    </p>

                    <ul class="visual-app-list">
                        <li>
                            <i class='bx bx-user-check'></i>
                            <div>
                                <strong data-en="Select your User Type" data-bm="Pilih Jenis Pengguna">Select your
                                    User Type</strong>
                                <span data-en="Choose whether you are a Public or Internal account holder."
                                    data-bm="Pilih sama ada anda pemegang akaun Awam atau Dalaman.">
                                    Choose whether you are a Public or Internal account holder.
                                </span>
                            </div>
                        </li>
                        <li>
                            <i class='bx bxs-envelope'></i>
                            <div>
                                <strong data-en="Enter your Email" data-bm="Masukkan Emel Anda">Enter your
                                    Email</strong>
                                <span data-en="Use the email address registered to your account."
                                    data-bm="Gunakan alamat emel yang didaftarkan dengan akaun anda.">
                                    Use the email address registered to your account.
                                </span>
                            </div>
                        </li>
                        <li>
                            <i class='bx bx-mail-send'></i>
                            <div>
                                <strong data-en="Check your Inbox" data-bm="Semak Peti Masuk Anda">Check your
                                    Inbox</strong>
                                <span data-en="We'll send reset instructions straight to your inbox."
                                    data-bm="Kami akan menghantar arahan set semula terus ke peti masuk anda.">
                                    We'll send reset instructions straight to your inbox.
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Right form panel --}}
            <div class="login-card-form">
                <div class="login-form-inner">
                    <div class="form-icon">
                        <i class="bx bxs-envelope"></i>
                    </div>

                    <h4 data-en="Forgot Password" data-bm="Lupa Kata Laluan">Forgot Password</h4>
                    <p class="subtitle" data-en="Enter your email and account type. The instructions will be sent to you!"
                        data-bm="Masukkan emel dan jenis akaun anda. Arahan akan dihantar kepada anda!">
                        Enter your email and account type. The instructions will be sent to you!
                    </p>

                    <div class="alert alert-success text-center mb-4" id="emailSent" style="display: none"></div>

                    <form class="form" action="#" id="forgotPasswordForm">

                        <div class="row gy-3">
                            <div class="col-12">
                                <label for="planSelect" class="form-label" data-en="User Type"
                                    data-bm="Jenis Pengguna">User Type</label>
                                <select id="planSelect" name="type" class="form-select xintra-select">
                                    <option value="public" data-en="Public" data-bm="Awam" selected>Public</option>
                                    <option value="internal" data-en="Internal" data-bm="Dalaman">Internal</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="useremail" class="form-label"><span data-en="Email"
                                        data-bm="Emel">Email</span></label>
                                <input type="email" class="form-control" id="useremail" name="email"
                                    data-en="Enter email" data-bm="Masukkan emel" data-i18n-attr="placeholder"
                                    placeholder="Enter email">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button class="btn btn-login-primary waves-effect waves-light" type="submit"
                                data-en="Reset" data-bm="Set Semula">Reset</button>
                        </div>

                    </form>

                    <div class="signin-hint">
                        <span data-en="Remember it?" data-bm="Teringat semula?">Remember it?</span>
                        <a href="/login" data-en="Sign in" data-bm="Log masuk"> Sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var STORAGE_KEY = 'qis_lang';
            var buttons = document.querySelectorAll('.lang-btn');
            var elements = document.querySelectorAll('[data-en]');

            function setLang(lang) {
                elements.forEach(function (el) {
                    var text = el.getAttribute('data-' + lang);
                    if (text === null) return;
                    if (el.getAttribute('data-i18n-attr') === 'placeholder') {
                        el.setAttribute('placeholder', text);
                    } else {
                        el.textContent = text;
                    }
                });

                buttons.forEach(function (btn) {
                    btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
                });

                document.documentElement.setAttribute('lang', lang === 'bm' ? 'ms' : 'en');

                try {
                    localStorage.setItem(STORAGE_KEY, lang);
                } catch (e) {
                    /* storage unavailable, ignore */
                }
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setLang(btn.getAttribute('data-lang'));
                });
            });

            var savedLang = 'en';
            try {
                savedLang = localStorage.getItem(STORAGE_KEY) || 'en';
            } catch (e) {
                /* storage unavailable, default to en */
            }

            setLang(savedLang);
        })();
    </script>
@endsection