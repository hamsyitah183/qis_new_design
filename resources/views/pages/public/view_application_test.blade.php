@extends('pages.app')

@section('pageName', 'View Application')

@push('scripts')
    @vite(['resources/js/pages/importPermit/test1.js'])
    @vite(['resources/js/pages/importPermit/test2.js'])
    {{-- @vite(['resources/js/pages/importPermit/application_reapply.js']) --}}
@endpush




@section('breadcrumb')

    @php
        $internalUser = auth('internal')->user();
        $isInternal = auth('internal')->check();
        $applicationUrl = $isInternal ? '/internal/view_import_permit' : '/public/view_import_permit';
    @endphp

    @if ($internalUser && $internalUser->hasRole('boundary officer'))
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => '/'],
            ['label' => 'Application: ', 'url' => '#'],
        ]" title="View Application">
        </x-breadcrumb>
    @else
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => '/'],
            ['label' => 'Application List', 'url' => '#'],
            ['label' => 'Application: ' , 'url' => '#'],
        ]" title="View Application">
        </x-breadcrumb>
    @endif

@endsection




@section('content')

    @php
        $authUuid = authUser()['user']->uuid ?? null;
        $status = '';
        $importerVerify = '';
    @endphp

    {{--
    Import Permit — Application View
    ---------------------------------
    Maps to IpApplication / IpConsignmentPermit. Every value you see here
    is rendered client-side from dummy data in importPermitView.js
    (window.IPV_DATA) so the layout can be reviewed before the real
    Eloquent data is wired in.

    To wire up later: pass `$application` (with importer, exporter,
    consignmentPermits, activity_log eager-loaded) into this view, drop
    the dummy IPV_DATA object in importPermitView.js, and feed the same
    shape from a small @json($application->toViewArray()) blob instead.
    The render functions don't need to change.

    Include with:
        @include('pages.public.view_permit_test.import_permit_view')
--}}

    <div class="ipv-wrapper row g-4">

        <!-- ============================================================ -->
        <!-- LEFT: Sidebar                                                  -->
        <!-- ============================================================ -->
        <div class="col-xl-4 col-lg-5">
            <div class="ipv-side-card">

                <div class="ipv-tags" id="ipvTags"></div>

                <div class="ipv-app-type" id="ipvAppType">Import Permit</div>
                <div class="ipv-app-id" id="ipvAppId">—</div>
                <div class="ipv-submitted-by">
                    <i class="bi bi-person-circle"></i>
                    <span id="ipvSubmittedBy">—</span>
                </div>

                <div class="ipv-action-row">
                    <button type="button" class="ipv-btn-primary" id="ipvPrintPermitBtn">
                        <i class="bi bi-printer"></i> Print Permit
                    </button>
                    <span class="ipv-download-badge" id="ipvDownloadBadge" title="Permits downloaded">
                        <i class="bi bi-download"></i> 0
                    </span>
                    <div class="schedule">
                        <button type="button" class="ipv-icon-btn" id="scheduleBtn" title="Schedule inspection">
                            <i class="bi bi-calendar3"></i>
                        </button>
                        <!-- Schedule Inspection — calendar popover -->
                        <div class="ipv-cal-popover" id="ipvSchedulePopover">
                            <div class="ipv-cal-popover-header">
                                <strong>Application Schedule</strong>
                                <button type="button" class="ipv-cal-popover-close" id="ipvScheduleClose"
                                    aria-label="Close">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <div class="ipv-cal-nav">
                                <button type="button" class="ipv-cal-nav-btn" id="ipvScheduleCalPrev"><i
                                        class="bi bi-chevron-left"></i></button>
                                <span class="ipv-cal-month-label" id="ipvScheduleMonthLabel">—</span>
                                <button type="button" class="ipv-cal-nav-btn" id="ipvScheduleCalNext"><i
                                        class="bi bi-chevron-right"></i></button>
                            </div>

                            <div class="ipv-cal-weekdays">
                                <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                            </div>

                            <div class="ipv-cal-grid" id="ipvScheduleGrid"></div>

                            <div class="ipv-cal-legend" id="ipvScheduleLegend"></div>
                        </div>
                    </div>
                    <button type="button" class="ipv-icon-btn" title="More actions">
                        <i class="bi bi-three-dots"></i>
                    </button>


                </div>

                <div class="ipv-value-box">
                    <div>
                        <div class="ipv-value-label">Total Consignment Value</div>
                        <div class="ipv-value-amount" id="ipvTotalValue">RM 0.00</div>
                    </div>
                    <button type="button" class="ipv-value-link" id="ipvViewPermitsLink">View</button>
                </div>

                <div class="ipv-divider"></div>

                <div class="ipv-section-label">Importer &amp; Exporter Details</div>

                <div class="ipv-party" id="ipvImporterBlock"></div>
                <div class="ipv-party" id="ipvExporterBlock"></div>

                <div class="ipv-divider"></div>

                <div class="ipv-section-label-row">
                    <span class="ipv-section-label">Application Documents</span>
                    <button type="button" class="ipv-download-all" id="ipvDownloadAllApp">
                        <i class="bi bi-download"></i> Download All
                    </button>
                </div>
                <div class="ipv-attach-list" id="ipvAppAttachments"></div>

                <div class="ipv-footer-note" id="ipvCreatedAt"></div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- RIGHT: Main panel                                              -->
        <!-- ============================================================ -->
        <div class="col-xl-8 col-lg-7">
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

    <!-- ============================================================ -->
    <!-- ATTACHMENT VIEWER OFFCANVAS (Vertical Tabs)                   -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="attachmentOffcanvas"
        aria-labelledby="attachmentOffcanvasLabel" style="width: 70%; max-width: 900px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="attachmentOffcanvasLabel">
                <i class="bi bi-paperclip me-2"></i> <span id="attachmentTitle">Attachment</span>
            </h5>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="attachmentPrevBtn" title="Previous">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="badge bg-light text-dark" id="attachmentCounter">1 / 1</span>
                <button class="btn btn-sm btn-outline-secondary" id="attachmentNextBtn" title="Next">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 60px);">

            <div class="pd-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" id="attachmentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="attach-view-tab" data-bs-toggle="tab"
                            data-bs-target="#attach-view" type="button" role="tab" aria-selected="true"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="View">
                            <i class="bi bi-eye me-2"></i>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attach-details-tab" data-bs-toggle="tab"
                            data-bs-target="#attach-details" type="button" role="tab" aria-selected="false"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Details">
                            <i class="bi bi-info-circle me-2"></i>
                        </button>
                    </li>
                </ul>
            </div>
            <!-- Tab Content -->
            <div class="tab-content flex-grow-1 p-3 overflow-auto" id="attachmentTabContent">
                <!-- View Tab -->
                <div class="tab-pane fade show active" id="attach-view" role="tabpanel">
                    <div id="attachmentViewer"
                        style="min-height: 400px; display: flex; align-items: center; justify-content: center; background: var(--gray-1); border-radius: 0.5rem;">
                        <div class="text-muted"><i class="bi bi-file-earmark-fill fs-1"></i><br>Select an attachment</div>
                    </div>
                </div>
                <!-- Details Tab -->
                <div class="tab-pane fade" id="attach-details" role="tabpanel">
                    <div id="attachmentDetails" class="py-2">
                        <!-- filled dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- PERMIT LIST OFFCANVAS (Download Permits)                      -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="permitListOffcanvas"
        aria-labelledby="permitListOffcanvasLabel" style="width: 60%; max-width: 800px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="permitListOffcanvasLabel">
                <i class="bi bi-file-earmark-pdf me-2"></i> Available Permits
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="permitDownloadTable">
                    <thead style="background: var(--gray-1); border-bottom: 2px solid var(--default-border);">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAllPermits" class="form-check-input">
                            </th>
                            <th
                                style="font-weight: 600; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">
                                Permit Number</th>
                            <th
                                style="font-weight: 600; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">
                                Item</th>
                            <th
                                style="font-weight: 600; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">
                                Status</th>
                            <th
                                style="font-weight: 600; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; text-align: right;">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody id="permitDownloadTableBody">
                        <!-- dynamically populated -->
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="offcanvas">Close</button>
                <button class="btn btn-sm btn-primary" id="downloadSelectedPermitsBtn" disabled>
                    <i class="bi bi-download me-1"></i> Download Selected (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- PERMIT DETAIL OFFCANVAS                                        -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="permitDetailOffcanvas"
        aria-labelledby="permitDetailOffcanvasLabel" style="width: 65%; max-width: 860px;">
        <div class="offcanvas-header border-bottom px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="ipv-permit-detail-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="ipv-permit-detail-eyebrow">Permit Details</div>
                    <h5 class="offcanvas-title mb-0 fw-bold" id="permitDetailOffcanvasLabel">—</h5>
                </div>
                <span class="ipv-badge ms-2" id="pdBadge">—</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 68px); overflow: hidden;">
            <!-- Vertical Nav -->
            <div class="pd-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" id="permitDetailTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pd-details-tab" data-bs-toggle="tab" data-bs-target="#pd-details"
                            type="button" role="tab" data-bs-toggle="tooltip" data-bs-placement="right"
                            title="Details">
                            <i class="bi bi-file-text"></i>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pd-activity-tab" data-bs-toggle="tab" data-bs-target="#pd-activity"
                            type="button" role="tab" data-bs-toggle="tooltip" data-bs-placement="right"
                            title="Activity Log">
                            <i class="bi bi-clock-history"></i>
                        </button>
                    </li>
                </ul>
            </div>
            <!-- Tab Content -->
            <div class="tab-content flex-grow-1 overflow-auto" id="permitDetailTabContent">
                <!-- Details Tab -->
                <div class="tab-pane fade show active p-4" id="pd-details" role="tabpanel">
                    <div id="pdDetailsContent"></div>
                </div>
                <!-- Activity Tab -->
                <div class="tab-pane fade p-4" id="pd-activity" role="tabpanel">
                    <div class="ipv-timeline" id="pdActivityTimeline"></div>
                </div>
            </div>
        </div>
    </div>
    <x-modal id="consignmentModal" title="">


        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        @endslot

    </x-modal>

    <x-modal id="activityLogModal" title="Activity Log">

        <!-- Your table goes here -->
        <div class="table-responsive scroll-div" style = "max-height: 400px;">
            <table class="table text-wrap table-hover" id="applicationLogTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Action</th>
                        <th scope="col">User</th>
                        <th scope="col">Remark</th>
                        <th scope="col">Status</th>
                        <th scope="col">Time and Date</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">

                </tbody>
            </table>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        @endslot

    </x-modal>

    {{-- @include('pages.public.view_permit_test.step2modal') --}}

@endsection

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    <script>
        // for form wizard next and prev button
        (function() {
            // 🟢 First wizard
            let firstWizardConfig = {
                wz_class: ".wizard-tab",
                highlight: true,
                highlight_time: 1000,
                progress: true,
                validate: true
            };
            new Wizard1(firstWizardConfig).init();

            // 🟢 Second wizard (with progress bar)
            let secondWizardConfig = {
                wz_class: ".wizard-second-tab", // ✅ fixed selector
                highlight: true,
                highlight_time: 1000,
                progress: true,
                validate: true
            };
            new Wizard1(secondWizardConfig).init();
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash === '#pending') {


                document.querySelectorAll('.wizard-step').forEach(el => {
                    el.classList.remove('active');
                });


                const pendingTab = document.getElementById('pendingTab');
                if (pendingTab) {
                    pendingTab.classList.add('active');
                }

            }
        });
    </script>
@endpush
