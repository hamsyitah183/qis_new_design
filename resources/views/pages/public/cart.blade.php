@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <!-- vite -->
    <script>
        window.PERMITS = @json($permits);
    </script>
    @vite(['resources/js/pages/checkout.js'])
@endpush

@section('pageName', 'Cart')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Application', 'url' => '#'], ['label' => 'Payment', 'url' => '#']]" title="">

    </x-breadcrumb>
@endsection

@section('content')
    {{-- @dd($order) --}}
    <div class="row">
        <form class="col-xl-12" method="POST" action="{{ url('/payment') }}" id="paymentForm">
            @csrf
            <div class="btn btn-primary mb-2" id= "returnToApplication" data-app-id = "{{ $application->application_id }}">
                Return to Application
            </div>
            {{-- <span id="applicationType" class="d-none"></span> --}}
            <input type="hidden" name="application_type" value = "import_permit" id="application_type">
            <input type="hidden" name="name" value="{{ authUser()['user']->fullname }}">
            <input type="hidden" name="email" value="{{ authUser()['user']->email }}">
            <input type="hidden" name="no_phone" value="{{ authUser()['user']->phone_number }}">

            <input type="hidden" name="amount" value="{{ $total }}">
            <input type="hidden" name="application_type" value="{{ $application->application_type }}">
            <input type="hidden" name="application_id" value = "{{ $application->application_id }}">
            <input type="hidden" name ="user_id" value = "{{ authUser()['user']->uuid }}">


            <div class="row">

                <div class="col-xl-9">
                    @if ($application->application_type == 'Import Permit' || $application->application_type == 'Inspection Certificate')
                        <div class="col-xl-12">
                            <div class="d-flex justify-content-between gap-2">
                                <div class="card border border-light custom-card">
                                    <div class="card-header">
                                        <div class="card-title">
                                            Importer Details
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-borderless table-sm mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th class="fw-bold" style="width: 30%;">Name:</th>
                                                        <td class="text-muted" id="importerFullName">
                                                            {{ $application->importer->fullname }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">Address:</th>
                                                        <td class="text-muted" id="importerAddress">
                                                            {{ $application->importer->address_1 }},
                                                            {{ $application->importer->address_2 ? $application->importer->address_2 . ',' : '' }}
                                                            {{ $application->importer->postcode }},
                                                            {{ $application->importer->state }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">No Phone:</th>
                                                        <td class="text-muted" id="importerNoPhone">
                                                            {{ $application->importer->phone_number }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                                <div class="card border border-light custom-card">
                                    <div class="card-header">
                                        <div class="card-title ">
                                            Exporter Details
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="table-responsive ">
                                            <table class="table table-borderless table-sm mb-0 ">
                                                <tbody>
                                                    <tr>
                                                        <th class="fw-bold" style="width: 30%;">Name:</th>
                                                        <td class="text-muted" id="exporterName">
                                                            {{ $application->exporter->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">Address:</th>
                                                        <td class="text-muted" id="exporterAddress">
                                                            {{ $application->exporter->address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">No Phone:</th>
                                                        <td class="text-muted" id= "exporterNoPhone">
                                                            {{ $application->exporter->phone_no }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="fw-bold">Country:</th>
                                                        <td class="text-muted" id="exporterCountry">
                                                            {{ $application->exporter->countryInfo->name }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-xl-12">
                        <div class="card custom-card overflow-hidden border" id="cart-container-delete">
                            <div class="card-header">
                                {{-- <div class="card-title p-2">
                                    <span class="fw-bold">Order No: </span> <span class="ms-2 text-muted"
                                        id="orderNo">{{ $order->order_number }}</span>
                                </div> --}}
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive table-bordered mb-4">
                                    <table class="table text-nowrap">
                                        <thead>
                                            <tr>
                                                <th class = "text-center">
                                                    #
                                                </th>
                                                <th>
                                                    Permit Number
                                                </th>
                                                <th>
                                                    Item Name
                                                </th>

                                                <th class = "text-center">
                                                    Total Price (RM)
                                                </th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($permits as $index => $permit)
                                                <tr data-id="{{ $permit->id }}" data-type="permit">
                                                    <td style="text-align:center">
                                                        {{ $index + 1 }}
                                                    </td>
                                                    <td class="cart-items01 text-wrap">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-fill">



                                                                <div class="">
                                                                    <div>

                                                                        <span class="fw-medium text-muted">
                                                                            {{ $permit->permit_number ?? '-' }}
                                                                        </span>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td class="cart-items01 text-wrap">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-fill">



                                                                <div class="">
                                                                    <div>

                                                                        <span class="fw-medium text-muted">
                                                                            {{ $permit->consignment_detail['item_name'] ?? '-' }}
                                                                        </span>
                                                                    </div>

                                                                </div>

                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <div class="fw-semibold fs-14 text-center">
                                                            RM 30
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                    </div>

                    {{-- @dd($total) --}}

                </div>

                <div class="col-xl-3">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                Order Summary
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-3 border-bottom border-block-end-dashed ">
                                <div class="tab-pane show active overflow-hidden p-0 border-0" id="freeshipping-pane"
                                    role="tabpanel" aria-labelledby="freeshipping" tabindex="0">
                                    <div class="fs-12 text-muted mb-3"><i class="ri-information-fill"></i> Choose the
                                        payment type</div>

                                    @foreach ($paymentMethod as $item)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input d-none" type="radio" name="paymentMethod"
                                                id="{{ $item->name }}" value="{{ $item->name }}"
                                                {{ $loop->first ? 'checked' : '' }}>

                                            <label class="form-check-label" for="{{ $item->name }}"
                                                style="cursor:pointer;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <img src="{{ $item->pic }}" alt="{{ $item->name }}"
                                                        style="width:70px; height:auto; border-radius:6px;"
                                                        class="payment-pic">

                                                    <div>{{ $item->name }}</div>
                                                </div>

                                            </label>
                                        </div>
                                    @endforeach


                                    {{-- <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="paymentMethod"
                                            value = "bayuPay" checked>
                                        <label class="form-check-label" for="bayuPay">
                                            Bayu Pay
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="paymentMethod"
                                            value = "YONO Pay">
                                        <label class="form-check-label" for="bayuPay">
                                            Yono Pay
                                        </label>
                                    </div> --}}


                                    <div class="d-flex align-items-center justify-content-between h5">
                                        <div class="fs-16">Total :</div>
                                        <div class="fw-semibold">RM <span id="amount">{{ $total }}</span></div>
                                    </div>
                                    <div class="d-grid">
                                        <button class="btn btn-primary btn-wave mb-2 waves-effect waves-light"
                                            id = "payNow">Pay
                                            Now</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>


    </div>

@endsection

@push('scripts')
@endpush
