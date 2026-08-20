@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

    <style>
        .filter-dropdown {
            width: 600px;
        }

        @media (max-width: 768px) {
            .filter-dropdown {
                width: 100%;
                left: 0 !important;
                right: 0 !important;
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
    @vite(['resources/js/pages/consignment/consignment_list.js'])
@endpush

@section('pageName', 'Consignment Certificate List')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#', 'data-en' => 'Home', 'data-bm' => 'Laman Utama']]" title="Consignment Certificate List" title_en="Consignment Certificate List" title_bm="Senarai Sijil Konsainan">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    <div class="ms-auto d-flex gap-2 align-items-center">
                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="filterDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span><span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="filterDropdownBtn">
                            <div class="row gx-3">
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1" data-en="Status" data-bm="Status">Status</label>
                                        <select id="filterStatus" class="form-select form-select-sm select2">
                                            <option value="" data-en="All Statuses" data-bm="Semua Status">All Statuses</option>
                                            {{-- Match actual application status values --}}
                                            <option value="Draft" data-en="Draft" data-bm="Draf">Draft</option>
                                            <option value="Application Submitted" data-en="Application Submitted" data-bm="Permohonan Dihantar">Application Submitted</option>
                                            <option value="Clerk Review In-Progress" data-en="Clerk Review In-Progress" data-bm="Dalam Semakan Kerani">Clerk Review In-Progress</option>
                                            <option value="Clerk Verified" data-en="Clerk Verified" data-bm="Kerani Disahkan">Clerk Verified</option>
                                            <option value="Clerk Rejected" data-en="Rejected" data-bm="Ditolak">Rejected</option>
                                            <option value="Officer Verification Completed" data-en="Officer Verification Completed" data-bm="Pengesahan Pegawai Selesai">Officer Verification Completed</option>
                                            <option value="Not Approved" data-en="Not Approved" data-bm="Tidak Diluluskan">Not Approved</option>
                                            <option value="wait for company approval" data-en="Wait for Company Approval" data-bm="Tunggu Kelulusan Syarikat">Wait for Company Approval</option>
                                            <option value="Completed" data-en="Completed" data-bm="Selesai">Completed</option>
                                        </select>
                                    </li>
                                </div>
                                @if (authUser()['type'] == 'internal')
                                    <div class="col-12 col-md-6">
                                        <li class="mb-3">
                                            <label class="form-label fw-semibold mb-1" data-en="Public User" data-bm="Pengguna Awam">Public User</label>
                                            <select id="filterPublicUser" class="form-select form-select-sm select2">
                                                <option value="" data-en="All Users" data-bm="Semua Pengguna">All Users</option>
                                            </select>
                                        </li>
                                    </div>
                                @endif
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1" data-en="Exporter" data-bm="Pengeksport">Exporter</label>
                                        <select id="filterExporter" class="form-select form-select-sm select2">
                                            <option value="" data-en="All Exporters" data-bm="Semua Pengeksport">All Exporters</option>
                                        </select>
                                    </li>
                                </div>
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1" data-en="Importer" data-bm="Pengimport">Importer</label>
                                        <select id="filterImporter" class="form-select form-select-sm select2">
                                            <option value="" data-en="All Importers" data-bm="Semua Pengimport">All Importers</option>
                                        </select>
                                    </li>
                                </div>
                                @if (authUser()['type'] == 'internal')
                                    <div class="col-12 col-md-6">
                                        <li class="mb-3">
                                            <label class="form-label fw-semibold mb-1" data-en="Submitted By" data-bm="Dihantar Oleh">Submitted By</label>
                                            <input type="text" id="filterUsername" class="form-control form-control-sm" placeholder="Enter username" data-en="Enter username" data-bm="Masukkan nama pengguna" data-i18n-attr="placeholder">
                                        </li>
                                    </div>
                                @endif
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1" data-en="Start Date" data-bm="Tarikh Mula">Start Date</label>
                                        <input type="date" id="filterStartDate" class="form-control form-control-sm">
                                    </li>
                                </div>
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1" data-en="End Date" data-bm="Tarikh Akhir">End Date</label>
                                        <input type="date" id="filterEndDate" class="form-control form-control-sm">
                                    </li>
                                </div>
                            </div>
                            <li class="d-flex justify-content-end gap-2 mt-2">
                                <button class="btn btn-sm btn-secondary" id="btnResetFilter">
                                    <i class="ti ti-refresh"></i> <span data-en="Reset" data-bm="Tetap Semula">Reset</span>
                                </button>
                                <button class="btn btn-sm btn-primary" id="btnFilter">
                                    <i class="ti ti-filter"></i> <span data-en="Apply" data-bm="Cari">Apply</span>
                                </button>
                            </li>
                        </ul>

                        @if($type === 'internal')
                            <button type="button" id="btnOpenExportModal" class="btn btn-sm btn-info">
                                <i class="ti ti-download"></i> <span data-en="Download Report" data-bm="Muat Turun Laporan">Download Report</span>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div id="" class="dataTables_wrapper dt-bootstrap5 no-footer">

                        <div class="row">
                            <div class="col-sm-12">
                                <table id="consignmentListTable"
                                    class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                                    aria-describedby="responsiveDataTable_info" style="width: 100%;">
                                    <thead class="mt-3">
                                        <tr class="even">
                                            <th>#</th>
                                            <th data-en="Importer" data-bm="Pengimport">Importer</th>
                                            <th data-en="Exporter" data-bm="Pengeksport">Exporter</th>
                                            <th data-en="Application Status" data-bm="Status Permohonan">Application Status</th>
                                            <th data-en="Permit Status" data-bm="Status Permit">Permit Status</th>
                                            @if (authUser()['type'] == 'internal')
                                                <th data-en="Submitted By" data-bm="Dihantar Oleh">Submitted By</th>
                                            @endif
                                            <th data-en="Action" data-bm="Tindakan">Action</th>
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

    <x-modal id="activityLogModal" title="Activity Log">

        <!-- Your table goes here -->
        <div class="table-responsive">
            <table class="table text-wrap table-hover" id="applicationLogTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" data-en="Action" data-bm="Tindakan">Action</th>
                        <th scope="col" data-en="User" data-bm="Pengguna">User</th>
                        <th scope="col" data-en="Remark" data-bm="Catatan">Remark</th>
                        <th scope="col" data-en="Status" data-bm="Status">Status</th>
                        <th scope="col" data-en="Time and Date" data-bm="Masa dan Tarikh">Time and Date</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">

                </tbody>
            </table>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
        @endslot

    </x-modal>

    @if($type === 'internal')
        {{-- 📑 Consignment Export Modal --}}
        <x-modal id="consignmentExportModal" title="Download Report" size="modal-dialog-centered">
            <div class="p-3 text-center">
                <p data-en="Select the format for your exported report. The current filters will be applied." data-bm="Pilih format untuk laporan yang dieksport. Tapis semasa akan digunakan.">Select the format for your exported report. The current filters will be applied.</p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-success btn-md d-flex align-items-center gap-2" id="btnConfirmExportExcel">
                        <i class="ti ti-file-spreadsheet fs-20"></i> Excel (CSV)
                    </button>
                    <button type="button" class="btn btn-danger btn-md d-flex align-items-center gap-2" id="btnConfirmExportPdf">
                        <i class="ti ti-file-description fs-20"></i> PDF Document
                    </button>
                </div>
            </div>
            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>
            @endslot
        </x-modal>
    @endif

@endsection

@push('scripts')
@endpush
