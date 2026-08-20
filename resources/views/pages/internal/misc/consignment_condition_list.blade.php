@extends('pages.app')

@section('pageName', 'Consignment List')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .filter-dropdown {
            width: 420px;
        }

        @media (max-width: 768px) {
            .filter-dropdown {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/misc/consignment_condition_list.js'])
@endpush


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'], ['label' => 'Consignment List', 'url' => '#', 'data-en' => 'Consignment List', 'data-bm' => 'Senarai Konsainan']]" title="Consignment List" title_en="Consignment List" title_bm="Senarai Konsainan">

    </x-breadcrumb>
@endsection


@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="Consignment Condition List" data-bm="Senarai Syarat Konsainan">Consignment Condition List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="consignCondFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            <span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="consignCondFilterDropdown">

                            {{-- Item Name --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Item Name" data-bm="Nama Item">Item Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterConsignItemName"
                                    placeholder="Search item name..." data-en="Search item name..." data-bm="Cari nama item..." data-i18n-attr="placeholder">
                            </li>

                            {{-- Category --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Category" data-bm="Kategori">Category</label>
                                <select class="form-select form-select-sm select2" id="filterConsignCategory">
                                    <option value="" data-en="All Categories" data-bm="Semua Kategori">All Categories</option>
                                </select>
                            </li>

                            {{-- Usage --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Usage" data-bm="Kegunaan">Usage</label>
                                <select class="form-select form-select-sm select2" id="filterConsignUsage">
                                    <option value="" data-en="All Usage" data-bm="Semua Kegunaan">All Usage</option>
                                </select>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetConsignCondFilter"><span data-en="Reset" data-bm="Set Semula">Reset</span></button>
                                <button class="btn btn-sm btn-primary" id="btnConsignCondFilter"><span data-en="Apply" data-bm="Cari">Apply</span></button>
                            </li>
                        </ul>

                        <a type="button" href="{{ url('internal/consignment_condition/add') }}"
                            class="btn btn-success btn-sm">
                            <i class="ti ti-plus me-1"></i> <span data-en="Add Consignment Item" data-bm="Tambah Item Konsainan">Add Consignment Item</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <table id="conditionTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                {{-- <th>#</th> --}}
                                <th class="text-wrap" data-en="Item Name" data-bm="Nama Item">Item Name</th>
                                <th class="text-wrap" data-en="Scientific Name" data-bm="Nama Saintifik">Scientific Name</th>
                                <th data-en="Category" data-bm="Kategori">Category</th>
                                <th data-en="Usage" data-bm="Kegunaan">Usage</th>
                                <th data-en="Action" data-bm="Tindakan">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody> <!-- Important for DataTables -->
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="showConditionModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Your content here -->
                    <!-- ITEM DETAILS -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-muted" data-en="Item Information" data-bm="Maklumat Item">Item Information</h5>
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <tr>
                                    <th width="25%" data-en="Item Name" data-bm="Nama Item">Item Name</th>
                                    <td id="itemNameCell"></td>
                                </tr>
                                <tr>
                                    <th width="25%" data-en="Scientific Name" data-bm="Nama Saintifik">Scientific Name</th>
                                    <td id="scientificNameCell"></td>
                                </tr>
                                <tr>
                                    <th data-en="Category" data-bm="Kategori">Category</th>
                                    <td id="categoryCell"></td>
                                </tr>
                                <tr>
                                    <th data-en="Usage / Consignment Application" data-bm="Kegunaan / Permohonan Konsainan">Usage / Consignment Application</th>
                                    <td id="usageCell"></td>
                                </tr>
                                <tr>
                                    <th data-en="Country" data-bm="Negara">Country</th>
                                    <td id="countryCell"></td>
                                </tr>
                                <tr>
                                    <th data-en="Quantity Limit" data-bm="Had Kuantiti">Quantity Limit</th>
                                    <td id="quantityLimit"></td>
                                </tr>
                                <tr>
                                    <th data-en="Date" data-bm="Tarikh">Date</th>
                                    <td id="date"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PERMIT CONDITION -->
                    <div class="mt-4">
                        <h5 class="fw-bold text-muted" data-en="Consignment Condition" data-bm="Syarat Konsainan">Consignment Condition</h5>
                        <div id="conditionHtml" class="border rounded p-3 bg-light" style="min-height: 150px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
                </div>

            </div>
        </div>
    </div>

@endsection
