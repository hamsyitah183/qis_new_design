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
    @vite(['resources/js/pages/inspection/inspection_list.js'])
@endpush

@section('pageName', 'List All Inspection Certificate')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="All Inspection Certificate List">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-body">
                    <div id="" class="dataTables_wrapper dt-bootstrap5 no-footer">

                        <div class="row">
                            <div class="col-sm-12">
                                <table id="inspectionListTable"
                                    class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                                    aria-describedby="responsiveDataTable_info" style="width: 100%;">
                                    <thead class="mt-3">
                                        <tr class="even">
                                            <th>#</th>
                                            <th>Category</th>
                                            <th>Importer</th>
                                            <th>Exporter</th>
                                            <th>ETA</th>
                                            <th>Transport</th>
                                            <th>Entry Point</th>
                                            <th>Status</th>
                                            <th>Action</th>
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

@endsection
