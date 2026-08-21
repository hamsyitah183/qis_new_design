@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .filter-dropdown {
            width: 500px;
        }

        .permit-cell {
            display: inline-grid;
            grid-template-columns: minmax(0, 1fr) 1.25rem 2rem;
            align-items: center;
            column-gap: 0.35rem;
            width: 13rem;
            max-width: 100%;
        }

        .permit-cell .permit-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .permit-cell .permit-qr-btn {
            width: 2rem;
            height: 2rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .permit-cell .permit-used-indicator {
            width: 1.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .permit-cell .permit-used-indicator.is-hidden {
            visibility: hidden;
        }

        @media (max-width: 768px) {
            .filter-dropdown {
                width: 100%;
            }

            .permit-cell {
                width: 11rem;
            }
        }
    </style>
@endpush


@php
    $type = authUser()['type'];

@endphp

@push('scripts')
    <script>
        window.AUTH_TYPE = @json($type);
        window.ENCRYPTED_QR_PAYLOAD_URL = @json(url('/order/encrypted-qr-payload'));
    </script>
    @vite(['resources/js/pages/order/order_list.js'])
@endpush

@section('pageName', 'List Order')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#', 'data-en' => 'Home', 'data-bm' => 'Laman Utama']]" title="Order List" title_en="Order List" title_bm="Senarai Pesanan">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title" data-en="Order List" data-bm="Senarai Pesanan">Order List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button" id="orderFilterDropdown"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            <span data-en="Filter" data-bm="Tapis">Filter</span>
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="orderFilterDropdown">

                            {{-- Order Status --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Order Status" data-bm="Status Pesanan">Order Status</label>
                                <select class="form-select form-select-sm select2" id="filterOrderStatus">
                                    <option value="SUCCESSFUL" data-en="Successful" data-bm="Berjaya">Successful</option>
                                    <option value="UNSUCCESSFUL" data-en="Unsuccessful" data-bm="Tidak Berjaya">Unsuccessful</option>
                                    <option value="PAYMENT PROCESSING" data-en="Payment Processing" data-bm="Pemprosesan Bayaran">Payment Processing</option>
                                    <option value="PENDING FOR AUTHORIZER TO APPROVE" data-en="Pending for Authorizer to Approve" data-bm="Menunggu Kelulusan Pengesah">Pending for Authorizer to Approve
                                    </option>
                                </select>
                            </li>

                            {{-- Application Type --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1" data-en="Application Type" data-bm="Jenis Permohonan">Application Type</label>
                                <select class="form-select form-select-sm select2" id="filterAppType">
                                    <option value="import_permit" data-en="Import Permit" data-bm="Permit Import">Import Permit</option>
                                    <option value="inspection" data-en="Inspection Certificate" data-bm="Sijil Pemeriksaan">Inspection Certificate</option>
                                    <option value="consignment" data-en="Consignment Certificate" data-bm="Sijil Konsainan">Consignment Certificate</option>
                                </select>
                            </li>

                            {{-- Date Range --}}
                            <li class="mb-2">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1" data-en="Start Date" data-bm="Tarikh Mula">Start Date</label>
                                        <input type="date" class="form-control form-control-sm" id="filterOrderStartDate">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1" data-en="End Date" data-bm="Tarikh Akhir">End Date</label>
                                        <input type="date" class="form-control form-control-sm" id="filterOrderEndDate">
                                    </div>
                                </div>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetOrderFilter" data-en="Reset" data-bm="Tetap Semula">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnOrderFilter" data-en="Apply" data-bm="Cari">Apply</button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <div id="" class="dataTables_wrapper dt-bootstrap5 no-footer">

                        <div class="row">
                            <div class="col-sm-12">
                                <table id="orderListTable"
                                    class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                                    aria-describedby="responsiveDataTable_info" style="width: 1588px;">
                                    <thead class="mt-3">
                                        <tr class="">
                                            {{-- <th>#</th> --}}
                                            <th data-en="Order Number" data-bm="Nombor Pesanan">Order Number</th>
                                            <th data-en="Permit Number" data-bm="Nombor Permit">Permit Number</th>
                                            <th data-en="Order Status" data-bm="Status Pesanan">Order Status</th>
                                            <th data-en="Application Type" data-bm="Jenis Permohonan">Application Type</th>
                                            @if (authUser()['type'] == 'internal')
                                                <th data-en="Transaction Data" data-bm="Data Transaksi">Transaction Data</th>
                                            @endif

                                            <th data-en="Payment Amount" data-bm="Jumlah Bayaran">Payment Amount</th>


                                            <th data-en="Action" data-bm="Tindakan">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal id="activityLogModal" title="Activity Log">

        <!-- Your table goes here -->
        <div class="table-responsive" style="max-height: 400px;">
            <table class="table text-wrap table-hover" id="applicationLogTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" data-en="Action" data-bm="Tindakan">Action</th>
                        <th scope="col" data-en="User" data-bm="Pengguna">User</th>
                        <th scope="col" data-en="Remark" data-bm="Catatan">Remark</th>
                        <th scope="col" data-en="Status" data-bm="Status">Status</th>
                        <th scope="col" data-en="Time and Date" data-bm="Masa dan Tarikh">Time and Date</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">

                </tbody>
            </table>
        </div>

        @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
        @endslot

    </x-modal>

    <x-modal id="permitQrModal" title="Permit QR Code" size="modal-sm">
        <div class="text-center">
            <img id="permitQrImage" alt="Permit QR Code" class="img-fluid border rounded p-2 bg-white" />
            <div class="mt-2 fw-semibold" id="permitQrValue">-</div>
        </div>

        @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
        @endslot
    </x-modal>

@endsection