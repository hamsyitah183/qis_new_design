@extends('pages.app')


@section('pageName', 'Verification List')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .filter-dropdown {
            width: 420px;
        }

        @media (max-width: 768px) {
            .filter-dropdown {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/user_management/verification_list.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'User Verification List', 'url' => '#']]"
        title="User Verification List" title_en="User Verification List" title_bm="Senarai Pengesahan Pengguna">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title" data-en="User Verification List" data-bm="Senarai Pengesahan Pengguna">User Verification List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="verifyFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            <span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="verifyFilterDropdown">

                            {{-- Name Search --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Name" data-bm="Nama">Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterVerifyName" data-en="Search by name..." data-bm="Cari dengan nama..." data-i18n-attr="placeholder"
                                    placeholder="Search by name...">
                            </li>

                            {{-- Date Range --}}
                            <li class="mb-2">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1" data-en="Submitted From" data-bm="Dihantar Dari">Submitted From</label>
                                        <input type="date" class="form-control form-control-sm" id="filterVerifyStartDate">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1" data-en="Submitted To" data-bm="Dihantar Hingga">Submitted To</label>
                                        <input type="date" class="form-control form-control-sm" id="filterVerifyEndDate">
                                    </div>
                                </div>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetVerifyFilter"><span data-en="Reset" data-bm="Set Semula">Reset</span></button>
                                <button class="btn btn-sm btn-primary" id="btnVerifyFilter"><span data-en="Apply" data-bm="Guna">Apply</span></button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <table id="verificationTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th data-en="Name" data-bm="Nama">Name</th>
                                <th data-en="Verification Attachment" data-bm="Lampiran Pengesahan">Verification Attachment</th>
                                <th data-en="Action" data-bm="Tindakan">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Reason Modal -->
    <x-modal id="rejectModal" title="Reject Verification">
        <form id="rejectForm">
            @csrf
            <input type="hidden" id="rejectUserUuid" name="user_uuid">

            <div class="mb-3">
                <label for="rejectReason" class="form-label"><span data-en="Reason for Rejection" data-bm="Sebab Penolakan">Reason for Rejection</span></label>
                <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
            </div>

            @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span data-en="Close" data-bm="Tutup">Close</span></button>
            <button type="submit" class="btn btn-danger" id="confirmRejectBtn"><span data-en="Reject" data-bm="Tolak">Reject</span></button>
            @endslot
        </form>
    </x-modal>

    <!-- Verification Modal -->
    <x-modal id="verificationModal" title="User Verification">
        <div class="mb-2 fs-14"><span class="fw-bold me-2 " data-en="User IC:" data-bm="KP Pengguna:">User IC: </span> <span class="ic"></span></div>

        <div class="" id="userIC"></div>

        <div class="status mt-3"></div>
        <div class="fs-12 mt-3"> <span class="fw-bold" data-en="Submitted On:" data-bm="Dihantar Pada:">Submitted On: </span> <span class="updated_at text-muted"></span>
        </div>

        @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span data-en="Close" data-bm="Tutup">Close</span></button>
        @endslot
    </x-modal>
@endsection