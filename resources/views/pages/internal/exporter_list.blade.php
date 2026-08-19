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
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#', 'data-en' => 'Home', 'data-bm' => 'Laman Utama'], ['label' => 'Exporter List', 'url' => '#', 'data-en' => 'Exporter List', 'data-bm' => 'Senarai Pengeksport']]"
        title="All Exporter List" title_en="All Exporter List" title_bm="Senarai Semua Pengeksport">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="Exporter List" data-bm="Senarai Pengeksport">Exporter List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="exporterFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            <span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="exporterFilterDropdown">

                            {{-- Name Search --}}
                            {{-- <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Exporter Name" data-bm="Nama Pengeksport">Exporter Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterExporterName"
                                    placeholder="Search by name..." data-en="Search by name..." data-bm="Cari dengan nama..." data-i18n-attr="placeholder">
                            </li> --}}

                            {{-- Country --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Country" data-bm="Negara">Country</label>
                                <select class="form-select form-select-sm" id="filterExporterCountry">
                                    <option value="" data-en="All Countries" data-bm="Semua Negara">All Countries</option>
                                    <option value="SWK" data-en="Sarawak, Malaysia" data-bm="Sarawak, Malaysia">Sarawak, Malaysia</option>
                                    <option value="BN" data-en="Brunei Darussalam" data-bm="Brunei Darussalam">Brunei Darussalam</option>
                                </select>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetExporterFilter"><span data-en="Reset" data-bm="Set Semula">Reset</span></button>
                                <button class="btn btn-sm btn-primary" id="btnExporterFilter"><span data-en="Apply" data-bm="Cari">Apply</span></button>
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
                                    <th data-en="Name" data-bm="Nama">Name</th>
                                    <th data-en="Phone No" data-bm="No Telefon">Phone No</th>
                                    <th data-en="Address" data-bm="Alamat">Address</th>
                                    <th data-en="Country" data-bm="Negara">Country</th>
                                    <th data-en="Registered By" data-bm="Didaftarkan Oleh">Registered By</th>
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