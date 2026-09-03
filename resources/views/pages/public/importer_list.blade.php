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
    @vite(['resources/js/pages/importer_list.js'])
@endpush

@section('pageName', 'List All Consignment Importer')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#', 'data-en' => 'Home', 'data-bm' => 'Laman Utama']]" title="All Consignment Certificate Importer List" title_en="All Consignment Certificate Importer List" title_bm="Senarai Semua Pengimport Sijil Konsainan">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    {{-- <div class="card-title" data-en="Importer List" data-bm="Senarai Pengimport">Importer List</div> --}}
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
                                <select class="form-select form-select-sm select2" id="filterImporterCountry">
                                    @foreach ($country as $coun)
                                        <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                                    @endforeach
                                </select>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetImporterFilter" data-en="Reset" data-bm="Tetap Semula">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnImporterFilter" data-en="Apply" data-bm="Cari">Apply</button>
                            </li>
                        </ul>

                        <div class="btn btn-primary btn-sm" id="addImporter">
                            <i class="ti ti-plus me-1"></i> <span data-en="Add Importer" data-bm="Tambah Pengimport">Add Importer</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="importerTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th data-en="Importer Name" data-bm="Nama Pengimport">Importer Name</th>
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

    <!-- Add Importer Modal -->
    <x-modal id="addImporterModal" title="Add Importer" title_en="Add Importer" title_bm="Tambah Pengimport" size="modal-lg">
        <form id="addImporterForm">
            @csrf

            <input type="hidden" name="id" id="id">

            {{-- Name --}}
            <div class="mb-3">
                <label for="addimpName" class="form-label" data-en="Name" data-bm="Nama">Name  </label><span class="text-danger">*</span>
                <input type="text" id="addimpName" name="name" class="form-control" required>
            </div>

            {{-- Phone --}}
            <div class="mb-3">
                <label for="addimpfonno" class="form-label" data-en="Phone No" data-bm="No Telefon">Phone No</label> <span class="text-danger">*</span>
                <input type="number" id="addimpfonno" name="phone_no" class="form-control" min="0" step="1" required >
            </div>

            {{-- Address --}}
            <div class="mb-3">
                <label for="addimpaddress" class="form-label" data-en="Address" data-bm="Alamat">Address</label>  <span class="text-danger">*</span>
                <textarea  id="addimpaddress1" name="address1" class="form-control mb-2"
                    placeholder="Address" data-en="Address" data-bm="Alamat" data-i18n-attr="placeholder">
                </textarea>
                {{-- <input type="text" id="addimpaddress2" name="address2" class="form-control" placeholder="Address Line 2" data-en="Address Line 2" data-bm="Alamat Baris 2" data-i18n-attr="placeholder"> --}}
            </div>

            {{-- Country --}}
            <div class="mb-3">
                <label for="addimpcountry" class="form-label" data-en="Country" data-bm="Negara">Country </label> <span class="text-danger">*</span>
                <select class="form-select" id="addimpcountry" name="country" required>
                    <option value="" data-en="-- Select Country --" data-bm="-- Pilih Negara --">-- Select Country --</option>
                    <option value="SWK">Sarawak, Malaysia</option>
                    <option value="BN">Brunei Darussalam</option>
                </select>
            </div>

            @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>

            <button type="button" id="addImporterBtn" class="btn btn-primary"
                data-route="{{ route('public.storeImporter') }}" data-en="Save Importer" data-bm="Simpan Pengimport">
                Save Importer
            </button>
            @endslot
        </form>
    </x-modal>

@endsection

@push('scripts')
@endpush