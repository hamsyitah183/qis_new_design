@extends('pages.app')

@php
    $user = authUser()['user'];
@endphp

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => ' ', 'url' => '/']]" title="Dashboard">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row custom-qis-layout">
        {{-- Main Left Column (Takes up 8 columns on large screens) --}}
        <div class="col-xl-8 col-lg-12">

            {{-- 1. Modern Welcome Hero Banner --}}
            <div class="card qis-radar-card border-0 mb-4 rounded-4 shadow-sm overflow-hidden bg-primary position-relative">
                {{-- Optional: Add a subtle background pattern or image here --}}
                <div class="card-body p-4 p-md-5 text-white position-relative" style="z-index: 1;">
                    <span class="badge bg-white text-primary mb-2 fw-semibold px-3 py-2 rounded-pill"
                        data-en="Sabah Plant Quarantine" data-bm="Kuarantin Tumbuhan Sabah">
                        Sabah Plant Quarantine
                    </span>
                    <h3 class="fw-bold mb-2 fs-24 text-white">
                        <span data-en="Welcome back," data-bm="Selamat kembali,">Welcome back,</span> {{ $user->fullname }}!
                    </h3>
                    <p class="mb-4 fs-14 opacity-75 w-75"
                        data-en="Manage your agricultural shipments, track permits, and ensure biosecurity compliance easily from your dashboard."
                        data-bm="Uruskan penghantaran pertanian, jejak permit, dan pastikan pematuhan biosekuriti dengan mudah dari papan pemuka anda.">
                        Manage your agricultural shipments, track permits, and ensure biosecurity compliance easily from
                        your dashboard.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('profile') }}"
                            class="btn btn-light text-primary btn-wave fw-medium rounded-pill px-4">
                            <i class="ti ti-user me-1"></i> <span data-en="My Profile" data-bm="Profil Saya">My Profile</span>
                        </a>
                    </div>
                </div>
                {{-- Decorative element for the right side of the banner --}}
                <div class="position-absolute top-0 end-0 h-100 w-50"
                    style="background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1)); z-index: 0;">
                    <i class="bx bxs-leaf position-absolute text-white opacity-25"
                        style="font-size: 150px; right: -20px; bottom: -30px; transform: rotate(-15deg);"></i>
                </div>
            </div>

            {{-- 2. Visual Service Cards (Replacing the JS toggle Quick Launch) --}}
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                <h5 class="fw-semibold mb-0" data-en="Quick Services" data-bm="Perkhidmatan Pantas">Quick Services</h5>
            </div>

            <div class="row g-3 mb-4">
                {{-- Import Permit Card --}}
                <div class="col-md-4">
                    <div class="card custom-card border-0 shadow-sm h-100 rounded-4 hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md bg-primary-transparent rounded-circle me-3">
                                    <i class='bx bx-package fs-20 text-primary'></i>
                                </div>
                                <h6 class="fw-semibold mb-0 fs-14" data-en="Import Permit" data-bm="Permit Import">Import
                                    Permit</h6>
                            </div>
                            <p class="text-muted fs-12 mb-3" data-en="Apply to import regulated agricultural goods."
                                data-bm="Mohon untuk mengimport barangan pertanian.">Apply to import regulated agricultural
                                goods.</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('public.permitApplication') }}"
                                    class="btn btn-sm btn-light btn-wave text-start fs-12">
                                    <i class="ti ti-user me-1"></i> <span data-en="Self Import" data-bm="Import Sendiri">Self Import</span>
                                </a>
                                <a href="/public/import_assign_application"
                                    class="btn btn-sm btn-light btn-wave text-start fs-12">
                                    <i class="ti ti-users me-1"></i> <span data-en="For Someone Else" data-bm="Untuk Orang Lain">For Someone Else</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Inspection Request Card --}}
                <div class="col-md-4">
                    <div class="card custom-card border-0 shadow-sm h-100 rounded-4 hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md bg-info-transparent rounded-circle me-3">
                                    <i class='bx bx-search-alt fs-20 text-info'></i>
                                </div>
                                <h6 class="fw-semibold mb-0 fs-14" data-en="Inspection" data-bm="Pemeriksaan">Inspection
                                </h6>
                            </div>
                            <p class="text-muted fs-12 mb-3" data-en="Request biosecurity inspection for goods."
                                data-bm="Mohon pemeriksaan biosekuriti.">Request biosecurity inspection for goods.</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('public.inspectionApplicationSelf') }}"
                                    class="btn btn-sm btn-light btn-wave text-start fs-12">
                                    <i class="ti ti-user me-1"></i> <span data-en="Self Request" data-bm="Permohonan Sendiri">Self Request</span>
                                </a>
                                <a href="{{ route('public.inspectionApplicationOthers') }}"
                                    class="btn btn-sm btn-light btn-wave text-start fs-12">
                                    <i class="ti ti-users me-1"></i> <span data-en="For Someone Else" data-bm="Untuk Orang Lain">For Someone Else</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Consignment Certificate Card --}}
                <div class="col-md-4">
                    <div class="card custom-card border-0 shadow-sm h-100 rounded-4 hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md bg-warning-transparent rounded-circle me-3">
                                    <i class='bx bx-file fs-20 text-warning'></i>
                                </div>
                                <h6 class="fw-semibold mb-0 fs-14" data-en="Consignment" data-bm="Consignment">Consignment
                                </h6>
                            </div>
                            <p class="text-muted fs-12 mb-3" data-en="Export authorization for Brunei borders."
                                data-bm="Kebenaran eksport ke sempadan Brunei.">Export authorization for Brunei borders.</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('public.consignment.app') }}"
                                    class="btn btn-sm btn-light btn-wave text-start fs-12">
                                    <i class="ti ti-user me-1"></i> <span data-en="Self Consignment" data-bm="Konsainan Sendiri">Self Consignment</span>
                                </a>
                                <a href="{{ route('public.consignmentOther.app') }}"
                                    class="btn btn-sm btn-light btn-wave text-start fs-12">
                                    <i class="ti ti-users me-1"></i> <span data-en="For Someone Else" data-bm="Untuk Orang Lain">For Someone Else</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        {{-- Right Sidebar Column (Takes up 4 columns on large screens) --}}
        <div class="col-xl-4 col-lg-12">

            {{-- KPI / Statistics Summary Slider --}}
            <div class="card custom-card border-0 shadow-sm rounded-4 mb-4">
                <div
                    class="card-header bg-transparent border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0" data-en="Overview" data-bm="Gambaran Keseluruhan">Overview</h6>

                    {{-- Custom Carousel Controls --}}
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-icon btn-light rounded-circle shadow-sm" type="button"
                            data-bs-target="#statsCarousel" data-bs-slide="prev">
                            <i class="ti ti-chevron-left"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-light rounded-circle shadow-sm" type="button"
                            data-bs-target="#statsCarousel" data-bs-slide="next">
                            <i class="ti ti-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div id="statsCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner">

                            {{-- Slide 1: Current Live Statistics (Your Original Code) --}}
                            <div class="carousel-item active">
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-3">
                                                <i class="ti ti-hourglass-low text-primary fs-16"></i>
                                            </div>
                                            <span class="fs-13 fw-medium text-muted" data-en="Under Review" data-bm="Dalam Semakan">Under Review</span>
                                        </div>
                                        <span class="fw-bold fs-16">{{ $pendingCount ?? 0 }}</span>
                                    </div>

                                    <div
                                        class="d-flex align-items-center justify-content-between p-3 bg-warning-transparent rounded-3 border border-warning border-opacity-25">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-warning rounded-circle me-3 text-white">
                                                <i class="ti ti-wallet fs-16"></i>
                                            </div>
                                            <span class="fs-13 fw-medium text-warning-emphasis" data-en="Pending Payment" data-bm="Menunggu Pembayaran">Pending Payment</span>
                                        </div>
                                        <span
                                            class="fw-bold fs-16 text-warning-emphasis">{{ $pendingPaymentCount ?? 0 }}</span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-success-transparent rounded-circle me-3">
                                                <i class="ti ti-circle-check text-success fs-16"></i>
                                            </div>
                                            <span class="fs-13 fw-medium text-muted" data-en="Verified / Issued" data-bm="Disahkan / Dikeluarkan">Verified / Issued</span>
                                        </div>
                                        <span class="fw-bold fs-16">{{ $verifiedCount ?? 0 }}</span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-danger-transparent rounded-circle me-3">
                                                <i class="ti ti-circle-x text-danger fs-16"></i>
                                            </div>
                                            <span class="fs-13 fw-medium text-muted" data-en="Rejected" data-bm="Ditolak">Rejected</span>
                                        </div>
                                        <span class="fw-bold fs-16">{{ $rejectedCount ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Slide 2: Bar Chart (Applications by Period) --}}
                            <div class="carousel-item">
                                {{-- Soft inset background to mimic the reference image --}}
                                <div class="p-3 rounded-4"
                                    style="background-color: var(--gray-1); border: 1px solid var(--default-border);">
                                    <h6 class="fs-13 fw-semibold text-center mb-1 text-muted" data-en="Applications Received" data-bm="Permohonan Diterima">Applications Received</h6>
                                    <div id="dummyBarChart" style="min-height: 220px;"></div>
                                </div>
                            </div>

                            {{-- Slide 3: Line Chart (Application Trend) --}}
                            <div class="carousel-item">
                                {{-- Soft inset background to mimic the reference image --}}
                                <div class="p-3 rounded-4"
                                    style="background-color: var(--gray-1); border: 1px solid var(--default-border);">
                                    <h6 class="fs-13 fw-semibold text-center mb-1 text-muted" data-en="Application Trends" data-bm="Trend Permohonan">Application Trends</h6>
                                    <div id="dummyLineChart" style="min-height: 220px;"></div>
                                </div>
                            </div>

                        </div>

                        {{-- Optional: Carousel Dots (Indicators) --}}
                        <div class="carousel-indicators position-relative mt-3 mb-0" style="bottom: 0;">
                            <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="0"
                                class="active bg-primary"></button>
                            <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="1"
                                class="bg-primary"></button>
                            <button type="button" data-bs-target="#statsCarousel" data-bs-slide-to="2"
                                class="bg-primary"></button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Exporter Management Shortcut (Moved from main menu to sidebar) --}}
            <div class="card custom-card border-0 shadow-sm rounded-4 mb-4 bg-dark text-white text-center p-4">
                <div class="mb-3 text-white">
                    <i class="bx bx-buildings fs-1 opacity-50"></i>
                </div>
                <h6 class="fw-semibold mb-2 text-white" data-en="Manage Exporters" data-bm="Urus Pengeksport">Manage Exporters</h6>
                <p class="fs-12 opacity-75 text-white mb-3" data-en="Update and manage your registered exporter profiles for seamless applications." data-bm="Kemaskini dan urus profil pengeksport berdaftar anda untuk kelancaran permohonan.">Update and manage your registered exporter profiles for
                    seamless applications.</p>
                <a href="{{ route('application.exporter') }}" class="btn btn-light btn-sm rounded-pill fw-medium">
                    <span data-en="Manage My Exporters" data-bm="Urus Pengeksport Saya">Manage My Exporters</span> <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>

        </div>

        <div class="col-xl-12">
            {{-- 3. Clean Recent Activity Table --}}
            <div class="card custom-card border-0 shadow-sm rounded-4">
                <div class="card-header justify-content-between bg-transparent border-bottom-0 pt-4 pb-2">
                    <h5 class="card-title fw-semibold mb-0" data-en="Recent Submissions" data-bm="Penyerahan Terkini">
                        Recent Submissions</h5>
                    <a href="{{ route('public.showallapplicationlist') }}"
                        class="btn btn-sm btn-primary-transparent rounded-pill px-3" data-en="View All" data-bm="Lihat Semua">View All</a>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover text-nowrap align-middle">
                            <thead class="bg-light rounded-3 text-muted fs-12 uppercase">
                                <tr>
                                    <th scope="col" class="rounded-start" data-en="Type" data-bm="Jenis">Type</th>
                                    <th scope="col" data-en="Application ID" data-bm="ID Permohonan">Application ID</th>
                                    <th scope="col" data-en="Submitted At" data-bm="Dihantar Pada">Submitted At</th>
                                    <th scope="col" data-en="Status" data-bm="Status">Status</th>
                                    <th scope="col" class="text-end rounded-end" data-en="Action" data-bm="Tindakan">Action</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse ($recentApplications as $app)
                                    <tr>
                                        <td>
                                            @php
                                                $typeDetails = match ($app->type) {
                                                    'Import Permit' => [
                                                        'class' => 'bg-primary-transparent text-primary',
                                                        'icon' => 'bx-package',
                                                    ],
                                                    'Inspection' => [
                                                        'class' => 'bg-info-transparent text-info',
                                                        'icon' => 'bx-search-alt',
                                                    ],
                                                    'Consignment' => [
                                                        'class' => 'bg-warning-transparent text-warning',
                                                        'icon' => 'bx-file',
                                                    ],
                                                    default => [
                                                        'class' => 'bg-secondary-transparent text-secondary',
                                                        'icon' => 'bx-layer',
                                                    ],
                                                };
                                            @endphp
                                            <span
                                                class="badge {{ $typeDetails['class'] }} d-flex align-items-center w-max-content px-2 py-1">
                                                <i class="bx {{ $typeDetails['icon'] }} me-1 fs-14"></i>
                                                <span data-en="{{ $app->type }}" 
                                                      data-bm="{{ $app->type == 'Import Permit' ? 'Permit Import' : ($app->type == 'Inspection' ? 'Pemeriksaan' : ($app->type == 'Consignment' ? 'Konsainan' : $app->type)) }}">
                                                    {{ $app->type }}
                                                </span>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold fs-13">{{ $app->application_id }}</span>
                                        </td>
                                        <td class="text-muted fs-13">{{ $app->created_at->format('d M Y, h:i A') }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match ($app->status) {
                                                    'Draft' => 'bg-light text-dark border',
                                                    'Clerk Review In-Progress',
                                                    'Clerk review in-progress'
                                                        => 'bg-primary-transparent text-primary',
                                                    'Clerk Verified' => 'bg-info-transparent text-info',
                                                    'Clerk Rejected', 'Rejected' => 'bg-danger-transparent text-danger',
                                                    'Officer Verification Completed'
                                                        => 'bg-success-transparent text-success',
                                                    default => 'bg-secondary-transparent text-secondary',
                                                };
                                            @endphp
                                            <span
                                                class="badge {{ $badgeClass }} rounded-pill px-2"
                                                data-en="{{ $app->status }}"
                                                data-bm="{{ match ($app->status) {
                                                    'Draft' => 'Draf',
                                                    'Clerk Review In-Progress', 'Clerk review in-progress' => 'Dalam Semakan Kerani',
                                                    'Clerk Verified' => 'Kerani Disahkan',
                                                    'Clerk Rejected' => 'Ditolak Kerani',
                                                    'Rejected' => 'Ditolak',
                                                    'Officer Verification Completed' => 'Pengesahan Pegawai Selesai',
                                                    default => $app->status
                                                } }}"
                                                >{{ $app->status }}</span>
                                        </td>
                                        <td class="text-end">
                                            @php
                                                $viewRoute = match ($app->type) {
                                                    'Import Permit' => route('viewApplication', $app->application_id),
                                                    'Inspection' => route(
                                                        'inspection.view_details',
                                                        $app->application_id,
                                                    ),
                                                    'Consignment' => route('consignment.view', $app->application_id),
                                                    default => '#',
                                                };
                                            @endphp
                                            <a href="{{ $viewRoute }}"
                                                class="btn btn-sm btn-icon btn-light rounded-circle shadow-sm"
                                                title="View Details">
                                                <i class="ti ti-arrow-right text-muted"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <div class="mb-2"><i class="bx bx-folder-open fs-1"
                                                    style="opacity: 0.2;"></i></div>
                                            <span data-en="No recent applications found. Start by requesting a new service above." data-bm="Tiada permohonan terkini ditemui. Mula dengan memohon perkhidmatan baharu di atas.">No recent applications found. Start by requesting a new service above.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- ApexCharts Library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/5.3.5/apexcharts.min.js"></script>
    {{-- Chart Scripts --}}
    {!! $statusChart->script() !!}

    {{-- Optional styling to add to your app.css for the hover lift effect --}}
    <style>
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }

        .w-max-content {
            width: max-content;
        }
    </style>
