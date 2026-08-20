@extends('pages.front')

@push('style')
    <style>
        /* ===== Shared auth-page shell (same as registration) ===== */
        .auth-page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), .08), rgba(var(--primary-tint2-rgb), .08));
            background: url('{{ asset('images/background.jpg') }}') center/cover no-repeat;
        }

        .auth-card {
            position: relative;
            width: 100%;
            max-width: 1180px;
            background: var(--custom-white);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .12);
            display: flex;
            min-height: 640px;
        }

        .auth-card-visual {
            flex: 0 0 34%;
            position: relative;
            background: linear-gradient(160deg, rgb(var(--primary-rgb)) 0%, rgb(var(--primary-tint2-rgb)) 100%);
            flex-direction: column;
            justify-content: space-between;
            padding: 40px;
            color: #fff;
        }

        .auth-card-visual .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            letter-spacing: .5px;
            font-size: 18px;
        }

        .auth-card-visual .brand img {
            height: 36px;
        }

        .visual-steps {
            display: flex;
            gap: 8px;
            margin-bottom: 22px;
        }

        .visual-steps span {
            width: 28px;
            height: 4px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .3);
            transition: background .2s ease;
        }

        .visual-steps span.active {
            background: #fff;
        }

        .visual-caption-block {
            display: none;
        }

        .visual-caption-block.active {
            display: block;
        }

        .visual-caption-block h5 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .visual-caption-block p {
            color: rgba(255, 255, 255, .85);
            font-size: 13.5px;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .visual-caption-block .step-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 999px;
            padding: 4px 12px;
            margin-bottom: 14px;
        }

        .auth-card-form {
            flex: 1 1 66%;
            padding: 40px;
            display: flex;
            align-items: center;
        }

        .auth-form-inner {
            width: 100%;
        }

        .btn-auth-primary {
            border-radius: 12px;
            padding: 10px 22px;
            font-weight: 600;
            background: rgb(var(--primary-rgb));
            border: none;
            color: #fff;
            transition: background .2s ease;
        }

        .btn-auth-primary:hover {
            background: rgb(var(--primary-tint1-rgb));
            color: #fff;
        }

        .btn-auth-secondary {
            border-radius: 12px;
            padding: 10px 22px;
            font-weight: 600;
            background: var(--gray-2);
            border: none;
            color: var(--default-text-color);
        }

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

        @media (max-width: 991.98px) {
            .auth-card {
                flex-direction: column;
                min-height: auto;
            }
            .auth-card-visual {
                display: none;
            }
            .auth-card-form {
                padding: 24px 18px;
            }
        }

        @media (max-width: 575.98px) {
            .auth-page-wrapper {
                padding: 0;
            }
            .auth-card {
                border-radius: 0;
                min-height: 100vh;
                box-shadow: none;
            }
            .auth-card-form {
                padding: 18px 14px;
            }
            .lang-toggle {
                top: 12px;
                right: 12px;
            }
        }

        @media (min-width: 992px) {
            .auth-card-visual {
                display: flex;
            }
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/auth/verify.js'])
@endpush

@section('content')
    <div class="auth-page-wrapper">
        <div class="auth-card">

            {{-- Language toggle --}}
            <div class="lang-toggle">
                <button type="button" class="lang-btn active" data-lang="en">EN</button>
                <button type="button" class="lang-btn" data-lang="bm">BM</button>
            </div>

            {{-- Left visual panel --}}
            <div class="auth-card-visual d-none">
                <div class="brand">
                    <img src="{{ asset('images/logo-DOA.png') }}" alt="Logo">
                    <span>QIS</span>
                </div>

                <div>
                    <div class="visual-steps">
                        <span class="step-dot active" data-step-dot="verify-tab-pane"></span>
                    </div>

                    <div class="visual-caption-block active" data-caption-for="verify-tab-pane">
                        <span class="step-tag" data-en="Verify" data-bm="Sahkan">Verify</span>
                        <h5 data-en="Check Your Email" data-bm="Semak E-mel Anda">Check Your Email</h5>
                        <p data-en="We’ve sent a verification link to your email address. Click the link to activate your account."
                            data-bm="Kami telah menghantar pautan pengesahan ke alamat e-mel anda. Klik pautan untuk mengaktifkan akaun anda.">
                            We’ve sent a verification link to your email address. Click the link to activate your account.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right form panel --}}
            <div class="auth-card-form">
                <div class="auth-form-inner">

                    
                    <div class="text-center">
                        <div class="avatar-md mx-auto">
                            <div class="avatar-title">
                                <i class="bx bxs-envelope h1 mb-0 text-primary"></i>
                            </div>
                        </div>
                        <div class="p-2 mt-4">
                            <h4 data-bm="Sahkan e-mel anda" data-en="Verify your email">Verify your email</h4>
                            <p>
                                <span data-bm="Kami telah menghantar e-mel pengesahan kepada" data-en="We have sent you verification email">We have sent you verification email</span>
                                <span class="fw-semibold">{{ $user->email ?? '' }}</span>,
                                <span data-bm="Sila semaknya" data-en="Please check it">Please check it</span>
                            </p>
                            <div class="alert alert-success text-center mb-4" id="emailSent" style="display: none"></div>
                            <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
                                <form method="POST" action="{{ route('verification.send') }}" id="verifyEmailForm">
                                    @csrf
                                    <button type="submit" class="btn-auth-primary" data-bm="Hantar Semula E-mel" data-en="Resend Email">Resend Email</button>
                                </form>

                                <a type="submit" class="btn-auth-secondary" id="logoutBtn" href="{{ route('logout') }}" data-bm="Log Keluar" data-en="Logout">Logout</a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-center">
                        <p>
                            <span data-bm="Tidak menerima e-mel ?" data-en="Didn't receive an email?">Didn't receive an email?</span>
                            <a href="#" class="fw-medium text-primary" data-bm="Hantar Semula" data-en="Resend"> Resend </a>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- Language toggle script (same as registration) --}}
    <script>
        (function () {
            var STORAGE_KEY = 'qis_lang';
            var langButtons = document.querySelectorAll('.lang-btn');
            var i18nElements = document.querySelectorAll('[data-en]');

            function setLang(lang) {
                i18nElements.forEach(function (el) {
                    var text = el.getAttribute('data-' + lang);
                    if (text === null) return;
                    if (el.getAttribute('data-i18n-attr') === 'placeholder') {
                        el.setAttribute('placeholder', text);
                    } else {
                        el.textContent = text;
                    }
                });

                langButtons.forEach(function (btn) {
                    btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
                });

                document.documentElement.setAttribute('lang', lang === 'bm' ? 'ms' : 'en');

                try {
                    localStorage.setItem(STORAGE_KEY, lang);
                } catch (e) { /* ignore */ }
            }

            langButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setLang(btn.getAttribute('data-lang'));
                });
            });

            var savedLang = 'en';
            try {
                savedLang = localStorage.getItem(STORAGE_KEY) || 'en';
            } catch (e) { /* default en */ }
            setLang(savedLang);
        })();
    </script>
@endsection