<div class="row">
    <div class="col-12">
        <div class="row">
            {{-- Summary Count Cards --}}
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
                                <p class="text-muted mb-1 fs-13" data-bm="Jumlah Permit Import" data-en="Total Import Permit">Total Import Permit</p>
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
                                <p class="text-muted mb-1 fs-13" data-bm="Jumlah Sijil Pemeriksaan" data-en="Total Inspection Certificate">Total Inspection Certificate</p>
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
                                <p class="text-muted mb-1 fs-13" data-bm="Jumlah Sijil Konsainan" data-en="Total Consignment Certificate">Total Consignment Certificate</p>
                                <h3 class="fw-semibold mb-0">{{ $totalConsignmentCerts ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Import Permit Table --}}
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card custom-card overflow-hidden">
                    <div class="card-header justify-content-between">
                        <div class="card-title d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm avatar-rounded bg-primary-transparent">
                                <i class="ti ti-file-import fs-16"></i>
                            </span>
                            <span data-bm="Permohonan Permit Import" data-en="Import Permit Applications">Import Permit Applications</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="importPermitTable" class="table text-nowrap table-compact">
                                <thead>
                                    <tr>
                                        <th data-bm="ID Permohonan" data-en="Application ID">Application ID</th>
                                        <th data-bm="Nama Pengguna" data-en="User Name">User Name</th>
                                        <th data-bm="Status" data-en="Status">Status</th>
                                        <th data-bm="Tindakan" data-en="Action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($importPermits ?? [] as $application)
                                        <tr>
                                            <td>{{ $application->application_id ?? '-' }}</td>
                                            <td>
                                                <div class="fw-medium">{{ $application->user->fullname ?? '-' }}</div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match ($application->status ?? 'Pending') {
                                                        'Approved', 'Completed' => 'bg-success',
                                                        'Pending' => 'bg-warning',
                                                        'Draft' => 'bg-secondary',
                                                        'Rejected', 'Clerk Rejected' => 'bg-danger',
                                                        'Submitted', 'Clerk Verified' => 'bg-info',
                                                        default => 'bg-primary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $application->status }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('viewApplication', $application->application_id) }}"
                                                    class="btn btn-sm btn-primary-light">
                                                    <i class="ti ti-eye"></i> <span data-bm="Lihat" data-en="View">View</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Inspection Certificate Table --}}
        <div class="row mt-2">
            <div class="col-lg-12">
                <div class="card custom-card overflow-hidden">
                    <div class="card-header justify-content-between">
                        <div class="card-title d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm avatar-rounded bg-secondary-transparent">
                                <i class="ti ti-file-certificate fs-16"></i>
                            </span>
                            <span data-bm="Permohonan Sijil Pemeriksaan" data-en="Inspection Certificate Applications">Inspection Certificate Applications</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="inspectionCertTable" class="table text-nowrap table-compact">
                                <thead>
                                    <tr>
                                        <th data-bm="ID Permohonan" data-en="Application ID">Application ID</th>
                                        <th data-bm="Nama Pengguna" data-en="User Name">User Name</th>
                                        <th data-bm="Status" data-en="Status">Status</th>
                                        <th data-bm="Tindakan" data-en="Action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inspectionCerts ?? [] as $application)
                                        <tr>
                                            <td>{{ $application->application_id ?? '-' }}</td>
                                            <td>
                                                <div class="fw-medium">{{ $application->user->fullname ?? '-' }}</div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match ($application->status ?? 'Pending') {
                                                        'Approved', 'Completed' => 'bg-success',
                                                        'Pending' => 'bg-warning',
                                                        'Draft' => 'bg-secondary',
                                                        'Rejected', 'Clerk Rejected' => 'bg-danger',
                                                        'Submitted', 'Clerk Verified' => 'bg-info',
                                                        default => 'bg-primary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $application->status }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('inspection.view_details', $application->application_id) }}"
                                                    class="btn btn-sm btn-primary-light">
                                                    <i class="ti ti-eye"></i> <span data-bm="Lihat" data-en="View">View</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Consignment Certificate Table --}}
        <div class="row mt-2">
            <div class="col-lg-12">
                <div class="card custom-card overflow-hidden">
                    <div class="card-header justify-content-between">
                        <div class="card-title d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm avatar-rounded bg-info-transparent">
                                <i class="ti ti-file-text fs-16"></i>
                            </span>
                            <span data-bm="Permohonan Sijil Konsainan" data-en="Consignment Certificate Applications">Consignment Certificate Applications</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="consignmentCertTable" class="table text-nowrap table-compact">
                                <thead>
                                    <tr>
                                        <th data-bm="ID Permohonan" data-en="Application ID">Application ID</th>
                                        <th data-bm="Nama Pengguna" data-en="User Name">User Name</th>
                                        <th data-bm="Status" data-en="Status">Status</th>
                                        <th data-bm="Tindakan" data-en="Action">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($consignmentCerts ?? [] as $application)
                                        <tr>
                                            <td>{{ $application->application_id ?? '-' }}</td>
                                            <td>
                                                <div class="fw-medium">{{ $application->user->fullname ?? '-' }}</div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match ($application->status ?? 'Pending') {
                                                        'Approved', 'Completed' => 'bg-success',
                                                        'Pending' => 'bg-warning',
                                                        'Draft' => 'bg-secondary',
                                                        'Rejected', 'Clerk Rejected' => 'bg-danger',
                                                        'Submitted', 'Clerk Verified' => 'bg-info',
                                                        default => 'bg-primary',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $application->status }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ url('/view_consignment/' . $application->application_id) }}"
                                                    class="btn btn-sm btn-primary-light">
                                                    <i class="ti ti-eye"></i> <span data-bm="Lihat" data-en="View">View</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
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
