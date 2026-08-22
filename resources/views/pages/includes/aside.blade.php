<aside class="app-sidebar sticky" id="sidebar">

    <div class="main-sidebar-header">
        <a href="{{ url('/') }}" class="header-logo">
            <img src="{{ asset('/asset/doa-logo.png') }}" alt="logo" class="desktop-logo">
            <img src="{{ asset('/asset/Logo-DOA.png') }}" alt="logo" class="toggle-dark">
            <img src="{{ asset('/asset/doa-logo.png') }}" alt="logo" class="desktop-dark">
            <img src="{{ asset('/asset/Logo-DOA.png') }}" alt="logo" class="toggle-logo">
            <img src="{{ asset('/asset/Logo-DOA.png') }}" alt="logo" class="toggle-white">
            <img src="{{ asset('/asset/doa-logo.png') }}" alt="logo" class="desktop-white">
        </a>
    </div>

    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">

            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>

            @php
                $currentRoute = Route::currentRouteName();
                $isInternal = auth('internal')->check();
                $isPublic = auth('public')->check();
                $internalUser = $isInternal ? auth('internal')->user() : null;
                $isSuperadmin = $internalUser?->hasRole('superadmin') ?? false;
                $isAdmin = $internalUser?->hasRole('admin') ?? false;
                $isFinance = $internalUser?->hasRole('finance') ?? false;
                $isBoundary = $internalUser?->hasRole('boundary officer') ?? false;
                $isRestricted = $isFinance || $isBoundary;

                $isSipitang = $internalUser?->branch === 'Sipitang';

                // ── User Management visibility ──────────────────────────────────
                $canReadPublicUser = $internalUser?->can('read public user') ?? false;
                $canReadInternalUser = $internalUser?->can('read internal user') ?? false;
                $canApprovePublicUser = $internalUser?->can('approve public user') ?? false;
                $canReadActivityLog = $internalUser?->can('read activity log') ?? false;
                $canManageRolePermission = $internalUser?->hasRole('superadmin') ?? false;

                $canSeeUserManagement =
                    $isSuperadmin ||
                    $canReadPublicUser ||
                    $canReadInternalUser ||
                    $canApprovePublicUser ||
                    $canReadActivityLog;

                $isApplicationActive = Str::contains($currentRoute, ['application', 'inspection', 'consignment']);
                $isUserManagementActive = collect([
                    'internal.public.list',
                    'internal.internal.list',
                    'internal.public.verification.list',
                    'internal.activity_logs',
                    'internal.internal.role',
                ])->contains(fn($prefix) => Str::startsWith($currentRoute, $prefix) || $currentRoute === $prefix);
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
                <li class="slide__category"><span class="category-name" data-en="Main" data-bm="Utama">Main</span></li>

                <li class="slide {{ Str::contains($currentRoute, 'dashboard') ? 'open active' : '' }}">
                    <a href="{{ route($isPublic ? 'public.dashboard' : 'internal.dashboard') }}"
                        class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span class="side-menu__label" data-en="Dashboard" data-bm="Dashboard">Dashboard</span>
                    </a>
                </li>

                {{-- ── Public Section ────────────────────────────────────────── --}}
                @if ($isPublic)
                    <li class="slide__category"><span class="category-name" data-en="Application"
                            data-bm="Permohonan">Application</span></li>

                    <li class="slide {{ $currentRoute === 'public.newApplication' ? 'active open' : '' }}">
                        <a href="{{ route('public.newApplication') }}" class="side-menu__item">
                            <i class="bi bi-box side-menu__icon"></i>
                            <span class="side-menu__label" data-en="Apply New" data-bm="Mohon Baru">Apply New</span>
                        </a>
                    </li>

                    <li class="slide {{ $currentRoute === '/application/exporter' ? 'active open' : '' }}">
                        <a href="/application/exporter" class="side-menu__item">
                            <i class="bi bi-truck side-menu__icon"></i>
                            <span class="side-menu__label" data-en="Exporter" data-bm="Pengeksport">Exporter</span>
                        </a>
                    </li>

                    <li class="slide {{ $currentRoute === 'application.importer' ? 'active open' : '' }}">
                        <a href="{{ route('application.importer') }}" class="side-menu__item">
                            <i class="bi bi-people side-menu__icon"></i>
                            <span class="side-menu__label" data-en="Importer" data-bm="Pengimport">Importer</span>
                        </a>
                    </li>


                    <li class="slide {{ Str::contains($currentRoute, 'vehicles') ? 'open active' : '' }}">
                        <a href="{{  route('vehicles.index') }}" class="side-menu__item">
                            <i class="bi bi-truck side-menu__icon"></i>
                            <span class="side-menu__label" data-en="Vehicle List" data-bm="Senarai Kenderaan">Vehicle
                                List</span>
                        </a>
                    </li>


                    <li class="slide has-sub {{ $isPublicAppActive ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <i class="bi bi-journal side-menu__icon"></i>
                            <span class="side-menu__label" style="white-space: pre-line; line-height: 1.2;"
                                data-en="Application&#10;List"
                                data-bm="Senarai&#10;Permohonan">Application&#10;List</span>
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide">
                                <a href="{{ route('public.verifyapplication') }}" class="side-menu__item"
                                    id="toReviewCount" data-bm="Untuk Disemak" data-en="To Review">To Review</a>
                            </li>
                            <li class="slide">
                                <a href="/public/agent_list" class="side-menu__item" data-en="Representative List"
                                    data-bm="Senarai Wakil">Representative List</a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('public.showallapplicationlist') }}" class="side-menu__item"
                                    data-en="Import Permit List" data-bm="Senarai Permit Import">Import Permit
                                    List</a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('public.showallinspectionlist') }}" class="side-menu__item"
                                    data-en="Inspection Certificate List"
                                    data-bm="Senarai Sijil Pemeriksaan">Inspection Certificate List</a>
                            </li>
                            <li class="slide">
                                <a href="{{ route('public.showallconsignmentlist') }}" class="side-menu__item"
                                    data-en="Consignment Certificate List"
                                    data-bm="Senarai Sijil Konsainan">Consignment Certificate List</a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- ── Internal Section ──────────────────────────────────────── --}}
                @if ($isInternal && !$isRestricted)

                    {{-- Application --}}
                    <li class="slide__category"><span class="category-name" data-en="Application"
                            data-bm="Permohonan">Application</span></li>

                    <li class="slide has-sub {{ $isApplicationActive ? 'open active' : '' }}">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                            <i class="bi bi-journal side-menu__icon"></i>
                            <span class="side-menu__label" data-en="Application List" data-bm="Senarai Permohonan">
                                Application List
                                <svg id="appListParentBadge"
                                    style="display:none; position: relative; top: -5px; left: 2px;" width="8"
                                    height="8" viewBox="0 0 8 8" xmlns="http://www.w3.org/2000/svg"
                                    aria-hidden="true">
                                    <circle cx="4" cy="4" r="4" fill="#dc3545" />
                                </svg>
                            </span>
                        </a>
                        <ul class="slide-menu child1">
                            <li class="slide side-menu__label1"><a href="javascript:void(0)"
                                    data-en="Application List" data-bm="Senarai Permohonan">Application List</a></li>

                            <li class="slide {{ $currentRoute === 'internal.application.list' ? 'active' : '' }}">
                                <a href="{{ route('internal.application.list') }}" class="side-menu__item"
                                    id="importPermitCount" data-en="Import Permit" data-bm="Permit Import">Import
                                    Permit</a>
                            </li>
                            <li class="slide {{ $currentRoute === 'internal.inspection.list' ? 'active' : '' }}">
                                <a href="{{ route('internal.inspection.list') }}" class="side-menu__item"
                                    id="inspectionAppCount" data-en="Inspection Certificate"
                                    data-bm="Sijil Pemeriksaan">Inspection Certificate</a>
                            </li>
                            <li class="slide {{ $currentRoute === 'internal.consignment.list' ? 'active' : '' }}">
                                <a href="{{ route('internal.consignment.list') }}" class="side-menu__item"
                                    id="consignmentAppCount" data-en="Consignment Certificate"
                                    data-bm="Sijil Konsainan">Consignment Certificate</a>
                            </li>
                        </ul>
                    </li>

                    {{-- User Management — only shown if user has at least one relevant permission --}}
                    @if ($canSeeUserManagement)
                        <li class="slide__category"><span class="category-name" data-en="User"
                                data-bm="Pengguna">User</span></li>

                        <li class="slide has-sub {{ $isUserManagementActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                <i class="ti ti-user side-menu__icon"></i>
                                <span class="side-menu__label" style="white-space: pre-line; line-height: 1.2;"
                                    data-en="User&#10;Management"
                                    data-bm="Pengurusan&#10;Pengguna">User&#10;Management</span>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1"><a href="javascript:void(0)" data-en="Users"
                                        data-bm="Pengguna">Users</a></li>

                                @if ($canReadPublicUser)
                                    <li class="slide {{ $currentRoute === 'internal.public.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.public.list') }}" class="side-menu__item"
                                            data-en="Public Users" data-bm="Pengguna Awam">Public Users</a>
                                    </li>
                                @endif

                                @if ($canReadInternalUser)
                                    <li
                                        class="slide {{ $currentRoute === 'internal.internal.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.internal.list') }}" class="side-menu__item"
                                            data-en="Internal Users" data-bm="Pengguna Dalaman">Internal Users</a>
                                    </li>
                                @endif

                                @if ($canApprovePublicUser)
                                    <li
                                        class="slide {{ $currentRoute === 'internal.public.verification.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.public.verification.list') }}"
                                            class="side-menu__item" id="verificationCount"
                                            data-en="User Verification" data-bm="Pengesahan Pengguna">User
                                            Verification</a>
                                    </li>
                                @endif

                                @if ($canReadActivityLog)
                                    <li
                                        class="slide {{ $currentRoute === 'internal.activity_logs' ? 'active' : '' }}">
                                        <a href="{{ route('internal.activity_logs') }}" class="side-menu__item"
                                            data-en="Activity Log" data-bm="Log Aktiviti">Activity Log</a>
                                    </li>
                                @endif

                                @if ($isSuperadmin)
                                    <li
                                        class="slide {{ $currentRoute === 'internal.internal.role' ? 'active' : '' }}">
                                        <a href="{{ route('internal.internal.role') }}" class="side-menu__item"
                                            data-en="Role and Permission" data-bm="Peranan dan Kebenaran">Role and
                                            Permission</a>
                                    </li>
                                    <li class="slide">
                                        <a href="{{ route('internal.boundary.list') }}" class="side-menu__item"
                                            data-en="Boundary Officer" data-bm="Pegawai Sempadan">Boundary Officer</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- Importer & Exporter --}}
                    @php
                        $canViewImporter = $internalUser?->can('view importer list') ?? false;
                        $canViewExporter = $internalUser?->can('view exporter list') ?? false;
                    @endphp

                    @if ($canViewImporter || $canViewExporter)
                        <li class="slide has-sub {{ $isImporterExporterActive ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                <i class="ti ti-user side-menu__icon"></i>
                                <span class="side-menu__label" style="white-space: pre-line; line-height: 1.2;"
                                    data-en="Importer &amp;&#10;Exporter"
                                    data-bm="Pengimport &amp;&#10;Pengeksport">Importer &amp;&#10;Exporter</span>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1"><a href="javascript:void(0)"
                                        data-en="Importer &amp; Exporter"
                                        data-bm="Pengimport &amp; Pengeksport">Importer &amp; Exporter</a></li>

                                @if ($canViewImporter)
                                    <li
                                        class="slide {{ $currentRoute === 'internal.importer.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.importer.list') }}" class="side-menu__item"
                                            data-en="Importer List" data-bm="Senarai Pengimport">Importer List</a>
                                    </li>
                                @endif

                                @if ($canViewExporter)
                                    <li
                                        class="slide {{ $currentRoute === 'internal.exporter.list' ? 'active' : '' }}">
                                        <a href="{{ route('internal.exporter.list') }}" class="side-menu__item"
                                            data-en="Exporter List" data-bm="Senarai Pengeksport">Exporter List</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- ── Vehicle List (Internal: admin & superadmin) ─────────── --}}
                    @if ($isSuperadmin || $isAdmin)
                        <li class="slide {{ Str::contains($currentRoute, 'vehicles') ? 'open active' : '' }}">
                            <a href="{{  route('vehicles.index') }}" class="side-menu__item">
                                <i class="bi bi-truck side-menu__icon"></i>
                                <span class="side-menu__label" data-en="Vehicle List"
                                    data-bm="Senarai Kenderaan">Vehicle List</span>
                            </a>
                        </li>
                    @endif

                    {{-- ── Document List (Superadmin only) ──────────────────── --}}
                    @if ($isSuperadmin)
                        <li class="slide {{ Str::contains($currentRoute, 'documents') ? 'open active' : '' }}">
                            <a href="{{ route('internal.documents.index') }}" class="side-menu__item">
                                <i class="bi bi-file-earmark-text side-menu__icon"></i>
                                <span class="side-menu__label" data-en="Document List"
                                    data-bm="Senarai Dokumen">Document List</span>
                            </a>
                        </li>
                    @endif

                    {{-- System Configuration --}}
                    @php
                        $canManageSettings = $internalUser?->can('manage settings') ?? false;
                    @endphp

                    @if ($canManageSettings)
                        <li class="slide__category"><span class="category-name" data-en="Misc"
                                data-bm="Pelbagai">Misc</span></li>

                        <li
                            class="slide has-sub {{ Str::startsWith($currentRoute, 'internal.') ? 'open active' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                <i class="bi bi-gear-wide side-menu__icon"></i>
                                <span class="side-menu__label" style="line-height:1.3rem">
                                    <span data-en="System Configuration" data-bm="Konfigurasi Sistem">System <br>
                                        Configuration</span>
                                </span>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1"><a href="javascript:void(0)" data-en="Misc"
                                        data-bm="Pelbagai">Misc</a></li>
                                <li class="slide {{ $currentRoute === 'internal.controlpanel' ? 'active' : '' }}">
                                    <a href="{{ url('/internal/control_panel') }}" class="side-menu__item"
                                        data-en="Control Panel" data-bm="Panel Kawalan">Control Panel</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/internal/permit_condition') }}" class="side-menu__item"
                                        data-en="Permit Item" data-bm="Item Permit">Permit Item</a>
                                </li>
                                <li class="slide">
                                    <a href="{{ url('/internal/consignment_condition') }}" class="side-menu__item"
                                        data-en="Consignment Item" data-bm="Item Konsainan">Consignment Item</a>
                                </li>
                                <li
                                    class="slide {{ $currentRoute === 'internal.state-district-management' ? 'active' : '' }}">
                                    <a href="{{ route('internal.state-district-management') }}"
                                        class="side-menu__item" data-en="State &amp; District Management"
                                        data-bm="Pengurusan Negeri &amp; Daerah">State &amp; District Management</a>
                                </li>
                                <li
                                    class="slide {{ $currentRoute === 'internal.branch-management' ? 'active' : '' }}">
                                    <a href="{{ route('internal.branch-management') }}" class="side-menu__item"
                                        data-en="Branch Management" data-bm="Pengurusan Cawangan">Branch
                                        Management</a>
                                </li>
                                <li
                                    class="slide {{ $currentRoute === 'internal.announcements.list' ? 'active' : '' }}">
                                    <a href="{{ route('internal.announcements.list') }}" class="side-menu__item"
                                        data-en="Announcements" data-bm="Pengumuman">Announcements</a>
                                </li>
                                <li class="slide {{ $currentRoute === 'internal.galleries.list' ? 'active' : '' }}">
                                    <a href="{{ route('internal.galleries.list') }}" class="side-menu__item"
                                        data-en="Gallery" data-bm="Galeri">Gallery</a>
                                </li>
                            </ul>
                        </li>
                    @endif

                @endif

                {{-- ── Order (hidden for finance & boundary officer) ──────────── --}}
                @if (!($isInternal && $isRestricted))
                    <li class="slide__category"><span class="category-name" data-en="Order"
                            data-bm="Pesanan">Order</span></li>

                    <li class="slide {{ Str::contains($currentRoute, 'order') ? 'open active' : '' }}">
                        <a href="/order/list" class="side-menu__item">
                            <i class="bi bi-card-list side-menu__icon"></i>
                            <span class="side-menu__label" data-en="Order" data-bm="Pesanan">Order</span>
                        </a>
                    </li>
                @endif

            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>

        </nav>
    </div>
</aside>
