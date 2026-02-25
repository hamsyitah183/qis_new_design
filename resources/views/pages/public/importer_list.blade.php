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
    @vite(['resources/js/pages/importer_list.js'])
@endpush

@section('pageName', 'List All Consignment Importer')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="All Consignment Certificate Importer List">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Importer List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">

                        <button class="btn btn-sm btn-primary filter dropdown-toggle" type="button"
                            id="importerFilterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false">
                            <span class="me-2"><i class="ti ti-adjustments-horizontal"></i></span>
                            Filter
                        </button>

                        <ul class="dropdown-menu p-3 filter-dropdown" aria-labelledby="importerFilterDropdown">

                            {{-- Name Search --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Importer Name</label>
                                <input type="text" class="form-control form-control-sm" id="filterImporterName"
                                    placeholder="Search by name...">
                            </li>

                            {{-- Country --}}
                            <li class="mb-2">
                                <label class="form-label fw-semibold mb-1">Country</label>
                                <select class="form-select form-select-sm" id="filterImporterCountry">
                                    <option value="">All Countries</option>
                                    <option value="SWK">Sarawak, Malaysia</option>
                                    <option value="BN">Brunei Darussalam</option>
                                </select>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetImporterFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnImporterFilter">Apply</button>
                            </li>
                        </ul>

                        <div class="btn btn-primary btn-sm" id="addImporter">
                            <i class="ti ti-plus me-1"></i> Add Importer
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="importerTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Importer Name</th>
                                    <th>Phone No</th>
                                    <th>Address</th>
                                    <th>Country</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Add Importer Modal -->
    <x-modal id="addImporterModal" title="Add Importer">
        <form id="addImporterForm">
            @csrf

            <input type="hidden" name="id" id="id">

            {{-- Name --}}
            <div class="mb-3">
                <label for="addimpName" class="form-label">Name</label>
                <input type="text" id="addimpName" name="name" class="form-control">
            </div>

            {{-- Phone --}}
            <div class="mb-3">
                <label for="addimpfonno" class="form-label">Phone No</label>
                <input type="text" id="addimpfonno" name="phone_no" class="form-control">
            </div>

            {{-- Address --}}
            <div class="mb-3">
                <label for="addimpaddress" class="form-label">Address</label>
                <input type="text" id="addimpaddress1" name="address1" class="form-control mb-2"
                    placeholder="Address Line 1">
                <input type="text" id="addimpaddress2" name="address2" class="form-control" placeholder="Address Line 2">
            </div>

            {{-- Country --}}
            <div class="mb-3">
                <label for="addimpcountry" class="form-label">Country</label>
                <select class="form-select" id="addimpcountry" name="country">
                    <option value="">-- Select Country --</option>
                    {{-- @foreach ($country as $coun)
                    <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                    @endforeach --}}
                    <option value="SWK">Sarawak, Malaysia</option>
                    <option value="BN">Brunei Darussalam</option>
                </select>
            </div>

            @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

            <button type="button" id="addImporterBtn" class="btn btn-primary"
                data-route="{{ route('public.storeImporter') }}">
                Save Importer
            </button>
            @endslot
        </form>
    </x-modal>

@endsection

@push('scripts')
@endpush