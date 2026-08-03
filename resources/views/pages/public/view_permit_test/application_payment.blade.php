@extends('pages.app')

@section('pageName', 'Select Items for Payment')

@push('scripts')
    @vite(['resources/js/pages/importPermit/userPaymentSelection.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard',                    'url' => '/'],
        ['label' => 'My Applications',              'url' => '/public/view_import_permit'],
        ['label' => 'Application: IP-2025-00456',   'url' => '#'],
    ]" title="Select Items for Payment">
    </x-breadcrumb>
@endsection

@section('content')

    <div class="ipv-wrapper row g-4">

        <!-- ============================================================ -->
        <!-- LEFT: Sidebar                                                  -->
        <!-- ============================================================ -->
        <div class="col-xl-4 col-lg-5">
            <div class="ipv-side-card">

                <div class="ipv-tags" id="psTags"></div>

                <div class="ipv-app-type" id="psAppType">Import Permit</div>
                <div class="ipv-app-id" id="psAppId">—</div>
                <div class="ipv-submitted-by">
                    <i class="bi bi-person-circle"></i>
                    <span id="psSubmittedBy">—</span>
                </div>

                <div class="ipv-action-row">
                    <button type="button" class="ipv-icon-btn" title="More actions">
                        <i class="bi bi-three-dots"></i>
                    </button>
                </div>

                <!-- Payment progress summary box (reuses otr-progress-box styling) -->
                <div class="otr-progress-box">
                    <div class="otr-progress-title">Payment Selection</div>
                    <div class="otr-progress-counts">
                        <div class="otr-count-cell">
                            <div class="otr-count-num" id="psCountTotal">0</div>
                            <div class="otr-count-label">Total</div>
                        </div>
                        <div class="otr-count-cell">
                            <div class="otr-count-num is-approved" id="psCountSelected">0</div>
                            <div class="otr-count-label">Selected</div>
                        </div>
                        <div class="otr-count-cell">
                            <div class="otr-count-num is-rejected" id="psCountRejected">0</div>
                            <div class="otr-count-label">Rejected</div>
                        </div>
                    </div>
                    <div class="otr-progress-track">
                        <div class="otr-progress-fill is-approved" id="psFillSelected" style="width:0%"></div>
                    </div>
                    <div class="otr-progress-hint" id="psProgressHint">
                        Tick the approved items you wish to pay for.
                    </div>
                </div>

                <!-- Fee summary -->
                <div class="ps-fee-summary-box" id="psFeeBox">
                    <div class="ps-fee-row">
                        <span>Items selected</span>
                        <span id="psFeeCount">0</span>
                    </div>
                    <div class="ps-fee-row">
                        <span>Fee per item</span>
                        <span>RM 15.00</span>
                    </div>
                    <div class="ps-fee-row is-total">
                        <span>Total payable</span>
                        <span id="psFeeTotalLabel">RM 0.00</span>
                    </div>
                </div>

                <div class="ipv-value-box">
                    <div>
                        <div class="ipv-value-label">Total Consignment Value</div>
                        <div class="ipv-value-amount" id="psTotalValue">RM 0.00</div>
                    </div>
                </div>

                <div class="ipv-divider"></div>

                <div class="ipv-section-label">Importer &amp; Exporter Details</div>
                <div class="ipv-party" id="psImporterBlock"></div>
                <div class="ipv-party" id="psExporterBlock"></div>

                <div class="ipv-divider"></div>

                <div class="ipv-section-label-row">
                    <span class="ipv-section-label">Application Documents</span>
                    <button type="button" class="ipv-download-all" id="psDownloadAllApp">
                        <i class="bi bi-download"></i> Download All
                    </button>
                </div>
                <div class="ipv-attach-list" id="psAppAttachments"></div>

                <div class="ipv-footer-note" id="psCreatedAt"></div>
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
                        <strong id="psStatusLabel">—</strong>
                    </div>
                    <div class="ipv-status-duration" id="psStatusDuration"></div>
                </div>

                <!-- Stage stepper -->
                <div class="ipv-stage-stepper" id="psStageStepper"></div>

                <!-- Info row -->
                <div class="ipv-info-row">
                    <div class="ipv-info-item">
                        <div class="ipv-info-icon"><i class="bi bi-person-badge"></i></div>
                        <div>
                            <div class="ipv-info-label">Reviewing Officer</div>
                            <div class="ipv-info-value" id="psAssignedOfficer">—</div>
                        </div>
                    </div>
                    <div class="ipv-info-item">
                        <div class="ipv-info-icon"><i class="bi bi-hourglass-split"></i></div>
                        <div>
                            <div class="ipv-info-label">Payment Due</div>
                            <div class="ipv-info-value" id="psSlaDue">—</div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="ipv-tabnav" role="tablist">
                    <button type="button" class="ipv-tabnav-item is-active"
                        data-ipv-tab="permits" role="tab">
                        Permit List
                        <span class="ipv-tab-count" id="psPermitCount">0</span>
                    </button>
                    <button type="button" class="ipv-tabnav-item"
                        data-ipv-tab="transport" role="tab">
                        Transportation Details
                    </button>
                    <button type="button" class="ipv-tabnav-item"
                        data-ipv-tab="condition" role="tab">
                        Condition
                    </button>
                    <button type="button" class="ipv-tabnav-item"
                        data-ipv-tab="activity" role="tab">
                        Activity
                    </button>
                </div>

                <div class="ipv-tabbody">

                    <!-- ============================================ -->
                    <!-- PERMIT LIST TAB                               -->
                    <!-- ============================================ -->
                    <div class="ipv-tabpane is-active" data-ipv-pane="permits">

                        <!-- Context hint (same style as otr-review-hint) -->
                        <div class="otr-review-hint">
                            <i class="bi bi-credit-card"></i>
                            Tick each <strong>approved</strong> permit you want to include in this
                            payment. Rejected permits cannot be paid. Each item costs
                            <strong>RM 15.00</strong>.
                        </div>

                        <div class="ipv-permit-accordion" id="psPermitAccordion"></div>

                        <!-- ======================================== -->
                        <!-- STICKY PAYMENT FOOTER BAR                -->
                        <!-- ======================================== -->
                        <div class="ps-pay-bar" id="psPayBar">
                            <div class="ps-pay-bar-left">
                                <div class="ps-pay-bar-count">
                                    <span class="ps-pay-bar-num" id="psBarCount">0</span>
                                    <span class="ps-pay-bar-label">item<span id="psBarPlural">s</span> selected</span>
                                </div>
                                <div class="ps-pay-bar-divider"></div>
                                <div class="ps-pay-bar-total">
                                    <span class="ps-pay-bar-currency">RM</span>
                                    <span class="ps-pay-bar-amount" id="psBarTotal">0.00</span>
                                </div>
                            </div>
                            <button type="button" class="ps-pay-bar-btn" id="psPayBtn" disabled>
                                <i class="bi bi-credit-card"></i>
                                Proceed to Payment
                            </button>
                        </div>

                    </div>

                    <!-- Transportation Details -->
                    <div class="ipv-tabpane" data-ipv-pane="transport">
                        <div id="psTransportDetails"></div>
                    </div>

                    <!-- Condition -->
                    <div class="ipv-tabpane" data-ipv-pane="condition">
                        <div id="psConditionList"></div>
                    </div>

                    <!-- Activity -->
                    <div class="ipv-tabpane" data-ipv-pane="activity">
                        <div class="ipv-timeline" id="psActivityTimeline"></div>
                    </div>

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
                <button class="btn btn-sm btn-outline-secondary" id="attachmentPrevBtn">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="badge bg-light text-dark" id="attachmentCounter">1 / 1</span>
                <button class="btn btn-sm btn-outline-secondary" id="attachmentNextBtn">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 60px);">
            <div class="pd-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab"
                            data-bs-target="#attach-view" type="button" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab"
                            data-bs-target="#attach-details" type="button" title="Details">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content flex-grow-1 p-3 overflow-auto">
                <div class="tab-pane fade show active" id="attach-view">
                    <div id="attachmentViewer"
                        style="min-height:400px; display:flex; align-items:center;
                               justify-content:center; background:var(--gray-1);
                               border-radius:0.5rem;">
                        <div class="text-muted">
                            <i class="bi bi-file-earmark-fill fs-1"></i><br>Select an attachment
                        </div>
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
        aria-labelledby="permitDetailOffcanvasLabel" style=" ">
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
                        <button class="nav-link active" data-bs-toggle="tab"
                            data-bs-target="#pd-details" type="button" title="Details">
                            <i class="bi bi-file-text"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab"
                            data-bs-target="#pd-activity" type="button" title="Activity">
                            <i class="bi bi-clock-history"></i>
                        </button>
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