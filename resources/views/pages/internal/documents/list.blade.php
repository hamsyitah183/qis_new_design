@extends('pages.app')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    @vite(['resources/js/pages/internal/documents/documents.js'])
@endpush

@section('pageName', 'Document Requirements')

@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
            ['label' => 'Documents', 'url' => '#', 'data-en' => 'Documents', 'data-bm' => 'Dokumen']
        ]" 
        title="Document Requirements"
        title_en="Document Requirements"
        title_bm="Keperluan Dokumen"
    />
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    {{-- <div class="card-title" data-en="Document Requirements" data-bm="Keperluan Dokumen">Document Requirements</div> --}}
                    <div class="ms-auto d-flex gap-2 align-items-center">
                        <button class="btn btn-primary btn-sm" id="btnAddDocument">
                            <i class="ti ti-plus me-1"></i> <span data-en="Add Document" data-bm="Tambah Dokumen">Add Document</span>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="documentTable" class="table table-bordered table-striped align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th data-en="Module" data-bm="Modul">Module</th>
                                    <th data-en="Name" data-bm="Nama">Name</th>
                                    <th data-en="Description" data-bm="Keterangan">Description</th>
                                    <th data-en="Required" data-bm="Wajib">Required</th>
                                    <th data-en="Expiry" data-bm="Tarikh Luput">Expiry</th>
                                    <th data-en="Status" data-bm="Status">Status</th>
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

    <!-- Add/Edit Modal -->
    <x-modal id="addDocumentModal" title="Document" title_en="Document" title_bm="Dokumen" size="modal-lg modal-dialog-centered" title_en="">
        <form id="documentForm">
            @csrf
            <input type="hidden" name="id" id="document_id">

            <div class="row gy-3">
                <div class="col-xl-12">
                    <label for="docModule" class="form-label" data-en="Module" data-bm="Modul">Module <span class="text-danger">*</span></label>
                    <select id="docModule" name="module" class="form-select" required>
                        <option value="user" data-en="User" data-bm="Pengguna">User</option>
                        <option value="import" data-en="Import Permit" data-bm="Permit Import">Import Permit</option>
                        <option value="inspection" data-en="Inspection" data-bm="Pemeriksaan">Inspection</option>
                        <option value="consignment" data-en="Consignment" data-bm="Konsainan">Consignment</option>
                        <option value="permit" data-en="Permit" data-bm="Permit">Permit</option>
                    </select>
                </div>

                <div class="col-xl-12">
                    <label for="docName" class="form-label" data-en="Document Name" data-bm="Nama Dokumen">Document Name <span class="text-danger">*</span></label>
                    <input type="text" id="docName" name="name" class="form-control" required>
                </div>

                <div class="col-xl-12">
                    <label for="docDescription" class="form-label" data-en="Description" data-bm="Keterangan">Description</label>
                    <textarea id="docDescription" name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="col-xl-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="docRequired" name="is_required">
                        <label class="form-check-label" for="docRequired" data-en="Required" data-bm="Wajib">Required</label>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="docExpiry" name="requires_expiry">
                        <label class="form-check-label" for="docExpiry" data-en="Requires Expiry" data-bm="Memerlukan Tarikh Luput">Requires Expiry</label>
                    </div>
                </div>

                <div class="col-xl-4 d-none">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="docActive" name="is_active" checked>
                        <label class="form-check-label" for="docActive" data-en="Active" data-bm="Aktif">Active</label>
                    </div>
                </div>
            </div>

            @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>
                <button type="button" id="btnSaveDocument" class="btn btn-primary" data-en="Save" data-bm="Simpan">Save</button>
            @endslot
        </form>
    </x-modal>

    <!-- View Modal -->
    <x-modal id="viewDocumentModal" title="View Document" title_en="View Document" title_bm="Lihat Dokumen" size="modal-lg modal-dialog-centered">
        <div class="row gy-3">
            <div class="col-xl-6"><strong data-en="Module" data-bm="Modul">Module:</strong> <span id="view_module"></span></div>
            <div class="col-xl-6"><strong data-en="Name" data-bm="Nama">Name:</strong> <span id="view_name"></span></div>
            <div class="col-xl-12"><strong data-en="Description" data-bm="Keterangan">Description:</strong> <span id="view_description"></span></div>
            <div class="col-xl-4"><strong data-en="Required" data-bm="Wajib">Required:</strong> <span id="view_required"></span></div>
            <div class="col-xl-4"><strong data-en="Requires Expiry" data-bm="Memerlukan Tarikh Luput">Requires Expiry:</strong> <span id="view_expiry"></span></div>
            <div class="col-xl-4"><strong data-en="Status" data-bm="Status">Status:</strong> <span id="view_status"></span></div>
            <div class="col-xl-12"><strong data-en="Created At" data-bm="Dicipta Pada">Created At:</strong> <span id="view_created_at"></span></div>
            <div class="col-xl-12"><strong data-en="Updated At" data-bm="Dikemaskini Pada">Updated At:</strong> <span id="view_updated_at"></span></div>
        </div>
        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
        @endslot
    </x-modal>
@endsection