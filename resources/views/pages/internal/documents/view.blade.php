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
    <x-breadcrumb 
        :items="[
            ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
            ['label' => 'Documents', 'url' => route('internal.documents.index'), 'data-en' => 'Documents', 'data-bm' => 'Dokumen'],
            ['label' => $document->name, 'url' => '#', 'data-en' => $document->name, 'data-bm' => $document->name]
        ]" 
        title="Document Details"
        title_en="Document Details"
        title_bm="Butiran Dokumen"
    />
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title" data-en="Document Details" data-bm="Butiran Dokumen">Document Details</div>
            </div>
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-xl-6">
                        <strong data-en="Name" data-bm="Nama">Name:</strong>
                        <span class="text-muted">{{ $document->name }}</span>
                    </div>
                    <div class="col-xl-6">
                        <strong data-en="Module" data-bm="Modul">Module:</strong>
                        <span class="text-muted">{{ $document->module }}</span>
                    </div>
                    <div class="col-xl-12">
                        <strong data-en="Description" data-bm="Keterangan">Description:</strong>
                        <p class="text-muted">{{ $document->description ?? '—' }}</p>
                    </div>
                    <div class="col-xl-4">
                        <strong data-en="Required" data-bm="Wajib">Required:</strong>
                        <span class="badge bg-{{ $document->is_required ? 'warning' : 'secondary' }}">
                            {{ $document->is_required ? 'Required' : 'Optional' }}
                        </span>
                    </div>
                    <div class="col-xl-4">
                        <strong data-en="Requires Expiry" data-bm="Memerlukan Tarikh Luput">Requires Expiry:</strong>
                        <span class="badge bg-{{ $document->requires_expiry ? 'info' : 'secondary' }}">
                            {{ $document->requires_expiry ? 'Has Expiry' : 'No Expiry' }}
                        </span>
                    </div>
                    <div class="col-xl-4">
                        <strong data-en="Status" data-bm="Status">Status:</strong>
                        <span class="badge bg-{{ $document->is_active ? 'success' : 'danger' }}">
                            {{ $document->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="col-xl-6">
                        <strong data-en="Created At" data-bm="Dicipta Pada">Created At:</strong>
                        <span class="text-muted">{{ $document->created_at ? $document->created_at->format('d M Y H:i') : '—' }}</span>
                    </div>
                    <div class="col-xl-6">
                        <strong data-en="Updated At" data-bm="Dikemaskini Pada">Updated At:</strong>
                        <span class="text-muted">{{ $document->updated_at ? $document->updated_at->format('d M Y H:i') : '—' }}</span>
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