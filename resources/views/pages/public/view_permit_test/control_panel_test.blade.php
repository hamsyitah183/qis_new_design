@extends('pages.app')

@section('pageName', 'Control Panel')

@push('scripts')
    @vite(['resources/js/pages/importPermit/controlPanel.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard',      'url' => '/'],
        ['label' => 'Control Panel',  'url' => '#'],
    ]" title="Control Panel">
    </x-breadcrumb>
@endsection

@section('content')

<div class="cp-wrapper">

    {{-- ============================================================ --}}
    {{-- LEFT: Control panel section nav                                 --}}
    {{-- ============================================================ --}}
    <aside class="cp-nav">
        <div class="cp-nav-title">Control Panel</div>
        <div class="cp-nav-list">
            <button type="button" class="cp-nav-item is-active" data-cp-panel="district">
                <i class="bi bi-signpost-split"></i>
                <span>District Entry Points</span>
            </button>
            <button type="button" class="cp-nav-item" data-cp-panel="purpose">
                <i class="bi bi-clipboard-check"></i>
                <span>Purpose of Import</span>
            </button>
            <button type="button" class="cp-nav-item" data-cp-panel="unit">
                <i class="bi bi-rulers"></i>
                <span>Unit Measurement</span>
            </button>
            <button type="button" class="cp-nav-item" data-cp-panel="condition">
                <i class="bi bi-tags"></i>
                <span>Description Form</span>
            </button>
        </div>
    </aside>

    {{-- ============================================================ --}}
    {{-- RIGHT: Main panel content                                       --}}
    {{-- ============================================================ --}}
    <div class="cp-main">

        {{-- ============================================================ --}}
        {{-- PANEL: District Entry Points                                   --}}
        {{-- ============================================================ --}}
        <section class="cp-panel is-active" data-cp-panel-content="district">

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold mb-1">District Entry Points</h3>
                    <div class="text-muted">
                        Manage the official entry points available under each district, used when
                        applicants select their Entry Point during application.
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="addDistrictBtn">
                    <i class="bi bi-plus-lg me-2"></i>
                    Add District
                </button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4 col-sm-6">
                    <div class="ipv-summary-card">
                        <small>Total Districts</small>
                        <h3 id="summaryDistricts">0</h3>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="ipv-summary-card">
                        <small>Total Entry Points</small>
                        <h3 id="summaryEntryPoints">0</h3>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="ipv-summary-card">
                        <small>Districts with No Entry Point</small>
                        <h3 id="summaryEmptyDistricts">0</h3>
                    </div>
                </div>
            </div>

            <div class="ipv-table-container">
                <div class="ipv-toolbar">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchDistrict"
                                placeholder="Search district or entry point...">
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="ipv-sort-label" for="cpTransportFilter">
                            <i class="bi bi-funnel"></i> Transport
                        </label>
                        <select class="form-select ipv-sort-select" id="cpTransportFilter">
                            <option value="">All types</option>
                            <option value="Air">Air</option>
                            <option value="Sea">Sea</option>
                            <option value="Land">Land</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle ipv-table">
                        <thead>
                            <tr>
                                <th>District</th>
                                <th>Entry Points</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody id="districtTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

        {{-- ============================================================ --}}
        {{-- PANEL: Purpose of Import (simple list)                       --}}
        {{-- ============================================================ --}}
        <section class="cp-panel" data-cp-panel-content="purpose">

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold mb-1">Purpose of Import</h3>
                    <div class="text-muted">
                        Manage the list of purposes an applicant can select for each permit item
                        (e.g. Commercial, Individual, Research use cases).
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4 cp-simple-add-btn" data-cp-type="purpose">
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Purpose
                </button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4 col-sm-6">
                    <div class="ipv-summary-card">
                        <small>Total Purposes</small>
                        <h3 id="summaryPurposeTotal">0</h3>
                    </div>
                </div>
            </div>

            <div class="ipv-table-container">
                <div class="ipv-toolbar">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control cp-simple-search" data-cp-type="purpose"
                                placeholder="Search purpose...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle ipv-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Purpose</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody id="purposeTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

        {{-- ============================================================ --}}
        {{-- PANEL: Unit Measurement (simple list)                          --}}
        {{-- ============================================================ --}}
        <section class="cp-panel" data-cp-panel-content="unit">

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold mb-1">Unit Measurement</h3>
                    <div class="text-muted">
                        Manage the units applicants can use to declare quantity for each permit item.
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4 cp-simple-add-btn" data-cp-type="unit">
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Unit
                </button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4 col-sm-6">
                    <div class="ipv-summary-card">
                        <small>Total Units</small>
                        <h3 id="summaryUnitTotal">0</h3>
                    </div>
                </div>
            </div>

            <div class="ipv-table-container">
                <div class="ipv-toolbar">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control cp-simple-search" data-cp-type="unit"
                                placeholder="Search unit...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle ipv-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Unit Name</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody id="unitTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

        {{-- ============================================================ --}}
        {{-- PANEL: Description Form (simple list)                        --}}
        {{-- ============================================================ --}}
        <section class="cp-panel" data-cp-panel-content="condition">

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold mb-1">Description Form</h3>
                    <div class="text-muted">
                        Manage the list of item condition categories used to describe how a
                        consignment item is presented (fresh, dried, frozen, etc.).
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4 cp-simple-add-btn" data-cp-type="condition">
                    <i class="bi bi-plus-lg me-2"></i>
                    Add Condition
                </button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4 col-sm-6">
                    <div class="ipv-summary-card">
                        <small>Total Conditions</small>
                        <h3 id="summaryConditionTotal">0</h3>
                    </div>
                </div>
            </div>

            <div class="ipv-table-container">
                <div class="ipv-toolbar">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control cp-simple-search" data-cp-type="condition"
                                placeholder="Search condition...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle ipv-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Condition</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody id="conditionTableBody"></tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>

