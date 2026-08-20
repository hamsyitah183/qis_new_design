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
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
        [
            'label' => 'User Verification List',
            'url' => '#',
            'data-en' => 'User Verification List',
            'data-bm' => 'Senarai Pengesahan Pengguna',
        ],
    ]" title="User Verification List" title_en="User Verification List"
        title_bm="Senarai Pengesahan Pengguna">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    {{-- <div class="card-title" data-en="User Verification List" data-bm="Senarai Pengesahan Pengguna">User Verification List</div> --}}
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="verifyFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            <span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="verifyFilterDropdown">

                            {{-- Name Search --}}
                            {{-- <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Name" data-bm="Nama">Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterVerifyName" data-en="Search by name..." data-bm="Cari dengan nama..." data-i18n-attr="placeholder"
                                    placeholder="Search by name...">
                            </li> --}}

                            {{-- Date Range --}}
                            <li class="mb-2">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1" data-en="Submitted From"
                                            data-bm="Dihantar Dari">Submitted From</label>
                                        <input type="date" class="form-control form-control-sm"
                                            id="filterVerifyStartDate">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1" data-en="Submitted To"
                                            data-bm="Dihantar Hingga">Submitted To</label>
                                        <input type="date" class="form-control form-control-sm" id="filterVerifyEndDate">
                                    </div>
                                </div>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetVerifyFilter"><span data-en="Reset"
                                        data-bm="Set Semula">Reset</span></button>
                                <button class="btn btn-sm btn-primary" id="btnVerifyFilter"><span data-en="Apply"
                                        data-bm="Cari">Apply</span></button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <table id="verificationTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th data-en="Name" data-bm="Nama">Name</th>
                                <th data-en="Verification Attachment" data-bm="Lampiran Pengesahan">Verification Attachment
                                </th>
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
                <label for="rejectReason" class="form-label"><span data-en="Reason for Rejection"
                        data-bm="Sebab Penolakan">Reason for Rejection</span></label>
                <textarea class="form-control" id="rejectReason" name="reason" rows="3" required></textarea>
            </div>

            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span data-en="Close"
                        data-bm="Tutup">Close</span></button>
                <button type="submit" class="btn btn-danger" id="confirmRejectBtn"><span data-en="Reject"
                        data-bm="Tolak">Reject</span></button>
            @endslot
        </form>
    </x-modal>

    <!-- Verification Modal -->
    <x-modal id="verificationModal" title="User Verification">
        <div class="mb-2 fs-14"><span class="fw-bold me-2 " data-en="User IC:" data-bm="KP Pengguna:">User IC: </span> <span
                class="ic"></span></div>

        <div class="" id="userIC"></div>

        <div class="status mt-3"></div>
        <div class="fs-12 mt-3"> <span class="fw-bold" data-en="Submitted On:" data-bm="Dihantar Pada:">Submitted On:
            </span> <span class="updated_at text-muted"></span>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span data-en="Close"
                    data-bm="Tutup">Close</span></button>
        @endslot
    </x-modal>

    <!-- Verification Offcanvas -->
    <!-- ============================================================ -->
    <!-- VERIFICATION ATTACHMENT OFFCANVAS                             -->
    <!-- ============================================================ -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="verificationOffcanvas"
        aria-labelledby="verificationOffcanvasLabel" style="z-index: 1046;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="verificationOffcanvasLabel">
                <i class="bi bi-paperclip me-2"></i>
                <span data-en="Verification Attachment" data-bm="Lampiran Pengesahan">Verification Attachment</span>
            </h5>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-sm btn-outline-secondary" id="vdPrevBtn" title="Previous">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span class="badge bg-light text-dark" id="vdCounter">1 / 1</span>
                <button class="btn btn-sm btn-outline-secondary" id="vdNextBtn" title="Next">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
        </div>
        <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 60px);">
            <!-- Vertical tabs (View / Details) -->
            <div class="pd-nav flex-shrink-0">
                <ul class="nav nav-pills flex-column" id="vdTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="vd-view-tab" data-bs-toggle="tab" data-bs-target="#vd-view"
                            type="button" role="tab" data-bs-placement="right" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="vd-details-tab" data-bs-toggle="tab" data-bs-target="#vd-details"
                            type="button" role="tab" data-bs-placement="right" title="Details">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content flex-grow-1 p-3 overflow-auto" id="vdTabContent">
                <!-- View pane -->
                <div class="tab-pane fade show active" id="vd-view" role="tabpanel">
                    <div id="vdAttachmentViewer">
                        <div class="text-muted text-center py-5">
                            <i class="bi bi-file-earmark-fill fs-1"></i>
                            <br><span data-en="Select an attachment" data-bm="Pilih lampiran">Select an attachment</span>
                        </div>
                    </div>
                </div>
                <!-- Details pane -->
                <div class="tab-pane fade" id="vd-details" role="tabpanel">
                    <div id="vdAttachmentDetails" class="py-2"></div>
                </div>
            </div>
        </div>
        <div class="offcanvas-footer border-top p-3">
            
            <div class="mt-2 d-flex gap-2">
                <button class="btn btn-success btn-sm" id="vdAcceptBtn">
                    <i class="bi bi-check-lg"></i> <span data-en="Accept" data-bm="Terima">Accept</span>
                </button>
                <button class="btn btn-danger btn-sm" id="vdRejectBtn">
                    <i class="bi bi-x-lg"></i> <span data-en="Reject" data-bm="Tolak">Reject</span>
                </button>
            </div>
        </div>
    </div>
@endsection
