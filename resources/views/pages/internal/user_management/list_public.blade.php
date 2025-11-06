@extends('pages.app')

@push('style')
  

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

                </div>

                <div class="card-body">
                    <table id="publicUsersTable"
                        class="table table-bordered text-nowrap w-100">
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
@endsection
