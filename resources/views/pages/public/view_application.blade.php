@extends('pages.app')

@section('pageName', 'View Application')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="View Application">

    </x-breadcrumb>
@endsection

@section('content')


    <!-- terssttt  -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        VIEW PERMIT APPLICATION
                    </div>
                </div>
                <div class="card-body p-0"> <!-- method="POST"  data-wizard="active" style="display: block;"-->
                    <form id="wizardForm" class="wizard wizard-tab horizontal">
                        <aside class="wizard-nav dots">
                            <div class="wizard-step active" data-step="0">
                                <span class="dot"></span>
                                <span>IMPORTER & EXPORTER</span>
                            </div>
                            <div class="wizard-step" data-step="1">
                                <span class="dot"></span>
                                <span>PERMIT DETAILS</span>
                            </div>
                            <div class="wizard-step" data-step="2">
                                <span class="dot"></span>
                                <span>PERMIT ITEMS</span>
                            </div>
                            <div class="wizard-step" data-step="3">
                                <span class="dot"></span>
                                <span>Payment</span>
                            </div>
                            <div class="wizard-step" data-step="4">
                                <span class="dot"></span>
                                <span>Confirmation</span>
                            </div>
                        </aside>
                        <aside class="wizard-content container">
                            <!-- step0 -->
                            <div class="wizard-step active" data-title="IMPORTER & EXPORTER"
                                data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="0">
                                <div class="row justify-content-center">
                                    <div class="col-xl-6">
                                        <div class="register-page">
                                            <h6 class="mb-3">Importer :</h6>
                                            <div class="row gy-3">
                                                <input type="hidden" id="app_cate" value="0">
                                                <div class="col-xl-12">
                                                    <label for="impname" class="form-label">Name</label>
                                                    <input type="hidden" id="impid"
                                                        value="{{ $application->importer_id }}">
                                                    <input type="text" class="form-control " id="impname"
                                                        name="impname" value="{{ $application->importer->fullname }}"
                                                        disabled>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label for="impfonno" class="form-label">Phone No</label>
                                                    <input type="text" class="form-control " id="impfonno"
                                                        name="impfonno" value="{{ $application->importer->phone_number }}"
                                                        disabled>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label for="impaddress" class="form-label">Address</label>
                                                    <input type="text" class="form-control mb-2" id="impaddress1"
                                                        name="impaddress1" value="{{ $application->importer->address_1 }}"
                                                        disabled>
                                                    <input type="text" class="form-control " id="impaddress2"
                                                        name="impaddress2" value="{{ $application->importer->address_2 }}"
                                                        disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 pt-sm-4 pt-lg-0">
                                        <div class="register-page">
                                            <h6 class="mb-3">Exporter :</h6>
                                            <div class="row gy-3">


                                                <div class="col-xl-12">
                                                    <input type="hidden" id="expid"
                                                        value="{{ $application->exporter_id }}">
                                                    <label for="expname" class="form-label">Name</label>
                                                    <input type="text" class="form-control " id="expname"
                                                        name="expname" value="{{ $application->exporter->name }}" disabled>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label for="expfonno" class="form-label">Phone No</label>
                                                    <input type="text" class="form-control " id="expfonno"
                                                        name="expfonno" value="{{ $application->exporter->phone_no }}"
                                                        disabled>
                                                </div>
                                                <div class="col-xl-12">
                                                    <label for="expaddress" class="form-label">Address</label>
                                                    <input type="text" class="form-control mb-2" id="expaddress1"
                                                        name="expaddress1" value="{{ $application->exporter->address }}"
                                                        disabled>
                                                    <!-- <input type="text" class="form-control " id="expaddress2"  name="expaddress2"> -->
                                                </div>
                                                <div class="col-lg-12">
                                                    <label for="expcountry" class="form-label">Country</label>
                                                    <input type="hidden" class="form-control mb-2" id="expcountryCode"
                                                        value="{{ $application->exporter->country }}"
                                                        name="expcountryCode">
                                                    <input type="text" class="form-control" id="expcountry"
                                                        value="{{ $application->exporter->country }}" name="expcountry"
                                                        disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- step1 -->
                            <div class="wizard-step" data-title="PERMIT DETAILS"
                                data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="1">
                                <div class="row justify-content-center">
                                    <div class="col-xl-12">
                                        <div class="register-page">
                                            <h6 class="mb-3">Permit Details :</h6>
                                            <div class="row gy-3 mb-3">
                                                <div class="col-xl-6">
                                                    <label for="eta" class="form-label">Estimated Time
                                                        Arrival</label>
                                                    <input type="text" class="form-control " id="eta"
                                                        name="eta"
                                                        value="{{ \Carbon\Carbon::parse($application->eta)->format('d/m/Y') }}"
                                                        disabled>
                                                </div>
                                            </div>
                                            <div class="row gy-3">
                                                <div class="col-xl-6">
                                                    <label for="trnptType" class="form-label">Transport Type</label>
                                                    <select class="form-select" id="trnptType" name="trnptType"
                                                        data-route="{{ route('public.getEntryPoint') }}" disabled>
                                                        <option value="">{{ $application->transport_type }}</option>
                                                        <option value="Air">Air</option>
                                                        <option value="Sea">Sea</option>
                                                        <option value="Land">Land</option>
                                                    </select>

                                                </div>
                                                <div class="col-xl-6">
                                                    <label for="entryPoint" class="form-label">Entry Point</label>
                                                    <select class="form-select" id="entryPoint" name="entryPoint"
                                                        disabled>
                                                        <option value=""> {{ $application->entry_point }}</option>

                                                    </select>
                                                    <input type="hidden" id="descEntryPoint">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- step2 -->


                            <!-- <div class="wizard-step" data-title="PERMIT ITEM DETAILS" data-id="H53WJiv9blN17MYTztq4g8U6eSVkaZDx" data-step="2">
                                                        <div class="row justify-content-center summary-view">
                                                            <div class="table-responsive">
                                                                <table id="itemListTbl" class="table text-nowrap">
                                                                    <thead class="table-success">
                                                                        <tr>
                                                                            <th scope="col">#</th>
                                                                            <th scope="col">Item Name</th>
                                                                            <th scope="col">Quantity</th>
                                                                            <th scope="col">Purpose</th>
                                                                            <th scope="col">Uses</th>
                                                                            <th scope="col">Value</th>
                                                                            <th scope="col">Uploaded Item</th>
                                                                            <th scope="col" style="text-align: center">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse ($consignmentDetails as $index => $item)
    <tr>
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>{{ $item['item_name'] ?? '—' }}</td>
                                                                                <td>{{ $item['quantity'] ?? '—' }}</td>
                                                                                <td>{{ $item['measure'] ?? '—' }}</td>
                                                                                <td>{{ $item['purpose'] ?? '—' }}</td>
                                                                                <td>{{ $item['uses'] ?? '—' }}</td>
                                                                                <td>{{ $item['value'] ?? '—' }}</td>
                                                                            </tr>
                                                                @empty
                                                                            <tr>
                                                                                <td colspan="7" class="text-center text-muted">
                                                                                    No consignment items found.
                                                                                </td>
                                                                            </tr>
    @endforelse
                                                                        <tr>
                                                                            <td>1</td>
                                                                            <td scope="row">Durian - Fresh Fruit</td>
                                                                            <td>500 KG</td>
                                                                            <td>Commercial (Trade)</td>
                                                                            <td>Fresh Produce</td>
                                                                            <td>RM 10,000</td>
                                                                            <td></td>
                                                                            <td style="text-align: center">
                                                                                <button type="button" class="btn btn-sm btn-primary-light">Remove</button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                                
                                                            </div>
                                                            
                                                        </div>
                                                    </div> -->
                            <!-- step3 -->
                            <div class="wizard-step" data-title="SUMMARY" data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu"
                                data-limit="3" data-step="3">
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="border border-bottom-0 rounded-1 mb-3 ">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table  text-nowrap">
                                                        <thead>
                                                            <tr class="bg-light">
                                                                <th scope="col">Importer & Exporter Details</th>
                                                                <th scope="col"></th>
                                                                <th scope="col"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span
                                                                        class="d-block fw-semibold text-end"><strong>Importer</strong>
                                                                        Name</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start  text-muted" id="importerName">
                                                                    {{ $application->importer->fullname }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span
                                                                        class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                                                        Phone</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start text-muted" id="importerPhoneno">
                                                                    {{ $application->importer->phone_number }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span
                                                                        class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                                                        Address</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start text-muted" id="simpAdd">
                                                                    {{ $application->importer->address_1 }} ,
                                                                    {{ $application->importer->address_2 }} ,
                                                                    {{ $application->importer->postcode }},
                                                                    {{ $application->importer->district }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span
                                                                        class="d-block fw-semibold text-end"><strong>Exporter</strong>
                                                                        Name</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start text-muted" id="sexpName">
                                                                    {{ $application->exporter->name }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span
                                                                        class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                                                        Phone</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start text-muted" id="sexpfonno">
                                                                    {{ $application->exporter->phone_no }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span
                                                                        class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                                                        Address</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start text-muted" id="sexpAddress">
                                                                    {{ $application->exporter->address }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span
                                                                        class="d-block fw-semibold text-end">&nbsp;&nbsp;&nbsp;&nbsp;
                                                                        Country</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start text-muted" id="sexpCountry">
                                                                    {{ $application->exporter->country }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="border border-bottom-0 rounded-1 mb-3 ">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table id="summaryTable" class="table text-nowrap">
                                                        <thead>
                                                            <tr class="bg-light">
                                                                <th scope="col">Permit Details</th>
                                                                <th scope="col"></th>
                                                                <th scope="col"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span class="d-block fw-semibold">Estimated Time
                                                                        Arrival</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start  text-muted" id="seta">
                                                                    {{ $application->eta }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span class="d-block fw-semibold">Transport Type</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start  text-muted" id="strty">
                                                                    {{ $application->transport_type }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w-25">
                                                                    <span class="d-block fw-semibold">Entry Point</span>
                                                                </td>
                                                                <td class="w-10">:</td>
                                                                <td class="text-start  text-muted" id="sentryp">
                                                                    {{ $application->entry_point }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                        <tfooter>
                                                            <tr>
                                                                <td colspan="3">
                                                                    <button type="button" class="btn btn-primary"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editExporterModal">
                                                                        <i class="bx bx-edit"></i> Edit Permit Detail
                                                                    </button>
                                                                </td>
                                                            </tr>

                                                            <div class="modal fade" id="editExporterModal"
                                                                tabindex="-1">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Edit Application Detail
                                                                            </h5>
                                                                            <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="mb-3">
                                                                                <label class="form-label">ETA</label>
                                                                                <input type="date" class="form-control"
                                                                                    id="edit_eta" name="edit_eta">
                                                                            </div>

                                                                            <!-- Registration Number -->
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Transport
                                                                                    Type</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="edit_transporttype">
                                                                            </div>

                                                                            <!-- Email Address -->
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Entry
                                                                                    Point</label>
                                                                                <input type="email" class="form-control"
                                                                                    id="edit_entryPoint">
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button class="btn btn-md btn-info">Update
                                                                                Details</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </tfooter>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12">
                                        <div class="border border-bottom-0 rounded-1 mb-3 ">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table text-nowrap">
                                                        <thead>
                                                            <tr class="bg-light">
                                                                <th scope="col">Consignment Details</th>
                                                                <th scope="col"></th>
                                                                <th scope="col"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="3">
                                                                    <div class="table-responsive">
                                                                        <table id="summaryTable3"
                                                                            class="table text-nowrap">
                                                                            <thead class="table-success">
                                                                                <tr>
                                                                                    <th scope="col">#</th>
                                                                                    <th scope="col">Item Name</th>
                                                                                    <th scope="col">Quantity</th>
                                                                                    <th scope="col" style="">
                                                                                        Purpose</th>
                                                                                    <th scope="col">Value</th>
                                                                                    <th scope="col">Attachment</th>
                                                                                    <th scope="col">Action</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @dd($consignmentDetails)
                                                                                @forelse ($consignmentDetails as $index => $item)
                                                                                    {{-- @dd($item) --}}
                                                                                    <tr>
                                                                                        <td>{{ $index + 1 }}</td>
                                                                                        <td>{{ $item['item_name'] ?? '—' }}
                                                                                        </td>
                                                                                        <td>{{ $item['quantity'] ?? '—' }}
                                                                                            {{ $item['measure'] ?? '—' }}
                                                                                        </td>
                                                                                        <td>{{ $item['uses'] ?? '—' }}</td>
                                                                                        <td>RM {{ $item['value'] ?? '—' }}
                                                                                        </td>
                                                                                        {{-- <td>
                                                                                            @foreach ($item['attachments'] as $file)
                                                                                                <img src="{{ asset($file->file_path) }}"
                                                                                                    style="width:100px; height:100px; object-fit:cover; margin-left:8px;"
                                                                                                    class="img-thumbnail">
                                                                                            @endforeach
                                                                                        </td> --}}
                                                                                        <td>
                                                                                            <a type="button"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#editIpItemModal"
                                                                                                class="btn btn-sm btn-info">Edit
                                                                                                Consignment Details
                                                                                            </a><br>
                                                                                            <a type="button"
                                                                                                class="btn btn-sm btn-danger mt-2">RemoveDetails</a>
                                                                                        </td>
                                                                                    </tr>
                                                                                @empty
                                                                                    <tr>
                                                                                        <td colspan="7"
                                                                                            class="text-center text-muted">
                                                                                            No consignment items found.
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforelse
                                                                            </tbody>
                                                                        </table>
                                                                        <div class="modal fade" id="editIpItemModal"
                                                                            tabindex="-1">
                                                                            <div class="modal-dialog modal-xl">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h5 class="modal-title">Edit
                                                                                            Consignment Details</h5>
                                                                                        <button type="button"
                                                                                            class="btn-close"
                                                                                            data-bs-dismiss="modal"></button>
                                                                                    </div>
                                                                                    <div class="modal-body">
                                                                                        <div class="row gy-4 mb-3">
                                                                                            <div
                                                                                                class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                                                <label for="itemSelect"
                                                                                                    class="form-label">Item
                                                                                                </label>
                                                                                                <!-- <select class="form-select" id="itemSelect" name="itemSelect">
                                                                                                                            <option value="aa" >-- Select Item</option>
                                                                                                                            <option value="aasda" >aaadwd</option>
                                                                                                                        </select> -->
                                                                                                <input type="text"
                                                                                                    class="form-control"
                                                                                                    value="Fresh Fruit - CORN"
                                                                                                    disabled>
                                                                                                <small
                                                                                                    style="color:red">Item
                                                                                                    refering to the
                                                                                                    exporter's
                                                                                                    Country</small>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                                                <label for="itemValue"
                                                                                                    class="form-label">Value
                                                                                                    (RM)</label>
                                                                                                <input type="text"
                                                                                                    class="form-control"
                                                                                                    id="itemValue"
                                                                                                    name="itemValue"
                                                                                                    placeholder="RM ...">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                                                <label for="itemQuantity"
                                                                                                    class="form-label">Quantity</label>
                                                                                                <input type="text"
                                                                                                    class="form-control"
                                                                                                    id="itemQuantity"
                                                                                                    name="itemQuantity">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                                                <label for="itemMeasure"
                                                                                                    class="form-label">Measurement
                                                                                                    Unit</label>
                                                                                                <select class="form-select"
                                                                                                    id="itemMeasure"
                                                                                                    name="itemMeasure">
                                                                                                    <option value="">
                                                                                                        -- Select
                                                                                                        Measurement Unit --
                                                                                                    </option>

                                                                                                </select>

                                                                                            </div>
                                                                                            <div
                                                                                                class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                                                <label for="itemPurpose"
                                                                                                    class="form-label">Purpose</label>
                                                                                                <select class="form-select"
                                                                                                    id="itemPurpose"
                                                                                                    name="itemPurpose">
                                                                                                    <option value="">
                                                                                                        -- Select Purpose --
                                                                                                    </option>

                                                                                                </select>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                                                                                <label for="itemUses"
                                                                                                    class="form-label">Uses</label>
                                                                                                <select class="form-select"
                                                                                                    id="itemUses"
                                                                                                    name="itemUses">

                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12"
                                                                                                style="display:none">
                                                                                                <label for="itemUses"
                                                                                                    class="form-label">Attachments</label>
                                                                                                <select class="form-select"
                                                                                                    id="itemUses"
                                                                                                    name="itemUses">

                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button
                                                                                            class="btn btn-info btn-md">Update
                                                                                            Details</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row justify-content-center" style="display:none">
                                    <div class="col-auto d-flex gap-3">
                                        <button id="generateSummary" type="button"
                                            class="btn btn-md btn-warning">Generate Summary</button>
                                        <button id="submitApps" type="button" class="btn btn-md btn-info">Submit
                                            Application</button>
                                    </div>
                                </div>
                            </div>
                            <!-- step4 -->
                            <div class="wizard-step" data-title="APPLICATION STATUS"
                                data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu" data-limit="3" data-step="4">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="tab-pane active show" id="finish" role="tabpanel">
                                            <div class="row d-flex justify-content-center">
                                                <div class="col-lg-10">
                                                    @if ($application->importer_id == auth()->id())
                                                        <div class="row justify-content-center">
                                                            <div class="col-auto d-flex gap-3">
                                                                <button id="rejectAppl" type="button"
                                                                    class="btn btn-md btn-warning">Reject
                                                                    Application</button>
                                                                <button id="verifyAppl" type="button"
                                                                    class="btn btn-md btn-info">Verify Application</button>
                                                            </div>
                                                        </div>
                                                    @else
                                                        @if (
                                                            $application->category_application == 0 ||
                                                                ($application->category_application == 1 && $application->importer_verify == true))
                                                            <div class="text-center p-4">
                                                                <span
                                                                    class="avatar avatar-xl avatar-rounded bg-success-transparent svg-success">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        viewBox="0 0 256 256">
                                                                        <rect width="256" height="256"
                                                                            fill="none"></rect>
                                                                        <circle cx="128" cy="128" r="96"
                                                                            opacity="0.2"></circle>
                                                                        <polyline points="88 136 112 160 168 104"
                                                                            fill="none" stroke="currentColor"
                                                                            stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="16"></polyline>
                                                                        <circle cx="128" cy="128" r="96"
                                                                            fill="none" stroke="currentColor"
                                                                            stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="16"></circle>
                                                                    </svg>
                                                                </span>
                                                                <h3 class="mt-2">Successful</h3>
                                                                <p>Your permit application has successfully submitted.</p>
                                                            </div>
                                                        @else
                                                            <div class="text-center p-4">
                                                                <span
                                                                    class="avatar avatar-xl avatar-rounded bg-warning-transparent svg-warning">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        viewBox="0 0 256 256">
                                                                        <rect width="256" height="256"
                                                                            fill="none"></rect>
                                                                        <circle cx="128" cy="128" r="96"
                                                                            opacity="0.2"></circle>
                                                                        <line x1="128" y1="80"
                                                                            x2="128" y2="136"
                                                                            stroke="currentColor" stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="16">
                                                                        </line>
                                                                        <circle cx="128" cy="172" r="12"
                                                                            fill="currentColor"></circle>
                                                                        <circle cx="128" cy="128" r="96"
                                                                            fill="none" stroke="currentColor"
                                                                            stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="16"></circle>
                                                                    </svg>
                                                                </span>
                                                                <h3 class="mt-2">Pending</h3>
                                                                <p>Your permit application is currently pending verification
                                                                    by the respective Importer.</p>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                        <!-- <aside class="wizard-buttons">
                                                    <button class="wizard-btn btn prev" disabled="true">Prev</button>
                                                    <button class="wizard-btn btn next">Next</button>
                                                    <button class="wizard-btn btn finish" style="display: none;">Submit</button>
                                                </aside> -->
                    </form>

                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    <script>
        // for form wizard next and prev button
        (function() {
            // 🟢 First wizard
            let firstWizardConfig = {
                wz_class: ".wizard-tab",
                highlight: true,
                highlight_time: 1000,
                progress: true,
                validate: true
            };
            new Wizard1(firstWizardConfig).init();

            // 🟢 Second wizard (with progress bar)
            let secondWizardConfig = {
                wz_class: ".wizard-second-tab", // ✅ fixed selector
                highlight: true,
                highlight_time: 1000,
                progress: true,
                validate: true
            };
            new Wizard1(secondWizardConfig).init();
        })();
    </script>
@endpush
