@extends('pages.app')

@section('pageName', 'View Application')

@push('scripts')
    @vite(['resources/js/pages/inspection/inspection1.js', 'resources/js/pages/inspection/inspection2.js', 'resources/js/pages/inspection/inspection-actions.js'])
@endpush

@section('breadcrumb')
    @php
        use Illuminate\Support\Facades\Gate;
        $internalUser = auth('internal')->user();
        $isInternal = auth('internal')->check();
        // Kept as-is from the original blade — these were hardcoded URLs, not named routes.
        $applicationUrl = $isInternal
            ? '/internal/inspection_certificates_list'
            : '/public/inspection_certificates_list';
    @endphp

    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Papan Pemuka'],
        [
            'label' => 'Inspection Certificate List',
            'url' => $applicationUrl,
            'data-en' => 'Inspection Certificate List',
            'data-bm' => 'Senarai Sijil Pemeriksaan',
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

        $isPublic = auth()->guard('public')->check();
        // Unlike Consignment (where roles are reversed), Inspection keeps the
        // original semantics: the applicant/owner is whoever submitted the
        // application (user_id) — same check the old blade used for the Edit
        // button.
        $isOwner = $isPublic && $application->user_id === $authUuid;

        $allPending = $application->inspectionItems->every(fn($permit) => $permit->status === 'pending for payment');

        $showPaymentTab = $isPublic && $application->user_id === $authUuid && $allPending;

        // [TODO] The old blade gated the clerk-review section purely on role
        // ($isAdminOrClerk), with no separate permission check — kept as-is
        // rather than inventing a permission name that may not exist for this
        // guard. If Inspection later gets its own "approve application"
        // permission, add it here to match Consignment's stricter gating.
$showClerkReviewActions = $isAdminOrClerk && str_contains($status, 'clerk review in-progress');

// [TODO] Consignment has a separate "re-evaluate a rejected
// application" admin flow ($showAdminRejectedActions). Nothing in the
// old Inspection blade/JS evidenced an equivalent — omitted here. Add
// it back if Inspection gets the same feature.

// ---------- FIX: Edit Application permission check ----------
$canEditInternal = $isInternal && auth('internal')->user()->can('edit application');
    @endphp

    {{-- Feed real application context to inspection_detail.js instead of URL-parsing --}}
    <script>
        window.baseUrl = "{{ url('/') }}";
        window.APPLICATION_ID = "{{ $application->application_id }}";
        window.authUser = {
            type: "{{ $isInternal ? 'internal' : 'public' }}",
            roles: {!! json_encode(
                $isInternal ? auth('internal')->user()->roles->map(fn($r) => ['name' => $r->name])->values() : [],
            ) !!}
        };
    </script>

    <div class="ipv-wrapper row g-4">

        {{-- ============================================================ --}}
        {{-- PAYMENT AWARENESS BANNER --}}
        {{-- ============================================================ --}}
        <div class="col-xl-12" id="ipvPaymentBannerWrap" style="display:none">
            <div class="ipv-payment-banner" id="ipvPaymentBanner"></div>
        </div>

        {{-- ============================================================ --}}
        {{-- APPLICATION-LEVEL ACTIONS BAR --}}
        {{-- ============================================================ --}}
        @if ($showClerkReviewActions)
            <div class="col-xl-12">
                <div class="ipv-actions-bar" id= "ipvBulkActionsWrap">
                    <div class="ipv-actions-bar-text">
                        <i class="bi bi-info-circle"></i>
                        <span data-en="This application is awaiting your review."
                            data-bm="Permohonan ini sedang menunggu semakan anda.">This application is awaiting your
                            review.</span>
                    </div>
                    <div class="ipv-actions-bar-buttons">
                        <button id="acceptAppl" class="ipv-btn-action is-success">
                            <i class="bi bi-check-lg"></i> <span data-en="Accept Application"
                                data-bm="Terima Permohonan">Accept Application</span>
                        </button>
                        <button id="rejectAdminAppl" class="ipv-btn-action is-danger">
                            <i class="bi bi-x-lg"></i> <span data-en="Reject Application" data-bm="Tolak Permohonan">Reject
                                Application</span>
                        </button>
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

                <div class="ipv-app-type" id="ipvAppType" data-en="Inspection Certificate" data-bm="Sijil Pemeriksaan">
                    Inspection Certificate</div>
                <div class="ipv-app-id" id="ipvAppId">—</div>
                <div class="ipv-submitted-by">
                    <i class="bi bi-person-circle"></i>
                    <span id="ipvSubmittedBy">—</span>
                </div>

                <div class="ipv-action-row">
                    {{-- [TODO] Old blade had no dedicated print-permit permission check for
                         Inspection — reusing the same role-based gate as elsewhere on this
                         page rather than guessing a permission name. --}}

                    @if ($isAdminOrClerk)
                        <button type="button" class="ipv-btn-primary" id="ipvPrintPermitBtn">
                            <i class="bi bi-printer"></i> <span data-en="Print Certificate" data-bm="Cetak Sijil">Print
                                Certificate</span>
                        </button>
                    @endif
                    <span class="ipv-download-badge" id="ipvDownloadBadge" title="Certificates downloaded">
                        <i class="bi bi-download"></i> 0
                    </span>

                    <button class="btn ipv-btn-primary btn-secondary" id="printApplication"
                        data-type = "{{ $application->type }}" data-application = "{{ $application->application_id }}">
                        <i class="fa-solid fa-print"></i> <span data-en='Print Application' data-bm="Cetak Permohonan">Print
                            Application</span>
                    </button>
                </div>

                <div class="ipv-value-box d-none">
                    <div>
                        <div class="ipv-value-label" data-en="Total Inspection Value" data-bm="Jumlah Nilai Pemeriksaan">
                            Total Inspection Value</div>
                        <div class="ipv-value-amount" id="ipvTotalValue">RM 0.00</div>
                    </div>
                    <button type="button" class="ipv-value-link" id="ipvViewPermitsLink" data-en="View"
                        data-bm="Lihat">View</button>
                </div>

                <div class="ipv-footer-note" id="ipvCreatedAt"></div>

                <div class="ipv-divider"></div>

                {{-- [TODO] Same caveat as the Consignment blade: only render this block if
                     your API's /inspection_application/{id}/data response actually includes
                     an application-level `attachment`/`attachments` array. The legacy
                     inspection_detail.js never populated one. --}}
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
                @if ( ($application->status == 'Draft' || $application->status == 'Clerk Rejected') && $application->user_id === $authUuid) 
                    @if ($application->category_application == '0')
                        <a class="ipv-btn-outline w-100 justify-content-center mt-3 btn btn-primary" id="editButton"
                            href="{{ route('public.inspectionApplicationSelf', ['id' => $application->application_id]) }}">
                            <i class="bi bi-pencil"></i> <span data-en="Edit Application"
                                data-bm="Kemaskini Permohonan">Edit
                                Application</span>
                        </a>
                    @else
                        <a class="ipv-btn-outline w-100 justify-content-center mt-3 btn btn-primary" id="editButton"
                            href="{{ route('public.inspectionApplicationOthers', ['id' => $application->application_id]) }}">
                            <i class="bi bi-pencil"></i> <span data-en="Edit Application"
                                data-bm="Kemaskini Permohonan">Edit
                                Application</span>
                        </a>
                    @endif
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
                        <span class="ipv-status-eyebrow" data-en="Application Type:"
                            data-bm="Jenis Permohonan:">Application
                            Type:</span>
                        <strong data-en="Inspection Certificate" data-bm="Sijil Pemeriksaan">Inspection
                            Certificate</strong>
                        <span class="ipv-status-sep">|</span>
                        <span class="ipv-status-eyebrow" data-en="Status:" data-bm="Status:">Status:</span>
                        <strong id="ipvStatusLabel">—</strong>
                    </div>
                    <div class="ipv-status-duration" id="ipvStatusDuration"></div>
                </div>

                <div class="ipv-stage-stepper" id="ipvStageStepper"></div>
                <div class="ipv-returned-note d-none" id="ipvReturnedNote"></div>

                <div class="ipv-tabnav" role="tablist">
                    <button type="button" class="ipv-tabnav-item is-active" data-ipv-tab="permits" role="tab">
                        <span data-en="Certificate List" data-bm="Senarai Sijil">Certificate List</span> <span
                            class="ipv-tab-count" id="ipvPermitCount">0</span>
                    </button>
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="importer_exporter" role="tab"
                        data-en="Importer & Exporter" data-bm="Pengimport & Pengeksport">
                        Importer & Exporter
                    </button>
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="transport" role="tab"
                        data-en="Application Details" data-bm="Butiran Permohonan">
                        Application Details
                    </button>

                    @if ($showPaymentTab)
                        <button type="button" class="ipv-tabnav-item" data-ipv-tab="payment" role="tab">
                            <span data-en="Pending Payment" data-bm="Pembayaran Tertangguh">Pending Payment</span> <span
                                class="ipv-tab-count" id="ipvPendingPaymentCount">0</span>
                        </button>
                    @endif
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="activity" role="tab"
                        data-en="Activity" data-bm="Aktiviti">
                        Activity
                    </button>
                </div>

                <div class="ipv-tabbody">

                    <div class="ipv-tabpane is-active" data-ipv-pane="permits">
                        <div class="ipv-permit-accordion" id="ipvPermitAccordion"></div>
                    </div>

                    <div class="ipv-tabpane" data-ipv-pane="importer_exporter">
                        <div id="importerExporterDetails">
                            <div class="ipv-section-label" data-en="Importer & Exporter Details"
                                data-bm="Butiran Pengimport & Pengeksport">Importer & Exporter Details</div>

                            <div class="ipv-party" id="ipvImporterBlock"></div>
                            <div class="ipv-party" id="ipvExporterBlock"></div>

                            <div class="ipv-divider"></div>
                        </div>
                    </div>

                    <div class="ipv-tabpane" data-ipv-pane="transport">
                        <div id="ipvTransportDetails"></div>
                    </div>

                    @if ($showPaymentTab)
                        <div class="ipv-tabpane" data-ipv-pane="payment">
                            <div class="table-responsive">
                                <table id="summaryTable4" class="table ipv-payment-table text-nowrap">
                                    <thead>
                                        <tr>
                                            <th data-en="Permit Number" data-bm="Nombor Permit">Permit Number</th>
                                            <th data-en="Item Name" data-bm="Nama Item">Item Name</th>
                                            {{-- <th class="text-end" data-en="Value" data-bm="Nilai">Value</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot></tfoot>
                                </table>
                            </div>
                        </div>
                    @endif

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
    <!-- CERTIFICATE DETAIL OFFCANVAS                                   -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="permitDetailOffcanvas"
        aria-labelledby="permitDetailOffcanvasLabel">
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

    {{-- Reapply modal — needed by inspection_detail.js's reapply() flow.
         Controller must pass $pubmeasure and $pubpurpose (same as the old
         wizard's addItemModal / consignment's equivalent). --}}
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
                                        data-description="{{ $purpose->description }}">{{ $purpose->description }}
                                    </option>
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
