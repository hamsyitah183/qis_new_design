@extends('pages.app')

@section('pageName', 'View Inspection Application')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        ['label' => 'Inspection List', 'url' => route('public.showallinspectionlist')],
        ['label' => 'Application: ' . $application->application_id, 'url' => '#'],
    ]" title="View Inspection Application">

    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        Application Details
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Application ID</label>
                            <p>{{ $application->application_id }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Created At</label>
                            <p>{{ $application->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Importer</label>
                            @php
                                $importerName = '-';
                                if (!empty($application->importer_detail) && is_array($application->importer_detail)) {
                                    $importerName =
                                        $application->importer_detail['fullname'] ??
                                        $application->importer_detail['name'] ??
                                        '-';
                                } elseif ($application->importer) {
                                    $importerName = $application->importer->fullname;
                                }
                            @endphp
                            <p>{{ $importerName }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Exporter</label>
                            <p>{{ $application->exporter->name ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">ETA</label>
                            <p>{{ $application->eta ? $application->eta->format('d M Y') : '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Transport Type</label>
                            <p>{{ ucfirst($application->transport_type) ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Entry Point</label>
                            <p>{{ $application->entryPoint->entry_name ?? '-' }}</p>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <a href="{{ route('public.showallinspectionlist') }}" class="btn btn-light">Back to List</a>
                </div>
            </div>
        </div>
    </div>

@endsection
