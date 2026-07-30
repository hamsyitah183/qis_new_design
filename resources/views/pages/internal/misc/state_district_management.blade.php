@extends('pages.app')

@section('pageName', 'State & District Management')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#'], ['label' => 'State & District', 'url' => '#']]"
        title="State & District Management"
        title_en="State & District Management"
        title_bm="Pengurusan Negeri & Daerah">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="Manage States and Districts" data-bm="Urus Negeri dan Daerah">Manage States and Districts</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="statesTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th data-en="State Name" data-bm="Nama Negeri">State Name</th>
                                    <th class="text-center" data-en="District Count" data-bm="Jumlah Daerah">District Count</th>
                                    <th class="text-center" data-en="Action" data-bm="Tindakan">Action</th>
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
                    <h5 class="modal-title" id="modalStateTitle" data-en="Manage Districts" data-bm="Urus Daerah">Manage Districts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add District -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" data-en="Add New District" data-bm="Tambah Daerah Baru">Add New District</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="newDistrictInput"
                                placeholder="Enter district name and press Enter or click Add"
                                data-en="Enter district name and press Enter or click Add"
                                data-bm="Masukkan nama daerah dan tekan Enter atau klik Tambah"
                                data-i18n-attr="placeholder">
                            <button class="btn btn-primary" type="button" id="addDistrictBtn">
                                <i class="ri-add-line me-1"></i><span data-en="Add" data-bm="Tambah">Add</span>
                            </button>
                        </div>
                    </div>

                    <!-- Districts DataTable -->
                    <label class="form-label fw-semibold" data-en="Districts List" data-bm="Senarai Daerah">Districts List</label>
                    <div class="table-responsive">
                        <table id="districtsTable" class="table table-bordered table-sm table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th data-en="District Name" data-bm="Nama Daerah">District Name</th>
                                    <th class="text-center" style="width:80px" data-en="Action" data-bm="Tindakan">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/pages/internal/state_district_management.js'])
@endpush