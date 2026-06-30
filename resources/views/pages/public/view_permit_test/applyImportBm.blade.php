@extends('pages.app')

@section('pageName', 'Mohon Permit Import')

@push('scripts')
    @vite(['resources/js/pages/importPermit/applyImportPermit.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        ['label' => 'Senarai Permohonan', 'url' => '/public/view_import_permit'],
        ['label' => 'Permohonan Baru', 'url' => '#'],
    ]" title="Mohon Permit Import">
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
                <div class="ips-hero-eyebrow">Permohonan Permit Import</div>
                <h3 class="ips-hero-title">Permohonan</h3>
                <p class="ips-hero-sub text-wrap">
                    Kebenaran rasmi yang diberikan kepada pengimport untuk mengimport barangan pertanian terkawal ke Sabah.
                </p>
            </div>
            <div class="ipa-draft-status" id="ipaDraftStatus">
                <span class="ipa-draft-dot is-unsaved"></span>
                <span>Belum disimpan</span>
            </div>
        </div>

        <!-- Transportation Details -->
        <div class="ipa-card">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-truck"></i></div>
                <div>
                    <div class="ips-card-title">Butiran Pengangkutan</div>
                    <div class="ips-card-sub">Maklumat laluan masuk dan logistik</div>
                </div>
            </div>
            <div class="ipa-form-grid">
                <div class="ipa-field">
                    <label>Masa Ketibaan <span class="ipa-required">*</span></label>
                    <input type="date" class="ipa-input" id="ipaEta">
                </div>
                <div class="ipa-field">
                    <label>Jenis Pengangkutan <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaTransportType">
                        <option value="">-- Pilih jenis pengangkutan --</option>
                        <option>Pengangkutan Laut</option>
                        <option>Pengangkutan Udara</option>
                        <option>Pengangkutan Darat</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Tempat Masuk <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaEntryPoint">
                        <option value="">-- Pilih tempat masuk --</option>
                        <option>Pelabuhan Kota Kinabalu</option>
                        <option>Pelabuhan Sandakan</option>
                        <option>Pelabuhan Tawau</option>
                        <option>Lapangan Terbang Antarabangsa Kota Kinabalu</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Importer / Exporter Details -->
        <div class="ipa-card">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-people"></i></div>
                <div>
                    <div class="ips-card-title">Butiran Pengimport &amp; Pengeksport</div>
                    <div class="ips-card-sub">Pihak yang terlibat dalam penghantaran ini</div>
                </div>
            </div>

            <div class="ipa-form-grid">
                <div class="ipa-field">
                    <label>Nama Pengimport <span class="ipa-required">*</span></label>
                    <input type="text" class="ipa-input" placeholder="Cari atau masukkan nama pengimport">
                </div>
                <div class="ipa-field">
                    <label>Nombor Telefon Pengimport</label>
                    <input type="text" class="ipa-input" placeholder="cth. (088) 244 511">
                </div>
                <div class="ipa-field">
                    <label>Nama Pengeksport <span class="ipa-required">*</span></label>
                    <input type="text" class="ipa-input" placeholder="Cari atau masukkan nama pengeksport">
                </div>
                <div class="ipa-field">
                    <label>Negara Pengeksport</label>
                    <input type="text" class="ipa-input" placeholder="cth. Singapura">
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Added Items List                                               -->
        <!-- ============================================================ -->
        <div class="ipa-card" id="ipaAddedItemsCard" style="display:none;">

            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-list-check"></i></div>
                <div>
                    <div class="ips-card-title">Item Ditambah <span class="ipa-item-count-badge ms-2"
                            id="ipaItemCountBadge">0</span></div>

                </div>
            </div>
            <p class="ipa-card-hint">Item yang telah disahkan dan ditambah ke permohonan ini.</p>
            <div class="ipa-added-list" id="ipaAddedList"></div>
        </div>

        <!-- ============================================================ -->
        <!-- Permit Item Form (single, reusable)                           -->
        <!-- ============================================================ -->
        <div class="ipa-card" id="ipaItemFormCard">
            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-box-seam"></i> </div>
                <div>
                    <div class="ips-card-title">Butiran Item Permit <span id="ipsItemsSubtitle"></span></div>
                    <div class="ips-card-sub" id="">Setiap item memerlukan set dokumen sokongan sendiri. Selepas
                        mengesahkan syarat-syarat, item akan ditambah ke senarai di atas.</div>
                </div>
            </div>



            <div class="ipa-form-grid" id="ipaItemFields">
                <div class="ipa-field">
                    <label>Kategori <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemCategory">
                        <option value="">-- Pilih kategori --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Nama Barang <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemName" disabled>
                        <option value="">-- Pilih kategori dahulu --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Kegunaan Barang</label>
                    <input type="text" class="ipa-input" id="ipaItemUsage" placeholder="cth. Jualan Komersial">
                </div>
                <div class="ipa-field">
                    <label>Tujuan Barang <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemPurpose">
                        <option value="">-- Pilih tujuan --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Kuantiti Barang <span class="ipa-required">*</span></label>
                    <input type="number" min="0" class="ipa-input" id="ipaItemQty" placeholder="0">
                </div>
                <div class="ipa-field">
                    <label>Unit <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemUnit">
                        <option value="">-- Pilih unit --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Nilai Barang (RM) <span class="ipa-required">*</span></label>
                    <input type="number" min="0" step="0.01" class="ipa-input" id="ipaItemValue"
                        placeholder="0.00">
                </div>
            </div>

            <div class="ipa-item-uploader-title">Lampiran Barang</div>
            <div id="ipaItemUploaderContainer"></div>

            <div class="ipa-item-form-footer">
                <button type="button" class="ipa-btn-reset" id="ipaResetItemBtn">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
                <button type="button" class="ipa-btn-add-confirm" id="ipaAddItemBtn">
                    <i class="bi bi-plus-circle"></i> Tambah Item ke Permohonan
                </button>
            </div>
        </div>

        <!-- Application-level Documents -->
        <div class="ipa-card">

            <div class="ips-card-head">
                <div class="ips-card-head-icon"><i class="bi bi-paperclip"></i></div>
                <div>
                    <div class="ips-card-title">Lampiran Permohonan</div>
                    <div class="ips-card-sub">Muat naik dokumen sokongan untuk permohonan secara keseluruhan — invois, senarai pembungkusan, surat kebenaran, dan sebagainya.</div>
                </div>
            </div>

            <div id="ipaAppUploader"></div>
        </div>

        <!-- Footer actions -->
        <div class="ipa-footer-actions">
            <div class="ipa-footer-status" id="ipaFooterStatus">
                <span class="ipa-draft-dot is-unsaved"></span>
                <span>Belum disimpan</span>
            </div>
            <div class="ipa-footer-buttons">
                <button type="button" class="ipa-btn-secondary" id="ipaSaveDraftBtn">Simpan sebagai Draf</button>
                <button type="button" class="ips-btn-submit" id="ipaSubmitBtn"><i class="bi bi-file-earmark-text"></i> Semak Permohonan</button>
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
                            style="font-size:0.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.2rem;">
                            Permit Import
                        </div>
                        <h5 class="modal-title fw-bold mb-0" id="ipaConditionModalLabel">
                            Syarat-syarat &amp; Pengakuan Item
                        </h5>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"
                        aria-label="Tutup"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">

                    <!-- Item summary -->
                    <div class="ipa-modal-item-summary" id="ipaModalItemSummary"></div>

                    <!-- Conditions list -->
                    <div class="ipa-modal-section-label">Syarat-syarat Import</div>
                    <div id="ipaModalConditions"></div>

                    <!-- Declaration -->
                    <div class="ipa-modal-section-label mt-3">Pengakuan</div>
                    <div class="ipa-modal-declaration">
                        <p>
                            Dengan menandakan kotak di bawah, saya mengesahkan bahawa saya telah membaca dan memahami
                            semua syarat import yang berkenaan untuk item ini. Saya mengaku bahawa maklumat yang diberikan
                            adalah tepat dan lengkap setahu saya.
                        </p>
                        <label class="ipa-agree-label" id="ipaAgreeLabel">
                            <input type="checkbox" id="ipaAgreeCheck" class="ipa-agree-check">
                            <span>Saya telah membaca dan bersetuju dengan semua syarat yang dinyatakan di atas.</span>
                        </label>
                    </div>

                </div>
                <div class="modal-footer border-top" style="padding: 1rem 1.5rem;">
                    <button type="button" class="ipa-btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="ipa-btn-confirm" id="ipaConfirmAddBtn" disabled>
                        <i class="bi bi-check-circle me-1"></i> Sahkan &amp; Tambah Item
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
                    <div class="ipa-offcanvas-eyebrow">Item Permit</div>
                    <h5 class="offcanvas-title mb-0 fw-bold" id="ipaItemDetailOffcanvasLabel">—</h5>
                </div>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 72px); overflow: hidden;">
            <!-- Vertical Nav -->
            <div class="ipa-oc-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ipa-oc-details-tab" data-bs-toggle="tab"
                            data-bs-target="#ipa-oc-details" type="button" role="tab">
                            <i class="bi bi-file-text me-2"></i> Butiran
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ipa-oc-docs-tab" data-bs-toggle="tab" data-bs-target="#ipa-oc-docs"
                            type="button" role="tab">
                            <i class="bi bi-paperclip me-2"></i> Dokumen
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