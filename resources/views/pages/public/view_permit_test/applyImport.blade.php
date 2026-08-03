@extends('pages.app')

@section('pageName', 'Apply Import Permit')

@push('scripts')
    @vite(['resources/js/pages/importPermit/applyImportPermit.js'])
    @vite(['resources/js/pages/importPermit/exporterImportPermit.js'])
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

    <div class="ipa-wrapper apy-wrapper">

        <!-- Header -->


        <div class="ips-hero">
            <div class="ips-hero-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <div class="ips-hero-eyebrow" data-bm="Permohonan Permit Import" data-en="Import Permit Application">Import Permit Application</div>
                <h3 class="ips-hero-title" data-bm="Permohonan" data-en="Application">Application</h3>
                <p class="ips-hero-sub text-wrap" data-bm="Kebenaran rasmi yang diberikan kepada pengimport untuk mengimport barangan pertanian terkawal ke Sabah." data-en="An official authorization granted to importers for the importation of regulated agricultural goods into Sabah.">
                    An official authorization granted to importers for the importation of regulated agricultural goods into
                    Sabah.
                </p>
            </div>
            <div class="ipa-draft-status" id="ipaDraftStatus">
                <span class="ipa-draft-dot is-unsaved"></span>
                <span data-bm="Belum disimpan" data-en="Not saved yet">Not saved yet</span>
            </div>
        </div>

        <!-- Transportation Details -->
        <div class="ipa-card">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-truck"></i></div>
                <div>
                    <div class="ips-card-title" data-bm="Butiran Pengangkutan" data-en="Transportation Details">Transportation Details</div>
                    <div class="ips-card-sub" data-bm="Maklumat laluan masuk dan logistik" data-en="Entry route and logistics information">Entry route and logistics information</div>
                </div>
            </div>
            <div class="ipa-form-grid">
                <div class="ipa-field">
                    <label><span data-bm="Masa Ketibaan" data-en="Estimated Time Arrival">Estimated Time Arrival</span> <span class="ipa-required">*</span></label>
                    <input type="date" class="ipa-input" id="ipaEta">
                </div>
                <div class="ipa-field">
                    <label><span data-bm="Jenis Pengangkutan" data-en="Transport Type">Transport Type</span> <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaTransportType">
                        <option value="" data-bm="-- Pilih jenis pengangkutan --" data-en="-- Select transport type --">-- Select transport type --</option>
                        <option data-bm="Pengangkutan Laut" data-en="Sea">Sea</option>
                        <option data-bm="Pengangkutan Udara" data-en="Air">Air</option>
                        <option data-bm="Pengangkutan Darat" data-en="Land / Road">Land / Road</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label><span data-bm="Tempat Masuk" data-en="Entry Point">Entry Point</span> <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaEntryPoint">
                        <option value="" data-bm="-- Pilih tempat masuk --" data-en="-- Select entry point --">-- Select entry point --</option>
                        <option data-bm="Pelabuhan Kota Kinabalu" data-en="Kota Kinabalu Port">Kota Kinabalu Port</option>
                        <option data-bm="Pelabuhan Sandakan" data-en="Sandakan Port">Sandakan Port</option>
                        <option data-bm="Pelabuhan Tawau" data-en="Tawau Port">Tawau Port</option>
                        <option data-bm="Lapangan Terbang Antarabangsa Kota Kinabalu" data-en="Kota Kinabalu International Airport">Kota Kinabalu International Airport</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Importer / Exporter Details -->
        <div class="ipa-card">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-people"></i></div>
                <div>
                    <div class="ips-card-title" data-bm="Butiran Pengimport &amp; Pengeksport" data-en="Importer &amp; Exporter Details">Importer &amp; Exporter Details</div>
                    <div class="ips-card-sub" data-bm="Pihak yang terlibat dalam penghantaran ini" data-en="Parties involved in this consignment">Parties involved in this consignment</div>
                </div>
            </div>

            <div class="ipa-form-grid">

                <!-- Importer -->
                <div class="ipa-field">
                    <label><span data-bm="Nama Pengimport" data-en="Importer">Importer</span> <span class="ipa-required">*</span></label>
                    <input type="text" id="importerName" class="ipa-input" value="Sabah Agro Trading Sdn Bhd" readonly>
                </div>

                <div class="ipa-field">
                    <label data-bm="Nombor Telefon Pengimport" data-en="Importer Phone">Importer Phone</label>
                    <input type="text" id="importerPhone" class="ipa-input" value="(088) 244 511" readonly>
                </div>

                <!-- Exporter -->
                <div class="ipa-field ipa-search-wrapper">

                    <label><span data-bm="Nama Pengeksport" data-en="Exporter">Exporter</span> <span class="ipa-required">*</span></label>

                    <input type="text" id="exporterSearch" class="ipa-input" autocomplete="off"
                        placeholder="Search exporter..." data-i18n-attr="placeholder" data-bm="Cari atau masukkan nama pengeksport" data-en="Search exporter...">

                    <input type="hidden" id="exporterId">

                    <div id="exporterSuggestion" class="ipa-search-result"></div>

                </div>

                <div class="ipa-field">
                    <label><span data-bm="Negara Pengeksport" data-en="Exporter Country">Exporter Country</span> <span class="ipa-required">*</span></label>

                    <input type="text" id="exporterCountry" class="ipa-input" readonly
                        placeholder="E.g Singapore" data-i18n-attr="placeholder" data-bm="cth. Singapura" data-en="E.g Singapore">
                </div>

               

            </div>
            <div class="ipa-field ipa-field-fullrow mt-3">
                <label><span data-bm="Alamat Pengeksport" data-en="Exporter Address">Exporter Address</span> <span class="ipa-required">*</span></label>
                <textarea class="ipa-input" rows="2" placeholder="Taman Cahaya" id="exporterAddress"></textarea>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Added Items List                                               -->
        <!-- ============================================================ -->
        <div class="ipa-card" id="ipaAddedItemsCard" style="display:none;">

            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-list-check"></i></div>
                <div>
                    <div class="ips-card-title"><span data-bm="Item Ditambah" data-en="Added Items">Added Items</span> <span class="ipa-item-count-badge ms-2"
                            id="ipaItemCountBadge">0</span></div>

                </div>
            </div>
            <p class="ipa-card-hint" data-bm="Item yang telah disahkan dan ditambah ke permohonan ini." data-en="Items confirmed and added to this application.">Items confirmed and added to this application.</p>
            <div class="ipa-added-list" id="ipaAddedList"></div>
        </div>

        <!-- ============================================================ -->
        <!-- Permit Item Form (single, reusable)                           -->
        <!-- ============================================================ -->
        <div class="ipa-card" id="ipaItemFormCard">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-box-seam"></i> </div>
                <div>
                    <div class="ips-card-title"><span data-bm="Butiran Item Permit" data-en="Permit Item Details">Permit Item Details</span> <span id="ipsItemsSubtitle"></span></div>
                    <div class="ips-card-sub" id="" data-bm="Setiap item memerlukan set dokumen sokongan sendiri. Selepas mengesahkan syarat-syarat, item akan ditambah ke senarai di atas." data-en="Each item requires its own set of supporting documents. After confirming the conditions, the item will be added to the list above.">Each item requires its own set of supporting documents. After
                        confirming the
                        conditions, the item will be added to the list above.</div>
                </div>
            </div>



            <div class="ipa-form-grid" id="ipaItemFields">
                <div class="ipa-field">
                    <label><span data-bm="Kategori" data-en="Category">Category</span> <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemCategory">
                        <option value="" data-bm="-- Pilih kategori --" data-en="-- Select category --">-- Select category --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label><span data-bm="Nama Barang" data-en="Item Name">Item Name</span> <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemName" disabled>
                        <option value="" data-bm="-- Pilih kategori dahulu --" data-en="-- Select category first --">-- Select category first --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label data-bm="Kegunaan Barang" data-en="Usage">Usage</label>
                    <input type="text" class="ipa-input" id="ipaItemUsage" placeholder="e.g. Commercial Sale" data-i18n-attr="placeholder" data-bm="cth. Jualan Komersial" data-en="e.g. Commercial Sale">
                </div>
                <div class="ipa-field">
                    <label><span data-bm="Tujuan Barang" data-en="Purpose">Purpose</span> <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemPurpose">
                        <option value="" data-bm="-- Pilih tujuan --" data-en="-- Select purpose --">-- Select purpose --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label><span data-bm="Kuantiti Barang" data-en="Quantity">Quantity</span> <span class="ipa-required">*</span></label>
                    <input type="number" min="0" class="ipa-input" id="ipaItemQty" placeholder="0">
                </div>
                <div class="ipa-field">
                    <label><span data-bm="Unit" data-en="Unit">Unit</span> <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemUnit">
                        <option value="" data-bm="-- Pilih unit --" data-en="-- Select unit --">-- Select unit --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label><span data-bm="Nilai Barang (RM)" data-en="Declared Value (RM)">Declared Value (RM)</span> <span class="ipa-required">*</span></label>
                    <input type="number" min="0" step="0.01" class="ipa-input" id="ipaItemValue"
                        placeholder="0.00">
                </div>
            </div>

            <div class="ipa-item-uploader-title" data-bm="Lampiran Barang" data-en="Supporting Documents for this Item">Supporting Documents for this Item</div>
            <div id="ipaItemUploaderContainer"></div>

            <div class="ipa-item-form-footer">
                <button type="button" class="ipa-btn-reset" id="ipaResetItemBtn">
                    <i class="bi bi-arrow-counterclockwise"></i> <span data-bm="Set Semula" data-en="Reset">Reset</span>
                </button>
                <button type="button" class="ipa-btn-add-confirm" id="ipaAddItemBtn">
                    <i class="bi bi-plus-circle"></i> <span data-bm="Tambah Item ke Permohonan" data-en="Add Item to Application">Add Item to Application</span>
                </button>
            </div>
        </div>

        <!-- Application-level Documents -->
        <div class="ipa-card">

            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-paperclip"></i></div>
                <div>
                    <div class="ips-card-title" data-bm="Lampiran Permohonan" data-en="Application Documents">Application Documents</div>
                    <div class="ips-card-sub" data-bm="Muat naik dokumen sokongan untuk permohonan secara keseluruhan — invois, senarai pembungkusan, surat kebenaran, dan sebagainya." data-en="Upload supporting documents for the application as a whole — invoice, packing list, authorization letter, and so on.">Upload supporting documents for the application as a whole — invoice, packing
                        list, authorization letter, and so on.</div>
                </div>
            </div>

            <div id="ipaAppUploader"></div>
        </div>

        <!-- Footer actions -->
        <div class="ipa-footer-actions">
            <div class="ipa-footer-status" id="ipaFooterStatus">
                <span class="ipa-draft-dot is-unsaved"></span>
                <span data-bm="Belum disimpan" data-en="Not saved yet">Not saved yet</span>
            </div>
            <div class="ipa-footer-buttons">
                <button type="button" class="ipa-btn-secondary" id="ipaSaveDraftBtn" data-bm="Simpan sebagai Draf" data-en="Save as Draft">Save as Draft</button>
                <button type="button" class="ips-btn-submit" id="ipaSubmitBtn"><i class="bi bi-file-earmark-text"></i>
                    <span data-bm="Semak Permohonan" data-en="Review Application">Review Application</span></button>
            </div>
        </div>

    </div>

    <!-- ============================================================ -->
    <!-- CONDITION AGREEMENT MODAL                                     -->
    <!-- ============================================================ -->
    <div class="modal fade" id="ipaConditionModal" tabindex="-1" aria-labelledby="ipaConditionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
            <div class="modal-content" style="border-radius: 1rem; border: 1px solid var(--default-border);">
                <div class="modal-header border-bottom" style="padding: 1.25rem 1.5rem;">
                    <div>
                        <div
                            style="font-size:0.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.2rem;" data-bm="Permit Import" data-en="Import Permit">
                            Import Permit
                        </div>
                        <h5 class="modal-title fw-bold mb-0" id="ipaConditionModalLabel" data-bm="Syarat-syarat &amp; Pengakuan Item" data-en="Item Conditions &amp; Declaration">
                            Item Conditions &amp; Declaration
                        </h5>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">

                    <!-- Item summary -->
                    <div class="ipa-modal-item-summary" id="ipaModalItemSummary"></div>

                    <!-- Conditions list -->
                    <div class="ipa-modal-section-label" data-bm="Syarat-syarat Import" data-en="Import Conditions">Import Conditions</div>
                    <div id="ipaModalConditions"></div>

                    <!-- Declaration -->
                    <div class="ipa-modal-section-label mt-3" data-bm="Pengakuan" data-en="Declaration">Declaration</div>
                    <div class="ipa-modal-declaration">
                        <p data-bm="Dengan menandakan kotak di bawah, saya mengesahkan bahawa saya telah membaca dan memahami semua syarat import yang berkenaan untuk item ini. Saya mengaku bahawa maklumat yang diberikan adalah tepat dan lengkap setahu saya." data-en="By checking the box below, I confirm that I have read and understood all import conditions applicable to this item. I declare that the information provided is accurate and complete to the best of my knowledge.">
                            By checking the box below, I confirm that I have read and understood all
                            import conditions applicable to this item. I declare that the information
                            provided is accurate and complete to the best of my knowledge.
                        </p>
                        <label class="ipa-agree-label" id="ipaAgreeLabel">
                            <input type="checkbox" id="ipaAgreeCheck" class="ipa-agree-check">
                            <span data-bm="Saya telah membaca dan bersetuju dengan semua syarat yang dinyatakan di atas." data-en="I have read and agree to all conditions stated above.">I have read and agree to all conditions stated above.</span>
                        </label>
                    </div>

                </div>
                <div class="modal-footer border-top" style="padding: 1rem 1.5rem;">
                    <button type="button" class="ipa-btn-secondary" data-bs-dismiss="modal" data-bm="Batal" data-en="Cancel">Cancel</button>
                    <button type="button" class="ipa-btn-confirm" id="ipaConfirmAddBtn" disabled>
                        <i class="bi bi-check-circle me-1"></i> <span data-bm="Sahkan &amp; Tambah Item" data-en="Confirm &amp; Add Item">Confirm &amp; Add Item</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ITEM DETAIL OFFCANVAS                                         -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="ipaItemDetailOffcanvas"
        aria-labelledby="ipaItemDetailOffcanvasLabel" style="width: 60%; max-width: 800px;">
        <div class="offcanvas-header border-bottom px-4">
            <div class="d-flex align-items-center gap-3">
                <div class="ipa-offcanvas-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="ipa-offcanvas-eyebrow" data-bm="Item Permit" data-en="Permit Item">Permit Item</div>
                    <h5 class="offcanvas-title mb-0 fw-bold" id="ipaItemDetailOffcanvasLabel">—</h5>
                </div>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 72px); overflow: hidden;">
            <!-- Vertical Nav -->
            <div class="ipa-oc-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ipa-oc-details-tab" data-bs-toggle="tab"
                            data-bs-target="#ipa-oc-details" type="button" role="tab">
                            <i class="bi bi-file-text me-2"></i> <span data-bm="Butiran" data-en="Details">Details</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ipa-oc-docs-tab" data-bs-toggle="tab" data-bs-target="#ipa-oc-docs"
                            type="button" role="tab">
                            <i class="bi bi-paperclip me-2"></i> <span data-bm="Dokumen" data-en="Documents">Documents</span>
                        </button>
                    </li>
                </ul>
            </div>
            <!-- Tab Content -->
            <div class="tab-content flex-grow-1 overflow-auto">
                <div class="tab-pane fade show active p-4" id="ipa-oc-details" role="tabpanel">
                    <div id="ipaOcDetailsContent"></div>
                </div>
                <div class="tab-pane fade p-4" id="ipa-oc-docs" role="tabpanel">
                    <div id="ipaOcDocsContent"></div>
                </div>
            </div>
        </div>
    </div>

@endsection
