<div class="main-header-container container-fluid">

    <!-- Start::header-content-left -->
    <div class="header-content-left">

        <!-- Start::header-element -->
        <div class="header-element">
            <div class="horizontal-logo">
                <a href="/" class="header-logo">
                    <img src="{{ asset('/asset/Logo-DOA.png') }}" alt="logo" class="desktop-logo">
                    <img src="{{ asset('/asset/Logo-DOA.png') }}" alt="logo" class="toggle-dark">
                    <img src="{{ asset('/asset/Logo-DOA.png') }}" alt="logo" class="desktop-dark">
                    <img src="{{ asset('/asset/Logo-DOA.png') }}" alt="logo" class="toggle-logo">
                    <img src="{{ asset('/asset/Logo-DOA.png') }}" alt="logo" class="toggle-white">
                    <img src="{{ asset('/asset/Logo-DOA.png') }}" alt="logo" class="desktop-white">
                </a>
            </div>
        </div>
        <!-- End::header-element -->

        <!-- Start::header-element -->
        <div class="header-element mx-lg-0 mx-2">
            <a aria-label="Hide Sidebar"
                class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
        </div>
        <!-- End::header-element -->

    </div>
    <!-- End::header-content-left -->

    <!-- Start::header-content-right -->
    <ul class="header-content-right  list-unstyled">

        <!-- Start::Language Switcher Element -->
        <li class="header-element px-2">
            {{-- Language toggle: visible on all breakpoints --}}
            <div class="lang-toggle d-flex align-items-center gap-1 border rounded-2 p-1 bg-light"
                style="border-color: var(--input-border) !important;">
                <button type="button" class="btn btn-sm py-1 px-2 lang-btn" data-lang="en">EN</button>
                <button type="button" class="btn btn-sm py-1 px-2 lang-btn" data-lang="bm">BM</button>
            </div>
        </li>
        <!-- End::Language Switcher Element -->

        <!-- Start::header-element -->
        <li class="header-element notifications-dropdown dropdown d-block">
            <!-- Start::header-link|dropdown-toggle -->
            <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown"
                data-bs-auto-close="outside" id="messageDropdown" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 header-link-icon" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                </svg>
                <span class="" id="pulse"></span>
            </a>
            <!-- End::header-link|dropdown-toggle -->

            <!-- Start::main-header-dropdown -->
            <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="bottom-end"
                style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate(0px, 34px);">

                <div class="p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="mb-0 fs-15 fw-medium">Notifications</p>
                        <span class="badge bg-secondary text-fixed-white" id="notifiation-data">5 Unread</span>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <ul class="list-unstyled mb-0 simplebar-scrollable-y" id="header-notification-scroll"
                    data-simplebar="init">
                    <div class="simplebar-wrapper" style="margin: 0px;">
                        <div class="simplebar-height-auto-observer-wrapper">
                            <div class="simplebar-height-auto-observer"></div>
                        </div>
                        <div class="simplebar-mask">
                            <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                <div class="simplebar-content-wrapper" tabindex="0" role="region"
                                    aria-label="scrollable content" style="height: auto; overflow: hidden scroll;">
                                    <div class="simplebar-content" style="padding: 0px;" id="notificationContent">

                                        <li class="dropdown-item">
                                            <div class="d-flex align-items-center">
                                                <div class="pe-2 lh-1">
                                                    <span class="avatar avatar-md bg-primary2 avatar-rounded">
                                                        <i class="ri-gift-line lh-1 fs-16"></i>
                                                    </span>
                                                </div>
                                                <div
                                                    class="flex-grow-1 d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <p class="mb-0 fw-medium"><a
                                                                href="https://laravelui.spruko.com/xintra/chat">Exclusive
                                                                Offers</a></p>
                                                        <div
                                                            class="text-muted fw-normal fs-12 header-notification-text text-truncate">
                                                            Enjoy<span class="text-success">20% off</span> on your next
                                                            purchase!</div>
                                                        <div class="fw-normal fs-10 text-muted op-8">5 hours ago</div>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);"
                                                            class="min-w-fit-content dropdown-item-close1">
                                                            <i class="ri-close-line"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="simplebar-placeholder" style="width: 334px; height: 367px;"></div>
                    </div>
                    <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                        <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
                    </div>
                    <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
                        <div class="simplebar-scrollbar"
                            style="height: 279px; display: block; transform: translate3d(0px, 0px, 0px);"></div>
                    </div>
                </ul>

                <div class="p-3 empty-header-item1 border-top">
                    <div class="d-grid">
                        <a href="/notifications" class="btn btn-primary btn-wave waves-effect waves-light">View
                            All</a>
                    </div>
                </div>
                <div class="p-5 empty-item1 d-none">
                    <div class="text-center">
                        <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">
                            <i class="ri-notification-off-line fs-2"></i>
                        </span>
                        <h6 class="fw-medium mt-3">No New Notifications</h6>
                    </div>
                </div>
            </div>
            <!-- End::main-header-dropdown -->
        </li>
        <!-- End::header-element -->

        <!-- Start::header-element -->
        <li class="header-element dropdown">
            @php
                $name = authUser()['user']['fullname'] ?? '';
                $parts = explode(' ', trim($name));
                $initials = strtoupper(($parts[0][0] ?? '') . ($parts[1][0] ?? ''));
            @endphp

            <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <div class="d-flex align-items-center">
                    <div
                        class="avatar avatar-sm bg-primary text-white fw-bold d-flex align-items-center justify-content-center rounded-circle">
                        {{ $initials }}
                    </div>
                </div>
            </a>

            <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                aria-labelledby="mainHeaderProfile">
                <li>
                    <div class="dropdown-item text-center border-bottom p-3" id="redirectProfile">
                        @php
                            $internalUser = Auth::guard('internal')->user();
                            $publicUser = Auth::guard('public')->user();
                        @endphp

                        <span class="fw-semibold fs-14 d-block mb-1">
                            {{ $internalUser->fullname ?? ($publicUser->fullname ?? 'Guest') }}
                        </span>

                        <span class="d-block fs-12 text-muted mb-2">
                            @if ($internalUser)
                                <span data-en="Internal User" data-bm="Pengguna Dalaman">Internal User</span>
                            @elseif ($publicUser)
                                <span data-en="Public User" data-bm="Pengguna Awam">Public User</span>
                            @else
                                <span data-en="Guest" data-bm="Pelawat">Guest</span>
                            @endif
                        </span>

                        {{-- Friendly Alert Block for Unverified Public Users --}}
                        @if ($publicUser && !$publicUser->doa_verified)
                            <div
                                class="alert alert-warning border-0 p-2 mb-0 mt-2 rounded-2 text-start d-flex align-items-start gap-2">
                                <i class="bx bx-info-circle fs-16 text-warning mt-1"></i>
                                <div>
                                    <div class="fw-medium fs-12 text-warning-emphasis"
                                        data-en="Account Pending Approval" data-bm="Akaun Menunggu Pengesahan">
                                        Account Pending Approval
                                    </div>
                                    <p class="mb-0 fs-11 text-muted mt-0.5"
                                        data-en="Some features are restricted until verified by DOA."
                                        data-bm="Beberapa fungsi dihadkan sehingga disahkan oleh DOA.">
                                        Some features are restricted until verified by DOA.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </li>

                <li class="p-4 p-md-0">
                    <a type="button" id="logoutButton" href="{{ route('logout') }}"
                        class="dropdown-item d-flex align-items-center text-start w-100 py-2.5">
                        <i class="ti ti-lock p-1 rounded-circle bg-primary-transparent me-2 fs-16"></i>
                        <span data-en="Log Out" data-bm="Log Keluar">Log Out</span>
                    </a>
                </li>
            </ul>
        </li>
        <!-- End::header-element -->

    </ul>
    <!-- End::header-content-right -->

