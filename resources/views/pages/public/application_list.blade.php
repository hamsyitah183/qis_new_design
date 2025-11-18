@extends('pages.app')

@section('pageName', 'List All Application')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="All Application List">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
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
                                            <th>#</th>
                                            <th>Importer</th>
                                            <th>Exporter</th>
                                            <th>Submitted By</th>
                                            <th style="text-align: center;">Importer Type</th> <!-- self or other -->
                                            <th>Date</th>
                                            <th>Status</th>
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
                                                <td style="text-align:center">
                                                    @if ($appl->category_application == 0)
                                                        <span class="badge bg-success">SELF</span>
                                                    @elseif ($appl->category_application == 1)
                                                        <span class="badge bg-primary">OTHERS</span>
                                                    @else
                                                        <span class="badge bg-secondary">Unknown</span>
                                                    @endif
                                                </td>
                                                <td>{{ $appl->created_at }}</td>
                                                <td><a type="button"
                                                        href="{{ route('public.viewApplication', $appl->application_id) }}"
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
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
