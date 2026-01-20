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
                        @php
                            $status = ucfirst($application->status);
                            $badgeClass = 'bg-secondary';
                            if ($status === 'Draft') $badgeClass = 'bg-purple-transparent';
                            elseif ($status === 'Pending') $badgeClass = 'bg-warning-transparent';
                            elseif ($status === 'Approved') $badgeClass = 'bg-success-transparent';
                            elseif ($status === 'Rejected') $badgeClass = 'bg-danger-transparent';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small uppercase mb-1">Application ID</label>
                            <p class="fs-14 fw-semibold mb-0">{{ $application->application_id }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small uppercase mb-1">Created At</label>
                            <p class="fs-14 mb-0">{{ $application->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small uppercase mb-1">Category</label>
                            <p class="fs-14 mb-0">{{ $application->category_application == 1 ? 'Apply For Others' : 'Self Apply' }}</p>
                        </div>

                        <div class="col-md-6 border-top pt-3">
                            <label class="form-label fw-bold text-muted small uppercase mb-1">Importer Details</label>
                            @php
                                $importerName = '-';
                                $importerEmail = '-';
                                $importerPhone = '-';
                                if (!empty($application->importer_detail) && is_array($application->importer_detail)) {
                                    $importerName = $application->importer_detail['fullname'] ?? $application->importer_detail['name'] ?? '-';
                                    $importerEmail = $application->importer_detail['email'] ?? '-';
                                    $importerPhone = $application->importer_detail['phone_number'] ?? $application->importer_detail['phone_no'] ?? '-';
                                } elseif ($application->importer) {
                                    $importerName = $application->importer->fullname;
                                    $importerEmail = $application->importer->email;
                                    $importerPhone = $application->importer->phone_number;
                                }
                            @endphp
                            <div class="d-flex align-items-center mb-1">
                                <span class="avatar avatar-sm avatar-rounded bg-light text-muted me-2 border"><i class="ti ti-user fs-14"></i></span>
                                <span class="fw-semibold">{{ $importerName }}</span>
                            </div>
                            <div class="d-flex align-items-center mb-1 ps-4 ms-2">
                                <span class="text-muted small"><i class="ti ti-mail me-1"></i> {{ $importerEmail }}</span>
                            </div>
                            <div class="d-flex align-items-center ps-4 ms-2">
                                <span class="text-muted small"><i class="ti ti-phone me-1"></i> {{ $importerPhone }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 border-top pt-3">
                            <label class="form-label fw-bold text-muted small uppercase mb-1">Exporter Details</label>
                            <div class="d-flex align-items-center mb-1">
                                <span class="avatar avatar-sm avatar-rounded bg-light text-muted me-2 border"><i class="ti ti-building fs-14"></i></span>
                                <span class="fw-semibold">{{ $application->exporter->name ?? '-' }}</span>
                            </div>
                            <div class="d-flex align-items-center mb-1 ps-4 ms-2">
                                <span class="text-muted small"><i class="ti ti-phone me-1"></i> {{ $application->exporter->phone_no ?? '-' }}</span>
                            </div>
                            <div class="ps-4 ms-2">
                                <span class="text-muted small text-wrap d-block"><i class="ti ti-map-pin me-1"></i> {{ $application->exporter->address ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="col-md-4 border-top pt-3">
                            <label class="form-label fw-bold text-muted small uppercase mb-1">ETA</label>
                            <p class="fs-14 mb-0"><i class="ti ti-calendar me-1 text-muted"></i> {{ $application->eta ? $application->eta->format('d M Y') : '-' }}</p>
                        </div>
                        <div class="col-md-4 border-top pt-3">
                            <label class="form-label fw-bold text-muted small uppercase mb-1">Transport Type</label>
                            @php
                                $transportIcon = 'ti-truck';
                                if(strtolower($application->transport_type) == 'air') $transportIcon = 'ti-plane-departure';
                                if(strtolower($application->transport_type) == 'sea') $transportIcon = 'ti-ship';
                            @endphp
                            <p class="fs-14 mb-0"><i class="ti {{ $transportIcon }} me-1 text-muted"></i> {{ ucfirst($application->transport_type) ?? '-' }}</p>
                        </div>
                        <div class="col-md-4 border-top pt-3">
                            <label class="form-label fw-bold text-muted small uppercase mb-1">Entry Point</label>
                            <p class="fs-14 mb-0"><i class="ti ti-map-2 me-1 text-muted"></i> {{ $application->entryPoint->entry_name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Consignment Items & Attachments</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-4">No</th>
                                    <th>Item Detail</th>
                                    <th>Quantity</th>
                                    <th>Purpose / Uses</th>
                                    <th>Attachments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($application->inspectionItems as $index => $item)
                                    <tr>
                                        <td class="ps-4 align-top">{{ $index + 1 }}</td>
                                        <td class="align-top">
                                            <span class="fw-semibold d-block">{{ $item->consignment_detail['item_name'] ?? '-' }}</span>
                                            <span class="text-muted small">Value: {{ $item->value }}</span>
                                        </td>
                                        <td class="align-top">
                                            {{ $item->quantity }} {{ $item->unit_measurement }}
                                        </td>
                                        <td class="align-top">
                                            <span class="d-block small"><strong class="text-muted">Purpose:</strong> {{ $item->purpose ?? '-' }}</span>
                                            <span class="d-block small"><strong class="text-muted">Uses:</strong> {{ $item->consignment_detail['uses'] ?? '-' }}</span>
                                        </td>
                                        <td class="align-top">
                                            <div class="d-flex flex-column gap-1">
                                                @forelse($item->attachments as $attachment)
                                                    <a href="{{ $attachment->file_path }}" target="_blank" class="btn btn-sm btn-outline-info d-flex align-items-center" style="max-width: fit-content;">
                                                        <i class="ti ti-file-text me-1"></i> {{ Str::limit($attachment->file_name, 20) }}
                                                    </a>
                                                @empty
                                                    <span class="text-muted italic small">No attachments</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center p-4">
                                            <div class="text-muted">
                                                <i class="ti ti-package fs-24 d-block mb-2"></i>
                                                No items found for this application.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ auth('internal')->check() ? route('internal.inspection.list') : route('public.showallinspectionlist') }}" class="btn btn-light-transparent">
                        <i class="ti ti-arrow-left me-1"></i> Back to List
                    </a>
                    @if(auth('internal')->check() && $application->status === 'Pending')
                        <div class="d-flex gap-2">
                            <button class="btn btn-danger-light inspection-reject" data-id="{{ $application->application_id }}">
                                <i class="ti ti-x me-1"></i> Reject
                            </button>
                            <button class="btn btn-success-light inspection-approve" data-id="{{ $application->application_id }}">
                                <i class="ti ti-check me-1"></i> Approve
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @if(auth('internal')->check())
        @vite(['resources/js/pages/inspection/inspection_list.js'])
    @endif
@endpush
