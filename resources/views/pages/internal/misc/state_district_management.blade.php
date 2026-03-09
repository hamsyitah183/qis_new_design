@extends('pages.app')

@section('pageName', 'State & District Management')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#'], ['label' => 'State & District', 'url' => '#']]"
        title="State & District Management">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Manage States and Districts</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="statesTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>State Name</th>
                                    <th class="text-center">District Count</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- District Management Modal -->
    <div class="modal fade" id="districtManagementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStateTitle">Manage Districts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add District -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Add New District</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="newDistrictInput"
                                placeholder="Enter district name and press Enter or click Add">
                            <button class="btn btn-primary" type="button" id="addDistrictBtn">
                                <i class="ri-add-line me-1"></i>Add
                            </button>
                        </div>
                    </div>

                    <!-- Districts DataTable -->
                    <label class="form-label fw-semibold">Districts List</label>
                    <div class="table-responsive">
                        <table id="districtsTable" class="table table-bordered table-sm table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>District Name</th>
                                    <th class="text-center" style="width:80px">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/pages/internal/state_district_management.js'])
@endpush