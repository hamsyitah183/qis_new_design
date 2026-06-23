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
    @vite(['resources/js/pages/order/order_list.js'])
@endpush

@section('pageName', 'Order Details')


@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Order List', 'url' => '/order/list'],
        ['label' => 'Order Details', 'url' => '#'],
    ]" title="Order Details">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-11 col-xxl">
            <div class="card custom-card school-card">
                <div class="card-body ">
                    <div class="">
                        <h5 class="mb-0 "><span class = "fw-semibold">{{ $order->order_number }}</span></h5>
                    </div>

                    <div class="">
                        <div class="">
                            <div class="mt-4">

                                <h6 class="fw-semibold">Order Details</h6>

                                <table class="table table-sm table-bordered mt-2">
                                    <tbody>
                                        <tr>
                                            <th class="fs-14 p-2" style="width: 160px;">Order Number</th>
                                            <td class="fs-14 p-2 text-muted text-wrap">{{ $order->order_number ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="fs-14 p-2">Order Status</th>
                                            <td class="fs-14 p-2 text-muted text-wrap">{{ $order->status ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="fs-14 p-2">Application ID</th>
                                            <td class="fs-14 p-2 text-muted text-wrap" style = "max-width: 100px;">
                                                {{ $order->order_details['application']['application_id'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="fs-14 p-2">Permit ID</th>
                                            <td class="fs-14 p-2 text-muted text-wrap">
                                                {{ $permits->pluck('permit_number')->implode(',  ') }}
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>




                            </div>
                        </div>

                        <div class="">
                            <h6 class="mt-4 fw-semibold">Payment Details</h6>

                            <table class="table table-sm table-bordered mt-2">
                                <tbody>
                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Seller Ref</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">{{ $order->seller_ref ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">FPX Seller Reference</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">{{ $order->fpx_seller_reference ?? '-' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Name</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">{{ $order->name ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Email</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">{{ $order->email ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Phone</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">{{ $order->phone ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Payment Amount</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">
                                            {{ $order->payment_amount ? 'RM ' . number_format($order->payment_amount, 2) : '-' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Transaction Data</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">{{ $order->transaction_data ?? '-' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Transaction Status</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">{{ $order->transaction_status ?? '-' }}
                                        </td>
                                    </tr>

                                    @if (authUser()['type'] == 'internal')
                                        <tr>
                                            <th class="fs-14 p-2" style="width: 160px;">Kod Transaksi</th>
                                            <td class="fs-14 p-2 text-muted text-wrap">{{ $order->kod_transaksi ?? '-' }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                        </div>


                        <div class="">
                            <h6 class="mt-4 fw-semibold">Application Details</h6>

                            

                            <table class="table table-sm table-bordered mt-2">
                                <tbody>
                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Application ID</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">
                                            {{ $application->application_id ?? '-' }}
                                        </td>

                                    </tr>
                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Exporter Name</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">
                                            {{ optional($application->exporter)->name ?? $fullname ?? '' }}

                                        </td>

                                    </tr>
                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Exporter Number Phone</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">
                                            {{ optional($application->exporter)->phone_no ?? $fullname ?? ''  ?? '-' }}</td>

                                    </tr>
                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Exporter Address</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">
                                            {{ $application->exporter->address ?? '-' }}</td>

                                    </tr>
                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Exporter Country</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">
                                            {{ $application->exporter->countryInfo->name ?? '-' }}</td>

                                    </tr>
                                    <tr>
                                        <th class="fs-14 p-2" style="width: 160px;">Importer Name</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">
                                            {{ $application->importer->fullname ?? '-' }}</td>

                                    </tr>
                                    <tr>
                                        
                                        <th class="fs-14 p-2" style="width: 160px;">Importer Address</th>
                                        <td class="fs-14 p-2 text-muted text-wrap">
                                            {{ $application->importer->address_1 ?? '-' }}
                                            @if (!empty($application->importer->address_2))
                                                , {{ $application->importer->address_2 }}
                                            @endif
                                            @if (!empty($application->importer->postcode))
                                                , {{ $application->importer->postcode }}
                                            @endif
                                            @if (!empty($application->importer->district))
                                                , {{ $application->importer->district }}
                                            @endif
                                            @if (!empty($application->importer->state))
                                                , {{ $application->importer->state }}
                                            @endif
                                        </td>
                                    </tr>



                                </tbody>
                            </table>

                        </div>

                        <div class="row">
                            <h6 class="mt-4 fw-semibold">Permit Details</h6>


                            <div class="col-12">
                                <table class="table table-sm table-bordered mt-2 mt-md-4">
                                    <thead>
                                        <tr>
                                            <td>Permit Number</td>
                                            <td>Item Name</td>
                                           
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach ($permits as $permit)
                                            <tr>
                                            <td class="text-muted">{{ $permit->permit_number }}</td>
                                            <td class = "text-muted">{{ $permit->consignment_detail['item_name'] }}</td>
                                        
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>


                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
