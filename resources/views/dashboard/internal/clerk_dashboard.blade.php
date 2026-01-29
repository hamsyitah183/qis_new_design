@php
    $user = authUser()['user'];
@endphp

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
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-primary1 text-white">
                            <i class="ti ti-file-invoice fs-24"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-13">Import Permits</p>
                        <h3 class="fw-semibold mb-0">{{ $pendingPermits ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-primary2 text-white">
                            <i class="ti ti-search fs-24"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-13">Inspections Certificate</p>
                        <h3 class="fw-semibold mb-0">{{ $pendingInspections ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-secondary text-white">
                            <i class="ti ti-box fs-24"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-13">Consignments Certificate</p>
                        <h3 class="fw-semibold mb-0">{{ $pendingConsignments ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="avatar avatar-lg bg-success text-white">
                            <i class="ti ti-check fs-24"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-13">Verified Today</p>
                        <h3 class="fw-semibold mb-0">{{ $verifiedToday ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Review Status Distribution (Donut Chart) --}}
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Review Status Distribution</div>
            </div>
            <div class="card-body">
                {!! $clerkStatusChart->container() !!}
            </div>
        </div>
    </div>

    {{-- Verification Performance (Line Chart) --}}
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Verification Performance</div>
            </div>
            <div class="card-body">
                {!! $clerkWorkloadChart->container() !!}
            </div>
        </div>
    </div>
</div>

{{-- <div class="row">

    <div class="col-xl-12">
        <div class="card custom-card">
          
            <div class="card-body">
                {!! $clerkVolumeChart->container() !!}
            </div>
        </div>
    </div>
</div> --}}

<div class="row">
    {{-- Action Needed Queue --}}
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Action Needed Queue</div>
                <div class="text-muted fs-11">Showing oldest pending applications</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mt-2">
                        <thead>
                            <tr>
                                <th scope="col">Type</th>
                                <th scope="col">Application ID</th>
                                <th scope="col">Submitter</th>
                                <th scope="col">Received Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingQueue as $app)
                                <tr>
                                    <td>
                                        @php
                                            $typeBadgeClass = match($app->type) {
                                                'Import Permit' => 'bg-primary1',
                                                'Inspection' => 'bg-primary2',
                                                'Consignment' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $typeBadgeClass }}">{{ $app->type }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $app->application_id }}</span>
                                    </td>
                                    <td>{{ $app->user->fullname ?? 'N/A' }}</td>
                                    <td>{{ $app->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($app->status) {
                                                'Draft' => 'bg-primary',
                                                'Clerk Review In-Progress', 'Clerk review in-progress' => 'bg-primary',
                                                'Clerk Verified' => 'bg-info',
                                                'Clerk Rejected', 'Rejected' => 'bg-danger',
                                                'Officer Verification Completed' => 'bg-success',
                                                default => 'bg-warning'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $app->status }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $viewUrl = match($app->type) {
                                                'Import Permit' => route('viewApplication', $app->application_id),
                                                'Inspection' => route('inspection.view_details', $app->application_id),
                                                'Consignment' => route('consignment.view', $app->application_id),
                                                default => '#'
                                            };
                                        @endphp
                                        <a href="{{ $viewUrl }}" class="btn btn-sm btn-primary">
                                            <i class="ti ti-eye me-1"></i> View & Verify
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No pending applications found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Daily Application Volume (Optional, based on image 2) --}}
{{-- If needed, another row with the volume chart can be added here --}}

<script src="{{ $clerkStatusChart->cdn() }}"></script>
<script src="{{ $clerkWorkloadChart->cdn() }}"></script>
<script src="{{ $clerkVolumeChart->cdn() }}"></script>

{{ $clerkStatusChart->script() }}
{{ $clerkWorkloadChart->script() }}
{{ $clerkVolumeChart->script() }}
