@extends('pages.app')

@section('pageName', 'Document Verification')

@push('scripts')
    @vite(['resources/js/pages/importPermit/clerkDocVerification.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        ['label' => 'Document Verification Queue', 'url' => '/internal/doc_verification'],
        ['label' => 'Application: ', 'url' => '#'],
    ]" title="Document Verification">
    </x-breadcrumb>
@endsection

@section('content')

    {{--
        Clerk — Document Verification
        ------------------------------
        Same layout/styling as the public View Application page
        (import_permit_view.blade.php), trimmed of the bits that don't
        make sense pre-approval (Print Permit / download permits —
        nothing's been issued yet at this stage), and with one thing
        added: the Clerk Verification panel, sitting right under the
        stage stepper.

        Decisions:
          - "Verify & Forward to Officer" is gated behind the 4
            checklist boxes — all of them must be ticked.
          - "Reject / Return for Amendment" is NOT gated by the
            checklist (a clerk can spot a problem and reject at any
            point) and opens a modal requiring a written remark.
            Confirming sets APPLICATION.status = 'returned', which
            reuses the existing returned-branch stepper styling and
            the ipv-returned-note banner — both already existed in
            the view page, just unused until now.

        Everything is dummy/client-only — see clerkDocVerification.js.

        Include with:
            @include('pages.internal.clerk_doc_verification')
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
                    <button type="button" class="ipv-icon-btn" title="More actions">
                        <i class="bi bi-three-dots"></i>
                    </button>
                </div>

                <div class="ipv-value-box">
                    <div>
                        <div class="ipv-value-label" data-bm="Jumlah Nilai Konsainan" data-en="Total Consignment Value">Total Consignment Value</div>
                        <div class="ipv-value-amount" id="ipvTotalValue">RM 0.00</div>
                    </div>
                    <button type="button" class="ipv-value-link" id="ipvViewPermitsLink" data-bm="Lihat" data-en="View">View</button>
                </div>

                <div class="ipv-divider"></div>

                <div class="ipv-section-label" data-bm="Butiran Pengimport &amp; Pengeksport" data-en="Importer &amp; Exporter Details">Importer &amp; Exporter Details</div>

                <div class="ipv-party" id="ipvImporterBlock"></div>
                <div class="ipv-party" id="ipvExporterBlock"></div>

                <div class="ipv-divider"></div>

                <div class="ipv-section-label-row">
                    <span class="ipv-section-label" data-bm="Dokumen Permohonan" data-en="Application Documents">Application Documents</span>
                    <button type="button" class="ipv-download-all" id="ipvDownloadAllApp">
                        <i class="bi bi-download"></i> <span data-bm="Muat Turun Semua" data-en="Download All">Download All</span>
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
                        <span class="ipv-status-eyebrow" data-bm="Jenis Permohonan:" data-en="Application Type:">Application Type:</span>
                        <strong data-bm="Permit Import" data-en="Import Permit">Import Permit</strong>
                        <span class="ipv-status-sep">|</span>
                        <span class="ipv-status-eyebrow" data-bm="Status:" data-en="Status:">Status:</span>
                        <strong id="ipvStatusLabel">—</strong>
                    </div>
                    <div class="ipv-status-duration" id="ipvStatusDuration"></div>
                </div>

                <!-- Stage stepper -->
                <div class="ipv-stage-stepper" id="ipvStageStepper"></div>
                <div class="ipv-returned-note d-none" id="ipvReturnedNote"></div>

                <!-- ============================================================ -->
                <!-- CLERK VERIFICATION PANEL                                      -->
                <!-- ============================================================ -->
                <div class="ipv-clerk-panel" id="ipvClerkPanel">
                    <div class="ipv-clerk-panel-header">
                        <i class="bi bi-patch-check"></i>
                        <div>
                            <div class="ipv-clerk-panel-title" data-bm="Pengesahan Dokumen" data-en="Document Verification">Document Verification</div>
                            <div class="ipv-clerk-panel-sub" data-bm="Sahkan item di bawah sebelum memajukan permohonan ini untuk Semakan Teknikal." data-en="Confirm the items below before forwarding this application to Technical Review.">
                                Confirm the items below before forwarding this application to Technical Review.
                            </div>
                        </div>
                    </div>

                    <div class="ipv-clerk-checklist">
                        <label class="ipv-clerk-check-item">
                            <input type="checkbox" data-clerk-check>
                            <span data-bm="Butiran borang permohonan adalah lengkap dan konsisten" data-en="Application form details are complete and consistent">Application form details are complete and consistent</span>
                        </label>
                        <label class="ipv-clerk-check-item">
                            <input type="checkbox" data-clerk-check>
                            <span data-bm="Butiran Pengimport &amp; Pengeksport telah disahkan" data-en="Importer &amp; Exporter details have been verified">Importer &amp; Exporter details have been verified</span>
                        </label>
                        <label class="ipv-clerk-check-item">
                            <input type="checkbox" data-clerk-check>
                            <span data-bm="Semua dokumen permohonan yang diperlukan dilampirkan" data-en="All required application documents are attached">All required application documents are attached</span>
                        </label>
                        <label class="ipv-clerk-check-item">
                            <input type="checkbox" data-clerk-check>
                            <span data-bm="Lampiran item permit dilampirkan dan boleh dibaca" data-en="Permit item attachments are attached and legible">Permit item attachments are attached and legible</span>
                        </label>
                    </div>

                    <div class="ipv-clerk-actions">
                        <button type="button" class="ipv-btn-reject" id="ipvRejectBtn">
                            <i class="bi bi-x-circle"></i> Reject / Return for Amendment
                        </button>
                        <button type="button" class="ipv-btn-verify" id="ipvVerifyBtn" disabled>
                            <i class="bi bi-check-circle"></i> <span data-bm="Sahkan &amp; Maju ke Pegawai" data-en="Verify &amp; Forward to Officer">Verify &amp; Forward to Officer</span>
                        </button>
                    </div>
                </div>

                <!-- Clerk / SLA row -->
                <div class="ipv-info-row">
                    <div class="ipv-info-item">
                        <div class="ipv-info-icon"><i class="bi bi-person-badge"></i></div>
                        <div>
                            <div class="ipv-info-label" data-bm="Kerani Ditugaskan" data-en="Assigned Clerk">Assigned Clerk</div>
                            <div class="ipv-info-value" id="ipvAssignedOfficer">—</div>
                        </div>
                        <button type="button" class="ipv-info-link" data-bm="Tugas Semula" data-en="Reassign">Reassign</button>
                    </div>
                    <div class="ipv-info-item">
                        <div class="ipv-info-icon"><i class="bi bi-hourglass-split"></i></div>
                        <div>
                            <div class="ipv-info-label" data-bm="Tindakan Seterusnya / SLA" data-en="Next Action / SLA">Next Action / SLA</div>
                            <div class="ipv-info-value" id="ipvSlaDue">—</div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="ipv-tabnav" role="tablist">
                    <button type="button" class="ipv-tabnav-item is-active" data-ipv-tab="permits" role="tab">
                        <span data-bm="Senarai Permit" data-en="Permit List">Permit List</span> <span class="ipv-tab-count" id="ipvPermitCount">0</span>
                    </button>
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="transport" role="tab">
                        Butiran Pengangkutan
                    </button>
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="condition" role="tab">
                        Syarat
                    </button>
                    <button type="button" class="ipv-tabnav-item" data-ipv-tab="activity" role="tab">
                        Aktiviti
                    </button>
                </div>

                <div class="ipv-tabbody">

                    <!-- Permit List -->
                    <div class="ipv-tabpane is-active" data-ipv-pane="permits">
                        <div class="ipv-permit-accordion" id="ipvPermitAccordion"></div>
                    </div>

                    <!-- Butiran Pengangkutan -->
                    <div class="ipv-tabpane" data-ipv-pane="transport">
                        <div id="ipvTransportDetails"></div>
                    </div>

                    <!-- Syarat -->
                    <div class="ipv-tabpane" data-ipv-pane="condition">
                        <div id="ipvSyaratList"></div>
                    </div>

                    <!-- Aktiviti -->
                    <div class="ipv-tabpane" data-ipv-pane="activity">
                        <div class="ipv-timeline" id="ipvAktivitiTimeline"></div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================ -->
    <!-- Reject / Return for Amendment popup                           -->
    <!-- ============================================================ -->
    <div class="modal fade" id="ipvRejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ipv-reject-modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-return-left me-2"></i><span data-bm="Kembalikan Permohonan untuk Pindaan" data-en="Return Application for Amendment">Return Application for Amendment</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="ipv-reject-modal-intro" data-bm="Beri tahu pemohon apa yang perlu diperbetulkan. Pengesahan akan menetapkan status permohonan kepada Dikembalikan / Ditolak dan menghantar nota di bawah kepada mereka." data-en="Let the applicant know what needs to be corrected. Confirming will set the application status to Returned / Rejected and send the note below back to them.">
                        Let the applicant know what needs to be corrected. Confirming will set the
                        application status to <strong>Returned / Rejected</strong> and send the
                        note below back to them.
                    </p>

                    <div class="ipv-reject-quick-reasons">
                        <button type="button" class="ipv-reject-chip" data-reason="One or more required attachments are missing or unreadable. Please re-upload clear copies.">
                            Lampiran tiada/tidak boleh dibaca
                        </button>
                        <button type="button" class="ipv-reject-chip" data-reason="Importer or exporter details do not match the submitted documents. Please review and correct.">
                            Butiran pihak tidak sepadan
                        </button>
                        <button type="button" class="ipv-reject-chip" data-reason="Declared quantity or value does not match the supporting invoice. Please verify and resubmit.">
                            Kuantiti/nilai tidak sepadan
                        </button>
                    </div>

                    <label class="ipv-reject-label" for="ipvRejectReason"><span data-bm="Nota kepada pemohon" data-en="Remark to applicant">Remark to applicant</span> <span class="ipv-required">*</span></label>
                    <textarea class="ipv-reject-textarea" id="ipvRejectReason" rows="4"
                        placeholder="Describe what the applicant needs to fix before resubmitting..." data-i18n-attr="placeholder" data-bm="Terangkan apa yang perlu diperbaiki oleh pemohon sebelum menghantar semula..." data-en="Describe what the applicant needs to fix before resubmitting..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ipv-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="ipv-btn-reject-confirm" id="ipvRejectConfirmBtn" disabled>
                        Sahkan Penolakan
                    </button>
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
                <i class="bi bi-paperclip me-2"></i> <span id="attachmentTitle" data-bm="Lampiran" data-en="Attachment">Attachment</span>
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
                            data-bs-target="#attach-view" type="button" role="tab" aria-selected="true" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attach-details-tab" data-bs-toggle="tab"
                            data-bs-target="#attach-details" type="button" role="tab" aria-selected="false" title="Details">
                            <i class="bi bi-info-circle"></i>
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
                        <div class="text-muted"><i class="bi bi-file-earmark-fill fs-1"></i><br>Pilih satu lampiran</div>
                    </div>
                </div>
                <!-- Details Tab -->
                <div class="tab-pane fade" id="attach-details" role="tabpanel">
                    <div id="attachmentDetails" class="py-2">
                        <!-- filled dynamically — includes the Mark as Verified toggle -->
                    </div>
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
                <div class="ipv-permit-detail-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="ipv-permit-detail-eyebrow" data-bm="Butiran Permit" data-en="Permit Details">Permit Details</div>
                    <h5 class="offcanvas-title mb-0 fw-bold" id="permitDetailOffcanvasLabel" data-bm="—" data-en="—">—</h5>
                </div>
                <span class="ipv-badge ms-2" id="pdBadge">—</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 68px); overflow: hidden;">
            <div class="pd-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" id="permitDetailTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pd-details-tab" data-bs-toggle="tab" data-bs-target="#pd-details"
                            type="button" role="tab" title="Details">
                            <i class="bi bi-file-text"></i>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content flex-grow-1 overflow-auto" id="permitDetailTabContent">
                <div class="tab-pane fade show active p-4" id="pd-details" role="tabpanel">
                    <div id="pdDetailsContent"></div>
                </div>
            </div>
        </div>
    </div>

@endsection