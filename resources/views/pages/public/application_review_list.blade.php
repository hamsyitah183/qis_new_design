@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    @vite(['resources/js/pages/importPermit/application_list.js'])
@endpush

@section('pageName', 'List All Application')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="Review Application">

    </x-breadcrumb>
@endsection

@section('content')

    {{-- <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        My Application - ALL
                    </div>
                </div>
                <div class="card-body">
                    <div id="responsiveDataTable_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="dataTables_length mb-3" id="responsiveDataTable_length">
                                    <label>Show
                                        <select name="responsiveDataTable_length" aria-controls="responsiveDataTable"
                                            class="form-select form-select-sm">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                        entries
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div id="responsiveDataTable_filter" class="dataTables_filter">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="Search..."
                                            aria-controls="responsiveDataTable">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="responsiveDataTable"
                                    class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                                    aria-describedby="responsiveDataTable_info" style="width: 1588px;">
                                    <thead class="mt-3">
                                        <tr class="even">
                                            <!-- <th>#</th>
                                                                <th>Importer</th>
                                                                <th>Exporter</th>
                                                                <th>Submitted By</th>
                                                                <th style="text-align: center;">Importer Type</th>-->
                                            <!-- self or other -->
                                            <!-- <th>Status</th> -->
                                            <th>#</th>
                                            <th>Importer</th>
                                            <th>Exporter</th>
                                            <th>Submitted By</th>
                                            <th style="text-align: center;">Importer Type</th> <!-- self or other -->
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($application as $appl)
                                            <tr class="odd">
                                                <td class="sorting_1 dtr-control" tabindex="0">#{{ $loop->iteration }}
                                                </td>
                                                <td>{{ $appl->importer->fullname }}</td>
                                                <td>{{ $appl->exporter->name }}</td>
                                                <td>{{ $appl->user->fullname }}</td>

                                                @if ($appl->category_application == 0)
                                                    <td style="text-align:center">
                                                        <span class="badge bg-success">SELF</span>
                                                    </td>
                                                    <td>{{ $appl->created_at }}</td>
                                                    <td style="text-align:center"><span
                                                            class="badge bg-success">Submitted</span></td>
                                                @elseif ($appl->category_application == 1)
                                                    <td style="text-align:center">
                                                        <span class="badge bg-primary">OTHERS</span>
                                                    </td>
                                                    <td>{{ $appl->created_at }}</td>
                                                    @if ($appl->importer_verify == false && $appl->date_importer_verify == null)
                                                        <td style="text-align:center">
                                                            <span class="badge bg-warning">Pending Verification</span>
                                                        </td>
                                                    @elseif ($appl->importer_verify == false && $appl->date_importer_verify != null)
                                                        <td style="text-align:center">
                                                            <span class="badge bg-danger">Rejected</span>
                                                        </td>
                                                    @endif
                                                @else
                                                    <td style="text-align:center">
                                                        <span class="badge bg-secondary">Unknown</span>
                                                    </td>
                                                    <td>{{ $appl->created_at }}</td>
                                                    <td></td>
                                                @endif

                                                <td style="text-align:center"><a type="button"
                                                        href="{{ route('viewApplication', $appl->application_id) }}"
                                                        class="btn btn-info btn-sm">View Application</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted" style="padding:20px">
                                                    No application records found.
                                                </td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="responsiveDataTable_info" role="status"
                                    aria-live="polite">Showing 1 to 10 of 50 entries</div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="responsiveDataTable_paginate">
                                    <ul class="pagination">
                                        <li class="paginate_button page-item previous disabled"
                                            id="responsiveDataTable_previous"><a href="#"
                                                aria-controls="responsiveDataTable" data-dt-idx="0" tabindex="0"
                                                class="page-link">Previous</a></li>
                                        <li class="paginate_button page-item active"><a href="#"
                                                aria-controls="responsiveDataTable" data-dt-idx="1" tabindex="0"
                                                class="page-link">1</a></li>
                                        <li class="paginate_button page-item "><a href="#"
                                                aria-controls="responsiveDataTable" data-dt-idx="2" tabindex="0"
                                                class="page-link">2</a></li>
                                        <li class="paginate_button page-item "><a href="#"
                                                aria-controls="responsiveDataTable" data-dt-idx="3" tabindex="0"
                                                class="page-link">3</a></li>
                                        <li class="paginate_button page-item "><a href="#"
                                                aria-controls="responsiveDataTable" data-dt-idx="4" tabindex="0"
                                                class="page-link">4</a></li>
                                        <li class="paginate_button page-item "><a href="#"
                                                aria-controls="responsiveDataTable" data-dt-idx="5" tabindex="0"
                                                class="page-link">5</a></li>
                                        <li class="paginate_button page-item next" id="responsiveDataTable_next"><a
                                                href="#" aria-controls="responsiveDataTable" data-dt-idx="6"
                                                tabindex="0" class="page-link">Next</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

     <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-body">
                    <div id="" class="dataTables_wrapper dt-bootstrap5 no-footer">

                        <div class="row">
                            <div class="col-sm-12">
                                <table id="reviewApplicationListTable"
                                    class="table table-bordered text-nowrap w-100 dataTable no-footer dtr-inline"
                                    aria-describedby="responsiveDataTable_info" style="width: 1588px;">
                                    <thead class="mt-3">
                                        <tr class="even">
                                            <th>#</th>
                                            <th>Importer</th>
                                            <th>Exporter</th>
                                            <th style="text-align: center;">Importer Type</th> <!-- self or other -->
                                            <th>ETA</th>
                                            <th>Status</th>
                                            <th>Submitted By</th>
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

@push('scripts')
@endpush