@endpush

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    
    // --- Common Chart Settings for clean UI (Mimicking image_3f59e9.png) ---
    const commonOptions = {
        chart: {
            toolbar: { show: false },
            parentHeightOffset: 0,
            sparkline: { enabled: false }
        },
        grid: {
            show: true,
            borderColor: 'var(--default-border)',
            strokeDashArray: 4, // Dashed lines like the reference image
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
            padding: { top: 0, right: 0, bottom: 0, left: 10 }
        },
        dataLabels: { enabled: false },
        tooltip: { theme: document.documentElement.getAttribute('data-theme-mode') === 'dark' ? 'dark' : 'light' },
        colors: ['rgb(45, 143, 79)'], // Using your --primary-rgb
    };

    // --- Slide 2: Bar Chart ---
    const barOptions = {
        ...commonOptions,
        series: [{
            name: 'Applications',
            data: [35, 48, 32, 60, 30] // Dummy Data
        }],
        chart: {
            type: 'bar',
            height: 220,
            fontFamily: 'var(--default-font-family)'
        },
        plotOptions: {
            bar: {
                borderRadius: 6, // Rounded top corners like reference image
                borderRadiusApplication: 'end',
                columnWidth: '45%',
                // Making some bars lighter to mimic the reference image's focus effect
                colors: {
                    ranges: [{
                        from: 0,
                        to: 40,
                        color: 'rgba(45, 143, 79, 0.3)' // Light green for lower values
                    }, {
                        from: 41,
                        to: 100,
                        color: 'rgb(45, 143, 79)' // Solid green for high values
                    }]
                }
            }
        },
        xaxis: {
            categories: ['1-10 Aug', '11-20 Aug', '21-30 Aug', '1-10 Sep', '11-20 Sep'],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: { colors: 'var(--text-muted)', fontSize: '11px' }
            }
        },
        yaxis: {
            tickAmount: 3,
            labels: {
                style: { colors: 'var(--text-muted)', fontSize: '11px' }
            }
        }
    };
    new ApexCharts(document.querySelector("#dummyBarChart"), barOptions).render();


    // --- Slide 3: Line Chart ---
    const lineOptions = {
        ...commonOptions,
        series: [{
            name: 'Submissions',
            data: [15, 30, 22, 45, 38, 55, 48] // Dummy Data
        }],
        chart: {
            type: 'area', // Area chart looks softer and more modern
            height: 220,
            fontFamily: 'var(--default-font-family)',
            dropShadow: {
                enabled: true,
                color: 'rgb(45, 143, 79)',
                top: 10,
                blur: 4,
                opacity: 0.1
            }
        },
        stroke: {
            curve: 'smooth', // Soft curved lines
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: { colors: 'var(--text-muted)', fontSize: '11px' }
            }
        },
        yaxis: {
            tickAmount: 3,
            labels: {
                style: { colors: 'var(--text-muted)', fontSize: '11px' }
            }
        }
    };
    new ApexCharts(document.querySelector("#dummyLineChart"), lineOptions).render();

});
</script>
@endpush