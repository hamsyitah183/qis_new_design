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
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="Review Application">

    </x-breadcrumb>
@endsection

@section('content')

    
     <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title">Application List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="agentAppFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            Filter
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="agentAppFilterDropdown">

                            {{-- Status --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Application Status</label>
                                <select class="form-select form-select-sm" id="filterAgentStatus">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="clerk_review">Clerk Review In-Progress</option>
                                    <option value="clerk_verified">Clerk Verified</option>
                                    <option value="officer_verified">Officer Verification Completed</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </li>

                            {{-- Application Type --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Application Type</label>
                                <select class="form-select form-select-sm" id="filterAgentAppType">
                                    <option value="">All Types</option>
                                    <option value="import_permit">Import Permit</option>
                                    <option value="inspection">Inspection Certificate</option>
                                    <option value="consignment">Consignment Certificate</option>
                                </select>
                            </li>

                            {{-- Date Range --}}
                            <li class="mb-2">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1">Start Date</label>
                                        <input type="date" class="form-control form-control-sm" id="filterAgentStartDate">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1">End Date</label>
                                        <input type="date" class="form-control form-control-sm" id="filterAgentEndDate">
                                    </div>
                                </div>
                            </li>

                            {{-- Submitted By --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Submitted By</label>
                                <input type="text" class="form-control form-control-sm" id="filterAgentSubmittedBy"
                                    placeholder="Search by name or email...">
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetAgentFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnAgentFilter">Apply</button>
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
                                            <th>Importer</th>
                                            <th>Exporter</th>
                                            
                                            {{-- <th style="text-align: center;">Importer Type</th> <!-- self or other --> --}}
                                            {{-- <th>ETA</th> --}}
                                            <th>Application Type</th>
                                            <th>Status</th>
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
@endsection

@push('scripts')
@endpush
