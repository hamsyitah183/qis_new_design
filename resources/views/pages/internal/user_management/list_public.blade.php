@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

    <style>
        .filter-dropdown {
            width: 450px;
            /* 🖥️ Desktop size */
        }

        @media (max-width: 768px) {
            .filter-dropdown {
                width: 100%;
                /* 📱 Mobile full width */
                left: 0 !important;
                right: 0 !important;
            }
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/user_management/public_list.js'])
@endpush


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'Public User List', 'url' => '#']]"
        title="Public User List" title_en="Public User List" title_bm="Senarai Pengguna Awam">

    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="ms-auto">
                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="dropdownMenuClickableOutside" data-bs-toggle="dropdown" data-bs-auto-close="inside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            <span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="dropdownMenuClickableOutside">
                            <div class="row gx-3">
                                <!-- Account Type Filter -->
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1" data-en="Account Type" data-bm="Jenis Akaun">Account Type</label>
                                        <select class="form-select form-select-sm" id="filterAccountType">
                                            <option value="" data-en="All" data-bm="Semua">All</option>
                                            <option value="individu" data-en="Individu" data-bm="Individu">Individu</option>
                                            <option value="company" data-en="Company" data-bm="Syarikat">Company</option>
                                        </select>
                                    </li>
                                </div>

                                <!-- Email Verification Filter -->
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1" data-en="Email Verification Status" data-bm="Status Pengesahan E-mel">Email Verification Status</label>
                                        <select class="form-select form-select-sm" id="filterEmailVerification">
                                            <option value="" data-en="All" data-bm="Semua">All</option>
                                            <option value="verified" data-en="Verified" data-bm="Disahkan">Verified</option>
                                            <option value="not_verified" data-en="Not Verified" data-bm="Belum Disahkan">Not Verified</option>
                                        </select>
                                    </li>
                                </div>

                                <!-- Account Verification Filter -->
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1" data-en="Account Verification Status" data-bm="Status Pengesahan Akaun">Account Verification Status</label>
                                        <select class="form-select form-select-sm" id="filterAccountVerification">
                                            <option value="" data-en="All" data-bm="Semua">All</option>
                                            <option value="verified" data-en="Verified" data-bm="Disahkan">Verified</option>
                                            <option value="not_verified" data-en="Not Verified" data-bm="Belum Disahkan">Not Verified</option>
                                        </select>
                                    </li>
                                </div>

                                <!-- Sort By Time Filter -->
                                <div class="col-12 col-md-6">
                                    <li class="mb-3">
                                        <label class="form-label fw-semibold mb-1" data-en="Sort By" data-bm="Susun Mengikut">Sort By</label>
                                        <select class="form-select form-select-sm" id="filterTime">
                                            <option value="" data-en="All" data-bm="Semua">All</option>
                                            <option value="created_at" data-en="Created Date" data-bm="Tarikh Dicipta">Created Date</option>
                                            <option value="latest" data-en="Latest" data-bm="Terkini">Latest</option>
                                        </select>
                                    </li>
                                </div>
                            </div>

                            <!-- Apply & Reset Buttons -->
                            <li class="d-flex justify-content-end gap-2 mt-2">
                                <button class="btn btn-sm btn-secondary" id="resetFilterBtn"><span data-en="Reset" data-bm="Set Semula">Reset</span></button>
                                <button class="btn btn-sm btn-primary" id="applyFilterBtn"><span data-en="Apply" data-bm="Guna">Apply</span></button>
                            </li>
                        </ul>



                        @can('create public user')
                            <button class="btn btn-success btn-sm addPublicUser-modal"> <span class="me-2"><i
                                    class="ti ti-plus"></i></span> <span data-en="Add Public User" data-bm="Tambah Pengguna Awam">Add Public User</span></button>
                        @endcan
                    </div>

                    <div class="button-group">

                    </div>
                </div>

                <div class="card-body">
                    <table id="publicUsersTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>

                                <th data-en="Name" data-bm="Nama">Name</th>
                                <th data-en="Account Type" data-bm="Jenis Akaun">Account Type</th>
                                <th data-en="Email" data-bm="E-mel">Email</th>
                                <th data-en="Phone Number" data-bm="Nombor Telefon">Phone Number</th>
                                <th data-en="Created At" data-bm="Tarikh Dicipta">Created At</th>
                                <th data-en="Verified" data-bm="Disahkan">Verified</th>
                                <th data-en="Action" data-bm="Tindakan">Action</th>
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
                    <label class="form-label text-default"><span data-en="Full Name" data-bm="Nama Penuh">Full Name</span> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-default"><span data-en="IC Number" data-bm="Nombor KP">IC Number</span> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="no_ic" name="no_ic" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-default"><span data-en="Email" data-bm="E-mel">Email</span> <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-default"><span data-en="Account Type" data-bm="Jenis Akaun">Account Type</span> <span class="text-danger">*</span></label>
                    <select class="form-select" id="account_type" name="account_type" required>
                        <option value="individu" data-en="Individu" data-bm="Individu">Individu</option>
                        <option value="company" data-en="Company" data-bm="Syarikat">Company</option>
                    </select>
                </div>

                <div class="col-md-6">

                    <label class="form-label text-default phoneLabel"><span data-en="Phone Number" data-bm="Nombor Telefon">Phone Number</span> <span
                            class="text-danger">*</span></label>
                    <div class="d-flex justify-content-between">
                        <div class="">
                            <select name="phoneNumber" id="phone_country" class="form-control">
                                @foreach ($countryNo as $item)
                                    <option value="{{  $item->start_no  }}">{{ $item->country }} ({{ $item->start_no }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-100">

                            <input type="text" name="phone_number" class="form-control" id="phone_number">
                        </div>
                    </div>

                </div>

                <div class="col-md-6">
                    <label class="form-label"><span data-en="Office Number" data-bm="Nombor Pejabat">Office Number</span></label>
                    <input type="text" class="form-control" id="office_number" name="office_number">
                </div>

                <div class="col-md-12">
                    <label class="form-label text-default"><span data-en="Address 1" data-bm="Alamat 1">Address 1</span> <span class="text-danger">*</span></label>

                    <textarea class="form-control" name="address_1" id="address_1" cols="30" rows="2" required></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label"><span data-en="Address 2" data-bm="Alamat 2">Address 2</span></label>

                    <textarea class="form-control" id="address_2" name="address_2" cols="30" rows="2"></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-default"><span data-en="State" data-bm="Negeri">State</span> <span class="text-danger">*</span></label>
                    <select class="form-select state-modal" id="state" name="state" required>
                        <option value="" data-en="Select State" data-bm="Pilih Negeri">Select State</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-default"><span data-en="District" data-bm="Daerah">District</span> <span class="text-danger">*</span></label>
                    <select class="form-select district-modal" id="district" name="district" required>
                        <option value="" data-en="Select District" data-bm="Pilih Daerah">Select District</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-default"><span data-en="Postcode" data-bm="Poskod">Postcode</span> <span class="text-danger">*</span></label>
                    <select class="form-select postcode-modal" id="postcode" name="postcode" required>
                        <option value="" data-en="Select Postcode" data-bm="Pilih Poskod">Select Postcode</option>
                    </select>
                </div>




            </div>

            @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span data-en="Close" data-bm="Tutup">Close</span></button>
            <button type="submit" form="publicUserForm" class="btn btn-primary" id="saveUserBtn"><span data-en="Save" data-bm="Simpan">Save</span></button>
            @endslot
        </form>
    </x-modal>

    <x-modal id="verificationModal" title="User Verification">
        <form id="userVerificationForm">
            @csrf
            {{-- Add any form fields here if needed --}}
            <div class="mb-2 fs-14"><span class="fw-bold me-2 " data-en="User IC:" data-bm="KP Pengguna:">User IC: </span> <span class="ic"></span></div>


            <div class="" id="userIC">



            </div>

            <div class="status mt-3"></div>
            <div class="fs-12 mt-3"> <span class="fw-bold" data-en="Submitted On:" data-bm="Dihantar Pada:">Submitted On: </span> <span class="updated_at text-muted"></span>
            </div>

            @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span data-en="Close" data-bm="Tutup">Close</span></button>
            <button type="submit" class="btn btn-primary" id="verificationBtn"><span data-en="Verified" data-bm="Disahkan">Verified</span></button>
            <button type="submit" class="btn btn-danger" id="unverificationBtn"><span data-en="Reject" data-bm="Tolak">Reject</span></button>
            @endslot
        </form>
    </x-modal>
@endsection