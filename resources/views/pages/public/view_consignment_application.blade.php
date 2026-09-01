@extends('pages.app')

@section('pageName', 'View Application')

@push('scripts')
    {{-- TODO: rename the compiled bundle if you keep the old consignment_detail.js path --}}
    @vite(['resources/js/pages/consignment/consignment1.js', 'resources/js/pages/consignment/consignment2.js', 'resources/js/pages/consignment/consignment-actions.js'])
@endpush

@section('breadcrumb')
    @php
        $internalUser = auth('internal')->user();
        $isInternal = auth('internal')->check();
        // TODO: confirm these route names match your consignment list routes
        $applicationUrl = $isInternal ? '/internal/consignment_certificates_list' : '/public/view_all_consignment';
    @endphp

    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Papan Pemuka'],
        [
            'label' => 'Consignment Certificate List',
            'url' => $applicationUrl,
            'data-en' => 'Consignment Certificate List',
            'data-bm' => 'Senarai Sijil Konsainan',
        ],
        [
            'label' => 'Application: ' . $application->application_id,
            'url' => '#',
            'data-en' => 'Application',
            'data-bm' => 'Permohonan',
        ],
    ]" title="View Application" title_en="View Application" title_bm="Lihat Permohonan">
    </x-breadcrumb>
@endsection

@section('content')

    @php
        $authUuid = authUser()['user']->uuid ?? null;
        $status = strtolower($application->status ?? '');

        $isInternal = auth()->guard('internal')->check();
        $isAdminOrClerk =
            $isInternal &&
            auth()
                ->guard('internal')
                ->user()
                ->hasAnyRole(['admin', 'clerk', 'superadmin']);
        $isAdmin =
            $isInternal &&
            auth()
                ->guard('internal')
                ->user()
                ->hasAnyRole(['admin', 'superadmin']);

        $isPublic = auth()->guard('public')->check();
        // In Consignment, the applicant/owner is the EXPORTER (roles are reversed vs Import Permit).
        $isOwner = $isPublic && $application->exporter_id === auth()->guard('public')->user()->uuid;

        // ---------- Officer Verification Completed ----------
        $isOfficerVerified = str_contains(strtolower($application->status ?? ''), 'officer verification completed');

        $allPending = $application->consignmentPermits->every(fn($permit) => $permit->status === 'pending for payment');

        $showPaymentTab = $isPublic && $application->user_id === $authUuid && ($allPending || $isOfficerVerified);

        $showPaybulkAction = $isPublic && $isOwner && $isOfficerVerified;

        $paymentTabActive = $isOfficerVerified && $showPaymentTab;

        $showClerkReviewActions =
            str_contains($status, 'clerk review in-progress') &&
            $isInternal &&
            auth()->guard('internal')->user()->can('approve application');
        $showAdminRejectedActions = str_contains($status, 'rejected') && $isAdmin;

        // ---------- FIX: Add permission check for internal users ----------
        $canEditInternal = $isInternal && auth('internal')->user()->can('edit application');

        // ---------- Compute whether this application has any custom items ----------
        $hasCustomItems = $application->consignmentPermits->contains(function ($permit) {
                return data_get($permit->consignment_detail, 'isCustom') === true;
            });
    @endphp

    {{-- Feed real application context to consignment1.js instead of URL-parsing --}}
    <script>
        window.baseUrl = "{{ url('/') }}";
        window.APPLICATION_ID = "{{ $application->application_id }}";
        window.authUser = {
            type: "{{ $isInternal ? 'internal' : 'public' }}",
            roles: {!! json_encode(
                $isInternal ? auth('internal')->user()->roles->map(fn($r) => ['name' => $r->name])->values() : [],
            ) !!}
        };
        // Pass custom items flag to JavaScript so it can be updated dynamically
        window.HAS_CUSTOM_ITEMS = {{ $hasCustomItems ? 'true' : 'false' }};
    </script>

    <div class="ipv-wrapper row g-4">

        {{-- ============================================================ --}}
        {{-- PAYMENT AWARENESS BANNER --}}
        {{-- ============================================================ --}}
        <div class="col-xl-12" id="ipvPaymentBannerWrap" style="display:none">
            <div class="ipv-payment-banner" id="ipvPaymentBanner"></div>
        </div>

        {{-- ============================================================ --}}
        {{-- APPLICATION-LEVEL ACTIONS BAR (Clerk / Admin)               --}}
        {{-- ============================================================ --}}
        @if ($showClerkReviewActions || $showAdminRejectedActions)
            <div class="col-xl-12">
                <div class="ipv-actions-bar">
                    <div class="ipv-actions-bar-text">
                        <i class="bi bi-info-circle"></i>
                        @if ($showClerkReviewActions)
                            <span data-en="This application is awaiting your review."
                                  data-bm="Permohonan ini sedang menunggu semakan anda.">This application is awaiting your review.</span>
                            {{-- CUSTOM ITEMS WARNING – dynamically controlled by JavaScript --}}
                            <span id="customItemsWarning" class="{{ $hasCustomItems ? '' : 'd-none' }}"
                                  data-en="There is an item that is not in the Consignment item list."
                                  data-bm="Terdapat item yang tiada dalam senarai item Konsainan.">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <span data-en="There is an item that is not in the Consignment item list."
                                      data-bm="Terdapat item yang tiada dalam senarai item Konsainan.">
                                    There is an item that is not in the Consignment item list.
                                </span>
                            </span>
                        @elseif ($showAdminRejectedActions)
                            <span data-en="This application was rejected. You may review and re-evaluate it."
                                  data-bm="Permohonan ini telah ditolak. Anda boleh menyemak dan menilai semula.">This application was rejected. You may review and re-evaluate it.</span>
                        @endif
                    </div>
                    <div class="ipv-actions-bar-buttons">
                        @if ($showClerkReviewActions)
                            <button id="acceptAppl" class="ipv-btn-action is-success">
                                <i class="bi bi-check-lg"></i> <span data-en="Accept Application"
                                    data-bm="Terima Permohonan">Accept Application</span>
                            </button>
                            <button id="rejectAdminAppl" class="ipv-btn-action is-danger">
                                <i class="bi bi-x-lg"></i> <span data-en="Reject Application"
                                    data-bm="Tolak Permohonan">Reject Application</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- PAYBULK ACTION BAR (Officer Verified)                        --}}
        {{-- ============================================================ --}}
        @if ($showPaybulkAction)
            <div class="col-xl-12">
                <div class="ipv-actions-bar">
                    <div class="ipv-actions-bar-text">
                        <i class="bi bi-info-circle"></i>
                        <span data-en="Your application has been verified. Please proceed to payment."
                              data-bm="Permohonan anda telah disahkan. Sila teruskan ke pembayaran.">
                            Your application has been verified. Please proceed to payment.
                        </span>
                    </div>
                    <div class="ipv-actions-bar-buttons">
                        <a href="#"
                           class="ipv-btn-action btn-primary" id="payBulkUser">
                            <i class="bi bi-credit-card"></i>
                            <span data-en="Go to Payment" data-bm="Pergi ke Pembayaran">Go to Payment</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- ============================================================ -->
        <!-- LEFT: Sidebar -->
        <!-- ============================================================ -->
        <div class="col-xl-4 col-lg-5" style="height: fit-content">
            <div class="ipv-side-card">

                <div class="ipv-tags" id="ipvTags"></div>

                <div class="ipv-app-type" id="ipvAppType" data-en="Consignment Certificate" data-bm="Sijil Konsainan">
                    Consignment Certificate</div>
                <div class="ipv-app-id" id="ipvAppId">—</div>
                <div class="ipv-submitted-by">
                    <i class="bi bi-person-circle"></i>
                    <span id="ipvSubmittedBy">—</span>
                </div>

                <div class="ipv-action-row">
                    @if ($isInternal && $application->status == 'Completed'  && auth()->guard('internal')->user()->can('print permit'))
                        <button type="button" class="ipv-btn-primary btn-info" id="ipvPrintPermitBtn">
                            <i class="bi bi-printer"></i> <span data-en="Print Certificate" data-bm="Cetak Sijil">Print
                                Certificate</span>
                        </button>
                    @endif
                    <span class="ipv-download-badge" id="ipvDownloadBadge" title="Certificates downloaded">
                        <i class="bi bi-download"></i> {{ $application->printCalc }}
                    </span>

                    <button class="btn ipv-btn-primary btn-secondary" id="printApplication" 
                    data-type="{{ $application->type }}" data-application="{{ $application->application_id }}">
                        <i class="fa-solid fa-print"></i>  <span data-en='Print Application' data-bm="Cetak Permohonan">Print Application</span> 
                    </button>
                </div>

                <div class="ipv-value-box">
                    <div>
                        <div class="ipv-value-label" data-en="Total Consignment Value" data-bm="Jumlah Nilai Konsainan">
                            Total Consignment Application</div>
                        <div class="ipv-value-amount" id="ipvTotalValue">RM 0.00</div>
                    </div>
                    <button type="button" class="ipv-value-link" id="ipvViewPermitsLink" data-en="View"
                        data-bm="Lihat">View</button>
                </div>

                <div class="ipv-footer-note" id="ipvCreatedAt"></div>

                <div class="ipv-divider"></div>

                {{-- TODO: only render this block if your API's /consignment_application/{id}/data
                     response includes application-level `attachment`/`attachments`. The legacy
                     consignment_detail.js never populated one — confirm before shipping. --}}
                <div class="ipv-section-label-row">
                    <span class="ipv-section-label" data-en="Application Documents" data-bm="Dokumen Permohonan">Application
                        Documents</span>
                    <button type="button" class="ipv-download-all" id="ipvDownloadAllApp">
                        <i class="bi bi-download"></i> <span data-en="Download All" data-bm="Muat Turun Semua">Download
                            All</span>
                    </button>
                </div>
                <div class="ipv-attach-list" id="ipvAppAttachments"></div>

                <div class="ipv-divider"></div>

                {{-- ============================================================ --}}
                {{-- EDIT APPLICATION BUTTON – FIXED CONDITION                    --}}
                {{-- ============================================================ --}}
                @if (($application->status == 'Draft' || $application->status == 'Clerk Rejected') && $application->user_id === $authUuid)
                    <a class="ipv-btn-outline w-100 justify-content-center mt-3 btn btn-primary" id="editButton"
                        href="/edit_consignment/{{ $application->application_id }}">
                        <i class="bi bi-pencil"></i> <span data-en="Edit Application" data-bm="Kemaskini Permohonan">Edit
                            Application</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- RIGHT: Main panel -->
        <!-- ============================================================ -->
        <div class="col-xl-8 col-lg-7">
            <div class="ipv-main-card">

                <div class="ipv-status-header">
                    <div>
                        <span class="ipv-status-eyebrow" data-en="Application Type:" data-bm="Jenis Permohonan:">Application
                            Type:</span>
                        <strong data-en="Consignment Certificate" data-bm="Sijil Konsainan">Consignment
                            Certificate</strong>
                        <span class="ipv-status-sep">|</span>
                        <span class="ipv-status-eyebrow" data-en="Status:" data-bm="Status:">Status:</span>
                        <strong id="ipvStatusLabel">—</strong>
                    </div>
                    <div class="ipv-status-duration" id="ipvStatusDuration"></div>
                </div>

                <div class="ipv-stage-stepper" id="ipvStageStepper"></div>
                <div class="ipv-returned-note d-none" id="ipvReturnedNote"></div>

                {{-- ============================================================ --}}
                {{-- TAB NAVIGATION – with dynamic active class on Payment tab     --}}
                {{-- ============================================================ --}}
                <div class="ipv-tabnav" role="tablist">
                    {{-- Permits tab --}}
                    <button type="button" class="ipv-tabnav-item {{ $paymentTabActive ? '' : 'is-active' }}"
                            data-ipv-tab="permits" role="tab">
                        <span data-en="Item List" data-bm="Senarai Item">Item List</span>
                        <span class="ipv-tab-count" id="ipvPermitCount">0</span>
                    </button>

                    {{-- Application Prices tab --}}
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="application_prices" role="tab"
                            id="applicationPrices">
                        <span data-en="Consignment Application Prices" data-bm="Harga Permohonan Konsainan"
                            class="text-wrap">Consignment Application Prices</span>
                    </button>

                    {{-- Importer & Exporter tab --}}
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="importer_exporter" role="tab"
                            data-en="Importer & Exporter" data-bm="Pengimport & Pengeksport">
                        Importer & Exporter
                    </button>

                    {{-- Transportation Details tab --}}
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="transport" role="tab"
                            data-en="Transportation Details" data-bm="Butiran Pengangkutan">
                        Transportation Details
                    </button>

                    {{-- Payment tab (conditionally shown) --}}
                    @if ($showPaymentTab)
                        <button type="button" class="ipv-tabnav-item {{ $paymentTabActive ? 'is-active' : '' }}"
                                data-ipv-tab="payment" role="tab">
                            <span data-en="Pending Payment" data-bm="Pembayaran Tertangguh">Pending Payment</span>
                            <span class="ipv-tab-count" id="ipvPendingPaymentCount"></span>
                        </button>
                    @endif

                    {{-- Activity tab --}}
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="activity" role="tab"
                            data-en="Activity" data-bm="Aktiviti">
                        Activity
                    </button>
                </div>

                {{-- ============================================================ --}}
                {{-- TAB PANES – with dynamic active class on Payment pane        --}}
                {{-- ============================================================ --}}
                <div class="ipv-tabbody">

                    {{-- Permits pane --}}
                    <div class="ipv-tabpane {{ $paymentTabActive ? '' : 'is-active' }}"
                         data-ipv-pane="permits">
                        <div class="ipv-permit-accordion" id="ipvPermitAccordion"></div>
                    </div>

                    {{-- Application Prices pane --}}
                    <div class="ipv-tabpane" data-ipv-pane="application_prices">
                        <div id="categoryTable"></div>
                    </div>

                    {{-- Importer & Exporter pane --}}
                    <div class="ipv-tabpane" data-ipv-pane="importer_exporter">
                        <div id="importerExporterDetails">
                            <div class="ipv-section-label" data-en="Importer & Exporter Details"
                                data-bm="Butiran Pengimport & Pengeksport">Importer & Exporter Details</div>

                            {{-- In Consignment: "Importer" block = the overseas importer_detail,
                                 "Exporter" block = the local applicant (exporter). --}}
                            <div class="ipv-party" id="ipvImporterBlock"></div>
                            <div class="ipv-party" id="ipvExporterBlock"></div>

                            <div class="ipv-divider"></div>
                        </div>
                    </div>

                    {{-- Transportation pane --}}
                    <div class="ipv-tabpane" data-ipv-pane="transport">
                        <div id="ipvTransportDetails"></div>
                    </div>

                    {{-- Payment pane (conditionally shown) --}}
                    @if ($showPaymentTab)
                        <div class="ipv-tabpane {{ $paymentTabActive ? 'is-active' : '' }}"
                             data-ipv-pane="payment">
                            <div class="table-responsive">
                                <table id="summaryTable4" class="table ipv-payment-table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col" data-en="Category" data-bm="Kategori">Category</th>
                                            <th scope="col" data-en="Quantity" data-bm="Kuantiti">Quantity</th>
                                            <th scope="col" class="text-end" data-en="Price (RM)"
                                                data-bm="Harga (RM)">Price (RM)</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold" data-en="Total:"
                                                data-bm="Jumlah:">Total:</td>
                                            <td class="text-end fw-bold" id="totalPaymentValue">RM 0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="ipv-checkout-bar">
                                <button class="ipv-btn-primary" id="checkoutPage" disabled>
                                    <i class="bi bi-credit-card"></i>
                                    <span data-en="Go To Checkout" data-bm="Pergi Ke Bayaran">Go To Checkout</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Activity pane --}}
                    <div class="ipv-tabpane" data-ipv-pane="activity">
                        <div class="ipv-timeline" id="ipvActivityTimeline"></div>
                    </div>

                </div> <!-- end ipv-tabbody -->
            </div> <!-- end ipv-main-card -->
        </div> <!-- end col -->

    </div> <!-- end ipv-wrapper -->

    <!-- ============================================================ -->
    <!-- ATTACHMENT VIEWER OFFCANVAS (Vertical Tabs)                   -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="attachmentOffcanvas"
        aria-labelledby="attachmentOffcanvasLabel" style="z-index: 1046;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="attachmentOffcanvasLabel">
                <i class="bi bi-paperclip me-2"></i> <span id="attachmentTitle" data-en="Attachment"
                    data-bm="Lampiran">Attachment</span>
            </h5>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-sm btn-outline-secondary" id="attachmentPrevBtn" title="Previous">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="badge bg-light text-dark" id="attachmentCounter">1 / 1</span>
                <button class="btn btn-sm btn-outline-secondary" id="attachmentNextBtn" title="Next">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" class="btn-close attachment-close" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 60px);">
            <div class="pd-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" id="attachmentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="attach-view-tab" data-bs-toggle="tab"
                            data-bs-target="#attach-view" type="button" role="tab" aria-selected="true"
                            data-bs-placement="right" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attach-details-tab" data-bs-toggle="tab"
                            data-bs-target="#attach-details" type="button" role="tab" aria-selected="false"
                            data-bs-placement="right" title="Details">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content flex-grow-1 p-3 overflow-auto" id="attachmentTabContent">
                <div class="tab-pane fade show active" id="attach-view" role="tabpanel">
                    <div id="attachmentViewer">
                        <div class="text-muted">
                            <i class="bi bi-file-earmark-fill fs-1"></i>
                            <br><span data-en="Select an attachment" data-bm="Pilih lampiran">Select an attachment</span>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="attach-details" role="tabpanel">
                    <div id="attachmentDetails" class="py-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- CERTIFICATE LIST OFFCANVAS (Download Certificates)             -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="permitListOffcanvas"
        aria-labelledby="permitListOffcanvasLabel" style="width: 60%; max-width: 800px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="permitListOffcanvasLabel">
                <i class="bi bi-file-earmark-pdf me-2"></i>
                <span data-en="Available Certificates" data-bm="Sijil Tersedia">Available Certificates</span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="permitDownloadTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" id="selectAllPermits"
                                    class="form-check-input"></th>
                            <th data-en="Permit Number" data-bm="Nombor Permit">Permit Number</th>
                            <th data-en="Item" data-bm="Item">Item</th>
                            <th data-en="Status" data-bm="Status">Status</th>
                            <th style="text-align: right;" data-en="Action" data-bm="Tindakan">Action</th>
                        </tr>
                    </thead>
                    <tbody id="permitDownloadTableBody"></tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="offcanvas" data-en="Close"
                    data-bm="Tutup">Close</button>
                <button class="btn btn-sm btn-primary" id="downloadSelectedPermitsBtn" disabled>
                    <i class="bi bi-download me-1"></i>
                    <span data-en="Download Selected" data-bm="Muat Turun Dipilih">Download Selected</span>
                    (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- CERTIFICATE DETAIL OFFCANVAS                                   -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="permitDetailOffcanvas"
        aria-labelledby="permitDetailOffcanvasLabel" style=" ">
        <div class="offcanvas-header border-bottom px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="ipv-permit-detail-icon"><i class="bi bi-box-seam"></i></div>
                <div class="d-flex justify-content-between gap-5">
                    <div>
                        <div class="ipv-permit-detail-eyebrow" data-en="Certificate Details" data-bm="Butiran Sijil">
                            Certificate Details</div>
                        <h5 class="offcanvas-title mb-0 fw-bold" id="permitDetailOffcanvasLabel">—</h5>
                    </div>
                    <span class="ipv-badge ms-2" id="pdBadge">—</span>
                </div>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 68px); overflow: hidden;">
            <div class="pd-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" id="permitDetailTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pd-details-tab" data-bs-toggle="tab" data-bs-target="#pd-details"
                            type="button" role="tab" data-bs-placement="right" title="Details">
                            <i class="bi bi-file-text"></i>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pd-activity-tab" data-bs-toggle="tab" data-bs-target="#pd-activity"
                            type="button" role="tab" data-bs-placement="right" title="Activity Log">
                            <i class="bi bi-clock-history"></i>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content flex-grow-1 overflow-auto" id="permitDetailTabContent">
                <div class="tab-pane fade show active p-4" id="pd-details" role="tabpanel">
                    <div id="pdDetailsContent"></div>
                </div>
                <div class="tab-pane fade p-4" id="pd-activity" role="tabpanel">
                    <div class="ipv-timeline" id="pdActivityTimeline"></div>
                </div>
            </div>
        </div>
    </div>

    <x-modal id="consignmentModal" title="">
        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close"
                data-bm="Tutup">Close</button>
        @endslot
    </x-modal>

    <x-modal id="activityLogModal" title="Activity Log">
        <div class="table-responsive scroll-div" style="max-height: 400px;">
            <table class="table text-wrap table-hover" id="applicationLogTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" data-en="Action" data-bm="Tindakan">Action</th>
                        <th scope="col" data-en="User" data-bm="Pengguna">User</th>
                        <th scope="col" data-en="Remark" data-bm="Catatan">Remark</th>
                        <th scope="col" data-en="Status" data-bm="Status">Status</th>
                        <th scope="col" data-en="Time and Date" data-bm="Masa dan Tarikh">Time and Date</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider"></tbody>
            </table>
        </div>
        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close"
                data-bm="Tutup">Close</button>
        @endslot
    </x-modal>

    {{-- Reapply modal — needed by consignment-actions.js's reapply() flow.
         Kept from the original wizard blade: controller must still pass
         $pubmeasure and $pubpurpose. --}}
    <div class="modal fade" id="addItemModal" tabindex="-1" data-bs-focus="false">
        <form class="modal-dialog modal-fullscreen">
            <input type="hidden" name="permit_id" value="permit_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addExporterModalLabel" data-en="Reapply" data-bm="Mohon Semula">Reapply
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-4 mb-3 p-4">
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="itemSelect" class="form-label" data-en="Item" data-bm="Item">Item</label>
                            <select class="form-select" id="itemSelect" name="itemSelect"></select>
                            <small style="color:red" data-en="Item refering to the exporter's Country"
                                data-bm="Item merujuk kepada Negara pengeksport">Item refering to the exporter's
                                Country</small>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="itemValue" class="form-label" data-en="Value (RM)" data-bm="Nilai (RM)">Value
                                (RM)</label>
                            <input type="number" class="form-control" id="itemValue" name="itemValue"
                                placeholder="RM ..." data-en="RM ..." data-bm="RM ..." data-i18n-attr="placeholder">
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="itemQuantity" class="form-label" data-en="Quantity"
                                data-bm="Kuantiti">Quantity</label>
                            <input type="number" class="form-control" id="itemQuantity" name="itemQuantity"
                                placeholder="0" data-en="0" data-bm="0" data-i18n-attr="placeholder">
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="itemMeasure" class="form-label" data-en="Measurement Unit"
                                data-bm="Unit Ukuran">Measurement Unit</label>
                            <select class="form-select" id="itemMeasure" name="itemMeasure">
                                <option value="" data-en="-- Select Measurement Unit --"
                                    data-bm="-- Pilih Unit Ukuran --">-- Select Measurement Unit --</option>
                                @foreach ($pubmeasure ?? [] as $measure)
                                    <option value="{{ $measure->cate_code }}">{{ $measure->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="itemPurpose" class="form-label" data-en="Purpose"
                                data-bm="Tujuan">Purpose</label>
                            <select class="form-select" id="itemPurpose" name="itemPurpose">
                                <option value="" data-en="-- Select Purpose --" data-bm="-- Pilih Tujuan --">--
                                    Select Purpose --</option>
                                @foreach ($pubpurpose ?? [] as $purpose)
                                    <option value="{{ $purpose->cate_code }}"
                                        data-description="{{ $purpose->description }}">{{ $purpose->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <label for="itemUses" class="form-label" data-en="Uses" data-bm="Kegunaan">Uses</label>
                            <select class="form-select" id="itemUses" name="itemUses"></select>
                        </div>
                        <div class="row gy-4">
                            <div class="col-xl-12">
                                <div class="card-header">
                                    <div class="card-title" data-en="Attachment" data-bm="Lampiran">Attachment</div>
                                </div>
                                <div class="card-body">
                                    <div id="itemDropzone" method="post" class="dz-clickable"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="dz-default dz-message">
                                            <button class="dz-button p-5 border w-100 border-radius" type="button">
                                                <span data-en="Drop files here to upload"
                                                    data-bm="Jatuhkan fail di sini untuk muat naik">Drop files here to
                                                    upload</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> <span data-en="Cancel" data-bm="Batal">Cancel</span>
                    </button>
                    <button id="saveBtn" type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> <span data-en="Reapply" data-bm="Mohon Semula">Reapply</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash === '#pending') {
                document.querySelector('.ipv-tabnav-item[data-ipv-tab="payment"]')?.click();
            }
        });
    </script>
@endpush