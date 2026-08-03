@extends('pages.app')

{{-- @push('style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush --}}

@php
    $type = authUser()['type'];

@endphp

@push('scripts')
    <script>
        window.AUTH_TYPE = @json($type);
    </script>
    @vite(['resources/js/pages/permit/consignment_permit_list.js'])
@endpush

@section('pageName', ' List Consignment Permit')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title=" Permit List">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-body">
                    <table id="permitListTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th data-bm="Nombor Permit" data-en="Permit Number">Permit Number</th>
                                <th data-bm="Nama Item" data-en="Item Name">Item Name</th>
                                <th data-bm="Pengimport" data-en="Importer">Importer</th>
                                <th data-bm="Tindakan" data-en="Action">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <x-modal id="activityLogModal" title="Activity Log">

        <!-- Your table goes here -->
        <div class="table-responsive" style="max-height: 400px;">
            <table class="table text-wrap table-hover" id="applicationLogTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" data-bm="Tindakan" data-en="Action">Action</th>
                        <th scope="col" data-bm="Pengguna" data-en="User">User</th>
                        <th scope="col" data-bm="Catatan" data-en="Remark">Remark</th>
                        <th scope="col" data-bm="Status" data-en="Status">Status</th>
                        <th scope="col" data-bm="Masa dan Tarikh" data-en="Time and Date">Time and Date</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">

                </tbody>
            </table>
        </div>

        @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-bm="Tutup" data-en="Close">Close</button>
        @endslot

    </x-modal>

@endsection