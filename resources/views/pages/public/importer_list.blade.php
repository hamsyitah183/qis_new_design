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
                <div class="card-title p-3">
                    <div class="d-flex justify-content-end">
                        <div class="btn btn-primary btn-sm" id="addImporter">
                            Add Importer
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
                    @foreach ($country as $coun)
                        <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                    @endforeach
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