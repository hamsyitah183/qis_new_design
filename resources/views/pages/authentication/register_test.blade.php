@extends('pages.front')

@push('style')
    <style>
        input[type="radio"].form-check-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .cursor-pointer {
            cursor: pointer !important;
        }

        @media (max-width: 576px) {
            .text-type {
                font-size: 11px !important;
                width: 80px;
            }

            .tab-content button {
                font-size: 11px;
            }

            .type-div .fs-15 {
                font-size: 13px
            }

            .button-group .text-start p {
                font-size: 11px;
            }
        }

        /* ===== Shared auth-page shell (same family as login / forgot password) ===== */
        .auth-page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), .08), rgba(var(--primary-tint2-rgb), .08));
            /* TODO: swap for your background image later, e.g.*/
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

        /* Left visual panel */
        .auth-card-visual {
            flex: 0 0 34%;
            position: relative;
            background: linear-gradient(160deg, rgb(var(--primary-rgb)) 0%, rgb(var(--primary-tint2-rgb)) 100%);
            /* TODO: swap for background-image: url('...') center/cover no-repeat; */
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

        /* Step indicator dots */
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

        /* Right form panel */
        .auth-card-form {
            flex: 1 1 66%;
            padding: 40px;
            display: flex;
            align-items: center;
        }

        .auth-form-inner {
            width: 100%;
        }

        .auth-form-inner .form-label {
            font-weight: 500;
            color: var(--default-text-color);
        }

        .auth-form-inner .form-control,
        .auth-form-inner .form-select {
            border-radius: 12px;
            padding: 10px 14px;
            border: 1px solid var(--input-border);
            background: var(--form-control-bg);
        }

        .auth-form-inner .form-control:focus,
        .auth-form-inner .form-select:focus {
            border-color: rgb(var(--primary-rgb));
            box-shadow: 0 0 0 .2rem rgba(var(--primary-rgb), .15);
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

        /* Wizard tab nav restyle */
        #myTab1.nav-tabs {
            border-bottom: 1px solid var(--default-border);
            margin-bottom: 4px;
        }

        #myTab1.nav-tabs .nav-link {
            border: none;
            color: var(--text-muted);
            font-weight: 500;
            border-bottom: 2px solid transparent;
        }

        #myTab1.nav-tabs .nav-link.active {
            color: rgb(var(--primary-rgb));
            border-bottom: 2px solid rgb(var(--primary-rgb));
            background: transparent;
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
    @vite(['resources/js/pages/auth/registerAction.js'])
@endpush

