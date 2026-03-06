@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
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
    @vite(['resources/js/pages/internal/internal_exporter_list.js'])
@endpush

@section('pageName', 'All Exporters')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#'], ['label' => 'Exporter List', 'url' => '#']]"
        title="All Exporter List">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Exporter List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="exporterFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            Filter
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="exporterFilterDropdown">

                            {{-- Name Search --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Exporter Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterExporterName"
                                    placeholder="Search by name...">
                            </li>

                            {{-- Country --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Country</label>
                                <select class="form-select form-select-sm" id="filterExporterCountry">
                                    <option value="">All Countries</option>
                                    <option value="SWK">Sarawak, Malaysia</option>
                                    <option value="BN">Brunei Darussalam</option>
                                </select>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetExporterFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnExporterFilter">Apply</button>
                            </li>
                        </ul>

                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="internalExporterTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone No</th>
                                    <th>Address</th>
                                    <th>Country</th>
                                    <th>Registered By</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection