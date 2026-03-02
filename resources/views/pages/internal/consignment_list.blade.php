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

@section('pageName', 'List All Consignment Certificate')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="All Consignment Certificate List">

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
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>Filter
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="filterDropdownBtn">
                            <div class="row gx-3">
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1">Status</label>
                                        <select id="filterStatus" class="form-select form-select-sm">
                                            <option value="">All Statuses</option>
                                            <option value="Draft">Draft</option>
                                            <option value="Application Submitted">Application Submitted</option>
                                            <option value="Clerk Review In-Progress">Clerk Review In-Progress</option>
                                            <option value="Clerk Verified">Clerk Verified</option>
                                            <option value="Clerk Rejected">Clerk Rejected</option>
                                            <option value="Officer Verification Completed">Officer Verification Completed</option>
                                            <option value="Not Approved">Not Approved</option>
                                        </select>
                                    </li>
                                </div>
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1">Public User</label>
                                        <select id="filterPublicUser" class="form-select form-select-sm">
                                            <option value="">All Users</option>
                                        </select>
                                    </li>
                                </div>
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1">Exporter</label>
                                        <select id="filterExporter" class="form-select form-select-sm">
                                            <option value="">All Exporters</option>
                                        </select>
                                    </li>
                                </div>
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1">Importer</label>
                                        <select id="filterImporter" class="form-select form-select-sm">
                                            <option value="">All Importers</option>
                                        </select>
                                    </li>
                                </div>
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1">Submitted By</label>
                                        <input type="text" id="filterUsername" class="form-control form-control-sm" placeholder="Enter username">
                                    </li>
                                </div>
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1">Start Date</label>
                                        <input type="date" id="filterStartDate" class="form-control form-control-sm">
                                    </li>
                                </div>
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1">End Date</label>
                                        <input type="date" id="filterEndDate" class="form-control form-control-sm">
                                    </li>
                                </div>
                            </div>
                            <li class="d-flex justify-content-end gap-2 mt-2">
                                <button class="btn btn-sm btn-secondary" id="btnResetFilter">
                                    <i class="ti ti-refresh"></i> Reset
                                </button>
                                <button class="btn btn-sm btn-primary" id="btnFilter">
                                    <i class="ti ti-filter"></i> Apply
                                </button>
                            </li>
                        </ul>

                        @if($type === 'internal')
                            <button type="button" id="btnOpenExportModal" class="btn btn-sm btn-info">
                                <i class="ti ti-download"></i> Download Report
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
                                            <th>Importer</th>
                                            <th>Exporter</th>
                                            <th>Application Status</th>
                                            <th>Permit Status</th>
                                            <th>Submitted By</th>
                                            <th>Action</th>
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

    @if($type === 'internal')
        {{-- 📑 Consignment Export Modal --}}
        <x-modal id="consignmentExportModal" title="Download Report" size="modal-dialog-centered">
            <div class="p-3 text-center">
                <p>Select the format for your exported report. The current filters will be applied.</p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn btn-success btn-lg" id="btnConfirmExportExcel">
                        <i class="ti ti-file-spreadsheet fs-20"></i><br>Excel (CSV)
                    </button>
                    <button type="button" class="btn btn-danger btn-lg" id="btnConfirmExportPdf">
                        <i class="ti ti-file-description fs-20"></i><br>PDF Document
                    </button>
                </div>
            </div>
            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            @endslot
        </x-modal>
    @endif

@endsection