@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
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

@push('scripts')
    @vite(['resources/js/pages/internal/internal_importer_list.js'])
@endpush

@section('pageName', 'All Importers')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#'], ['label' => 'Importer List', 'url' => '#']]"
        title="All Importer List">
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
                                    @foreach ($country as $coun)
                                        <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                                    @endforeach
                                </select>
                            </li>

                            <li class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                <button class="btn btn-sm btn-secondary" id="btnResetImporterFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="btnImporterFilter">Apply</button>
                            </li>
                        </ul>

                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="internalImporterTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone No</th>
                                    <th>Address</th>
                                    <th>Country</th>
                                    <th>Registered By</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection