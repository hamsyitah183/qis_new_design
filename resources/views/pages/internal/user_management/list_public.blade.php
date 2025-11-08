@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/user_management/public_list.js'])
@endpush


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'Public User List', 'url' => '#']]" title="Public User List">

    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="ms-auto">
                        <button class="btn btn-success btn-sm addPublicUser-modal">Add Public User</button>
                    </div>
                </div>

                <div class="card-body">
                    <table id="publicUsersTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>

                                <th>Name</th>
                                <th>Account Type</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Created At</th>
                                <th>Verified</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody> <!-- Important for DataTables -->
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-modal id="publicUserModal" title='Add Public User'>
        <form id="publicUserForm">
            @csrf
            <div id="formPublicUser" class="alert alert-danger d-none"></div>

            <div class="row g-3">
                <input type="hidden" id="userUuid" name="uuid">

                <div class="col-md-6">
                    <label class="form-label ">Full Name</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label ">IC Number</label>
                    <input type="text" class="form-control" id="no_ic" name="no_ic" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Account Type</label>
                    <select class="form-select" id="account_type" name="account_type" required>
                        <option value="individu">Individu</option>
                        <option value="company">Company</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Office Number</label>
                    <input type="text" class="form-control" id="office_number" name="office_number">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Address 1</label>

                    <textarea class="form-control" name="address_1" id="address_1" cols="30" rows="2" required></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Address 2</label>

                    <textarea class="form-control" id="address_2" name="address_2" cols="30" rows="2"></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" class="form-control" id="state" name="state" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">District</label>
                    <input type="text" class="form-control" id="district" name="district" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Postcode</label>
                    <input type="text" class="form-control" id="postcode" name="postcode" required>
                </div>




            </div>

            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="publicUserForm" class="btn btn-primary" id="saveUserBtn">Save</button>
            @endslot
        </form>
    </x-modal>

    <x-modal id="verificationModal" title="User Verification">
        <form id="userVerificationForm">
            @csrf
            {{-- Add any form fields here if needed --}}

            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="userVerificationForm" class="btn btn-primary"
                    id="verificationBtn">Save</button>
            @endslot
        </form>
    </x-modal>
@endsection