</div>

{{-- ================================================================== --}}
{{-- ADD / EDIT DISTRICT OFFCANVAS (unchanged)                            --}}
{{-- ================================================================== --}}
<div class="offcanvas offcanvas-end cp-offcanvas" tabindex="-1" id="districtOffcanvas"
    aria-labelledby="districtOffcanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="mb-1" id="districtOffcanvasLabel">Add District</h5>
            <small class="text-muted" id="districtOffcanvasSub">Define a district and its entry points</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
        <div class="cp-form-group">
            <label class="cp-form-label">District Name <span class="cp-required">*</span></label>
            <input type="text" class="form-control" id="districtNameInput"
                placeholder="e.g. Kota Kinabalu">
            <div class="cp-form-hint">This matches public_code.description for cate_name = 'district_entry'.</div>
        </div>

        <div class="cp-divider"></div>

        <div class="cp-form-group">
            <div class="cp-section-label-row">
                <label class="cp-form-label mb-0">Entry Points</label>
                <button type="button" class="cp-btn-add-entry" id="addEntryPointBtn">
                    <i class="bi bi-plus-circle"></i> Add Entry Point
                </button>
            </div>
            <div class="cp-form-hint mb-3">
                Add every official entry point under this district, and set its transport type.
            </div>

            <div class="cp-entry-list" id="entryPointList"></div>

            <div class="cp-entry-empty" id="entryPointEmpty">
                <i class="bi bi-signpost"></i>
                <p>No entry points yet. Click "Add Entry Point" to get started.</p>
            </div>
        </div>
    </div>

    <div class="offcanvas-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveDistrictBtn">
            <i class="bi bi-check-lg me-1"></i> Save District
        </button>
    </div>
</div>

{{-- ================================================================== --}}
{{-- ADD / EDIT SIMPLE LIST OFFCANVAS (shared: purpose / unit / condition) --}}
{{-- ================================================================== --}}
<div class="offcanvas offcanvas-end cp-offcanvas cp-offcanvas-sm" tabindex="-1" id="simpleListOffcanvas"
    aria-labelledby="simpleListOffcanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="mb-1" id="simpleListOffcanvasLabel">Add Item</h5>
            <small class="text-muted" id="simpleListOffcanvasSub">—</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
        <div class="cp-form-group" id="simpleCodeGroup">
            <label class="cp-form-label" id="simpleCodeLabel">Code <span class="cp-required">*</span></label>
            <input type="text" class="form-control" id="simpleCodeInput" placeholder="e.g. KG">
            <div class="cp-form-hint" id="simpleCodeHint">Short unique code stored in public_code.cate_code.</div>
        </div>

        <div class="cp-form-group" style="margin-top: 18px;">
            <label class="cp-form-label" id="simpleNameLabel">Name <span class="cp-required">*</span></label>
            <input type="text" class="form-control" id="simpleNameInput" placeholder="e.g. Kilogram">
        </div>
    </div>

    <div class="offcanvas-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveSimpleBtn">
            <i class="bi bi-check-lg me-1"></i> <span id="saveSimpleBtnLabel">Save</span>
        </button>
    </div>
</div>

@endsection