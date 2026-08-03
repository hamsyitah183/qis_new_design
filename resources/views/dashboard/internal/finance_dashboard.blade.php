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
                    <div class="card-title" data-bm="Pesanan Permit Import" data-en="Import Permit Orders">
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
                                    <th data-bm="Nombor Pesanan" data-en="Order Number">Order Number</th>
                                    <th data-bm="Tarikh Transaksi" data-en="Transaction Date">Transaction Date</th>
                                    <th data-bm="Rujukan FPX" data-en="FPX Reference">FPX Reference</th>
                                    <th data-bm="Nama Pengguna" data-en="User Name">User Name</th>
                                    <th data-bm="Nombor Permit" data-en="Permit Number">Permit Number</th>
                                    <th data-bm="Status Pesanan" data-en="Order Status">Order Status</th>
                                    <th data-bm="Jenis Permohonan" data-en="Application Type">Application Type</th>
                                    <th data-bm="Data Transaksi" data-en="Transaction Data">Transaction Data</th>
                                    <th data-bm="Jumlah Bayaran" data-en="Payment Amount">Payment Amount</th>
                                    <th data-bm="Tindakan" data-en="Action">Action</th>
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
                    <div class="card-title" data-bm="Pesanan Sijil Pemeriksaan" data-en="Inspection Certificate Orders">
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
                                    <th data-bm="Nombor Pesanan" data-en="Order Number">Order Number</th>
                                    <th data-bm="Tarikh Transaksi" data-en="Transaction Date">Transaction Date</th>
                                    <th data-bm="Rujukan FPX" data-en="FPX Reference">FPX Reference</th>
                                    <th data-bm="Nama Pengguna" data-en="User Name">User Name</th>
                                    <th data-bm="Nombor Permit" data-en="Permit Number">Permit Number</th>
                                    <th data-bm="Status Pesanan" data-en="Order Status">Order Status</th>
                                    <th data-bm="Jenis Permohonan" data-en="Application Type">Application Type</th>
                                    <th data-bm="Data Transaksi" data-en="Transaction Data">Transaction Data</th>
                                    <th data-bm="Jumlah Bayaran" data-en="Payment Amount">Payment Amount</th>
                                    <th data-bm="Tindakan" data-en="Action">Action</th>
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
                    <div class="card-title" data-bm="Pesanan Sijil Konsainan" data-en="Consignment Certificate Orders">
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
                                    <th data-bm="Nombor Pesanan" data-en="Order Number">Order Number</th>
                                    <th data-bm="Tarikh Transaksi" data-en="Transaction Date">Transaction Date</th>
                                    <th data-bm="Rujukan FPX" data-en="FPX Reference">FPX Reference</th>
                                    <th data-bm="Nama Pengguna" data-en="User Name">User Name</th>
                                    <th data-bm="Nombor Permit" data-en="Permit Number">Permit Number</th>
                                    <th data-bm="Status Pesanan" data-en="Order Status">Order Status</th>
                                    <th data-bm="Jenis Permohonan" data-en="Application Type">Application Type</th>
                                    <th data-bm="Data Transaksi" data-en="Transaction Data">Transaction Data</th>
                                    <th data-bm="Jumlah Bayaran" data-en="Payment Amount">Payment Amount</th>
                                    <th data-bm="Tindakan" data-en="Action">Action</th>
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