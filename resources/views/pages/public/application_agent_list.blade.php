@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .filter-dropdown {
            width: 500px;
        }

        @media (max-width: 768px) {
            .filter-dropdown {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/importPermit/application_list.js'])
@endpush

@section('pageName', 'List All Application')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#', 'data-en' => 'Home', 'data-bm' => 'Laman Utama']]" title="Representative List" title_en="Representative List" title_bm="Senarai Wakil">

    </x-breadcrumb>
@endsection

@section('content')

    
     <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title" data-en="Application List" data-bm="Senarai Permohonan">Application List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="agentAppFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            <span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="agentAppFilterDropdown">

                            {{-- Status --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Application Status" data-bm="Status Permohonan">Application Status</label>
                                <select class="form-select form-select-sm select2" id="filterAgentStatus">
                                    <option value="draft" data-en="Draft" data-bm="Draf">Draft</option>
                                    <option value="submitted" data-en="Submitted" data-bm="Dihantar">Submitted</option>
                                    <option value="clerk_review" data-en="Clerk Review In-Progress" data-bm="Dalam Semakan Kerani">Clerk Review In-Progress</option>
                                    <option value="clerk_verified" data-en="Clerk Verified" data-bm="Kerani Disahkan">Clerk Verified</option>
                                    <option value="officer_verified" data-en="Officer Verification Completed" data-bm="Pengesahan Pegawai Selesai">Officer Verification Completed</option>
                                    <option value="rejected" data-en="Rejected" data-bm="Ditolak">Rejected</option>
                                </select>
                            </li>

                            {{-- Application Type --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Application Type" data-bm="Jenis Permohonan">Application Type</label>
                                <select class="form-select form-select-sm select2" id="filterAgentAppType">
                                    <option value="import_permit" data-en="Import Permit" data-bm="Permit Import">Import Permit</option>
                                    <option value="inspection" data-en="Inspection Certificate" data-bm="Sijil Pemeriksaan">Inspection Certificate</option>
                                    <option value="consignment" data-en="Consignment Certificate" data-bm="Sijil Konsainan">Consignment Certificate</option>
                                </select>
                            </li>

                            {{-- Date Range --}}
                            <li class="mb-2">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1" data-en="Start Date" data-bm="Tarikh Mula">Start Date</label>
                                        <input type="date" class="form-control form-control-sm" id="filterAgentStartDate">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1" data-en="End Date" data-bm="Tarikh Akhir">End Date</label>
                                        <input type="date" class="form-control form-control-sm" id="filterAgentEndDate">
                                    </div>
                                </div>
                            </li>

                            {{-- Submitted By --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Submitted By" data-bm="Dihantar Oleh">Submitted By</label>
                                <input type="text" class="form-control form-control-sm" id="filterAgentSubmittedBy"
                                    placeholder="Search by name or email..." data-en="Search by name or email..." data-bm="Cari dengan nama atau e-mel..." data-i18n-attr="placeholder">
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetAgentFilter" data-en="Reset" data-bm="Tetap Semula">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnAgentFilter" data-en="Apply" data-bm="Cari">Apply</button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <div id="" class="dataTables_wrapper dt-bootstrap5 no-footer">

                        <div class="row">
                            <div class="col-sm-12">
                                <table id="agentApplicationListTable"
                                    class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                                    aria-describedby="responsiveDataTable_info" style="width: 1588px;">
                                    <thead class="mt-3">
                                        <tr class="even">
                                            <th>#</th>
                                            <th data-en="Importer" data-bm="Pengimport">Importer</th>
                                            <th data-en="Exporter" data-bm="Pengeksport">Exporter</th>
                                            
                                            {{-- <th style="text-align: center;">Importer Type</th> <!-- self or other --> --}}
                                            {{-- <th>ETA</th> --}}
                                            <th data-en="Application Type" data-bm="Jenis Permohonan">Application Type</th>
                                            <th data-en="Status" data-bm="Status">Status</th>
                                            <th data-en="Submitted By" data-bm="Dihantar Oleh">Submitted By</th>
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
@endsection

@push('scripts')
@endpush
