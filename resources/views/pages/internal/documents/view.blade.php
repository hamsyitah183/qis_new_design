@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
        window.documentId = "{{ $document->id }}";
        window.documentName = "{{ $document->name }}";
    </script>
    @vite(['resources/js/pages/internal/documents/view.js'])
@endpush

@section('pageName', 'Document Details')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
        [
            'label' => 'Documents',
            'url' => route('internal.documents.index'),
            'data-en' => 'Documents',
            'data-bm' => 'Dokumen',
        ],
        ['label' => $document->name, 'url' => '#', 'data-en' => $document->name, 'data-bm' => $document->name],
    ]" title="Document Details" title_en="Document Details" title_bm="Butiran Dokumen" />
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    {{-- <div class="card-title " data-en="Document Details" data-bm="Butiran Dokumen">Document Details</div> --}}

                    <div class="d-flex ms-auto">
                        <a href = "/internal/documents/{{ $document->id }}/edit"
                        class="btn btn-info" data-bm="Sunting Maklumat Dokumen" data-en="Edit Document Detail">Edit Document Detail</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-xl-6">
                            <div class="border rounded p-3 h-100">
                                <div class="detail-label" data-en="Name" data-bm="Nama">Name</div>
                                <div class="detail-value">{{ $document->name }}</div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="border rounded p-3 h-100">
                                <div class="detail-label" data-en="Module" data-bm="Modul">Module</div>
                                <div class="detail-value">{{ ucfirst($document->module) }}</div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="border rounded p-3 h-100">
                                <div class="detail-label" data-en="Required" data-bm="Wajib">Required</div>
                                <span class="badge bg-{{ $document->is_required ? 'warning text-dark' : 'secondary' }}"
                                      data-en="{{ $document->is_required ? 'Required' : 'Optional' }}"
                                      data-bm="{{ $document->is_required ? 'Wajib' : 'Pilihan' }}">
                                    {{ $document->is_required ? 'Required' : 'Optional' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="border rounded p-3 h-100">
                                <div class="detail-label" data-en="Requires Expiry" data-bm="Memerlukan Tarikh Luput">
                                    Requires Expiry</div>
                                <span class="badge bg-{{ $document->requires_expiry ? 'info' : 'secondary' }}"
                                      data-en="{{ $document->requires_expiry ? 'Has Expiry' : 'No Expiry' }}"
                                      data-bm="{{ $document->requires_expiry ? 'Tarikh Luput' : 'Tiada Tarikh Luput' }}">
                                    {{ $document->requires_expiry ? 'Has Expiry' : 'No Expiry' }}
                                </span>
                            </div>
                        </div>
                       

                        <div class="col-xl-6">
                            <div class="border rounded p-3 h-100">
                                <div class="detail-label" data-en="Created At" data-bm="Dicipta Pada">Created At</div>
                                <div class="detail-value">
                                    <i class="ti ti-calendar-plus me-1 text-muted"></i>
                                    {{ $document->created_at ? $document->created_at->format('d M Y, H:i') : '—' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="border rounded p-3 h-100">
                                <div class="detail-label" data-en="Updated At" data-bm="Dikemaskini Pada">Updated At</div>
                                <div class="detail-value">
                                    <i class="ti ti-calendar-time me-1 text-muted"></i>
                                    {{ $document->updated_at ? $document->updated_at->format('d M Y, H:i') : '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12">
                            <div class="border rounded description-accordion">
                                <div class="d-flex align-items-center justify-content-between description-accordion-toggle p-3"
                                    role="button" aria-expanded="false">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ti ti-file-description fs-18 text-muted"></i>
                                        <span class="fw-semibold fs-14" data-en="Description"
                                            data-bm="Keterangan">Description</span>
                                    </div>
                                    <i class="ti ti-chevron-down description-accordion-icon fs-16 text-muted"></i>
                                </div>
                                <div class="description-accordion-panel d-none">
                                    <div class="px-3 pb-3 pt-2 border-top">
                                        <div class="document-description-content">
                                            {!! $document->description ?? '<span class="text-muted">No description provided.</span>' !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="User Attachments" data-bm="Lampiran Pengguna">User Attachments</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="attachmentTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th data-en="User" data-bm="Pengguna">User</th>
                                    <th data-en="File Name" data-bm="Nama Fail">File Name</th>
                                    <th data-en="File Type" data-bm="Jenis Fail">File Type</th>
                                    <th data-en="Size" data-bm="Saiz">Size</th>
                                    <th data-en="Valid From" data-bm="Sah Dari">Valid From</th>
                                    <th data-en="Valid Until" data-bm="Sah Sehingga">Valid Until</th>
                                    <th data-en="Uploaded At" data-bm="Dimuat Naik Pada">Uploaded At</th>
                                    <th data-en="Status" data-bm="Status">Status</th>
                                    <th data-en="Rejection" data-bm="Penolakan">Rejection</th>
                                    <th data-en="Action" data-bm="Tindakan">Action</th>
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

@push('style')
    <style>
        .detail-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #6c757d;
            margin-bottom: 6px;
        }

        .detail-value {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .document-description-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            display: block;
            margin: 12px 0;
            border: 1px solid #dee2e6;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.querySelector('.description-accordion-toggle');
            const panel = document.querySelector('.description-accordion-panel');
            const icon = document.querySelector('.description-accordion-icon');

            if (toggle && panel) {
                toggle.addEventListener('click', function() {
                    const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                    panel.classList.toggle('d-none', isExpanded);
                    toggle.setAttribute('aria-expanded', String(!isExpanded));
                    icon.classList.toggle('ti-chevron-up', !isExpanded);
                    icon.classList.toggle('ti-chevron-down', isExpanded);
                });
            }
        });
    </script>
@endpush
