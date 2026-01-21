<aside class="app-sidebar sticky" id="sidebar">

    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="{{ url('/') }}" class="header-logo">
            <img src="{{ asset('/asset/doa-logo.png') }}" alt="logo" class="desktop-logo">

            <img src="{{ asset('/asset/doa-logo.png') }}" alt="logo" class="toggle-dark">

            <img src="{{ asset('/asset/doa-logo.png') }}" alt="logo" class="desktop-dark">

            <img src="{{ asset('/asset/doa-logo.png') }}" alt="logo" class="toggle-logo">

            <img src="{{ asset('/asset/doa-logo.png') }}" alt="logo" class="toggle-white">

            <img src="{{ asset('/asset/doa-logo.png') }}" alt="logo" class="desktop-white">
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
                <li class="slide {{ Str::contains($currentRoute, 'dashboard') ? 'open active' : '' }}">
                    <a href="{{ route(authUser()['type'] == 'public' ? 'public.dashboard' : 'internal.dashboard') }}"
                        class="side-menu__item">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>

                        <span class="side-menu__label">Dashboards</span>
                    </a>
                </li>


                <!-- Public -->
                @if (auth('public')->check())
                    <li class="slide__category"><span class="category-name">Application</span></li>

                    @php
                        $publicAppRoutes = [
                            'public.permitApplication',
                            'public.permitAssignApplication',
                            'public.newApplication',
                            'public.verifyapplication',
                            'public.showallapplicationlist',
                            'public.viewApplication',
                        ];

                        $isPublicAppActive = in_array($currentRoute, $publicAppRoutes);
                    @endphp

                    <!-- Apply New -->
                    <li class="slide {{ $currentRoute === 'public.newApplication' ? 'active open' : '' }}">
                        <a href="{{ route('public.newApplication') }}" class="side-menu__item">
                            <i class="bi bi-box side-menu__icon"></i>
                            <span class="side-menu__label">Apply New</span>
                        </a>
                    </li>
                    {{-- <li class="slide">
                        <a href="/application/exporter" class="side-menu__item">Exporter</a>
                    </li> --}}
                    <li class="slide {{ $currentRoute === '/application/exporter' ? 'active open' : '' }}">
                        <a href="/application/exporter" class="side-menu__item">
                            <i class="bi bi-truck side-menu__icon"></i>
                            <span class="side-menu__label">Exporter</span>
                        </a>
                    </li>

                    <!-- Application List -->
                    <li class="slide has-sub {{ $isPublicAppActive ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                            </svg>
                            <span class="side-menu__label">Application List</span>
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                        </a>

                        <ul class="slide-menu child1">

                            <li class="slide">
                                <a href="{{ route('public.verifyapplication') }}" class="side-menu__item">To Review</a>
                            </li>

                            <li class="slide">
                                <a href="{{ route('public.showallapplicationlist') }}" class="side-menu__item">Import Permit List</a>
                            </li>

                            <li class="slide">
                                <a href="{{ route('public.showallinspectionlist') }}" class="side-menu__item">Inspection Certificate List</a>
                            </li>
                            
                            <li class="slide">
                                <a href="{{ route('public.showallconsignmentlist') }}" class="side-menu__item">Consignment Certificate List</a>
                            </li>
                        </ul>
                    </li>
                @endif


                <!-- User Management -->
                @if (auth('internal')->check())
                    {{-- Application Section --}}
                    <li class="slide__category"><span class="category-name">Application</span></li>

                    @php
                        // Check if the route name contains "application" or "inspection"
                        $isApplicationActive = Str::contains($currentRoute, ['application', 'inspection']);
                    @endphp

                    <li class="slide has-sub {{ $isApplicationActive ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                            <i class="ti ti-file-info side-menu__icon"></i>
                            <span class="side-menu__label">Applications</span>
                        </a>

                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1"><a href="javascript:void(0)">Application</a></li>

                            <li class="slide {{ $currentRoute === 'internal.application.list' ? 'active' : '' }}">
                                <a href="{{ route('internal.application.list') }}" class="side-menu__item">
                                    Import Permit List
                                </a>
                            </li>

                            <li class="slide {{ $currentRoute === 'internal.inspection.list' ? 'active' : '' }}">
                                <a href="{{ route('internal.inspection.list') }}" class="side-menu__item">
                                    Inspection Certificate List
                                </a>
                            </li>

                        </ul>
                    </li>




                    <li class="slide__category"><span class="category-name">User</span></li>

                    @php
                        $userManagementRoutes = [
                            'internal.public.',
                            'internal.internal.',
                            'internal.internal.activity_log',
                            'internal.internal.role',
                        ];

                        $isUserManagementActive = false;

                        foreach ($userManagementRoutes as $prefix) {
                            if (Str::startsWith($currentRoute, $prefix)) {
                                $isUserManagementActive = true;
                                break;
                            }
                        }
                    @endphp

                    <li class="slide has-sub {{ $isUserManagementActive ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                            <i class="ti ti-user side-menu__icon"></i>
                            <span class="side-menu__label">User Management</span>
                        </a>

                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1"><a href="javascript:void(0)">Users</a></li>

                            <li class="slide {{ $currentRoute === 'internal.public.list' ? 'active' : '' }}">
                                <a href="{{ route('internal.public.list') }}" class="side-menu__item">Public
                                    Users</a>
                            </li>

                            <li class="slide {{ $currentRoute === 'internal.internal.list' ? 'active' : '' }}">
                                <a href="{{ route('internal.internal.list') }}" class="side-menu__item">Internal
                                    Users</a>
                            </li>

                            <li
                                class="slide {{ $currentRoute === 'internal.internal.activity_log' ? 'active' : '' }}">
                                <a href="{{ route('internal.internal.activity_log') }}"
                                    class="side-menu__item">Activity Log</a>
                            </li>

                            <li class="slide {{ $currentRoute === 'internal.internal.role' ? 'active' : '' }}">
                                <a href="{{ route('internal.internal.role') }}" class="side-menu__item">Role and
                                    Permission</a>
                            </li>
                        </ul>
                    </li>


                    <li class="slide__category"><span class="category-name">Misc</span></li>
                    <li class="slide has-sub {{ Str::startsWith($currentRoute, 'internal.') ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                            <i class="ti ti-user side-menu__icon"></i>
                            <span class="side-menu__label" style="line-height: 1.3rem">
                                <span>System <br> Configuration</span>
                            </span>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1"><a href="javascript:void(0)">Misc</a></li>

                            <li class="slide {{ $currentRoute === 'internal.controlpanel' ? 'active' : '' }}">
                                <a href="{{ url('/internal/control_panel') }}" class="side-menu__item">Control
                                    Panel</a>
                            </li>

                            <li class="slide {{ $currentRoute === 'internal.internal.list' ? 'active' : '' }}">
                                <a href="{{ url('/internal/permit_condition') }}" class="side-menu__item">Permit
                                    Condition</a>
                            </li>

                            <li class="slide {{ $currentRoute === 'internal.state-district-management' ? 'active' : '' }}">
                                <a href="{{ route('internal.state-district-management') }}" class="side-menu__item">State & District Management</a>
                            </li>

                            <li style="display:none"
                                class="slide {{ $currentRoute === 'internal.internal.activity_log' ? 'active' : '' }} ">
                                <a href="{{ route('internal.internal.activity_log') }}" class="side-menu__item">
                                    Activity Log</a>
                            </li>
                        </ul>
                    </li>
                @endif



            </ul>



            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z">
                    </path>
                </svg></div>
        </nav>
        <!-- End::nav -->

    </div>
    <!-- End::main-sidebar -->

</aside>
