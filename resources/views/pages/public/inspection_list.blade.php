@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush


@php
    $type = authUser()['type'];

@endphp

@push('scripts')
    <script>
        window.AUTH_TYPE = @json($type);
    </script>
    @vite(['resources/js/pages/inspection/inspection_list.js'])
@endpush

@section('pageName', 'List All Inspection Certificate')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="All Inspection Certificate List">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-body">
                    <div id="" class="dataTables_wrapper dt-bootstrap5 no-footer">

                        <!-- Filter Section -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">Filter Applications</h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label for="filterStatus" class="form-label">Status</label>
                                                <select id="filterStatus" class="form-select">
                                                    <option value="">All Statuses</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="clerk review in-progress">Clerk Review In-Progress</option>
                                                    <option value="clerk verified">Clerk Verified</option>
                                                    <option value="officer verification completed">Officer Verification Completed</option>
                                                    <option value="rejected">Rejected</option>
                                                    <option value="not approved">Not Approved</option>
                                                    <option value="draft">Draft</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filterStartDate" class="form-label">Start Date</label>
                                                <input type="date" id="filterStartDate" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filterEndDate" class="form-label">End Date</label>
                                                <input type="date" id="filterEndDate" class="form-control">
                                            </div>
                                            @if (authUser()['type'] == 'internal')
                                                <div class="col-md-3">
                                                    <label for="filterPublicUser" class="form-label">Public User</label>
                                                    <select id="filterPublicUser" class="form-select">
                                                        <option value="">All Users</option>
                                                    </select>
                                                </div>
                                            @endif
                                            <div class="col-md-3">
                                                <label for="filterExporter" class="form-label">Exporter</label>
                                                <select id="filterExporter" class="form-select">
                                                    <option value="">All Exporters</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filterImporter" class="form-label">Importer</label>
                                                <select id="filterImporter" class="form-select">
                                                    <option value="">All Importers</option>
                                                </select>
                                            </div>
                                            @if (authUser()['type'] == 'internal')
                                                <div class="col-md-3">
                                                    <label for="filterUsername" class="form-label">Submitted By</label>
                                                    <input type="text" id="filterUsername" class="form-control" placeholder="Enter username">
                                                </div>
                                            @endif
                                            <div class="col-md-12">
                                                <button type="button" id="btnFilter" class="btn btn-primary">
                                                    <i class="ti ti-filter"></i> Filter
                                                </button>
                                                <button type="button" id="btnResetFilter" class="btn btn-secondary">
                                                    <i class="ti ti-refresh"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Filter Section -->

                        <div class="row">
                            <div class="col-sm-12">
                                <table id="inspectionListTable"
                                    class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                                    aria-describedby="responsiveDataTable_info" style="width: 100%;">
                                    <thead class="mt-3">
                                        <tr class="even">
                                            <th>#</th>
                                            <th>Importer</th>
                                            <th>Exporter</th>
                                            <th>Application Status</th>
                                            <th>Inspection Status</th>
                                            @if (authUser()['type'] == 'internal')
                                                <th>Submitted By</th>
                                            @endif
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

    <x-modal id="activityLogModal" title="Activity Log">

        <!-- Your table goes here -->
        <div class="table-responsive">
            <table class="table text-nowrap table-hover" id="inspectionLogTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Action</th>
                        <th scope="col">User</th>
                        <th scope="col">Remark</th>
                        <th scope="col">Status</th>
                        <th scope="col">Time and Date</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">

                </tbody>
            </table>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        @endslot

    </x-modal>

@endsection