</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Logout logic
            const logoutForm = document.getElementById("logoutForm");
            if (logoutForm) {
                logoutForm.addEventListener("submit", function(e) {
                    e.preventDefault();
                    this.submit();
                });
            }

            // Client-side Localization Sandbox Controller
            (function() {
                var STORAGE_KEY = "qis_lang";
                var buttons = document.querySelectorAll(".lang-btn");
                var elements = document.querySelectorAll("[data-en]");

                function setLang(lang) {
                    elements.forEach(function(el) {
                        var text = el.getAttribute("data-" + lang);
                        if (text === null) return;
                        if (el.getAttribute("data-i18n-attr") === "placeholder") {
                            el.setAttribute("placeholder", text);
                        } else {
                            el.textContent = text;
                        }
                    });

                    buttons.forEach(function(btn) {
                        btn.classList.toggle(
                            "active",
                            btn.getAttribute("data-lang") === lang
                        );
                    });

                    document.documentElement.setAttribute(
                        "lang",
                        lang === "bm" ? "ms" : "en"
                    );

                    try {
                        localStorage.setItem(STORAGE_KEY, lang);
                    } catch (e) {
                        /* storage unavailable, ignore */
                    }

                    // Dispatch event for JS components to re-render
                    window.dispatchEvent(new CustomEvent("lang-changed", { detail: lang }));
                }

                buttons.forEach(function(btn) {
                    btn.addEventListener("click", function() {
                        setLang(btn.getAttribute("data-lang"));
                    });
                });

                var savedLang = "en";
                try {
                    savedLang = localStorage.getItem(STORAGE_KEY) || "en";
                } catch (e) {
                    /* storage unavailable, default to en */
                }

                setLang(savedLang);
            })();
        });
    </script>
@endpush
