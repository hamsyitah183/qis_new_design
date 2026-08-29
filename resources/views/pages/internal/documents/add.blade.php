@extends('pages.app')

@push('style')
    {{-- <link rel="stylesheet" href="{{ asset('build2/assets/libs/quill/quill.snow.css') }}"> --}}
@endpush

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    @vite(['resources/js/pages/internal/documents/add.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Papan Pemuka'],
        ['label' => 'Documents', 'url' => url('/internal/documents'), 'data-en' => 'Documents', 'data-bm' => 'Dokumen'],
        [
            'label' => 'Add Document Requirement',
            'url' => '#',
            'data-en' => 'Add Document Requirement',
            'data-bm' => 'Tambah Keperluan Dokumen',
        ],
    ]" title="Document Requirements" title_en="Document Requirements"
        title_bm="Keperluan Dokumen" />
@endsection

@section('pageName', 'Add Document Requirement')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="Document Requirement" data-bm="Keperluan Dokumen">
                        Document Requirement
                    </div>
                </div>
                <div class="card-body">
                    <form id="documentForm">
                        @csrf
                        <input type="hidden" name="id" id="document_id">

                        <div class="row gy-3">
                            <div class="col-xl-6">
                                <label for="docModule" class="form-label" data-en="Module" data-bm="Modul">
                                    Module 
                                </label><span class="text-danger">*</span>
                                <select id="docModule" name="module" class="form-select" required>
                                    <option value="user" data-en="User" data-bm="Pengguna">User</option>
                                    <option value="import" data-en="Import Permit" data-bm="Permit Import">Import Permit</option>
                                    <option value="inspection" data-en="Inspection" data-bm="Pemeriksaan">Inspection</option>
                                    <option value="consignment" data-en="Consignment" data-bm="Konsainan">Consignment</option>
                                    <option value="permit" data-en="Permit" data-bm="Permit">Permit</option>
                                </select>
                            </div>

                            <div class="col-xl-12">
                                <label for="docName" class="form-label" data-en="Document Name" data-bm="Nama Dokumen">
                                    Document Name
                                </label><span class="text-danger">*</span>
                                <input type="text" id="docName" name="name" class="form-control" required
                                    data-en="Enter document name"
                                    data-bm="Masukkan nama dokumen"
                                    placeholder="Enter document name">
                            </div>

                            <div class="col-xl-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="docRequired" name="is_required">
                                    <label class="form-check-label" for="docRequired"
                                        data-en="Required" data-bm="Wajib">
                                        Required
                                    </label>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="docExpiry" name="requires_expiry">
                                    <label class="form-check-label" for="docExpiry"
                                        data-en="Requires Expiry" data-bm="Perlu Tarikh Luput">
                                        Requires Expiry
                                    </label>
                                </div>
                            </div>

                            <div class="col-xl-4 d-none">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="docActive" name="is_active" checked>
                                    <label class="form-check-label" for="docActive"
                                        data-en="Active" data-bm="Aktif">
                                        Active
                                    </label>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <label for="content-editor" class="form-label" data-en="Description" data-bm="Keterangan">
                                    Description
                                </label>
                                <div id="content-editor" style="height: 300px;"></div>
                            </div>

                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" id="btnSaveDocument" class="btn btn-primary"
                                data-en="Save" data-bm="Simpan">
                                Save
                            </button>
                            <a href="{{ url('/internal/documents') }}" class="btn btn-secondary"
                                data-en="Cancel" data-bm="Batal">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection