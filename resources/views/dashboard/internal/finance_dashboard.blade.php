@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush


@php
    $type = authUser()['type'];

@endphp

@push('scripts')
    <script>
        window.AUTH_TYPE = @json($type);
    </script>
    @vite(['resources/js/pages/dashboard/finance_dashboard.js'])
@endpush

@section('pageName', 'Finance Dashboard')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="Finance Dashboard">

    </x-breadcrumb>
@endsection

@section('content')

    {{-- Filter Panel --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        <i class="ti ti-filter me-1"></i> Filters
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="filterOrderNumber" class="form-label">Order Number</label>
                            <input type="text" class="form-control" id="filterOrderNumber"
                                placeholder="Search order number...">
                        </div>

                        <div class="col-md-2">
                            <label for="filterStartDate" class="form-label">Transaction Date Start</label>
                            <input type="date" class="form-control" id="filterStartDate">
                        </div>
                        <div class="col-md-2">
                            <label for="filterEndDate" class="form-label">Transaction Date End</label>
                            <input type="date" class="form-control" id="filterEndDate">
                        </div>
                        <div class="col-md-3">
                            <label for="filterFpxReference" class="form-label">FPX Reference</label>
                            <input type="text" class="form-control" id="filterFpxReference"
                                placeholder="Search FPX reference...">
                        </div>
                        <div class="col-md-2">
                            <label for="filterOrderStatus" class="form-label">Order Status</label>
                            <select class="form-select" id="filterOrderStatus">
                                <option value="">All</option>
                                <option value="SUCCESSFUL">SUCCESSFUL</option>
                                <option value="UNSUCCESSFUL">UNSUCCESSFUL</option>
                                <option value="PENDING">PENDING</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filterApplicationType" class="form-label">Application Type</label>
                            <select class="form-select" id="filterApplicationType">
                                <option value="">All</option>
                                <option value="import_permit">Import Permit</option>
                                <option value="inspection">Inspection Certificate</option>
                                <option value="consignment">Consignment Certificate</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="btnFilter">
                                <i class="ti ti-search me-1"></i> Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnReset">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- All Orders --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        All Orders
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="financeOrderTable"
                            class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                            style="width: 100%;">
                            <thead class="mt-3">
                                <tr>
                                    <th>Order Number</th>
                                    <th>Transaction Date</th>
                                    <th>FPX Reference</th>
                                    <th>User Name</th>
                                    <th>Permit Number</th>
                                    <th>Order Status</th>
                                    <th>Application Type</th>
                                    <th>Transaction Data</th>
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

@endsection