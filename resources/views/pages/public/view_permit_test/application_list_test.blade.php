@extends('pages.app')

@section('pageName', 'View Import Permit')

@push('scripts')
    @vite(['resources/js/pages/importPermit/applicationList.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        ['label' => 'Application List', 'url' => '/public/view_import_permit'],
        ['label' => 'New Application', 'url' => '#'],
    ]" title="Apply Import Permit">
    </x-breadcrumb>
@endsection

@section('content')

    <div class="container-fluid ipv-list-wrapper">

        {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

            <div>
                <h3 class="fw-bold mb-1">
                    Import Permit Applications
                </h3>

                <div class="text-muted">
                    View, monitor and manage all import permit applications.
                </div>
            </div>

            <button class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i>
                New Application
            </button>

        </div>


        {{-- =========================================================
        SUMMARY
    ========================================================== --}}

        <div class="row g-3 mb-4">

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small>Total Applications</small>
                    <h3 id="summaryTotal">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small>Submitted</small>
                    <h3 id="summarySubmitted">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small>Technical Review</small>
                    <h3 id="summaryTechnical">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small>Awaiting Payment</small>
                    <h3 id="summaryPayment">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small>Completed</small>
                    <h3 id="summaryCompleted">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small>Returned</small>
                    <h3 id="summaryReturned">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small>Document Verification</small>
                    <h3 id="summaryDocVerify">0</h3>
                </div>
            </div>

            <div class="col-xl col-md-4 col-sm-6">
                <div class="ipv-summary-card">
                    <small>Payment Processing</small>
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
                        Filter
                    </h5>

                    <button class="btn-close" id="closeFilter">
                    </button>

                </div>

                <div class="ipv-filter-body">

                    <div class="mb-4">

                        <label class="form-label fw-semibold">
                            Status
                        </label>

                        <div class="d-grid gap-2">

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Submitted" type="checkbox">

                                <label class="form-check-label">
                                    Submitted
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Document Verification" type="checkbox">

                                <label class="form-check-label">
                                    Document Verification
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Technical Review" type="checkbox">

                                <label class="form-check-label">
                                    Technical Review
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Awaiting Payment" type="checkbox">

                                <label class="form-check-label">
                                    Awaiting Payment
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Payment Processing" type="checkbox">

                                <label class="form-check-label">
                                    Payment Processing
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input status-filter" value="Completed" type="checkbox">

                                <label class="form-check-label">
                                    Completed
                                </label>
                            </div>

                        </div>

                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Transport</label>
                        <select class="form-select ipv-multi-select" id="filterTransport" multiple>
                            <option value="Air">Air</option>
                            <option value="Sea">Sea</option>
                            <option value="Land">Land</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Entry Point</label>
                        <select class="form-select ipv-multi-select" id="filterEntryPoint" multiple>
                            <option value="Kota Kinabalu">Kota Kinabalu</option>
                            <option value="Tawau">Tawau</option>
                            <option value="Sandakan">Sandakan</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Submission Date</label>
                        <div class="d-flex flex-column gap-2">
                            <div>
                                <label class="ipv-date-sublabel">From</label>
                                <input type="date" class="form-control" id="filterDateFrom">
                            </div>
                            <div>
                                <label class="ipv-date-sublabel">To</label>
                                <input type="date" class="form-control" id="filterDateTo">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" id="applyFilterBtn">Apply Filter</button>
                        <button class="btn btn-light border" id="clearFilterBtn">Clear</button>
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
                            Filter
                        </button>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchApplication"
                                placeholder="Search application...">
                        </div>

                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <label class="ipv-sort-label" for="sortSelect">
                            <i class="bi bi-arrow-down-up"></i> Sort by
                        </label>
                        <select class="form-select ipv-sort-select" id="sortSelect">
                            <option value="created_desc">Date Created (Newest)</option>
                            <option value="created_asc">Date Created (Oldest)</option>
                            <option value="eta_asc">ETA (Earliest)</option>
                            <option value="eta_desc">ETA (Latest)</option>
                            <option value="value_desc">Value (Highest)</option>
                            <option value="value_asc">Value (Lowest)</option>
                            <option value="permits_desc">Permits (Most)</option>
                            <option value="permits_asc">Permits (Fewest)</option>
                        </select>
                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle ipv-table">

                        <thead>

                            <tr>

                                <th>Application</th>

                                <th>Applicant</th>

                                <th>Importer</th>

                                <th>ETA</th>

                                <th>Transport</th>

                                <th>Permits</th>

                                <th>Value</th>

                                <th>Status</th>

                                <th width="170">
                                    Action
                                </th>

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

                <h5 class="mb-1">
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
                            <span class="ipv-status-eyebrow">Application Type:</span>
                            <strong>Import Permit</strong>
                            <span class="ipv-status-sep">|</span>
                            <span class="ipv-status-eyebrow">Status:</span>
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
                                <div class="ipv-info-label">Assigned Officer</div>
                                <div class="ipv-info-value" id="ipvAssignedOfficer">—</div>
                            </div>
                            <button type="button" class="ipv-info-link">Reassign</button>
                        </div>
                        <div class="ipv-info-item">
                            <div class="ipv-info-icon"><i class="bi bi-hourglass-split"></i></div>
                            <div>
                                <div class="ipv-info-label">Next Action / SLA</div>
                                <div class="ipv-info-value" id="ipvSlaDue">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="ipv-tabnav" role="tablist">
                        <button type="button" class="ipv-tabnav-item is-active" data-ipv-tab="permits" role="tab">
                            Permit List <span class="ipv-tab-count" id="ipvPermitCount">0</span>
                        </button>
                        <button type="button" class="ipv-tabnav-item" data-ipv-tab="transport" role="tab">
                            Transportation Details
                        </button>
                        <button type="button" class="ipv-tabnav-item" data-ipv-tab="condition" role="tab">
                            Condition
                        </button>
                        <button type="button" class="ipv-tabnav-item" data-ipv-tab="activity" role="tab">
                            Activity
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
