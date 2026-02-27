@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <style>
        .filter-dropdown {
            width: 500px;
        }

        @media (max-width: 768px) {
            .filter-dropdown {
                width: 100%;
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
    </script>
    @vite(['resources/js/pages/order/order_list.js'])
@endpush

@section('pageName', 'List Order')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="Order List">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title">Order List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="orderFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            Filter
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="orderFilterDropdown">

                            {{-- Order Status --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Order Status</label>
                                <select class="form-select form-select-sm" id="filterOrderStatus">
                                    <option value="">All Status</option>
                                    <option value="SUCCESSFUL">Successful</option>
                                    <option value="UNSUCCESSFUL">Unsuccessful</option>
                                    <option value="PAYMENT PROCESSING">Payment Processing</option>
                                    <option value="PENDING FOR AUTHORIZER TO APPROVE">Pending for Authorizer to Approve</option>
                                </select>
                            </li>

                            {{-- Application Type --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Application Type</label>
                                <select class="form-select form-select-sm" id="filterAppType">
                                    <option value="">All Types</option>
                                    <option value="import_permit">Import Permit</option>
                                    <option value="inspection">Inspection Certificate</option>
                                    <option value="consignment">Consignment Certificate</option>
                                </select>
                            </li>

                            {{-- Date Range --}}
                            <li class="mb-2">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1">Start Date</label>
                                        <input type="date" class="form-control form-control-sm" id="filterOrderStartDate">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold mb-1">End Date</label>
                                        <input type="date" class="form-control form-control-sm" id="filterOrderEndDate">
                                    </div>
                                </div>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetOrderFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnOrderFilter">Apply</button>
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
                                            <th>Order Number</th>
                                            <th>Order Status</th>
                                            <th>Application Type</th>
                                            @if (authUser()['type'] == 'internal')
                                              <th>Transaction Data</th>
                                            @endif
                                            
                                            <th>Payment Amount</th>
                                           

                                            <th>Action</th>
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
        <div class="table-responsive" style = "max-height: 400px;">
            <table class="table text-wrap table-hover" id="applicationLogTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Action</th>
                        <th scope="col">User</th>
                        <th scope="col">Remark</th>
                        <th scope="col">Status</th>
                        <th scope="col">Time and Date</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">

                </tbody>
            </table>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        @endslot

    </x-modal>

@endsection


