@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    @vite(['resources/js/pages/vehicles/vehicle.js'])
@endpush

@section('pageName', 'Vehicle List')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
        ['label' => 'Vehicles', 'url' => '#', 'data-en' => 'Vehicles', 'data-bm' => 'Kenderaan'],
    ]" title="Vehicle List" title_en="Vehicle List" title_bm="Senarai Kenderaan" />
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="Vehicle List" data-bm="Senarai Kenderaan">Vehicle List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">
                        @if (authUser()['type'] == 'public')
                            <button class="btn btn-primary btn-sm" id="btnAddVehicle">
                                <i class="ti ti-plus me-1"></i> <span data-en="Add Vehicle" data-bm="Tambah Kenderaan">Add
                                    Vehicle</span>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="vehicleTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th data-en="Owner" data-bm="Pemilik">Owner</th>
                                    <th data-en="Vehicle Name" data-bm="Nama Kenderaan">Vehicle Name</th>
                                    <th data-en="Vehicle Number" data-bm="Nombor Kenderaan">Vehicle Number</th>
                                    <th data-en="Type" data-bm="Jenis">Type</th>
                                    <th data-en="Registration No." data-bm="No. Pendaftaran">Registration No.</th>
                                    <th data-en="Valid From" data-bm="Berkuatkuasa Dari">Valid From</th>
                                    <th data-en="Valid Until" data-bm="Berkuatkuasa Hingga">Valid Until</th>
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

    <!-- Add/Edit Modal -->
    <x-modal id="addVehicleModal" title="Vehicle" title_en="Vehicle" title_bm="Kenderaan"
        size="modal-lg modal-dialog-centered">
        <form id="addVehicleForm">
            @csrf
            <input type="hidden" name="id" id="vehicle_id">

            <div class="row gy-3">
                <div class="col-xl-12">
                    <label for="vehicleName" class="form-label" data-en="Vehicle Name" data-bm="Nama Kenderaan">Vehicle Name
                        <span class="text-danger">*</span></label>
                    <input type="text" id="vehicleName" name="vehicleName" class="form-control" required>
                </div>

                <div class="col-xl-6">
                    <label for="vehicleNumber" class="form-label" data-en="Vehicle Number (License Plate)"
                        data-bm="Nombor Kenderaan (Plat)">Vehicle Number <span class="text-danger">*</span></label>
                    <input type="text" id="vehicleNumber" name="vehicleNumber" class="form-control" required>
                </div>

                <div class="col-xl-6">
                    <label for="vehicleType" class="form-label" data-en="Vehicle Type" data-bm="Jenis Kenderaan">Vehicle
                        Type</label>
                    <input type="text" id="vehicleType" name="vehicleType" class="form-control">
                </div>

                <div class="col-xl-12">
                    <label for="vehicleRegNumber" class="form-label" data-en="Registration Document Number"
                        data-bm="Nombor Pendaftaran Dokumen">Registration Document Number <span
                            class="text-danger">*</span></label>
                    <input type="text" id="vehicleRegNumber" name="vehicleRegNumber" class="form-control" required>
                </div>

                <div class="col-xl-6">
                    <label for="validFrom" class="form-label" data-en="Valid From" data-bm="Berkuatkuasa Dari">Valid
                        From</label>
                    <input type="date" id="validFrom" name="validFrom" class="form-control">
                </div>

                <div class="col-xl-6">
                    <label for="validUntil" class="form-label" data-en="Valid Until" data-bm="Berkuatkuasa Hingga">Valid
                        Until</label>
                    <input type="date" id="validUntil" name="validUntil" class="form-control">
                </div>
            </div>

            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel"
                    data-bm="Batal">Cancel</button>
                <button type="button" id="btnSaveVehicle" class="btn btn-primary" data-en="Save Vehicle"
                    data-bm="Simpan Kenderaan">Save Vehicle</button>
            @endslot
        </form>
    </x-modal>

    <!-- View Modal -->
    <x-modal id="viewVehicleModal" title="View Vehicle" title_en="View Vehicle" title_bm="Lihat Kenderaan"
        size="modal-lg modal-dialog-centered">
        <div class="row gy-3">
            <div class="col-xl-6"><strong data-en="Vehicle Name" data-bm="Nama Kenderaan">Vehicle Name:</strong> <span
                    id="view_vehicle_name"></span></div>
            <div class="col-xl-6"><strong data-en="Vehicle Number" data-bm="Nombor Kenderaan">Vehicle Number:</strong>
                <span id="view_vehicle_number"></span></div>
            <div class="col-xl-6"><strong data-en="Vehicle Type" data-bm="Jenis Kenderaan">Vehicle Type:</strong> <span
                    id="view_vehicle_type"></span></div>
            <div class="col-xl-6"><strong data-en="Registration No." data-bm="No. Pendaftaran">Registration No.:</strong>
                <span id="view_vehicle_reg_number"></span></div>
            <div class="col-xl-6"><strong data-en="Valid From" data-bm="Berkuatkuasa Dari">Valid From:</strong> <span
                    id="view_valid_from"></span></div>
            <div class="col-xl-6"><strong data-en="Valid Until" data-bm="Berkuatkuasa Hingga">Valid Until:</strong> <span
                    id="view_valid_until"></span></div>
            <div class="col-xl-12"><strong data-en="Owner" data-bm="Pemilik">Owner:</strong> <span
                    id="view_owner"></span></div>
        </div>
        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close"
                data-bm="Tutup">Close</button>
        @endslot
    </x-modal>
@endsection
