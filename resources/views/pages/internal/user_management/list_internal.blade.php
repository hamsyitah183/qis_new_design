@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@php
    $userId = authUser()['user']->uuid;
    // dd($userId);
@endphp

@push('scripts')
    <script>
        let userId = @json($userId);
        // console.log('Current User ID:', userId);

    </script>

    @vite(['resources/js/pages/internal/user_management/internal_list.js'])
@endpush


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'Internal User List', 'url' => '#']]"
        title="Internal User List">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="ms-auto">
                        <button class="btn btn-success btn-sm addInternalUser-modal">Add Internal User</button>
                    </div>
                </div>

                <div class="card-body">
                    <table id="internalUsersTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>

                                <th>Name</th>

                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Position</th>
                                <th>Role</th>
                                <th>Office</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody> <!-- Important for DataTables -->
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Internal User Modal -->
    <div class="modal fade" id="internalUserModal" tabindex="-1" aria-labelledby="internalUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="internalUserModalLabel">Internal User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="internalUserForm">
                    @csrf
                    <div class="modal-body">
                        <div id="formInternalUser" class="alert alert-danger d-none"></div>

                        <div class="row g-3">
                            <input type="hidden" id="userUuid" name="uuid">

                            <div class="col-md-6">
                                <label class="form-label ">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                                <div class="invalid-feedback" id="error-fullname"></div>

                            </div>

                            <div class="col-md-6">
                                <label class="form-label ">IC Number</label>
                                <input type="text" class="form-control" id="no_ic" name="no_ic" required>
                                <div class="invalid-feedback" id="error-no_ic"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback" id="error-email"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                                <div class="invalid-feedback" id="error-phone"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position" required>
                                <div class="invalid-feedback" id="error-position"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Office</label>
                                <input type="text" class="form-control" id="office" name="office" required>
                                <div class="invalid-feedback" id="error-office"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="admin">Admin</option>
                                    <option value="officer">Officer</option>
                                    <option value="clerk">Clerk</option>
                                    <option value="boundary officer">Boundary Officer</option>
                                    <option value="finance">Finance</option>
                                </select>
                            </div>


                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection