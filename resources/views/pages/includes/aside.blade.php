<aside class="app-sidebar sticky" id="sidebar">

    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="{{ url('/') }}" class="header-logo">
            <img src="{{ asset('build2/assets/images/brand-logos/desktop-logo.png') }}" alt="logo"
                class="desktop-logo">

            <img src="{{ asset('build2/assets/images/brand-logos/toggle-dark.png') }}" alt="logo" class="toggle-dark">

            <img src="{{ asset('build2/assets/images/brand-logos/desktop-dark.png') }}" alt="logo"
                class="desktop-dark">

            <img src="{{ asset('build2/assets/images/brand-logos/toggle-logo.png') }}" alt="logo"
                class="toggle-logo">

            <img src="{{ asset('build2/assets/images/brand-logos/toggle-white.png') }}" alt="logo"
                class="toggle-white">

            <img src="{{ asset('build2/assets/images/brand-logos/desktop-white.png') }}" alt="logo"
                class="desktop-white">
        </a>
    </div>

    <!-- End::main-sidebar-header -->

    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">

        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>
            @php
                $currentRoute = Route::currentRouteName();
            @endphp

            <ul class="main-menu">
                <!-- Start::slide__category -->
                <li class="slide__category"><span class="category-name">Main</span></li>
                <!-- End::slide__category -->

                <!-- Dashboards -->
                <li class="slide has-sub {{ Str::startsWith($currentRoute, 'dashboard') ? 'open active' : '' }}">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span class="side-menu__label">Dashboards</span>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Dashboards</a></li>
                        <li class="slide {{ $currentRoute === 'dashboard.sales' ? 'active' : '' }}">
                            <a href="#" class="side-menu__item">Sales</a>
                        </li>
                    </ul>
                </li>

                <!-- User Management -->
                <li class="slide__category"><span class="category-name">User</span></li>

              <li class="slide has-sub {{ Str::startsWith($currentRoute, 'public.') || Str::startsWith($currentRoute, 'internal.') ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-arrow-down-s-line side-menu__angle"></i>
                    <i class="ti ti-user side-menu__icon"></i>
                    <span class="side-menu__label">User Management</span>
                </a>
                <ul class="slide-menu child1">
                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Users</a></li>

                    <li class="slide {{ $currentRoute === 'internal.public.list' ? 'active' : '' }}">
                        <a href="{{ route('internal.public.list') }}" class="side-menu__item">Public Users</a>
                    </li>

                    <li class="slide {{ $currentRoute === 'internal.internal.list' ? 'active' : '' }}">
                        <a href="#" class="side-menu__item">Internal Users</a>
                    </li>
                </ul>
            </li>

            </ul>



            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg></div>
        </nav>
        <!-- End::nav -->

    </div>
    <!-- End::main-sidebar -->

</aside>
