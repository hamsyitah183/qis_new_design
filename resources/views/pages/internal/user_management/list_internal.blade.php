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
        let isAdminOrSuperadmin = @json($isAdminOrSuperadmin);
    </script>

    @vite(['resources/js/pages/internal/user_management/internal_list.js'])
@endpush


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'], ['label' => 'Internal User List', 'url' => '#', 'data-en' => 'Internal User List', 'data-bm' => 'Senarai Pengguna Dalaman']]"
        title="Internal User List" title_en="Internal User List" title_bm="Senarai Pengguna Dalaman">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    @can('create internal user')
                        <div class="ms-auto">
                            <button class="btn btn-success btn-sm addInternalUser-modal"><span data-en="Add Internal User" data-bm="Tambah Pengguna Dalaman">Add Internal User</span></button>
                        </div>
                    @endcan
                </div>

                <div class="card-body">
                    <table id="internalUsersTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>

                                <th data-en="Name" data-bm="Nama">Name</th>

                                <th data-en="Email" data-bm="E-mel">Email</th>
                                <th data-en="Phone Number" data-bm="Nombor Telefon">Phone Number</th>
                                <th data-en="Position" data-bm="Jawatan">Position</th>
                                <th data-en="Role" data-bm="Peranan">Role</th>
                                <th data-en="Branch" data-bm="Cawangan">Branch</th>
                                <th data-en="Action" data-bm="Tindakan">Action</th>
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
                    <h5 class="modal-title" id="internalUserModalLabel" data-en="Internal User Details" data-bm="Butiran Pengguna Dalaman">Internal User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="internalUserForm">
                    @csrf
                    <div class="modal-body">
                        <div id="formInternalUser" class="alert alert-danger d-none"></div>

                        <div class="row g-3">
                            <input type="hidden" id="userUuid" name="uuid">

                            <div class="col-md-6">
                                <label class="form-label text-default"><span data-en="Full Name" data-bm="Nama Penuh">Full Name</span> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                                <div class="invalid-feedback" id="error-fullname"></div>

                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-default"><span data-en="IC Number" data-bm="Nombor KP">IC Number</span> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="no_ic" name="no_ic" required>
                                <div class="invalid-feedback" id="error-no_ic"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-default"><span data-en="Email" data-bm="E-mel">Email</span> <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback" id="error-email"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-default"><span data-en="Phone Number" data-bm="Nombor Telefon">Phone Number</span> <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                                <div class="invalid-feedback" id="error-phone"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-default"><span data-en="Position" data-bm="Jawatan">Position</span> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="position" name="position" required>
                                <div class="invalid-feedback" id="error-position"></div>
                            </div>

                            <div class="col-md-6 d-none">
                                <label class="form-label text-default"><span data-en="Office" data-bm="Pejabat">Office</span> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="office" name="office">
                                <div class="invalid-feedback" id="error-office"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-default"><span data-en="Role" data-bm="Peranan">Role</span> <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="superadmin" data-en="Superadmin" data-bm="Pentadbir Super">Superadmin</option>
                                    <option value="admin" data-en="Admin" data-bm="Pentadbir">Admin</option>
                                    <option value="officer" data-en="Officer" data-bm="Pegawai">Officer</option>
                                    <option value="clerk" data-en="Clerk" data-bm="Kerani">Clerk</option>
                                    <option value="boundary officer" data-en="Boundary Officer" data-bm="Pegawai Sempadan">Boundary Officer</option>
                                    <option value="finance" data-en="Finance" data-bm="Kewangan">Finance</option>
                                </select>
                            </div>

                            @if ($isAdminOrSuperadmin)
                            <div class="col-md-6" id="branchField">
                                <label class="form-label text-default" data-en="Branch" data-bm="Cawangan">Branch</label>
                                <select class="form-select" id="branch" name="branch">
                                    <option value="" data-en="Select Branch" data-bm="Pilih Cawangan">Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="error-branch"></div>
                            </div>
                            @endif

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span data-en="Close" data-bm="Tutup">Close</span></button>
                        <button type="submit" class="btn btn-success"><span data-en="Save Changes" data-bm="Simpan Perubahan">Save Changes</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection