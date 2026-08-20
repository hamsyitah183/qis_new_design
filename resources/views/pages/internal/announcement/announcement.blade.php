@extends('pages.app')

@section('pageName', 'Announcements')



@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    @vite(['resources/js/pages/internal/announcement/announcement.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
            ['label' => 'Announcements', 'url' => '#', 'data-en' => 'Announcements', 'data-bm' => 'Pengumuman']
        ]" 
        title="Announcements"
        title_en="Announcements"
        title_bm="Pengumuman"
    >
    </x-breadcrumb>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="Announcement List" data-bm="Senarai Pengumuman">Announcement List</div>
                    <div class="ms-auto d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-success btn-sm" id="btnAddAnnouncement">
                            <i class="ti ti-plus me-1"></i> <span data-en="Add Announcement" data-bm="Tambah Pengumuman">Add Announcement</span>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <table id="announcementTable" class="table table-bordered text-nowrap w-100">
                        <thead>
                            <tr>
                                <th class="text-wrap" data-en="Title" data-bm="Tajuk">Title</th>
                                <th data-en="Released By" data-bm="Dikeluarkan Oleh">Released By</th>
                                <th data-en="Valid From" data-bm="Sah Dari">Valid From</th>
                                <th data-en="Valid Until" data-bm="Sah Sehingga">Valid Until</th>
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

    <!-- Announcement Modal -->
    <x-modal id="announcementModal" title="Announcement" title_en="Announcement" title_bm="Pengumuman" size="modal-lg modal-dialog-centered">
        <form id="announcementForm">
            <input type="hidden" id="announcement_id" name="id">
            <div class="row gy-3">
                <div class="col-xl-12">
                    <label for="title" class="form-label" data-en="Title" data-bm="Tajuk">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                
                <div class="col-xl-6">
                    <label class="form-label" data-en="Valid From" data-bm="Sah Dari">Valid From</label>
                    <input type="date" class="form-control" id="valid_from" name="valid_from">
                </div>
                <div class="col-xl-6">
                    <label class="form-label" data-en="Valid Until" data-bm="Sah Sehingga">Valid Until</label>
                    <input type="date" class="form-control" id="valid_until" name="valid_until">
                </div>

                <div class="col-xl-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active" data-en="Is Active" data-bm="Aktif">Is Active</label>
                    </div>
                </div>

                <div class="col-xl-12">
                    <label class="form-label d-block" data-en="Content" data-bm="Kandungan">Content <span class="text-danger">*</span></label>
                    <div class="border rounded" style="min-height: 150px; background-color: var(--bs-body-bg);">
                        <div id="content-editor" style="min-height: 150px; border: none;"></div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <label class="form-label" data-en="Attachments (Images Only)" data-bm="Lampiran (Gambar Sahaja)">Attachments (Images Only)</label>
                    <input type="file" class="form-control" id="attachments" name="attachments[]" multiple accept="image/*">
                    
                    <!-- Preview new attachments (add mode) -->
                    <div id="new-attachments-preview" class="mt-2 d-flex flex-wrap gap-2"></div>

                    <!-- Existing attachments preview (edit mode) -->
                    <div id="existing-attachments" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </form>
        <x-slot name="footer">
            <div class="w-100 d-flex justify-content-end gap-2" style="position: relative; z-index: 9999;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveAnnouncement" data-en="Save" data-bm="Simpan">Save</button>
            </div>
        </x-slot>
    </x-modal>
    <!-- View Announcement Modal -->
    <x-modal id="viewAnnouncementModal" title="View Announcement" title_en="View Announcement" title_bm="Lihat Pengumuman" size="modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="row gy-3">
            <div class="col-xl-12">
                <h5 id="view_title" class="fw-bold mb-1"></h5>
                <p class="text-muted mb-3" style="font-size: 0.85rem;" id="view_dates"></p>
                <div class="border-top pt-3">
                    <div id="view_content" class="mt-2 text-wrap" style="word-break: break-word;"></div>
                </div>
            </div>
            <div class="col-xl-12 border-top pt-3" id="view_attachments_container" style="display: none;">
                <h6 class="fw-bold mb-2" data-en="Attachments" data-bm="Lampiran">Attachments</h6>
                <div id="view_attachments" class="d-flex flex-wrap gap-2"></div>
            </div>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
        </x-slot>
    </x-modal>
@endsection
