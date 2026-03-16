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

    {{-- Import Permit Orders --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Import Permit Orders
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="importPermitOrderTable"
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

    {{-- Inspection Certificate Orders --}}
    <div class="row mt-3">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Inspection Certificate Orders
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="inspectionCertOrderTable"
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

    {{-- Consignment Certificate Orders --}}
    <div class="row mt-3">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Consignment Certificate Orders
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="consignmentCertOrderTable"
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