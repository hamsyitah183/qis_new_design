@extends('pages.app')

@section('pageName', 'Technical Review')

@push('scripts')
    @vite(['resources/js/pages/importPermit/officerTechnicalReview.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        ['label' => 'Technical Review Queue', 'url' => '/internal/technical_review'],
        ['label' => 'Application: IP-2025-00456', 'url' => '#'],
    ]" title="Technical Review">
    </x-breadcrumb>
@endsection

@section('content')

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
                    <button type="button" class="ipv-icon-btn" title="More actions">
                        <i class="bi bi-three-dots"></i>
                    </button>
                </div>

                <!-- Review progress summary -->
                <div class="otr-progress-box" id="otrProgressBox">
                    <div class="otr-progress-title">Review Progress</div>
                    <div class="otr-progress-counts" id="otrProgressCounts">
                        <div class="otr-count-cell">
                            <div class="otr-count-num" id="otrCountTotal">0</div>
                            <div class="otr-count-label">Total</div>
                        </div>
                        <div class="otr-count-cell">
                            <div class="otr-count-num is-pending" id="otrCountPending">0</div>
                            <div class="otr-count-label">Pending</div>
                        </div>
                        <div class="otr-count-cell">
                            <div class="otr-count-num is-approved" id="otrCountApproved">0</div>
                            <div class="otr-count-label">Approved</div>
                        </div>
                        <div class="otr-count-cell">
                            <div class="otr-count-num is-rejected" id="otrCountRejected">0</div>
                            <div class="otr-count-label">Rejected</div>
                        </div>
                    </div>
                    <div class="otr-progress-track">
                        <div class="otr-progress-fill is-approved"  id="otrFillApproved"  style="width:0%"></div>
                        <div class="otr-progress-fill is-rejected"  id="otrFillRejected"  style="width:0%"></div>
                    </div>
                    <div class="otr-progress-hint" id="otrProgressHint">Review each permit item below.</div>
                </div>

                <div class="ipv-value-box">
                    <div>
                        <div class="ipv-value-label">Total Consignment Value</div>
                        <div class="ipv-value-amount" id="ipvTotalValue">RM 0.00</div>
                    </div>
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
                            <div class="ipv-info-label">Reviewing Officer</div>
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

                    <!-- ================================================ -->
                    <!-- PERMIT LIST — officer decisions live here          -->
                    <!-- ================================================ -->
                    <div class="ipv-tabpane is-active" data-ipv-pane="permits">
                        <div class="otr-review-hint">
                            <i class="bi bi-info-circle"></i>
                            Expand each permit item to review its details and attachments,
                            then <strong>Approve</strong> or <strong>Reject</strong> it.
                            Once all items are reviewed you can finalise the application.
                        </div>
                        <div class="ipv-permit-accordion" id="ipvPermitAccordion"></div>

                        <!-- Finalise bar — appears once every item has a decision -->
                        <div class="otr-finalise-bar d-none" id="otrFinaliseBar">
                            <div class="otr-finalise-summary" id="otrFinaliseSummary"></div>
                            <button type="button" class="otr-btn-finalise" id="otrFinaliseBtn">
                                <i class="bi bi-check2-all"></i> Finalise &amp; Forward to Payment
                            </button>
                        </div>
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
    <!-- REJECT PERMIT MODAL                                            -->
    <!-- ============================================================ -->
    <div class="modal fade" id="otrRejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
            <div class="modal-content ipv-reject-modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle me-2 text-danger"></i>
                        Reject Permit — <span id="otrRejectModalPermitNo">—</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="ipv-reject-modal-intro">
                        Provide a reason for rejecting this permit. The applicant will see this
                        note in their activity log.
                    </p>

                    <div class="ipv-reject-quick-reasons">
                        <button type="button" class="ipv-reject-chip"
                            data-reason="Annual import quota for this category has been exhausted for the current period.">
                            Quota exhausted
                        </button>
                        <button type="button" class="ipv-reject-chip"
                            data-reason="Declared quantity exceeds the permitted limit for a single consignment.">
                            Quantity exceeds limit
                        </button>
                        <button type="button" class="ipv-reject-chip"
                            data-reason="Required supporting documents for this item are insufficient or do not meet standards.">
                            Insufficient documents
                        </button>
                        <button type="button" class="ipv-reject-chip"
                            data-reason="This species / item is currently under an import restriction or moratorium.">
                            Import restriction
                        </button>
                    </div>

                    <label class="ipv-reject-label" for="otrRejectReason">
                        Rejection reason <span class="ipv-required">*</span>
                    </label>
                    <textarea class="ipv-reject-textarea" id="otrRejectReason" rows="3"
                        placeholder="State clearly why this permit is being rejected…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ipv-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="ipv-btn-reject-confirm" id="otrRejectConfirmBtn" disabled>
                        <i class="bi bi-x-circle me-1"></i> Confirm Rejection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ATTACHMENT VIEWER OFFCANVAS                                    -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="attachmentOffcanvas"
        aria-labelledby="attachmentOffcanvasLabel" style="width: 70%; max-width: 900px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="attachmentOffcanvasLabel">
                <i class="bi bi-paperclip me-2"></i>
                <span id="attachmentTitle">Attachment</span>
            </h5>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="attachmentPrevBtn"><i class="bi bi-chevron-left"></i></button>
                <span class="badge bg-light text-dark" id="attachmentCounter">1 / 1</span>
                <button class="btn btn-sm btn-outline-secondary" id="attachmentNextBtn"><i class="bi bi-chevron-right"></i></button>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 60px);">
            <div class="pd-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#attach-view"
                            type="button" title="View"><i class="bi bi-eye"></i></button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#attach-details"
                            type="button" title="Details"><i class="bi bi-info-circle"></i></button>
                    </li>
                </ul>
            </div>
            <div class="tab-content flex-grow-1 p-3 overflow-auto">
                <div class="tab-pane fade show active" id="attach-view">
                    <div id="attachmentViewer"
                        style="min-height:400px; display:flex; align-items:center; justify-content:center;
                               background:var(--gray-1); border-radius:0.5rem;">
                        <div class="text-muted"><i class="bi bi-file-earmark-fill fs-1"></i><br>Select an attachment</div>
                    </div>
                </div>
                <div class="tab-pane fade" id="attach-details">
                    <div id="attachmentDetails" class="py-2"></div>
                </div>
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
                <div class="ipv-permit-detail-icon"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="ipv-permit-detail-eyebrow">Permit Details</div>
                    <h5 class="offcanvas-title mb-0 fw-bold" id="permitDetailOffcanvasLabel">—</h5>
                </div>
                <span class="ipv-badge ms-2" id="pdBadge">—</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 68px); overflow:hidden;">
            <div class="pd-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pd-details"
                            type="button" title="Details"><i class="bi bi-file-text"></i></button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pd-activity"
                            type="button" title="Activity"><i class="bi bi-clock-history"></i></button>
                    </li>
                </ul>
            </div>
            <div class="tab-content flex-grow-1 overflow-auto">
                <div class="tab-pane fade show active p-4" id="pd-details">
                    <div id="pdDetailsContent"></div>
                </div>
                <div class="tab-pane fade p-4" id="pd-activity">
                    <div class="ipv-timeline" id="pdActivityTimeline"></div>
                </div>
            </div>
        </div>
    </div>

@endsection