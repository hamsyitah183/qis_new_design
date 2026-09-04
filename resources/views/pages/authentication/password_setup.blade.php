@extends('pages.front')

@push('scripts')
    {{-- Add validation JS if needed --}}
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

        .visual-steps {
            display: flex;
            gap: 8px;
            margin: 20px 0 16px;
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

        .login-form-inner .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid var(--input-border);
            background: var(--form-control-bg);
        }

        .login-form-inner .form-control:focus {
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

        .password-strength {
            display: flex;
            gap: 4px;
            margin-top: 6px;
        }
        .password-strength .bar {
            flex: 1;
            height: 3px;
            border-radius: 999px;
            background: #e9ecef;
            transition: background .3s ease;
        }
        .password-strength .bar.weak { background: #dc3545; }
        .password-strength .bar.medium { background: #ffc107; }
        .password-strength .bar.strong { background: #28a745; }

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

            {{-- Language toggle --}}
            <div class="lang-toggle">
                <button type="button" class="lang-btn active" data-lang="en">EN</button>
                <button type="button" class="lang-btn" data-lang="bm">BM</button>
            </div>

            {{-- Left visual panel --}}
            <div class="login-card-visual">
                <div class="brand">
                    <img src="{{ asset('images/Logo-DOA.png') }}" alt="Logo">
                    <span>QIS</span>
                </div>

                <div>
                    <div class="visual-steps">
                        <span class="active"></span>
                    </div>

                    <div class="visual-caption">
                        <h5 data-en="Set Up Your Password" data-bm="Tetapkan Kata Laluan Anda">Set Up Your Password</h5>
                        <p data-en="Create a secure password to complete your account setup."
                           data-bm="Cipta kata laluan yang selamat untuk melengkapkan persediaan akaun anda.">
                            Create a secure password to complete your account setup.
                        </p>
                    </div>

                    <ul class="visual-app-list">
                        <li>
                            <i class='bx bx-lock-alt'></i>
                            <div>
                                <strong data-en="Choose a Strong Password" data-bm="Pilih Kata Laluan Yang Kuat">Choose a Strong Password</strong>
                                <span data-en="Use at least 8 characters with letters, numbers, and symbols."
                                      data-bm="Gunakan sekurang-kurangnya 8 aksara dengan huruf, nombor dan simbol.">
                                    Use at least 8 characters with letters, numbers, and symbols.
                                </span>
                            </div>
                        </li>
                        <li>
                            <i class='bx bx-check-shield'></i>
                            <div>
                                <strong data-en="Confirm Your Password" data-bm="Sahkan Kata Laluan Anda">Confirm Your Password</strong>
                                <span data-en="Make sure both passwords match before submitting."
                                      data-bm="Pastikan kedua-dua kata laluan sepadan sebelum menghantar.">
                                    Make sure both passwords match before submitting.
                                </span>
                            </div>
                        </li>
                        <li>
                            <i class='bx bx-log-in-circle'></i>
                            <div>
                                <strong data-en="Login to Your Account" data-bm="Log Masuk ke Akaun Anda">Login to Your Account</strong>
                                <span data-en="Once set, you can log in with your new password."
                                      data-bm="Setelah ditetapkan, anda boleh log masuk dengan kata laluan baharu anda.">
                                    Once set, you can log in with your new password.
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
                        <i class="bx bxs-lock-alt"></i>
                    </div>

                    <h4 data-en="Set Password" data-bm="Tetapkan Kata Laluan">Set Password</h4>
                    <p class="subtitle" data-en="Enter your new password below to complete your account setup."
                        data-bm="Masukkan kata laluan baharu anda di bawah untuk melengkapkan persediaan akaun.">
                        Enter your new password below to complete your account setup.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success text-center mb-3">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger text-center mb-3">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.setup.update') }}" id="passwordSetupForm">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token ?? '' }}">
                        <input type="hidden" name="email" value="{{ $email ?? '' }}">
                        <input type="hidden" name="type" value="{{ $type ?? 'public' }}">

                        {{-- @dd($token, $email, $type) --}}

                        <div class="mb-3">
                            <label for="password" class="form-label" data-en="New Password" data-bm="Kata Laluan Baharu">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required minlength="8">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="password-strength" id="passwordStrength">
                                <span class="bar" data-strength="0"></span>
                                <span class="bar" data-strength="1"></span>
                                <span class="bar" data-strength="2"></span>
                            </div>
                            <small class="text-muted" data-en="Minimum 8 characters with letters, numbers, and symbols."
                                   data-bm="Minimum 8 aksara dengan huruf, nombor dan simbol.">
                                Minimum 8 characters with letters, numbers, and symbols.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label" data-en="Confirm Password" data-bm="Sahkan Kata Laluan">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-login-primary w-100" data-en="Set Password" data-bm="Tetapkan Kata Laluan">Set Password</button>
                    </form>

                    <div class="signin-hint">
                        <span data-en="Already have a password?" data-bm="Sudah ada kata laluan?">Already have a password?</span>
                        <a href="{{ route('login') }}" data-en="Sign in" data-bm="Log masuk"> Sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        // ─── Password strength indicator ──────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const bars = document.querySelectorAll('#passwordStrength .bar');

            if (passwordInput && bars.length) {
                passwordInput.addEventListener('input', function () {
                    const password = this.value;
                    let strength = 0;

                    if (password.length >= 8) strength++;
                    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                    if (/\d/.test(password)) strength++;
                    if (/[@$!%*#?&]/.test(password)) strength++;

                    // Map 0-4 to 0-3 for bar display
                    const level = Math.min(Math.floor(strength / 2), 2);

                    bars.forEach(function (bar, index) {
                        bar.className = 'bar';
                        if (index <= level) {
                            if (level === 0) bar.classList.add('weak');
                            else if (level === 1) bar.classList.add('medium');
                            else if (level === 2) bar.classList.add('strong');
                        }
                    });
                });
            }
        });
    </script>
@endsection