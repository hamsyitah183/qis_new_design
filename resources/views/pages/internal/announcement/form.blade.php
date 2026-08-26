@extends('pages.app')

@section('pageName', isset($announcement) ? 'Edit Announcement' : 'Add Announcement')

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
        window.announcementId = "{{ isset($announcement) ? $announcement->id : '' }}";
    </script>
    <!-- Add Quill CSS in case it is needed here. It's also imported in the JS file -->
    @vite(['resources/js/pages/internal/announcement/form.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
            ['label' => 'Announcements', 'url' => route('internal.announcements.list'), 'data-en' => 'Announcements', 'data-bm' => 'Pengumuman'],
            ['label' => isset($announcement) ? 'Edit Announcement' : 'Add Announcement', 'url' => '#', 'data-en' => isset($announcement) ? 'Edit Announcement' : 'Add Announcement', 'data-bm' => isset($announcement) ? 'Sunting Pengumuman' : 'Tambah Pengumuman']
        ]" 
        title="{{ isset($announcement) ? 'Edit Announcement' : 'Add Announcement' }}"
        title_en="{{ isset($announcement) ? 'Edit Announcement' : 'Add Announcement' }}"
        title_bm="{{ isset($announcement) ? 'Sunting Pengumuman' : 'Tambah Pengumuman' }}"
    >
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="{{ isset($announcement) ? 'Edit Announcement' : 'Add Announcement' }}" data-bm="{{ isset($announcement) ? 'Sunting Pengumuman' : 'Tambah Pengumuman' }}">{{ isset($announcement) ? 'Edit Announcement' : 'Add Announcement' }}</div>
                </div>

                <div class="card-body">
                    <form id="announcementForm">
                        <input type="hidden" id="announcement_id" name="id" value="{{ isset($announcement) ? $announcement->id : '' }}">
                        <div class="row gy-3">
                            <div class="col-xl-12">
                                <label for="title" class="form-label" data-en="Title" data-bm="Tajuk">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ isset($announcement) ? $announcement->title : '' }}" required>
                            </div>
                            
                            <div class="col-xl-6">
                                <label class="form-label" data-en="Valid From" data-bm="Sah Dari">Valid From</label>
                                <input type="date" class="form-control" id="valid_from" name="valid_from" value="{{ isset($announcement) ? $announcement->valid_from : '' }}">
                            </div>
                            <div class="col-xl-6">
                                <label class="form-label" data-en="Valid Until" data-bm="Sah Sehingga">Valid Until</label>
                                <input type="date" class="form-control" id="valid_until" name="valid_until" value="{{ isset($announcement) ? $announcement->valid_until : '' }}">
                            </div>

                            <div class="col-xl-12">
                                <div class="d-flex gap-4 flex-wrap">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ isset($announcement) ? ($announcement->is_active ? 'checked' : '') : 'checked' }}>
                                        <label class="form-check-label" for="is_active" data-en="Is Active" data-bm="Aktif">Is Active</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="pin_announcement" name="pin_announcement" {{ isset($announcement) && $announcement->pin_announcement ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pin_announcement" data-en="Pin Announcement" data-bm="Semat Pengumuman">Pin Announcement</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <label class="form-label d-block" data-en="Content" data-bm="Kandungan">Content <span class="text-danger">*</span></label>
                                <div class="border rounded" style="min-height: 150px; background-color: var(--bs-body-bg);">
                                    <div id="content-editor" style="min-height: 150px; border: none;">{!! isset($announcement) ? $announcement->content : '' !!}</div>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <label class="form-label" data-en="Attachments" data-bm="Lampiran">Attachments</label>
                                <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                                
                                <!-- Preview new attachments (add mode) -->
                                <div id="new-attachments-preview" class="mt-2 d-flex flex-wrap gap-2"></div>

                                <!-- Existing attachments preview (edit mode) -->
                                <div id="existing-attachments" class="mt-3 d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="{{ route('internal.announcements.list') }}" class="btn btn-secondary" data-en="Cancel" data-bm="Batal">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="btnSaveAnnouncement" data-en="Save" data-bm="Simpan">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- File Preview Modal -->
    <x-modal id="filePreviewModal" title="File Preview" title_en="File Preview" title_bm="Pratonton Fail" size="modal-xl modal-dialog-centered">
        <div class="text-center w-100 h-100" id="previewModalBody">
            <img id="previewImageModalSrc" src="" alt="Preview" class="img-fluid rounded d-none" style="max-height: 70vh;">
            <iframe id="previewPdfModalSrc" src="" class="w-100 d-none" style="height: 75vh; border: none;"></iframe>
            <div id="previewUnsupportedMessage" class="d-none py-5">
                <i class="ti ti-file-download text-primary mb-3" style="font-size: 48px;"></i>
                <h5>This file type cannot be previewed in the browser.</h5>
                <a id="previewDownloadLink" href="#" target="_blank" class="btn btn-primary mt-3" data-en="Download File" data-bm="Muat Turun Fail">Download File</a>
            </div>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
        </x-slot>
    </x-modal>
@endsection
