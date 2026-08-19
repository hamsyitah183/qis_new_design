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
    @vite(['resources/js/pages/internal/internal_importer_list.js'])
@endpush

@section('pageName', 'All Importers')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#', 'data-en' => 'Home', 'data-bm' => 'Laman Utama'], ['label' => 'Importer List', 'url' => '#', 'data-en' => 'Importer List', 'data-bm' => 'Senarai Pengimport']]"
        title="All Importer List" title_en="All Importer List" title_bm="Senarai Semua Pengimport">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="Importer List" data-bm="Senarai Pengimport">Importer List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="importerFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            <span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="importerFilterDropdown">

                            {{-- Name Search --}}
                            {{-- <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Importer Name" data-bm="Nama Pengimport">Importer Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterImporterName"
                                    placeholder="Search by name..." data-en="Search by name..." data-bm="Cari dengan nama..." data-i18n-attr="placeholder">
                            </li> --}}

                            {{-- Country --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Country" data-bm="Negara">Country</label>
                                <select class="form-select form-select-sm" id="filterImporterCountry">
                                    <option value="" data-en="All Countries" data-bm="Semua Negara">All Countries</option>
                                    @foreach ($country as $coun)
                                        <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                                    @endforeach
                                </select>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetImporterFilter"><span data-en="Reset" data-bm="Set Semula">Reset</span></button>
                                <button class="btn btn-sm btn-primary" id="btnImporterFilter"><span data-en="Apply" data-bm="Cari">Apply</span></button>
                            </li>
                        </ul>

                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="internalImporterTable" class="table table-bordered table-striped align-middle w-100">
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