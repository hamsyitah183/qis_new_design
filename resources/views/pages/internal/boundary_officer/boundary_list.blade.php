@extends('pages.app')

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


@php
    $type = authUser()['type'];

@endphp

@push('scripts')
    <script>
        window.AUTH_TYPE = @json($type);
    </script>
    @vite(['resources/js/pages/boundary/boundary_list.js'])
@endpush

@section('pageName', 'Boundary Officer List')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="Boundary Officer List">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-header">
                    <div class="card-title">Boundary Officer List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="boundaryFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            Filter
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="boundaryFilterDropdown">

                            {{-- Name Search --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterBoundaryName"
                                    placeholder="Search by name...">
                            </li>

                            {{-- Place --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Place / Entry Point</label>
                                <input type="text" class="form-control form-control-sm" id="filterBoundaryPlace"
                                    placeholder="Search by place...">
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetBoundaryFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnBoundaryFilter">Apply</button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <div id="" class="">

                        <div class="row">
                            <div class="col-sm-12">
                                <table id="boundaryTable"
                                    class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                                    style="width: 1588px;">
                                    <thead class="mt-3">
                                        <tr class="even">
                                            <td>Name</td>
                                            <td>Place</td>
                                            <td>Action</td>
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


    <x-modal id="boundaryModal" title="User Details">

        <input type="hidden" name="id">
        <div class="my-2">
            <label for="trnptType" class="name">User Name</label>
            <input type="text" id="name" readonly class="form-control" name="name">
        </div>

        <div class="my-2">
            <label for="trnptType" class="form-label">Transport Type</label>
            <select class="form-select" id="trnptType" name="trnptType" data-route="/internal/get_entry_point"
                required>
                <option value="">-- Select Transport --</option>
                <option value="Air">Air</option>
                <option value="Sea">Sea</option>
                <option value="Land">Land</option>
            </select>
        </div>
       
        <div class="my-2">
            <label for="entryPoint" class="form-label">Entry Point</label>
            <select class="form-select" id="entryPoint" name="entryPoint" required>
                <option value="">-- Select Entry Point --</option>
    
            </select>
            <input type="hidden" id="descEntryPoint">
        </div>
        

        @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
        @endslot

    </x-modal>

@endsection

@push('scripts')
@endpush
