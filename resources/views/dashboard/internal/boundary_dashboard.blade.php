<div class="row">
    <div class="col-12">
        <div class="row">
            {{-- Counts can be kept or removed nicely. I'll keep them as they are useful summary metrics. --}}
            <div class="col-xxl-4 col-md-6">
                <div class="card custom-card overflow-hidden h-100 w-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-primary1 svg-white">
                                    <i class="ti ti-file-invoice fs-24"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Total Import Permit</p>
                                <h3 class="fw-semibold mb-0">{{ $totalImportPermits ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-md-6">
                <div class="card custom-card overflow-hidden h-100 w-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-primary2 svg-white">
                                    <i class="ti ti-search fs-24"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Total Inspection Certificate</p>
                                <h3 class="fw-semibold mb-0">{{ $totalInspectionCerts ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-md-6">
                <div class="card custom-card overflow-hidden h-100 w-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-secondary svg-white">
                                    <i class="ti ti-box fs-24"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Total Consignment Certificate</p>
                                <h3 class="fw-semibold mb-0">{{ $totalConsignmentCerts ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

<div class="card custom-card overflow-hidden">
    <div class="card-header justify-content-between">
        <div class="card-title">
            Recent Applications
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="boundaryApplicationsTable" class="table text-nowrap table-compact">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 25%;">
                    <col style="width: 15%;">
                    <col style="width: 10%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Application Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestApplications ?? [] as $application)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $application['user']['fullname']  ?? $application['user']['name']}}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($application['application_type'] == 'Import Permit')
                                        <span
                                            class="avatar avatar-sm avatar-rounded bg-primary-transparent">
                                            <i class="ti ti-file-import fs-16"></i>
                                        </span>
                                    @elseif($application['application_type'] == 'Inspection Certificate')
                                        <span
                                            class="avatar avatar-sm avatar-rounded bg-secondary-transparent">
                                            <i class="ti ti-file-certificate fs-16"></i>
                                        </span>
                                    @else
                                        <span
                                            class="avatar avatar-sm avatar-rounded bg-info-transparent">
                                            <i class="ti ti-file-text fs-16"></i>
                                        </span>
                                    @endif
                                    <div class="fw-medium">{{ $application['application_type'] }}</div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($application['status']) {
                                        'Approved' => 'bg-success',
                                        'Pending' => 'bg-warning',
                                        'Draft' => 'bg-secondary',
                                        'Rejected', 'Clerk Rejected' => 'bg-danger',
                                        'Submitted', 'Clerk Verified' => 'bg-info',
                                        default => 'bg-primary',
                                    };
                                @endphp
                                <span
                                    class="badge {{ $statusClass }}">{{ $application['status'] }}</span>
                            </td>
                            <td>
                                @if ($application['application_type'] == 'Import Permit')
                                    <a href="{{ route('viewApplication', $application['application_id']) }}"
                                        class="btn btn-sm btn-primary-light">
                                        <i class="ti ti-eye"></i> View
                                    </a>
                                @elseif($application['application_type'] == 'Inspection Certificate')
                                    <a href="{{ route('inspection.view_details', $application['application_id']) }}"
                                        class="btn btn-sm btn-primary-light">
                                        <i class="ti ti-eye"></i> View
                                    </a>
                                @else
                                    <a href="{{ url('/view_consignment/' . $application['application_id']) }}"
                                        class="btn btn-sm btn-primary-light">
                                        <i class="ti ti-eye"></i> View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        {{-- Empty state handled by DataTable usually, but keeping it for initial render doesn't hurt --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/pages/boundary_dashboard.js'])
@endpush
            </div>
        </div>
    </div>
</div>
