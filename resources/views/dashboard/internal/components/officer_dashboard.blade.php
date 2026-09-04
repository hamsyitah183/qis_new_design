
<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-xxl-4 col-md-6">
                <div class="card custom-card overflow-hidden h-100 w-100">
                    <div class="card-body">
                        {{-- <div class="mb-3 d-flex align-items-start justify-content-between">
                            <span class="avatar avatar-lg bg-primary1 svg-white">
                                <i class="ri-check-double-line fs-24"></i>
                            </span>
                          
                        </div>
                        <div class="d-flex align-items-end justify-content-between flex-wrap">
                            <div class="flex-shrink-0">
                                <div class="text-muted mb-1">Import Permit</div>
                                <h4 class="mb-0 fs-20 fw-medium">122</h4>
                            </div>
                           
                        </div> --}}

                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-primary1 svg-white">
                                    <i class="ti ti-file-invoice fs-24"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13" data-bm="Jumlah Permit Import" data-en="Total Import Permit">Total Import Permit</p>
                                <h3 class="fw-semibold mb-0" id="ipCount">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-md-6">
                <div class="card custom-card overflow-hidden h-100 w-100">
                    <div class="card-body">
                        {{-- <div class="mb-3 d-flex align-items-start justify-content-between">
                            <span class="avatar avatar-lg bg-primary2 svg-white">
                                <i class="ri-check-double-line fs-24"></i>
                            </span>
                          
                        </div>
                        <div class="d-flex align-items-end justify-content-between flex-wrap">
                            <div class="flex-shrink-0">
                                <div class="text-muted mb-1">Inspection Certificate</div>
                                <h4 class="mb-0 fs-20 fw-medium">122</h4>
                            </div>
                           
                        </div> --}}
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-primary2 svg-white">
                                    <i class="ti ti-search fs-24"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13" data-bm="Jumlah Sijil Pemeriksaan" data-en="Total Inspection Certificate Permit">Total Inspection Certificate Permit</p>
                                <h3 class="fw-semibold mb-0" id="icCount">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-md-6">
                <div class="card custom-card overflow-hidden h-100 w-100">
                    <div class="card-body">
                        {{-- <div class="mb-3 d-flex align-items-start justify-content-between">
                            <span class="avatar avatar-lg bg-secondary svg-white">
                                <i class="ri-check-double-line fs-24"></i>
                            </span>
        
                        </div>
                        <div class="d-flex align-items-end justify-content-between flex-wrap">
                            <div class="flex-shrink-0">
                                <div class="text-muted mb-1">Consignment Certificate</div>
                                <h4 class="mb-0 fs-20 fw-medium">122</h4>
                            </div>
        
                        </div> --}}
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-secondary svg-white">
                                    <i class="ti ti-box fs-24"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13" data-bm="Jumlah Sijil Konsainan" data-en="Total Consignment Certificate Permit">Total Consignment Certificate Permit</p>
                                <h3 class="fw-semibold mb-0" id="ccCount">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           

        </div>
        <div class="row mt-4">
            {{-- <div class="col-lg-8">
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
            </div> --}}
            <div class="col-lg-12">
                @include('dashboard.internal.components.officer_daily_chart')

            </div>
        </div>
        <div class="row mt-2">
            {{-- Action Needed Queue --}}
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title" data-bm="Barisan Tindakan Diperlukan" data-en="Action Needed Queue">Action Needed Queue</div>
                        <div class="text-muted fs-11" data-bm="Menunjukkan permohonan tertangguh yang terawal" data-en="Showing oldest pending applications">Showing oldest pending applications</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap mt-2">
                                <thead>
                                    <tr>
                                        <th scope="col" data-bm="Jenis" data-en="Type">Type</th>
                                        <th scope="col" data-bm="ID Permohonan" data-en="Application ID">Application ID</th>
                                        <th scope="col" data-bm="Pemohon" data-en="Submitter">Submitter</th>
                                        <th scope="col" data-bm="Tarikh Diterima" data-en="Received Date">Received Date</th>
                                        <th scope="col" data-bm="Status" data-en="Status">Status</th>
                                        <th scope="col" data-bm="Tindakan" data-en="Action">Action</th>
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
                                                        'Officer Verification Completed', 'Completed' => 'bg-success',
                                                        default => 'bg-warning'
                                                    };
                                                    $bmStatus = match($app->status) {
                                                        'Completed' => 'Selesai',
                                                        'Approved' => 'Diluluskan',
                                                        'Draft' => 'Draf',
                                                        'Clerk Review In-Progress', 'Clerk review in-progress' => 'Semakan Kerani Dalam Proses',
                                                        'Clerk Verified' => 'Disahkan Kerani',
                                                        'Clerk Rejected' => 'Ditolak Kerani',
                                                        'Rejected' => 'Ditolak',
                                                        'Officer Verification Completed' => 'Pengesahan Pegawai Selesai',
                                                        'Pending' => 'Menunggu',
                                                        default => $app->status
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}" data-en="{{ $app->status }}" data-bm="{{ $bmStatus }}">{{ $app->status }}</span>
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
                                                    <i class="ti ti-eye me-1"></i> <span data-bm="Lihat & Sahkan" data-en="View & Verify">View & Verify</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center" data-bm="Tiada permohonan tertangguh dijumpai." data-en="No pending applications found.">No pending applications found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
