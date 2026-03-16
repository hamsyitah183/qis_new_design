@extends('pages.app')

@section('pageName', 'Permit List')

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
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
          
        ]" 
        title="Consignment List"
    >
     
    </x-breadcrumb>
@endsection


@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Consignment Condition List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="consignCondFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            Filter
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="consignCondFilterDropdown">

                            {{-- Item Name --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Item Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterConsignItemName"
                                    placeholder="Search item name...">
                            </li>

                            {{-- Category --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Category</label>
                                <select class="form-select form-select-sm" id="filterConsignCategory">
                                    <option value="">All Categories</option>
                                </select>
                            </li>

                            {{-- Usage --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Usage</label>
                                <select class="form-select form-select-sm" id="filterConsignUsage">
                                    <option value="">All Usage</option>
                                </select>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetConsignCondFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnConsignCondFilter">Apply</button>
                            </li>
                        </ul>

                        <a type="button" href="{{ url('internal/consignment_condition/add') }}" class="btn btn-success btn-sm">
                            <i class="ti ti-plus me-1"></i> Add Consignment Item
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <table id="conditionTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                {{-- <th>#</th> --}}
                                <th class="text-wrap">Item Name</th>
                                <th>Category</th>
                                <th>Usage</th>
                                <th>Action</th>
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
                        <h5 class="fw-bold text-muted">Item Information</h5>
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <tr>
                                    <th width="25%">Item Name</th>
                                    <td id="itemNameCell"></td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td id="categoryCell"></td>
                                </tr>
                                <tr>
                                    <th>Usage / Consignment Application</th>
                                    <td id="usageCell"></td>
                                </tr>
                                <tr>
                                    <th>Country</th>
                                    <td id="countryCell"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PERMIT CONDITION -->
                    <div class="mt-4">
                        <h5 class="fw-bold text-muted">Consignment Condition</h5>
                        <div id="conditionHtml" 
                            class="border rounded p-3 bg-light"
                            style="min-height: 150px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

@endsection
