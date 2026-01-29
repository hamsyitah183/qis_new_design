<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card overflow-hidden main-content-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <span class="text-muted d-block mb-1">Total Import Permit</span>
                                <h4 class="fw-medium mb-0">{{ $totalImportPermits ?? 0 }}</h4>
                            </div>
                            <div class="lh-1">
                                <span class="avatar avatar-md avatar-rounded bg-primary">
                                    <i class="ti ti-file-import fs-5"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card overflow-hidden main-content-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <span class="text-muted d-block mb-1">Total Inspection Cert</span>
                                <h4 class="fw-medium mb-0">{{ $totalInspectionCerts ?? 0 }}</h4>
                            </div>
                            <div class="lh-1">
                                <span class="avatar avatar-md avatar-rounded bg-secondary">
                                    <i class="ti ti-file-certificate fs-5"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card overflow-hidden main-content-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <span class="text-muted d-block mb-1">Total Consignment Cert</span>
                                <h4 class="fw-medium mb-0">{{ $totalConsignmentCerts ?? 0 }}</h4>
                            </div>
                            <div class="lh-1">
                                <span class="avatar avatar-md avatar-rounded bg-info">
                                    <i class="ti ti-file-text fs-5"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card overflow-hidden main-content-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <span class="text-muted d-block mb-1">Total Accepted</span>
                                <h4 class="fw-medium mb-0">{{ $totalAccepted ?? 0 }}</h4>
                            </div>
                            <div class="lh-1">
                                <span class="avatar avatar-md avatar-rounded bg-success">
                                    <i class="ti ti-check-circle fs-5"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <div class="card custom-card overflow-hidden">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            Latest Applications
                        </div>
                    </div>
                    <div class="card-body p-0 pb-1">
                        <div class="table-responsive">
                            <table class="table text-nowrap table-compact">
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
                                                <div class="fw-medium">{{ $application['user_name'] }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($application['type'] == 'Import Permit')
                                                        <span
                                                            class="avatar avatar-sm avatar-rounded bg-primary-transparent">
                                                            <i class="ti ti-file-import fs-16"></i>
                                                        </span>
                                                    @elseif($application['type'] == 'Inspection Certificate')
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
                                                    <div class="fw-medium">{{ $application['type'] }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match ($application['status']) {
                                                        'Approved' => 'bg-success',
                                                        'Pending' => 'bg-warning',
                                                        'Draft' => 'bg-secondary',
                                                        'Rejected' => 'bg-danger',
                                                        'Submitted' => 'bg-info',
                                                        default => 'bg-primary',
                                                    };
                                                @endphp
                                                <span
                                                    class="badge {{ $statusClass }}">{{ $application['status'] }}</span>
                                            </td>
                                            <td>
                                                @if ($application['type'] == 'Import Permit')
                                                    <a href="{{ route('viewApplication', $application['application_id']) }}"
                                                        class="btn btn-sm btn-primary-light">
                                                        <i class="ti ti-eye"></i> View
                                                    </a>
                                                @elseif($application['type'] == 'Inspection Certificate')
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
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="ti ti-inbox fs-24 mb-2"></i>
                                                <p class="mb-0">No applications found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
           @include('dashboard.internal.components.recent_activity')
            </div>
        </div>
