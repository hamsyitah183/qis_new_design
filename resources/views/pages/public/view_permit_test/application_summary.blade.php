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

    <div class="ips-wrapper apy-wrapper">

        <!-- ============================================================ -->
        <!-- Page header                                                    -->
        <!-- ============================================================ -->
        <div class="ips-hero">
            <div class="ips-hero-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <div class="ips-hero-eyebrow" data-bm="Permohonan Permit Import" data-en="Import Permit Application">Import Permit Application</div>
                <h3 class="ips-hero-title" data-bm="Semakan &amp; Pengakuan" data-en="Review &amp; Declaration">Review &amp; Declaration</h3>
                <p class="ips-hero-sub" data-bm="Sila semak semua butiran dengan teliti sebelum menghantar. Setelah dihantar, permohonan anda akan dimajukan kepada kerani untuk pengesahan dokumen." data-en="Please review all details carefully before submitting. Once submitted, your application will be forwarded to the clerk for document verification.">
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
                <div class="ips-ref-label" data-bm="Rujukan Draf" data-en="Draft Reference">Draft Reference</div>
                <div class="ips-ref-value" id="ipsRefId">—</div>
            </div>
            <div class="ips-ref-cell">
                <div class="ips-ref-label" data-bm="Disediakan Oleh" data-en="Prepared By">Prepared By</div>
                <div class="ips-ref-value" id="ipsRefPreparedBy">—</div>
            </div>
            <div class="ips-ref-cell">
                <div class="ips-ref-label" data-bm="Tarikh Disediakan" data-en="Date Prepared">Date Prepared</div>
                <div class="ips-ref-value" id="ipsRefDate">—</div>
            </div>
            <div class="ips-ref-cell">
                <div class="ips-ref-label" data-bm="Jumlah Item" data-en="Total Items">Total Items</div>
                <div class="ips-ref-value" id="ipsRefItemCount">—</div>
            </div>
            <div class="ips-ref-cell">
                <div class="ips-ref-label" data-bm="Jumlah Nilai Diisytiharkan" data-en="Total Declared Value">Total Declared Value</div>
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
                    <div class="ips-card-title" data-bm="Butiran Pengangkutan" data-en="Transportation Details">Transportation Details</div>
                    <div class="ips-card-sub" data-bm="Maklumat laluan masuk dan logistik" data-en="Entry route and logistics information">Entry route and logistics information</div>
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
                    <div class="ips-card-title" data-bm="Butiran Pengimport &amp; Pengeksport" data-en="Importer &amp; Exporter Details">Importer &amp; Exporter Details</div>
                    <div class="ips-card-sub" data-bm="Pihak yang terlibat dalam penghantaran ini" data-en="Parties involved in this consignment">Parties involved in this consignment</div>
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
                    <div class="ips-card-title" data-bm="Item Permit" data-en="Permit Items">Permit Items</div>
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
                    <div class="ips-card-title" data-bm="Dokumen Permohonan" data-en="Application Documents">Application Documents</div>
                    <div class="ips-card-sub" data-bm="Dokumen sokongan untuk keseluruhan permohonan" data-en="Supporting documents for the overall application">Supporting documents for the overall application</div>
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
                    <div class="ips-card-title" data-bm="Dihantar Kepada" data-en="Submitting To">Submitting To</div>
                    <div class="ips-card-sub" data-bm="Permohonan anda akan diproses oleh" data-en="Your application will be processed by">Your application will be processed by</div>
                </div>
            </div>
            <div class="ips-contact-body">
                <div class="ips-contact-name">Jabatan Pertanian Sabah</div>
                <div class="ips-contact-rows">
                    <div class="ips-contact-row">
                        <div class="ips-contact-icon"><i class="bi bi-geo-alt"></i></div>
                        <div>
                            <div class="ips-contact-label" data-bm="Alamat" data-en="Address">Address</div>
                            <div class="ips-contact-value">
                                Plant Biosecurity & Quarantine Division, Department of Agriculture Sabah,
                                Wisma Pertanian, Jalan Tasik, Beg Berkunci No. 2050,<br>
                                88632 Kota Kinabalu, Sabah
                            </div>
                        </div>
                        <a href="https://www.google.com/maps/search/Wisma+Pertanian+Jalan+Tasik+Kota+Kinabalu"
                           target="_blank" class="ips-contact-map-link" title="Open in Google Maps">
                            <i class="bi bi-box-arrow-up-right"></i> <span data-bm="Peta" data-en="Map">Map</span>
                        </a>
                    </div>
                    <div class="ips-contact-row">
                        <div class="ips-contact-icon"><i class="bi bi-telephone"></i></div>
                        <div>
                            <div class="ips-contact-label" data-bm="Telefon" data-en="Phone">Phone</div>
                            <div class="ips-contact-value">
                                <a href="tel:+6088211736">(088) 211 736</a>
                            </div>
                        </div>
                    </div>
                    <div class="ips-contact-row">
                        <div class="ips-contact-icon"><i class="bi bi-clock"></i></div>
                        <div>
                            <div class="ips-contact-label" data-bm="Waktu Pejabat" data-en="Office Hours">Office Hours</div>
                            <div class="ips-contact-value" data-bm="Isnin – Jumaat, 8:00 AM – 5:00 PM" data-en="Monday – Friday, 8:00 AM – 5:00 PM">Monday – Friday, 8:00 AM – 5:00 PM</div>
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
                    <div class="ips-declaration-title" data-bm="Pengakuan oleh Pemohon" data-en="Declaration by Applicant">Declaration by Applicant</div>
                    <div class="ips-declaration-sub" data-bm="Anda mesti membaca dan bersetuju dengan semua kenyataan sebelum menghantar." data-en="You must read and agree to all statements before submitting.">
                        You must read and agree to all statements before submitting.
                    </div>
                </div>
            </div>

            <div class="ips-declaration-statements">

                <label class="ips-decl-item" id="ipsDeclAccuracy">
                    <input type="checkbox" class="ips-decl-check" data-decl="accuracy">
                    <div class="ips-decl-body">
                        <div class="ips-decl-label" data-bm="Ketepatan Maklumat" data-en="Accuracy of Information">Accuracy of Information</div>
                        <div class="ips-decl-text" data-bm="Saya mengaku bahawa semua maklumat yang diberikan dalam permohonan ini — termasuk butiran pengimport dan pengeksport, penerangan item, kuantiti, dan nilai yang diisytiharkan — adalah benar, tepat, dan lengkap setahu saya." data-en="I declare that all information provided in this application — including importer and exporter details, item descriptions, quantities, and declared values — is true, accurate, and complete to the best of my knowledge.">
                            I declare that all information provided in this application — including
                            importer and exporter details, item descriptions, quantities, and declared
                            values — is true, accurate, and complete to the best of my knowledge.
                        </div>
                    </div>
                </label>

                <label class="ips-decl-item" id="ipsDeclConditions">
                    <input type="checkbox" class="ips-decl-check" data-decl="conditions">
                    <div class="ips-decl-body">
                        <div class="ips-decl-label" data-bm="Syarat Import Diakui" data-en="Import Conditions Acknowledged">Import Conditions Acknowledged</div>
                        <div class="ips-decl-text" data-bm="Saya mengesahkan bahawa saya telah membaca dan memahami semua syarat import yang terpakai bagi setiap item yang disenaraikan dalam permohonan ini, seperti yang ditunjukkan semasa proses kemasukan item. Saya bersetuju untuk mematuhi semua syarat yang dinyatakan." data-en="I confirm that I have read and understood all import conditions applicable to each item listed in this application, as presented during the item entry process. I agree to comply with all stated conditions.">
                            I confirm that I have read and understood all import conditions
                            applicable to each item listed in this application, as presented during
                            the item entry process. I agree to comply with all stated conditions.
                        </div>
                    </div>
                </label>

                <label class="ips-decl-item" id="ipsDeclDocs">
                    <input type="checkbox" class="ips-decl-check" data-decl="docs">
                    <div class="ips-decl-body">
                        <div class="ips-decl-label" data-bm="Dokumen adalah Sah" data-en="Documents are Authentic">Documents are Authentic</div>
                        <div class="ips-decl-text" data-bm="Saya mengesahkan bahawa semua dokumen sokongan yang dimuat naik bersama permohonan ini adalah tulen, tidak diubah, dan dikeluarkan oleh pihak berkuasa yang berkaitan. Penyerahan dokumen yang dipalsukan boleh menyebabkan penolakan dan tindakan undang-undang." data-en="I certify that all supporting documents uploaded with this application are genuine, unaltered, and issued by the appropriate authorities. Submission of falsified documents may result in rejection and legal action.">
                            I certify that all supporting documents uploaded with this application
                            are genuine, unaltered, and issued by the appropriate authorities.
                            Submission of falsified documents may result in rejection and legal action.
                        </div>
                    </div>
                </label>

                <label class="ips-decl-item" id="ipsDeclAuthority">
                    <input type="checkbox" class="ips-decl-check" data-decl="authority">
                    <div class="ips-decl-body">
                        <div class="ips-decl-label" data-bm="Kuasa untuk Memohon" data-en="Authority to Apply">Authority to Apply</div>
                        <div class="ips-decl-text" data-bm="Saya mengesahkan bahawa saya diberi kuasa untuk menghantar permohonan ini bagi pihak pengimport yang dinamakan dalam borang ini, dan bahawa pengimport memegang semua lesen dan permit yang diperlukan untuk mengimport item yang disenaraikan." data-en="I confirm that I am authorised to submit this application on behalf of the importer named in this form, and that the importer holds all necessary licences and permits required for the importation of the listed items.">
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
                <div class="ips-decl-progress-label" id="ipsDeclProgressLabel" data-bm="0 daripada 4 disahkan" data-en="0 of 4 confirmed">0 of 4 confirmed</div>
            </div>

        </div>

        <!-- ============================================================ -->
        <!-- Footer actions                                                 -->
        <!-- ============================================================ -->
        <div class="ips-footer">
            <a href="/public/apply_import_permit" class="ips-btn-back">
                <i class="bi bi-arrow-left"></i> <span data-bm="Kembali &amp; Sunting" data-en="Back &amp; Edit">Back &amp; Edit</span>
            </a>
            <div class="ips-footer-right">
                <div class="ips-submit-hint" id="ipsSubmitHint" data-bm="Sila sahkan semua 4 pengakuan di atas untuk menghantar." data-en="Please confirm all 4 declarations above to submit.">
                    Please confirm all 4 declarations above to submit.
                </div>
                <button type="button" class="ips-btn-submit" id="ipsSubmitBtn" disabled>
                    <i class="bi bi-send"></i> <span data-bm="Hantar Permohonan" data-en="Submit Application">Submit Application</span>
                </button>
            </div>
        </div>

    </div>

@endsection