@section('content')
    <div class="auth-page-wrapper">
        <div class="auth-card">

            {{-- Language toggle: visible on all breakpoints --}}
            <div class="lang-toggle">
                <button type="button" class="lang-btn active" data-lang="en">EN</button>
                <button type="button" class="lang-btn" data-lang="bm">BM</button>
            </div>

            {{-- Left visual panel: caption changes per wizard step --}}
            <div class="auth-card-visual">
                <div class="brand">
                    <img src="{{ asset('images/logo-DOA.png') }}" alt="Logo">
                    <span>QIS</span>
                </div>

                <div>
                    <div class="visual-steps">
                        <span class="step-dot active" data-step-dot="order-tab-pane"></span>
                        <span class="step-dot" data-step-dot="confirm-tab-pane"></span>
                        <span class="step-dot" data-step-dot="shipped-tab-pane"></span>
                    </div>

                    <div class="visual-caption-block active" data-caption-for="order-tab-pane">
                        <span class="step-tag" data-en="Step 1 of 3" data-bm="Langkah 1 daripada 3">Step 1 of 3</span>
                        <h5 data-en="Get Started" data-bm="Mulakan">Get Started</h5>
                        <p data-en="Choose the account type that best describes you so we can tailor your registration experience."
                            data-bm="Pilih jenis akaun yang paling sesuai dengan anda supaya kami dapat menyesuaikan proses pendaftaran anda.">
                            Choose the account type that best describes you so we can tailor your registration
                            experience.
                        </p>
                    </div>

                    <div class="visual-caption-block" data-caption-for="confirm-tab-pane">
                        <span class="step-tag" data-en="Step 2 of 3" data-bm="Langkah 2 daripada 3">Step 2 of 3</span>
                        <h5 data-en="Tell Us About You" data-bm="Beritahu Kami Tentang Anda">Tell Us About You</h5>
                        <p data-en="Provide your personal or company details so we can verify your identity and keep your account secure."
                            data-bm="Berikan butiran peribadi atau syarikat anda supaya kami dapat mengesahkan identiti anda dan memastikan akaun anda selamat.">
                            Provide your personal or company details so we can verify your identity and keep your
                            account secure.
                        </p>
                    </div>

                    <div class="visual-caption-block" data-caption-for="shipped-tab-pane">
                        <span class="step-tag" data-en="Step 3 of 3" data-bm="Langkah 3 daripada 3">Step 3 of 3</span>
                        <h5 data-en="Almost There" data-bm="Hampir Selesai">Almost There</h5>
                        <p data-en="Upload your supporting documents (e.g. IC, business registration) to complete verification. You can add multiple files."
                            data-bm="Muat naik dokumen sokongan anda (cth. IC, pendaftaran perniagaan) untuk melengkapkan pengesahan. Anda boleh menambah beberapa fail.">
                            Upload your supporting documents (e.g. IC, business registration) to complete
                            verification. You can add multiple files.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right form panel: wizard preserved as-is --}}
            <div class="auth-card-form">
                <div class="auth-form-inner">

                    <div class="d-flex d-lg-none justify-content-center align-items-center p-1 flex-column mb-2">
                        <h4 class="text-center" data-en="Register" data-bm="Daftar">Register</h4>
                    </div>

                    <form enctype="multipart/form-data" id="registerForm" action="/register">
                        <ul class="nav nav-tabs tab-style-8 d-flex justify-content-around border-bottom-0 rounded-top"
                            id="myTab1" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link p-1 p-md-3 active" id="order-tab" data-bs-toggle="tab"
                                    data-bs-target="#order-tab-pane" type="button" role="tab"
                                    aria-controls="order-tab" aria-selected="true">
                                    <span class="d-flex flex-column gap-1 align-items-center d-md-inline">
                                        <i class="ti ti-user me-2 align-middle"></i>
                                        <span class="text-type text-wrap" data-en="Type" data-bm="Jenis">Type</span>
                                    </span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link p-1 p-md-3" id="confirmed-tab" data-bs-toggle="tab"
                                    data-bs-target="#confirm-tab-pane" type="button" role="tab"
                                    aria-controls="confirmed-tab" aria-selected="false" tabindex="-1">
                                    <span class="d-flex flex-column gap-1 align-items-center d-md-inline">
                                        <i class="ti ti-edit me-2 align-middle"></i>
                                        <span class="text-type text-wrap" data-en="Account Details"
                                            data-bm="Butiran Akaun">Account Details</span>
                                    </span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link p-1 p-md-3" id="shipped-tab" data-bs-toggle="tab"
                                    data-bs-target="#shipped-tab-pane" type="button" role="tab"
                                    aria-controls="shipped-tab" aria-selected="false" tabindex="-1">
                                    <span class="d-flex flex-column gap-1 align-items-center d-md-inline">
                                        <i class="ti ti-download me-2 align-middle"></i>
                                        <span class="text-type text-wrap" data-en="Verification Attachment"
                                            data-bm="Lampiran Pengesahan">Verification Attachment</span>
                                    </span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            @include('pages.authentication.includes.register.1')
                            @include('pages.authentication.includes.register.2')
                            @include('pages.authentication.includes.register.3')
                        </div>
                    </form>

                    <div class="modal fade" id="fileLabelModal" tabindex="-1" aria-labelledby="fileLabelModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="fileLabelModalLabel" data-en="Label your file" data-bm="Berikan label pada fail">Label your file</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-5">
                                            <div class="border rounded p-3 text-center">
                                                <img id="fileLabelPreview" src="" alt="Preview" class="img-fluid d-none" />
                                                <div id="filePreviewIcon" class="d-none text-center py-5">
                                                    <i class="ti ti-file-text ti-5x text-muted"></i>
                                                    <p class="mt-3 text-muted" data-en="File preview not available" data-bm="Pratonton fail tidak tersedia">File preview not available</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <p class="mb-2"><strong data-en="Selected file:" data-bm="Fail dipilih:">Selected file:</strong> <span id="fileLabelName"></span></p>
                                            <label for="fileLabelInput" class="form-label" data-en="File label" data-bm="Label fail">File label</label>
                                            <input type="text" class="form-control" id="fileLabelInput" placeholder="e.g. IC front page, IC back page">
                                            <div class="form-text" data-en="Enter a descriptive name for this upload so you can easily identify it later." data-bm="Masukkan nama deskriptif untuk muat naik ini supaya ia mudah dikenal kemudian.">Enter a descriptive name for this upload so you can easily identify it later.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="saveFileLabelBtn" data-en="Save" data-bm="Simpan">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            /* ---------- Language toggle (shared pattern with login / forgot password) ---------- */
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

            /* ---------- Left panel caption + step dots follow the active wizard tab ---------- */
            var captionBlocks = document.querySelectorAll('.visual-caption-block');
            var stepDots = document.querySelectorAll('.step-dot');

            function showCaption(paneId) {
                captionBlocks.forEach(function (block) {
                    block.classList.toggle('active', block.getAttribute('data-caption-for') === paneId);
                });
                stepDots.forEach(function (dot) {
                    dot.classList.toggle('active', dot.getAttribute('data-step-dot') === paneId);
                });
            }

            // Reacts to Bootstrap's own tab-shown event (covers nav clicks AND any
            // registerAction.js code that calls bootstrap.Tab.show() programmatically)
            document.querySelectorAll('#myTab1 button[data-bs-toggle="tab"]').forEach(function (btn) {
                btn.addEventListener('shown.bs.tab', function (e) {
                    var target = e.target.getAttribute('data-bs-target');
                    if (target) showCaption(target.replace('#', ''));
                });
            });

            // Fallback: also react directly to the wizard's Next/Back buttons in case
            // registerAction.js swaps tabs without going through the Bootstrap Tab API.
            var buttonToPane = {
                'nextToPersonalTab': 'confirm-tab-pane',
                'backToAccountTab': 'order-tab-pane',
                'nextToSummaryTab': 'shipped-tab-pane',
                'backToDetailsTab': 'confirm-tab-pane'
            };
            Object.keys(buttonToPane).forEach(function (btnId) {
                var el = document.getElementById(btnId);
                if (el) {
                    el.addEventListener('click', function () {
                        showCaption(buttonToPane[btnId]);
                    });
                }
            });
        })();
    </script>
@endsection