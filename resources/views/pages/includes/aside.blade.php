<aside class="app-sidebar sticky" id="sidebar">

    <div class="main-sidebar-header">
        <a href="{{ url('/') }}" class="header-logo">
            <img src="{{ asset('/asset/doa-logo.png') }}"  alt="logo" class="desktop-logo">
            <img src="{{ asset('/asset/Logo-DOA.png') }}"  alt="logo" class="toggle-dark">
            <img src="{{ asset('/asset/doa-logo.png') }}"  alt="logo" class="desktop-dark">
            <img src="{{ asset('/asset/Logo-DOA.png') }}"  alt="logo" class="toggle-logo">
            <img src="{{ asset('/asset/Logo-DOA.png') }}"  alt="logo" class="toggle-white">
            <img src="{{ asset('/asset/doa-logo.png') }}"  alt="logo" class="desktop-white">
        </a>
    </div>

    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">

            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"/>
                </svg>
            </div>

            @php
                $currentRoute  = Route::currentRouteName();
                $isInternal    = auth('internal')->check();
                $isPublic      = auth('public')->check();
                $internalUser  = $isInternal ? auth('internal')->user() : null;
                $isSuperadmin  = $internalUser?->hasRole('superadmin')       ?? false;
                $isFinance     = $internalUser?->hasRole('finance')           ?? false;
                $isBoundary    = $internalUser?->hasRole('boundary officer')  ?? false;
                $isRestricted  = $isFinance || $isBoundary;

                
                $isSipitang = $internalUser?->branch === 'Sipitang';

                // ── User Management visibility ──────────────────────────────────
                $canSeeUserManagement = $isSuperadmin || ($internalUser?->canAny([
                    'read public user',
                    'read internal user',
                    'approve public user',
                    'read activity log',
                ]) ?? false);

                // ── Active state helpers ────────────────────────────────────────
                $isApplicationActive = Str::contains($currentRoute, ['application', 'inspection', 'consignment']);

                $isUserManagementActive = collect([
                    'internal.public.',
                    'internal.internal.',
                    'activity_logs',
                    'internal.internal.role',
                ])->contains(fn($prefix) => Str::startsWith($currentRoute, $prefix));

                $isImporterExporterActive = in_array($currentRoute, [
                    'internal.exporter.list',
                    'internal.importer.list',
                ]);

                $isPublicAppActive = in_array($currentRoute, [
                    'public.permitApplication',
                    'public.permitAssignApplication',
                    'public.newApplication',
                    'public.verifyapplication',
                    'public.showallapplicationlist',
                    'public.viewApplication',
                ]);
            @endphp

            <ul class="main-menu">

                {{-- ── Main ──────────────────────────────────────────────────── --}}
                <li class="slide__category"><span class="category-name">Main</span></li>

                <li class="slide {{ Str::contains($currentRoute, 'dashboard') ? 'open active' : '' }}">
                    <a href="{{ route($isPublic ? 'public.dashboard' : 'internal.dashboard') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                        </svg>
                        <span class="side-menu__label">Dashboards</span>
                    </a>
                </li>

                {{-- ── Public Section ────────────────────────────────────────── --}}
                @if($isPublic)
                    <li class="slide__category"><span class="category-name">Application</span></li>

                    <li class="slide {{ $currentRoute === 'public.newApplication' ? 'active open' : '' }}">
                        <a href="{{ route('public.newApplication') }}" class="side-menu__item">
                            <i class="bi bi-box side-menu__icon"></i>
                            <span class="side-menu__label">Apply New</span>
                        </a>
                    </li>

                    <li class="slide {{ $currentRoute === '/application/exporter' ? 'active open' : '' }}">
                        <a href="/application/exporter" class="side-menu__item">
                            <i class="bi bi-truck side-menu__icon"></i>
                            <span class="side-menu__label">Exporter</span>
                        </a>
                    </li>

                    <li class="slide {{ $currentRoute === 'application.importer' ? 'active open' : '' }}">
                        <a href="{{ route('application.importer') }}" class="side-menu__item">
                            <i class="bi bi-people side-menu__icon"></i>
                            <span class="side-menu__label">Importer</span>
                        </a>
                    </li>

                    <li class="slide has-sub {{ $isPublicAppActive ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <i class="bi bi-journal side-menu__icon"></i>
                            <span class="side-menu__label">Application List</span>
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide">
                                <a href="{{ route('public.verifyapplication') }}" class="side-menu__item" id="toReviewCount">To Review</a>
                            </li>
                            <li class="slide">
                                <a href="/public/agent_list" class="side-menu__item">Representative List</a>
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

                {{-- ── Internal Section ──────────────────────────────────────── --}}
                @if($isInternal && !$isRestricted)

                    {{-- Application --}}
                    <li class="slide__category"><span class="category-name">Application</span></li>

                    <li class="slide has-sub {{ $isApplicationActive ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                            <i class="bi bi-journal side-menu__icon"></i>
                            <span class="side-menu__label">Application List</span>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1"><a href="javascript:void(0)">Application List</a></li>
                    
                                <li class="slide {{ $currentRoute === 'internal.application.list' ? 'active' : '' }}">
                                    <a href="{{ route('internal.application.list') }}" class="side-menu__item">Import Permit</a>
                                </li>
                                <li class="slide {{ $currentRoute === 'internal.inspection.list' ? 'active' : '' }}">
                                    <a href="{{ route('internal.inspection.list') }}" class="side-menu__item">Inspection Certificate</a>
                                </li>
                        
                            <li class="slide {{ $currentRoute === 'internal.consignment.list' ? 'active' : '' }}">
                                <a href="{{ route('internal.consignment.list') }}" class="side-menu__item">Consignment Certificate</a>
                            </li>
                        </ul>
                    </li>

                    {{-- User Management — only shown if user has at least one relevant permission --}}
                    @if($canSeeUserManagement)
                        <li class="slide__category"><span class="category-name">User</span></li>

                        <li class="slide has-sub {{ $isUserManagementActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                <i class="ti ti-user side-menu__icon"></i>
                                <span class="side-menu__label">User Management</span>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1"><a href="javascript:void(0)">Users</a></li>

                                @can('read public user')
                                    <li class="slide {{ $currentRoute === 'internal.public.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.public.list') }}" class="side-menu__item">Public Users</a>
                                    </li>
                                @endcan

                                @can('read internal user')
                                    <li class="slide {{ $currentRoute === 'internal.internal.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.internal.list') }}" class="side-menu__item">Internal Users</a>
                                    </li>
                                @endcan

                                @can('approve public user')
                                    <li class="slide {{ $currentRoute === 'internal.public.verification.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.public.verification.list') }}" class="side-menu__item" id="verificationCount">User Verification</a>
                                    </li>
                                @endcan

                                @can('read activity log')
                                    <li class="slide {{ $currentRoute === 'internal.activity_logs' ? 'active' : '' }}">
                                        <a href="{{ route('internal.activity_logs') }}" class="side-menu__item">Activity Log</a>
                                    </li>
                                @endcan

                                @if($isSuperadmin)
                                    <li class="slide {{ $currentRoute === 'internal.internal.role' ? 'active' : '' }}">
                                        <a href="{{ route('internal.internal.role') }}" class="side-menu__item">Role and Permission</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{ route('internal.boundary.list') }}" class="side-menu__item">Boundary Officer</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- Importer & Exporter --}}
                    @canany(['view importer list', 'view exporter list'])
                        <li class="slide has-sub {{ $isImporterExporterActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                <i class="ti ti-user side-menu__icon"></i>
                                <span class="side-menu__label">Importer &amp; Exporter</span>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1"><a href="javascript:void(0)">Importer &amp; Exporter</a></li>

                                @can('view importer list')
                                    <li class="slide {{ $currentRoute === 'internal.importer.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.importer.list') }}" class="side-menu__item">Importer List</a>
                                    </li>
                                @endcan

                                @can('view exporter list')
                                    <li class="slide {{ $currentRoute === 'internal.exporter.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.exporter.list') }}" class="side-menu__item">Exporter List</a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    {{-- System Configuration --}}
                    @can('manage settings')
                        <li class="slide__category"><span class="category-name">Misc</span></li>

                        <li class="slide has-sub {{ Str::startsWith($currentRoute, 'internal.') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                <i class="bi bi-gear-wide side-menu__icon"></i>
                                <span class="side-menu__label" style="line-height:1.3rem">
                                    <span>System <br> Configuration</span>
                                </span>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1"><a href="javascript:void(0)">Misc</a></li>
                                <li class="slide {{ $currentRoute === 'internal.controlpanel' ? 'active' : '' }}">
                                    <a href="{{ url('/internal/control_panel') }}" class="side-menu__item">Control Panel</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/internal/permit_condition') }}" class="side-menu__item">Permit Item</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/internal/consignment_condition') }}" class="side-menu__item">Consignment Item</a>
                                </li>
                                <li class="slide {{ $currentRoute === 'internal.state-district-management' ? 'active' : '' }}">
                                    <a href="{{ route('internal.state-district-management') }}" class="side-menu__item">State &amp; District Management</a>
                                </li>
                                <li class="slide {{ $currentRoute === 'internal.branch-management' ? 'active' : '' }}">
                                    <a href="{{ route('internal.branch-management') }}" class="side-menu__item">Branch Management</a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                @endif

                {{-- ── Order (hidden for finance & boundary officer) ──────────── --}}
                @if(!($isInternal && $isRestricted))
                    <li class="slide__category"><span class="category-name">Order</span></li>

                    <li class="slide {{ Str::contains($currentRoute, 'order') ? 'open active' : '' }}">
                        <a href="/order/list" class="side-menu__item">
                            <i class="bi bi-card-list side-menu__icon"></i>
                            <span class="side-menu__label">Order</span>
                        </a>
                    </li>
                @endif

            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/>
                </svg>
            </div>

        </nav>
    </div>
</aside>