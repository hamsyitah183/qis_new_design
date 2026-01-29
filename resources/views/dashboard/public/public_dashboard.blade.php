@extends('pages.app')

@php
    $user = authUser()['user'];
@endphp

@section('breadcrumb')
<x-breadcrumb :items="[['label' => ' ', 'url' => '/']]" title="Welcome  {{ authUser()['user']->fullname }}">

</x-breadcrumb>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12 d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title fw-semibold fs-18 mb-0"></h4>
        <a href="{{ route('profile') }}" class="btn btn-primary btn-wave">
            <i class="ti ti-user me-1"></i> My Profile
        </a>
    </div>
</div>

<div class="row">
    {{-- KPI Cards --}}
    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-primary text-white">
                            <i class="ti ti-file-pencil fs-24"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-13">Draft Applications</p>
                        <h3 class="fw-semibold mb-0">{{ $draftCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-warning text-white">
                            <i class="ti ti-hourglass-low fs-24"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-13">Under Review</p>
                        <h3 class="fw-semibold mb-0">{{ $pendingCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-success text-white">
                            <i class="ti ti-circle-check fs-24"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-13">Verified / Issued</p>
                        <h3 class="fw-semibold mb-0">{{ $verifiedCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-danger text-white">
                            <i class="ti ti-circle-x fs-24"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-13">Rejected</p>
                        <h3 class="fw-semibold mb-0">{{ $rejectedCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-info text-white">
                            <i class="ti ti-wallet fs-24"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-13">Pending Payment</p>
                        <h3 class="fw-semibold mb-0">{{ $pendingPaymentCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Quick Launch & Expiring Items --}}
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Quick Launch</div>
            </div>
            <div class="card-body" id="quick-launch-container">
                {{-- Main Menu --}}
                <div id="main-quick-launch" class="d-grid gap-2">
                    <button type="button" onclick="showCategories('permit')" class="btn btn-primary-transparent btn-wave text-start">
                        <i class="ti ti-plus me-2"></i> New Import Permit
                    </button>
                    <button type="button" onclick="showCategories('inspection')" class="btn btn-info-transparent btn-wave text-start">
                        <i class="ti ti-plus me-2"></i> New Inspection Request
                    </button>
                    <button type="button" onclick="showCategories('consignment')" class="btn btn-warning-transparent btn-wave text-start">
                        <i class="ti ti-plus me-2"></i> New Consignment Certificate
                    </button>
                    <a href="{{ route('application.exporter') }}" class="btn btn-light btn-wave text-start">
                        <i class="ti ti-users me-2"></i> Manage My Exporters
                    </a>
                </div>

                {{-- Import Permit Categories --}}
                <div id="categories-permit" class="d-grid gap-2 d-none">
                    <p class="text-muted fs-12 mb-2">Select category for Import Permit:</p>
                    <a href="{{ route('public.permitApplication') }}" class="btn btn-primary btn-wave text-start">
                        <i class="ti ti-user me-2"></i> Self Import
                    </a>
                    <a href="{{ route('public.permitAssignApplication') }}" class="btn btn-outline-primary btn-wave text-start">
                        <i class="ti ti-users me-2"></i> For Someone Else
                    </a>
                    <button type="button" onclick="showMainMenu()" class="btn btn-light btn-wave mt-2">
                        <i class="ti ti-arrow-left me-1"></i> Back
                    </button>
                </div>

                {{-- Inspection Categories --}}
                <div id="categories-inspection" class="d-grid gap-2 d-none">
                    <p class="text-muted fs-12 mb-2">Select category for Inspection Request:</p>
                    <a href="{{ route('public.inspectionApplicationSelf') }}" class="btn btn-primary btn-wave text-start">
                        <i class="ti ti-user me-2"></i> Self
                    </a>
                    <a href="{{ route('public.inspectionApplicationOthers') }}" class="btn btn-outline-primary btn-wave text-start">
                        <i class="ti ti-users me-2"></i> For Someone Else
                    </a>
                    <button type="button" onclick="showMainMenu()" class="btn btn-light btn-wave mt-2">
                        <i class="ti ti-arrow-left me-1"></i> Back
                    </button>
                </div>

                {{-- Consignment Categories --}}
                <div id="categories-consignment" class="d-grid gap-2 d-none">
                    <p class="text-muted fs-12 mb-2">Select category for Consignment Certificate:</p>
                    <a href="{{ route('public.consignment.app') }}" class="btn btn-primary btn-wave text-start">
                        <i class="ti ti-user me-2"></i> Self
                    </a>
                    <a href="{{ route('public.consignmentOther.app') }}" class="btn btn-outline-primary btn-wave text-start">
                        <i class="ti ti-users me-2"></i> For Someone Else
                    </a>
                    <button type="button" onclick="showMainMenu()" class="btn btn-light btn-wave mt-2">
                        <i class="ti ti-arrow-left me-1"></i> Back
                    </button>
                </div>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Expiring Soon</div>
                <span class="badge bg-danger-transparent">Biosecurity Alert</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted fs-12">No items expiring in the next 10 days</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Recent Applications Queue --}}
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Recent Submissions</div>
                <a href="{{ route('public.showallapplicationlist') }}" class="btn btn-sm btn-primary-transparent">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mt-2">
                        <thead>
                            <tr>
                                <th scope="col">Type</th>
                                <th scope="col">Application ID</th>
                                <th scope="col">Submitted At</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentApplications as $app)
                                <tr>
                                    <td>
                                        @php
                                            $typeBadgeClass = match($app->type) {
                                                'Import Permit' => 'bg-primary',
                                                'Inspection' => 'bg-info',
                                                'Consignment' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $typeBadgeClass }}">{{ $app->type }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $app->application_id }}</span>
                                    </td>
                                    <td>{{ $app->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($app->status) {
                                                'Draft' => 'bg-primary',
                                                'Clerk Review In-Progress', 'Clerk review in-progress' => 'bg-primary',
                                                'Clerk Verified' => 'bg-info',
                                                'Clerk Rejected', 'Rejected' => 'bg-danger',
                                                'Officer Verification Completed' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $app->status }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $viewRoute = match($app->type) {
                                                'Import Permit' => route('viewApplication', $app->application_id),
                                                'Inspection' => route('inspection.view_details', $app->application_id),
                                                'Consignment' => route('consignment.view', $app->application_id),
                                                default => '#'
                                            };
                                        @endphp
                                        <a href="{{ $viewRoute }}" class="btn btn-sm btn-icon btn-primary" title="View Details">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent applications found.</td>
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    {{-- Chart Scripts --}}
    {!! $statusChart->script() !!}

    <script>
        function showCategories(type) {
            // Hide all lists first
            document.getElementById('main-quick-launch').classList.add('d-none');
            document.getElementById('categories-permit').classList.add('d-none');
            document.getElementById('categories-inspection').classList.add('d-none');
            document.getElementById('categories-consignment').classList.add('d-none');

            // Show selected category list
            document.getElementById('categories-' + type).classList.remove('d-none');
        }

        function showMainMenu() {
            // Hide all category lists
            document.getElementById('categories-permit').classList.add('d-none');
            document.getElementById('categories-inspection').classList.add('d-none');
            document.getElementById('categories-consignment').classList.add('d-none');

            // Show main menu
            document.getElementById('main-quick-launch').classList.remove('d-none');
        }
    </script>
@endpush
