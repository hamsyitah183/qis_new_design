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
                        <a href="{{ route('internal.announcements.create') }}" class="btn btn-success btn-sm">
                            <i class="ti ti-plus me-1"></i> <span data-en="Add Announcement" data-bm="Tambah Pengumuman">Add Announcement</span>
                        </a>
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

    <!-- Share via Email Confirmation Modal -->
    <div class="modal fade" id="shareEmailModal" tabindex="-1" aria-labelledby="shareEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareEmailModalLabel" data-en="Share via Email" data-bm="Kongsi melalui E-mel">
                        <i class="ti ti-mail me-2 text-primary"></i> Share via Email
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1" data-en="You are about to send the following announcement to all registered public users:" data-bm="Anda akan menghantar pengumuman berikut kepada semua pengguna awam berdaftar:">
                        You are about to send the following announcement to all registered public users:
                    </p>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong id="share_email_title"></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmShareEmail" data-en="Confirm Send" data-bm="Hantar">
                        <i class="ti ti-send me-1"></i> Confirm Send
                    </button>
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
