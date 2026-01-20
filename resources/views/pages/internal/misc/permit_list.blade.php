@extends('pages.app')

@section('pageName', 'Permit List')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    @vite(['resources/js/pages/internal/misc/permit_condition_list.js'])
@endpush


@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
          
        ]" 
        title="Permit Condition List"
    >
     
    </x-breadcrumb>
@endsection


@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="ms-auto">
                        <a type="button" href="{{ url('internal/permit_add_condition') }}" class="btn btn-success btn-sm">Add Permit Condition</a>
                    </div>
                </div>

                <div class="card-body">
                    <table id="conditionTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Usage</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody> <!-- Important for DataTables -->
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="showConditionModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Your content here -->
                    <!-- ITEM DETAILS -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-muted">Item Information</h5>
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <tr>
                                    <th width="25%">Item Name</th>
                                    <td id="itemNameCell"></td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td id="categoryCell"></td>
                                </tr>
                                <tr>
                                    <th>Usage / Consignment Application</th>
                                    <td id="usageCell"></td>
                                </tr>
                                <tr>
                                    <th>Country</th>
                                    <td id="countryCell"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PERMIT CONDITION -->
                    <div class="mt-4">
                        <h5 class="fw-bold text-muted">Permit Condition</h5>
                        <div id="conditionHtml" 
                            class="border rounded p-3 bg-light"
                            style="min-height: 150px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

@endsection
