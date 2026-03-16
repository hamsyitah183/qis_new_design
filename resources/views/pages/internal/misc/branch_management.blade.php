@extends('pages.app')

@section('pageName', 'Branch Management')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#'], ['label' => 'System Config', 'url' => '#']]"
        title="Branch Management">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between d-sm-flex d-block">
                    <div class="card-title">Branch Management</div>
                    <div class="mt-sm-0 mt-2">
                        <button class="btn btn-sm btn-primary" id="addBranchBtn">
                            <i class="ri-add-line me-1"></i> Add Branch
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="branchTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>Branch Name</th>
                                    <th class="text-center" style="width:120px">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Branch Modal -->
    <div class="modal fade" id="addBranchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-add-line me-1"></i> Add Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Name</label>
                        <input type="text" class="form-control" id="addBranchName" placeholder="Enter branch name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBranchBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Branch Modal -->
    <div class="modal fade" id="editBranchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-edit-line me-1"></i> Edit Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editBranchId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Name</label>
                        <input type="text" class="form-control" id="editBranchName" placeholder="Enter branch name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateBranchBtn">Update</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/branch_management.js'])
@endpush