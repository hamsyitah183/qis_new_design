@extends('pages.app')

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


@php
    $type = authUser()['type'];

@endphp

@push('scripts')
    <script>
        window.AUTH_TYPE = @json($type);
    </script>
    @vite(['resources/js/pages/export_list.js'])
@endpush

@section('pageName', 'List All Exporter')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#', 'data-en' => 'Home', 'data-bm' => 'Laman Utama']]" title="All Exporter List" title_en="All Exporter List" title_bm="Senarai Semua Pengeksport">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    {{-- <div class="card-title" data-en="Exporter List" data-bm="Senarai Pengeksport">Exporter List</div> --}}
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
                                <select class="form-select form-select-sm select2" id="filterExporterCountry">
                                    @foreach ($country as $coun)
                                        <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                                    @endforeach
                                </select>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetExporterFilter" data-en="Reset" data-bm="Tetap Semula">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnExporterFilter" data-en="Apply" data-bm="Cari">Apply</button>
                            </li>
                        </ul>

                        <div class="btn btn-primary btn-sm" id="addExporter">
                            <i class="ti ti-plus me-1"></i> <span data-en="Add Exporter" data-bm="Tambah Pengeksport">Add Exporter</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="exporterTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th data-en="Exporter Name" data-bm="Nama Pengeksport">Exporter Name</th>
                                    <th data-en="Phone No" data-bm="No Telefon">Phone No</th>
                                    <th data-en="Address" data-bm="Alamat">Address</th>
                                    <th data-en="Country" data-bm="Negara">Country</th>
                                    <th data-en="Action" data-bm="Tindakan">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Add Exporter Modal -->
    <x-modal id="addExporterModal" title="Add Exporter">
        <form id="addExporterForm">
            @csrf

            <input type="hidden" name="id" id="id">
            {{-- Name --}}
            <div class="mb-3">
                <label for="addexpName" class="form-label" data-en="Name" data-bm="Nama">Name</label> <a style="color:red"> * </a>
                <input type="text" id="addexpName" name="addexpName" class="form-control">
            </div>

            {{-- Phone --}}
            <div class="mb-3">
                <label for="addexpfonno" class="form-label" data-en="Phone No" data-bm="No Telefon">Phone No</label> <a style="color:red"> * </a>
                <input type="number" id="addexpfonno" name="addexpfonno" class="form-control" min="0" step="1">
            </div>

            {{-- Address --}}
            <div class="mb-3">
                <label for="addexpaddress" class="form-label" data-en="Address" data-bm="Alamat">Address</label> <a style="color:red"> * </a>
                {{-- <input type="text" id="addexpaddress1" name="addexpaddress1" class="form-control mb-2"> --}}
                <textarea id="addexpaddress1" name="addexpaddress1" class="form-control" rows="3" ></textarea>
                {{-- <input type="text" id="addexpaddress2" name="addexpaddress2" class="form-control"> --}}
            </div>

            {{-- Country --}}
            <div class="mb-3">
                <label for="addexpcountry" class="form-label" data-en="Country" data-bm="Negara">Country</label> <a style="color:red"> * </a>
                <select class="form-select" id="addexpcountry" name="addexpcountry">
                    <option value="" data-en="-- Select Country --" data-bm="-- Pilih Negara --">-- Select Country --</option>
                    @foreach ($country as $coun)
                        <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                    @endforeach
                </select>
            </div>

            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>

                <button type="button" id="addExporterbtn" class="btn btn-primary" data-en="Save Exporter" data-bm="Simpan Pengeksport">
                    Save Exporter
                </button>
            @endslot
        </form>
    </x-modal>

@endsection

@push('scripts')
@endpush
