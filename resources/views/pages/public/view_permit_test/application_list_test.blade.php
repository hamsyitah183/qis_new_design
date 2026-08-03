@extends('pages.app')

@section('pageName', 'View Import Permit')

@push('scripts')
    @vite(['resources/js/pages/importPermit/applicationList.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('Dashboard'), 'url' => '/'],
        ['label' => __('Application List'), 'url' => '/public/view_import_permit'],
        ['label' => __('New Application'), 'url' => '#'],
    ]" :title="__('Apply Import Permit')">
    </x-breadcrumb>
@endsection

@section('content')

    <div class="container-fluid ipv-list-wrapper">

        {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

            <div>
                <h3 class="fw-bold mb-1" data-en="Import Permit Applications" data-bm="Permohonan Permit Import">
                    Import Permit Applications
                </h3>

                <div class="text-muted" data-en="View, monitor and manage all import permit applications." 
                     data-bm="Lihat, pantau dan urus semua permohonan permit import.">
                    View, monitor and manage all import permit applications.
                </div>
            </div>

            <button class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i>
                <span data-en="New Application" data-bm="Permohonan Baru">New Application</span>
            </button>

        </div>


        {{-- =========================================================
        SUMMARY
    ========================================================== --}}

        <div class="row g-3 mb-4">

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small data-en="Total Applications" data-bm="Jumlah Permohonan">Total Applications</small>
                    <h3 id="summaryTotal">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small data-en="Submitted" data-bm="Dihantar">Submitted</small>
                    <h3 id="summarySubmitted">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small data-en="Technical Review" data-bm="Penilaian Pegawai">Technical Review</small>
                    <h3 id="summaryTechnical">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small data-en="Awaiting Payment" data-bm="Menunggu Pembayaran">Awaiting Payment</small>
                    <h3 id="summaryPayment">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small data-en="Completed" data-bm="Selesai">Completed</small>
                    <h3 id="summaryCompleted">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small data-en="Returned" data-bm="Dikembalikan">Returned</small>
                    <h3 id="summaryReturned">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small data-en="Document Verification" data-bm="Semakan Dokumen">Document Verification</small>
                    <h3 id="summaryDocVerify">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small data-en="Payment Processing" data-bm="Proses Pengesahan Bayaran">Payment Processing</small>
                    <h3 id="summaryPayProc">0</h3>
                </div>
            </div>

        </div>


        {{-- =========================================================
        CONTENT
    ========================================================== --}}

        <div class="ipv-list-layout">

            {{-- =========================================
            FILTER
        ========================================== --}}

            <aside class="ipv-filter-panel" id="filterPanel">

                <div class="ipv-filter-header">

                    <h5>
                        <i class="bi bi-sliders me-2"></i>
                        <span data-en="Filter" data-bm="Penapis">Filter</span>
                    </h5>

                    <button class="btn-close" id="closeFilter">
                    </button>

                </div>

                <div class="ipv-filter-body">

                    <div class="mb-4">

                        <label class="form-label fw-semibold" data-en="Status" data-bm="Status">
                            Status
                        </label>

                        <div class="d-grid gap-2">

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Submitted" type="checkbox">
                                <label class="form-check-label" data-en="Submitted" data-bm="Dihantar">Submitted</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Document Verification" type="checkbox">
                                <label class="form-check-label" data-en="Document Verification" data-bm="Semakan Dokumen">Document Verification</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Technical Review" type="checkbox">
                                <label class="form-check-label" data-en="Technical Review" data-bm="Penilaian Pegawai">Technical Review</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Awaiting Payment" type="checkbox">
                                <label class="form-check-label" data-en="Awaiting Payment" data-bm="Menunggu Pembayaran">Awaiting Payment</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Payment Processing" type="checkbox">
                                <label class="form-check-label" data-en="Payment Processing" data-bm="Proses Pengesahan Bayaran">Payment Processing</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Completed" type="checkbox">
                                <label class="form-check-label" data-en="Completed" data-bm="Selesai">Completed</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Returned" type="checkbox">
                                <label class="form-check-label" data-en="Returned" data-bm="Dikembalikan">Returned</label>
                            </div>

                        </div>

                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" data-en="Transport" data-bm="Pengangkutan">Transport</label>
                        <select class="form-select ipv-multi-select" id="filterTransport" multiple>
                            <option value="Air" data-en="Air" data-bm="Udara">Air</option>
                            <option value="Sea" data-en="Sea" data-bm="Laut">Sea</option>
                            <option value="Land" data-en="Land" data-bm="Darat">Land</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" data-en="Entry Point" data-bm="Pintu Masuk">Entry Point</label>
                        <select class="form-select ipv-multi-select" id="filterEntryPoint" multiple>
                            <option value="Kota Kinabalu" data-en="Kota Kinabalu" data-bm="Kota Kinabalu">Kota Kinabalu</option>
                            <option value="Tawau" data-en="Tawau" data-bm="Tawau">Tawau</option>
                            <option value="Sandakan" data-en="Sandakan" data-bm="Sandakan">Sandakan</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" data-en="Submission Date" data-bm="Tarikh Penyerahan">Submission Date</label>
                        <div class="d-flex flex-column gap-2">
                            <div>
                                <label class="ipv-date-sublabel" data-en="From" data-bm="Dari">From</label>
                                <input type="date" class="form-control" id="filterDateFrom">
                            </div>
                            <div>
                                <label class="ipv-date-sublabel" data-en="To" data-bm="Hingga">To</label>
                                <input type="date" class="form-control" id="filterDateTo">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" id="applyFilterBtn">
                            <span data-en="Apply Filter" data-bm="Guna Penapis">Apply Filter</span>
                        </button>
                        <button class="btn btn-light border" id="clearFilterBtn">
                            <span data-en="Clear" data-bm="Kosongkan">Clear</span>
                        </button>
                    </div>

                </div>

            </aside>


            {{-- =========================================
            TABLE
        ========================================== --}}

            <div class="ipv-table-container">

                <div class="ipv-toolbar">

                    <div class="d-flex align-items-center gap-2">

                        <button class="btn btn-light border" id="openFilter">
                            <i class="bi bi-funnel"></i>
                            <span data-en="Filter" data-bm="Penapis">Filter</span>
                        </button>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchApplication"
                                placeholder="Search application..." 
                                data-en="Search application..." 
                                data-bm="Cari permohonan..." 
                                data-i18n-attr="placeholder">
                        </div>

                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <label class="ipv-sort-label" for="sortSelect">
                            <i class="bi bi-arrow-down-up"></i> 
                            <span data-en="Sort by" data-bm="Susun mengikut">Sort by</span>
                        </label>
                        <select class="form-select ipv-sort-select" id="sortSelect">
                            <option value="created_desc" data-en="Date Created (Newest)" data-bm="Tarikh Dicipta (Terbaru)">Date Created (Newest)</option>
                            <option value="created_asc" data-en="Date Created (Oldest)" data-bm="Tarikh Dicipta (Tertua)">Date Created (Oldest)</option>
                            <option value="eta_asc" data-en="ETA (Earliest)" data-bm="ETA (Terawal)">ETA (Earliest)</option>
                            <option value="eta_desc" data-en="ETA (Latest)" data-bm="ETA (Terakhir)">ETA (Latest)</option>
                            <option value="value_desc" data-en="Value (Highest)" data-bm="Nilai (Tertinggi)">Value (Highest)</option>
                            <option value="value_asc" data-en="Value (Lowest)" data-bm="Nilai (Terendah)">Value (Lowest)</option>
                            <option value="permits_desc" data-en="Permits (Most)" data-bm="Permit (Terbanyak)">Permits (Most)</option>
                            <option value="permits_asc" data-en="Permits (Fewest)" data-bm="Permit (Tersedikit)">Permits (Fewest)</option>
                        </select>
                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle ipv-table">

                        <thead>

                            <tr>
                                <th data-en="Application" data-bm="Permohonan">Application</th>
                                <th data-en="Applicant" data-bm="Pemohon">Applicant</th>
                                <th data-en="Importer" data-bm="Pengimport">Importer</th>
                                <th data-en="ETA" data-bm="ETA">ETA</th>
                                <th data-en="Transport" data-bm="Pengangkutan">Transport</th>
                                <th data-en="Permits" data-bm="Permit">Permits</th>
                                <th data-en="Value" data-bm="Nilai">Value</th>
                                <th data-en="Status" data-bm="Status">Status</th>
                                <th width="170" data-en="Action" data-bm="Tindakan">Action</th>
                            </tr>

                        </thead>

                        <tbody id="applicationTableBody">

                            {{-- JS --}}

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>


    {{-- ===========================================================
OFFCANVAS
=========================================================== --}}

    <div class="offcanvas offcanvas-end" tabindex="-1" id="applicationPreview">

        <div class="offcanvas-header">

            <div>

                <h5 class="mb-1" data-en="Application Preview" data-bm="Pratonton Permohonan">
                    Application Preview
                </h5>

                <small class="text-muted" id="previewApplicationID">
                </small>

            </div>

            <div class="d-flex align-items-center gap-2">

                <a href="/view_import/test" class="btn btn-light">

                    <i class="bi bi-arrow-up-right-square"></i>

                </a>

                <button class="btn-close" data-bs-dismiss="offcanvas">
                </button>

            </div>

        </div>

        <div class="offcanvas-body">

            <div class="col-xl-12">
                <div class="ipv-main-card">

                    <!-- Status header -->
                    <div class="ipv-status-header">
                        <div>
                            <span class="ipv-status-eyebrow" data-en="Application Type:" data-bm="Jenis Permohonan:">Application Type:</span>
                            <strong data-en="Import Permit" data-bm="Permit Import">Import Permit</strong>
                            <span class="ipv-status-sep">|</span>
                            <span class="ipv-status-eyebrow" data-en="Status:" data-bm="Status:">Status:</span>
                            <strong id="ipvStatusLabel">—</strong>
                        </div>
                        <div class="ipv-status-duration" id="ipvStatusDuration"></div>
                    </div>

                    <!-- Stage stepper -->
                    <div class="ipv-stage-stepper" id="ipvStageStepper"></div>
                    <div class="ipv-returned-note d-none" id="ipvReturnedNote"></div>

                    <!-- Officer / SLA row -->
                    <div class="ipv-info-row">
                        <div class="ipv-info-item">
                            <div class="ipv-info-icon"><i class="bi bi-person-badge"></i></div>
                            <div>
                                <div class="ipv-info-label" data-en="Assigned Officer" data-bm="Pegawai Bertugas">Assigned Officer</div>
                                <div class="ipv-info-value" id="ipvAssignedOfficer">—</div>
                            </div>
                            <button type="button" class="ipv-info-link" data-en="Reassign" data-bm="Tukar">Reassign</button>
                        </div>
                        <div class="ipv-info-item">
                            <div class="ipv-info-icon"><i class="bi bi-hourglass-split"></i></div>
                            <div>
                                <div class="ipv-info-label" data-en="Next Action / SLA" data-bm="Tindakan Seterusnya / SLA">Next Action / SLA</div>
                                <div class="ipv-info-value" id="ipvSlaDue">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="ipv-tabnav" role="tablist">
                        <button type="button" class="ipv-tabnav-item is-active" data-ipv-tab="permits" role="tab">
                            <span data-en="Permit List" data-bm="Senarai Permit">Permit List</span>
                            <span class="ipv-tab-count" id="ipvPermitCount">0</span>
                        </button>
                        <button type="button" class="ipv-tabnav-item" data-ipv-tab="transport" role="tab">
                            <span data-en="Transportation Details" data-bm="Butiran Pengangkutan">Transportation Details</span>
                        </button>
                        <button type="button" class="ipv-tabnav-item" data-ipv-tab="condition" role="tab">
                            <span data-en="Condition" data-bm="Syarat">Condition</span>
                        </button>
                        <button type="button" class="ipv-tabnav-item" data-ipv-tab="activity" role="tab">
                            <span data-en="Activity" data-bm="Aktiviti">Activity</span>
                        </button>
                    </div>

                    <div class="ipv-tabbody">

                        <!-- Permit List -->
                        <div class="ipv-tabpane is-active" data-ipv-pane="permits">
                            <div class="ipv-permit-accordion" id="ipvPermitAccordion"></div>
                        </div>

                        <!-- Transportation Details -->
                        <div class="ipv-tabpane" data-ipv-pane="transport">
                            <div id="ipvTransportDetails"></div>
                        </div>

                        <!-- Condition -->
                        <div class="ipv-tabpane" data-ipv-pane="condition">
                            <div id="ipvConditionList"></div>
                        </div>

                        <!-- Activity -->
                        <div class="ipv-tabpane" data-ipv-pane="activity">
                            <div class="ipv-timeline" id="ipvActivityTimeline"></div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection