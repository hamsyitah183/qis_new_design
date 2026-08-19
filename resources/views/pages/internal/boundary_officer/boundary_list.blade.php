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
    @vite(['resources/js/pages/boundary/boundary_list.js'])
@endpush

@section('pageName', 'Boundary Officer List')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'], ['label' => 'Boundary Officer List', 'url' => '#', 'data-en' => 'Boundary Officer List', 'data-bm' => 'Senarai Pegawai Sempadan']]" title="Boundary Officer List" title_en="Boundary Officer List" title_bm="Senarai Pegawai Sempadan">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title" data-en="Boundary Officer List" data-bm="Senarai Pegawai Sempadan">Boundary Officer List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="boundaryFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            <span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="boundaryFilterDropdown">

                            {{-- Name Search --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Name" data-bm="Nama">Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterBoundaryName"
                                    placeholder="Search by name..." data-en="Search by name..." data-bm="Cari dengan nama..." data-i18n-attr="placeholder">
                            </li>

                            {{-- Place --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Place / Entry Point" data-bm="Tempat / Titik Masuk">Place / Entry Point</label>
                                <input type="text" class="form-control form-control-sm" id="filterBoundaryPlace"
                                    placeholder="Search by place..." data-en="Search by place..." data-bm="Cari dengan tempat..." data-i18n-attr="placeholder">
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetBoundaryFilter"><span data-en="Reset" data-bm="Set Semula">Reset</span></button>
                                <button class="btn btn-sm btn-primary" id="btnBoundaryFilter"><span data-en="Apply" data-bm="Cari">Apply</span></button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <div id="" class="">

                        <div class="row">
                            <div class="col-sm-12">
                                <table id="boundaryTable"
                                    class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                                    style="width: 1588px;">
                                    <thead class="mt-3">
                                        <tr class="even">
                                            <td data-en="Name" data-bm="Nama">Name</td>
                                            <td data-en="Place" data-bm="Tempat">Place</td>
                                            <td data-en="Action" data-bm="Tindakan">Action</td>
                                        </tr>
                                    </thead>
                                    <tbody>


                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <x-modal id="boundaryModal" title="User Details" title_en="User Details" title_bm="Butiran Pengguna">

        <input type="hidden" name="id">
        <div class="my-2">
            <label for="trnptType" class="name"><span data-en="User Name" data-bm="Nama Pengguna">User Name</span></label>
            <input type="text" id="name" readonly class="form-control" name="name">
        </div>

        <div class="my-2">
            <label for="trnptType" class="form-label"><span data-en="Transport Type" data-bm="Jenis Pengangkutan">Transport Type</span></label>
            <select class="form-select" id="trnptType" name="trnptType" data-route="/internal/get_entry_point"
                required>
                <option value="" data-en="-- Select Transport --" data-bm="-- Pilih Pengangkutan --">-- Select Transport --</option>
                <option value="Air" data-en="Air" data-bm="Udara">Air</option>
                <option value="Sea" data-en="Sea" data-bm="Laut">Sea</option>
                <option value="Land" data-en="Land" data-bm="Darat">Land</option>
            </select>
        </div>
       
        <div class="my-2">
            <label for="entryPoint" class="form-label"><span data-en="Entry Point" data-bm="Titik Masuk">Entry Point</span></label>
            <select class="form-select" id="entryPoint" name="entryPoint" required>
                <option value="" data-en="-- Select Entry Point --" data-bm="-- Pilih Titik Masuk --">-- Select Entry Point --</option>
    
            </select>
            <input type="hidden" id="descEntryPoint">
        </div>
        

        @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span data-en="Close" data-bm="Tutup">Close</span></button>
        <button type="button" class="btn btn-primary" id="saveBtn"><span data-en="Save" data-bm="Simpan">Save</span></button>
        @endslot

    </x-modal>

@endsection

@push('scripts')
@endpush
