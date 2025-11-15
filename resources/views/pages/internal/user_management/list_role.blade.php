@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

    <style>
        #roleTable tbody tr {
            cursor: pointer;
        }

        /* Wrapper for smooth layout animation */
        .role-layout {
            display: flex;
            flex-wrap: nowrap;
            gap: 15px;
        }

        /* Table always full width initially */
        #roleTableWrapper {
            width: 100% !important;
            transition: width 0.3s ease;
        }

        /* When showing details, shrink table */
        .table-shrink {
            width: 45% !important;
            /* matches col-lg-5 */
        }

        /* Details hidden initially */
        #roleDetailsWrapper {
            width: 55%;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #roleTableWrapper {
            transition: width 0.3s ease;
        }

        #roleTable tbody tr.active {
            background: pink;
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/user_management/role_list.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'List Role', 'url' => '#']]" title="List Role" />
@endsection

@section('content')
    <!-- FLEX layout (important for animation) -->
    <div class="role-layout">

        <!-- LEFT TABLE -->
        <div id="roleTableWrapper">
            <div class="card custom-card">
                <div class="card-body">
                    <table id="roleTable" class="table table-hover table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-center">User</th>
                                <th class="text-center">Permission</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT DETAILS (hidden first) -->
        <div id="roleDetailsWrapper">
            <div class="card custom-card" style="min-height: 400px;">
                <div class="card-header">
                    <div class="card-title">Role Details</div>
                </div>
                <div class="card-body" id="roleDetailsContentDesktop">
                    <div class="border border-container rounded p-3 d-flex justify-content-center align-items-center w-100">
                        <div class="text-muted fs-15">Select a role to see role details</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- MOBILE MODAL -->
    <div class="modal fade" id="roleDetailsModal" tabindex="-1" aria-labelledby="roleDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleDetailsModalLabel">Role Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="roleDetailsContentModal">
                    Select a role to see details
                </div>
            </div>
        </div>
    </div>
@endsection
