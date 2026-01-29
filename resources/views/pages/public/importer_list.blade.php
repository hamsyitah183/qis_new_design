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
    @vite(['resources/js/pages/importer_list.js'])
@endpush

@section('pageName', 'List All Consignment Importer')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="All Importer List">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-title">
                    <div class="d-flex justify-content-end">
                        <div class="btn btn-primary btn-sm" id="addExporter">
                            Add Importer
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="exporterTable" class="table table-bordered table-striped align-middle w-100">
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

    <!-- Add Exporter Modal -->
    <x-modal id="addExporterModal" title="Add Exporter">
        <form id="addExporterForm">
            @csrf

            <input type="hidden" name="id" id="id">

            {{-- Name --}}
            <div class="mb-3">
                <label for="addexpName" class="form-label">Name</label>
                <input type="text" id="addexpName" name="addexpName" class="form-control">
            </div>

            {{-- Phone --}}
            <div class="mb-3">
                <label for="addexpfonno" class="form-label">Phone No</label>
                <input type="text" id="addexpfonno" name="addexpfonno" class="form-control">
            </div>

            {{-- Address --}}
            <div class="mb-3">
                <label for="addexpaddress" class="form-label">Address</label>
                <input type="text" id="addexpaddress1" name="addexpaddress1" class="form-control mb-2">
                <input type="text" id="addexpaddress2" name="addexpaddress2" class="form-control">
            </div>

            {{-- Country --}}
            <div class="mb-3">
                <label for="addexpcountry" class="form-label">Country</label>
                <select class="form-select" id="addexpcountry" name="addexpcountry">
                    <option value="">-- Select Country --</option>
                    @foreach ($country as $coun)
                        <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                    @endforeach
                </select>
            </div>

            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <button type="button" id="addExporterbtn" class="btn btn-primary" data-route="{{ route('public.storeImporter') }}">
                    Save Importer
                </button>
            @endslot
        </form>
    </x-modal>

@endsection

@push('scripts')
@endpush
