@extends('pages.app')

@section('pageName', 'Application Summary')

@push('scripts')
    @vite(['resources/js/pages/importPermit/applicationSummary.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        ['label' => 'Application List', 'url' => '/public/view_import_permit'],
        ['label' => 'New Application', 'url' => '/public/apply_import_permit'],
        ['label' => 'Summary & Declaration', 'url' => '#'],
    ]" title="Application Summary">
    </x-breadcrumb>
@endsection

@section('content')

    <div class="ips-wrapper">

        <!-- ============================================================ -->
        <!-- Page header                                                    -->
        <!-- ============================================================ -->
        <div class="ips-hero">
            <div class="ips-hero-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <div class="ips-hero-eyebrow">Import Permit Application</div>
                <h3 class="ips-hero-title">Review &amp; Declaration</h3>
                <p class="ips-hero-sub">
                    Please review all details carefully before submitting. Once submitted, your
                    application will be forwarded to the clerk for document verification.
                </p>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Application reference + status strip                          -->
        <!-- ============================================================ -->
        <div class="ips-ref-strip">
            <div class="ips-ref-cell">
                <div class="ips-ref-label">Draft Reference</div>
                <div class="ips-ref-value" id="ipsRefId">—</div>
            </div>
            <div class="ips-ref-cell">
                <div class="ips-ref-label">Prepared By</div>
                <div class="ips-ref-value" id="ipsRefPreparedBy">—</div>
            </div>
            <div class="ips-ref-cell">
                <div class="ips-ref-label">Date Prepared</div>
                <div class="ips-ref-value" id="ipsRefDate">—</div>
            </div>
            <div class="ips-ref-cell">
                <div class="ips-ref-label">Total Items</div>
                <div class="ips-ref-value" id="ipsRefItemCount">—</div>
            </div>
            <div class="ips-ref-cell">
                <div class="ips-ref-label">Total Declared Value</div>
                <div class="ips-ref-value" id="ipsRefTotalValue">—</div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Section 1 — Transportation Details                            -->
        <!-- ============================================================ -->
        <div class="ips-card">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-truck"></i></div>
                <div>
                    <div class="ips-card-title">Transportation Details</div>
                    <div class="ips-card-sub">Entry route and logistics information</div>
                </div>
            </div>
            <div class="ips-info-grid" id="ipsTransportGrid"></div>
        </div>

        <!-- ============================================================ -->
        <!-- Section 2 — Importer & Exporter                               -->
        <!-- ============================================================ -->
        <div class="ips-card">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-people"></i></div>
                <div>
                    <div class="ips-card-title">Importer &amp; Exporter Details</div>
                    <div class="ips-card-sub">Parties involved in this consignment</div>
                </div>
            </div>
            <div class="ips-party-row">
                <div class="ips-party-block" id="ipsImporterBlock"></div>
                <div class="ips-party-divider"></div>
                <div class="ips-party-block" id="ipsExporterBlock"></div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Section 3 — Permit Items                                      -->
        <!-- ============================================================ -->
        <div class="ips-card">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-list-check"></i></div>
                <div>
                    <div class="ips-card-title">Permit Items</div>
                    <div class="ips-card-sub" id="ipsItemsSubtitle">—</div>
                </div>
            </div>
            <div id="ipsItemsAccordion"></div>
        </div>

        <!-- ============================================================ -->
        <!-- Section 4 — Application Documents                             -->
        <!-- ============================================================ -->
        <div class="ips-card">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-paperclip"></i></div>
                <div>
                    <div class="ips-card-title">Application Documents</div>
                    <div class="ips-card-sub">Supporting documents for the overall application</div>
                </div>
            </div>
            <div id="ipsAppDocs"></div>
        </div>

        <!-- ============================================================ -->
        <!-- Section 5 — Jabatan Pertanian Contact                         -->
        <!-- ============================================================ -->
        <div class="ips-card ips-contact-card">
            <div class="ips-card-head">
                <div class="ips-card-head-icon is-teal"><i class="bi bi-building"></i></div>
                <div>
                    <div class="ips-card-title">Submitting To</div>
                    <div class="ips-card-sub">Your application will be processed by</div>
                </div>
            </div>
            <div class="ips-contact-body">
                <div class="ips-contact-name">Jabatan Pertanian Sabah</div>
                <div class="ips-contact-rows">
                    <div class="ips-contact-row">
                        <div class="ips-contact-icon"><i class="bi bi-geo-alt"></i></div>
                        <div>
                            <div class="ips-contact-label">Address</div>
                            <div class="ips-contact-value">
                                Wisma Pertanian, Jalan Tasik, Beg Berkunci No. 2050,<br>
                                88632 Kota Kinabalu, Sabah
                            </div>
                        </div>
                        <a href="https://www.google.com/maps/search/Wisma+Pertanian+Jalan+Tasik+Kota+Kinabalu"
                           target="_blank" class="ips-contact-map-link" title="Open in Google Maps">
                            <i class="bi bi-box-arrow-up-right"></i> Map
                        </a>
                    </div>
                    <div class="ips-contact-row">
                        <div class="ips-contact-icon"><i class="bi bi-telephone"></i></div>
                        <div>
                            <div class="ips-contact-label">Phone</div>
                            <div class="ips-contact-value">
                                <a href="tel:+6088211736">(088) 211 736</a>
                            </div>
                        </div>
                    </div>
                    <div class="ips-contact-row">
                        <div class="ips-contact-icon"><i class="bi bi-clock"></i></div>
                        <div>
                            <div class="ips-contact-label">Office Hours</div>
                            <div class="ips-contact-value">Monday – Friday, 8:00 AM – 5:00 PM</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Declaration & Agreement                                        -->
        <!-- ============================================================ -->
        <div class="ips-declaration-card" id="ipsDeclarationCard">
            <div class="ips-declaration-head">
                <i class="bi bi-shield-check ips-declaration-shield"></i>
                <div>
                    <div class="ips-declaration-title">Declaration by Applicant</div>
                    <div class="ips-declaration-sub">
                        You must read and agree to all statements before submitting.
                    </div>
                </div>
            </div>

            <div class="ips-declaration-statements">

                <label class="ips-decl-item" id="ipsDeclAccuracy">
                    <input type="checkbox" class="ips-decl-check" data-decl="accuracy">
                    <div class="ips-decl-body">
                        <div class="ips-decl-label">Accuracy of Information</div>
                        <div class="ips-decl-text">
                            I declare that all information provided in this application — including
                            importer and exporter details, item descriptions, quantities, and declared
                            values — is true, accurate, and complete to the best of my knowledge.
                        </div>
                    </div>
                </label>

                <label class="ips-decl-item" id="ipsDeclConditions">
                    <input type="checkbox" class="ips-decl-check" data-decl="conditions">
                    <div class="ips-decl-body">
                        <div class="ips-decl-label">Import Conditions Acknowledged</div>
                        <div class="ips-decl-text">
                            I confirm that I have read and understood all import conditions
                            applicable to each item listed in this application, as presented during
                            the item entry process. I agree to comply with all stated conditions.
                        </div>
                    </div>
                </label>

                <label class="ips-decl-item" id="ipsDeclDocs">
                    <input type="checkbox" class="ips-decl-check" data-decl="docs">
                    <div class="ips-decl-body">
                        <div class="ips-decl-label">Documents are Authentic</div>
                        <div class="ips-decl-text">
                            I certify that all supporting documents uploaded with this application
                            are genuine, unaltered, and issued by the appropriate authorities.
                            Submission of falsified documents may result in rejection and legal action.
                        </div>
                    </div>
                </label>

                <label class="ips-decl-item" id="ipsDeclAuthority">
                    <input type="checkbox" class="ips-decl-check" data-decl="authority">
                    <div class="ips-decl-body">
                        <div class="ips-decl-label">Authority to Apply</div>
                        <div class="ips-decl-text">
                            I confirm that I am authorised to submit this application on behalf of
                            the importer named in this form, and that the importer holds all
                            necessary licences and permits required for the importation of the
                            listed items.
                        </div>
                    </div>
                </label>

            </div>

            <div class="ips-decl-progress-row">
                <div class="ips-decl-progress-track">
                    <div class="ips-decl-progress-fill" id="ipsDeclProgressFill" style="width:0%"></div>
                </div>
                <div class="ips-decl-progress-label" id="ipsDeclProgressLabel">0 of 4 confirmed</div>
            </div>

        </div>

        <!-- ============================================================ -->
        <!-- Footer actions                                                 -->
        <!-- ============================================================ -->
        <div class="ips-footer">
            <a href="/public/apply_import_permit" class="ips-btn-back">
                <i class="bi bi-arrow-left"></i> Back &amp; Edit
            </a>
            <div class="ips-footer-right">
                <div class="ips-submit-hint" id="ipsSubmitHint">
                    Please confirm all 4 declarations above to submit.
                </div>
                <button type="button" class="ips-btn-submit" id="ipsSubmitBtn" disabled>
                    <i class="bi bi-send"></i> Submit Application
                </button>
            </div>
        </div>

    </div>

@endsection