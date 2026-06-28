@extends('pages.app')

@section('pageName', 'Apply Import Permit')

@push('scripts')
    @vite(['resources/js/pages/importPermit/applyImportPermit.js'])
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

    <div class="ipa-wrapper">

        <!-- Header -->
        <div class="ipa-header-card">
            <div class="ipa-header-top">
                <div>
                    <div class="ipa-header-eyebrow">Import Permit</div>
                    <h4 class="ipa-header-title">New Application</h4>
                </div>
                <div class="ipa-draft-status" id="ipaDraftStatus">
                    <span class="ipa-draft-dot is-unsaved"></span>
                    <span>Not saved yet</span>
                </div>
            </div>
            <p class="ipa-header-sub">
                Fill in transportation and party details, then add each permit item one at a time.
                Each item must be confirmed before it is added to the application.
            </p>
        </div>

        <!-- Transportation Details -->
        <div class="ipa-card">
            <div class="ipa-card-title"><i class="bi bi-truck"></i> Transportation Details</div>
            <div class="ipa-form-grid">
                <div class="ipa-field">
                    <label>ETA <span class="ipa-required">*</span></label>
                    <input type="date" class="ipa-input" id="ipaEta">
                </div>
                <div class="ipa-field">
                    <label>Transport Type <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaTransportType">
                        <option value="">-- Select transport type --</option>
                        <option>Sea Freight</option>
                        <option>Air Freight</option>
                        <option>Land / Road</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Entry Point <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaEntryPoint">
                        <option value="">-- Select entry point --</option>
                        <option>Kota Kinabalu Port</option>
                        <option>Sandakan Port</option>
                        <option>Tawau Port</option>
                        <option>Kota Kinabalu International Airport</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Importer / Exporter Details -->
        <div class="ipa-card">
            <div class="ipa-card-title"><i class="bi bi-people"></i> Importer &amp; Exporter Details</div>
            <div class="ipa-form-grid">
                <div class="ipa-field">
                    <label>Importer <span class="ipa-required">*</span></label>
                    <input type="text" class="ipa-input" placeholder="Search or enter importer name">
                </div>
                <div class="ipa-field">
                    <label>Importer Phone</label>
                    <input type="text" class="ipa-input" placeholder="e.g. (088) 244 511">
                </div>
                <div class="ipa-field">
                    <label>Exporter <span class="ipa-required">*</span></label>
                    <input type="text" class="ipa-input" placeholder="Search or enter exporter name">
                </div>
                <div class="ipa-field">
                    <label>Exporter Country</label>
                    <input type="text" class="ipa-input" placeholder="e.g. Singapore">
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Added Items List                                               -->
        <!-- ============================================================ -->
        <div class="ipa-card" id="ipaAddedItemsCard" style="display:none;">
            <div class="ipa-card-title-row">
                <div class="ipa-card-title">
                    <i class="bi bi-list-check"></i> Added Items
                    <span class="ipa-item-count-badge" id="ipaItemCountBadge">0</span>
                </div>
            </div>
            <p class="ipa-card-hint">Items confirmed and added to this application.</p>
            <div class="ipa-added-list" id="ipaAddedList"></div>
        </div>

        <!-- ============================================================ -->
        <!-- Permit Item Form (single, reusable)                           -->
        <!-- ============================================================ -->
        <div class="ipa-card" id="ipaItemFormCard">
            <div class="ipa-card-title-row">
                <div class="ipa-card-title"><i class="bi bi-box-seam"></i> Permit Item Details</div>
                <span class="ipa-card-hint mb-0" style="margin:0;">Fill in details for one item, then click "Add Item".</span>
            </div>
            <p class="ipa-card-hint">
                Each item requires its own set of supporting documents. After confirming the
                conditions, the item will be added to the list above.
            </p>

            <div class="ipa-form-grid" id="ipaItemFields">
                <div class="ipa-field">
                    <label>Category <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemCategory">
                        <option value="">-- Select category --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Item Name <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemName" disabled>
                        <option value="">-- Select category first --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Usage</label>
                    <input type="text" class="ipa-input" id="ipaItemUsage" placeholder="e.g. Commercial Sale">
                </div>
                <div class="ipa-field">
                    <label>Purpose <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemPurpose">
                        <option value="">-- Select purpose --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Quantity <span class="ipa-required">*</span></label>
                    <input type="number" min="0" class="ipa-input" id="ipaItemQty" placeholder="0">
                </div>
                <div class="ipa-field">
                    <label>Unit <span class="ipa-required">*</span></label>
                    <select class="ipa-input" id="ipaItemUnit">
                        <option value="">-- Select unit --</option>
                    </select>
                </div>
                <div class="ipa-field">
                    <label>Declared Value (RM) <span class="ipa-required">*</span></label>
                    <input type="number" min="0" step="0.01" class="ipa-input" id="ipaItemValue" placeholder="0.00">
                </div>
            </div>

            <div class="ipa-item-uploader-title">Supporting Documents for this Item</div>
            <div id="ipaItemUploaderContainer"></div>

            <div class="ipa-item-form-footer">
                <button type="button" class="ipa-btn-reset" id="ipaResetItemBtn">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
                <button type="button" class="ipa-btn-add-confirm" id="ipaAddItemBtn">
                    <i class="bi bi-plus-circle"></i> Add Item to Application
                </button>
            </div>
        </div>

        <!-- Application-level Documents -->
        <div class="ipa-card">
            <div class="ipa-card-title"><i class="bi bi-paperclip"></i> Application Documents</div>
            <p class="ipa-card-hint">
                Upload supporting documents for the application as a whole — invoice, packing
                list, authorization letter, and so on.
            </p>
            <div id="ipaAppUploader"></div>
        </div>

        <!-- Footer actions -->
        <div class="ipa-footer-actions">
            <div class="ipa-footer-status" id="ipaFooterStatus">
                <span class="ipa-draft-dot is-unsaved"></span>
                <span>Not saved yet</span>
            </div>
            <div class="ipa-footer-buttons">
                <button type="button" class="ipa-btn-secondary" id="ipaSaveDraftBtn">Save as Draft</button>
                <button type="button" class="ipa-btn-primary" id="ipaSubmitBtn">Submit Application</button>
            </div>
        </div>

    </div>

    <!-- ============================================================ -->
    <!-- CONDITION AGREEMENT MODAL                                     -->
    <!-- ============================================================ -->
    <div class="modal fade" id="ipaConditionModal" tabindex="-1" aria-labelledby="ipaConditionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
            <div class="modal-content" style="border-radius: 1rem; border: 1px solid var(--default-border);">
                <div class="modal-header border-bottom" style="padding: 1.25rem 1.5rem;">
                    <div>
                        <div style="font-size:0.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.4px; margin-bottom:0.2rem;">
                            Import Permit
                        </div>
                        <h5 class="modal-title fw-bold mb-0" id="ipaConditionModalLabel">
                            Item Conditions &amp; Declaration
                        </h5>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">

                    <!-- Item summary -->
                    <div class="ipa-modal-item-summary" id="ipaModalItemSummary"></div>

                    <!-- Conditions list -->
                    <div class="ipa-modal-section-label">Import Conditions</div>
                    <div id="ipaModalConditions"></div>

                    <!-- Declaration -->
                    <div class="ipa-modal-section-label mt-3">Declaration</div>
                    <div class="ipa-modal-declaration">
                        <p>
                            By checking the box below, I confirm that I have read and understood all
                            import conditions applicable to this item. I declare that the information
                            provided is accurate and complete to the best of my knowledge.
                        </p>
                        <label class="ipa-agree-label" id="ipaAgreeLabel">
                            <input type="checkbox" id="ipaAgreeCheck" class="ipa-agree-check">
                            <span>I have read and agree to all conditions stated above.</span>
                        </label>
                    </div>

                </div>
                <div class="modal-footer border-top" style="padding: 1rem 1.5rem;">
                    <button type="button" class="ipa-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="ipa-btn-confirm" id="ipaConfirmAddBtn" disabled>
                        <i class="bi bi-check-circle me-1"></i> Confirm &amp; Add Item
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
                    <div class="ipa-offcanvas-eyebrow">Permit Item</div>
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
                            <i class="bi bi-file-text me-2"></i> Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ipa-oc-docs-tab" data-bs-toggle="tab"
                            data-bs-target="#ipa-oc-docs" type="button" role="tab">
                            <i class="bi bi-paperclip me-2"></i> Documents
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