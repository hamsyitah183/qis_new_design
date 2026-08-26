<div class="card custom-card adm-announce-card">
    <div class="card-body">
        <div class="adm-card-title-row">
            <div>
                <h6 data-en="Announcements" data-bm="Pengumuman">Announcements</h6>
                <span class="adm-card-sub" data-en="What's currently posted to applicants" data-bm="Apa yang sedang disiarkan kepada pemohon">What's currently posted to applicants</span>
            </div>
        </div>

        <div class="adm-announce-list" id="admAnnounceList">
            @forelse($announcements as $a)
                <div class="adm-announce-item">
                    <span class="adm-icon"><i class='bx bx-bell'></i></span>
                    <div style="width: 100%;">
                        <div class="d-flex align-items-center mb-1">
                            <b class="mb-0">{{ $a->title }}</b>
                            @if($a->pin_announcement)
                                <span class="badge bg-warning-transparent text-warning border border-warning ms-2" style="font-size: 10px; padding: 2px 6px;">
                                    <i class='bx bxs-pin me-1'></i>Pinned
                                </span>
                            @endif
                        </div>
                        <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 0.5rem;">
                            {!! $a->content !!}
                        </div>
                        <div class="adm-announce-meta mb-2">
                            <span>{{ $a->created_at->format('d M Y') }}</span>
                            <span class="adm-badge {{ $a->is_active ? 'adm-published' : 'adm-draft' }}">
                                {{ $a->is_active ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        @if($a->attachments->isNotEmpty())
                            @php
                                $att = $a->attachments->first();
                                $isImg = str_starts_with($att->file_type, 'image/');
                                $isPdf = $att->file_type === 'application/pdf' || str_ends_with(strtolower($att->file_name), '.pdf');
                                $isDoc = str_contains($att->file_type, 'word') || str_contains($att->file_type, 'document');
                                $isXls = str_contains($att->file_type, 'excel') || str_contains($att->file_type, 'spreadsheet');
                                $fileUrl = asset('storage/' . $att->file_path);
                            @endphp

                            @if($isImg)
                                <div class="mt-2" style="cursor: pointer;" onclick="openDashboardPreview('{{ $fileUrl }}', 'image')">
                                    <img src="{{ $fileUrl }}" 
                                         alt="attachment" 
                                         class="rounded border"
                                         style="max-width: 100%; max-height: 200px; object-fit: contain;">
                                </div>
                            @else
                                <div class="mt-2 border rounded p-2" style="max-width: fit-content; cursor: pointer;" onclick="openDashboardPreview('{{ $fileUrl }}', '{{ $isPdf ? 'pdf' : 'other' }}')">
                                    <div class="d-flex align-items-center text-decoration-none">
                                        @if($isPdf)
                                            <i class="ti ti-file-type-pdf text-danger fs-24"></i>
                                        @elseif($isDoc)
                                            <i class="ti ti-file-type-doc text-primary fs-24"></i>
                                        @elseif($isXls)
                                            <i class="ti ti-file-type-xls text-success fs-24"></i>
                                        @else
                                            <i class="ti ti-file-description text-secondary fs-24"></i>
                                        @endif
                                        <span class="ms-2 text-dark text-truncate" style="max-width: 250px;" title="{{ $att->file_name }}">{{ $att->file_name }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="adm-cal-empty-msg" data-en="No announcements yet." data-bm="Tiada pengumuman lagi.">No announcements yet.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Dashboard File Preview Logic -->
<script>
    if (typeof openDashboardPreview !== 'function') {
        function openDashboardPreview(url, type) {
            document.getElementById('dashboardPreviewImageModalSrc').classList.add('d-none');
            document.getElementById('dashboardPreviewPdfModalSrc').classList.add('d-none');
            document.getElementById('dashboardPreviewUnsupportedMessage').classList.add('d-none');

            if (type === 'image') {
                document.getElementById('dashboardPreviewImageModalSrc').src = url;
                document.getElementById('dashboardPreviewImageModalSrc').classList.remove('d-none');
            } else if (type === 'pdf') {
                document.getElementById('dashboardPreviewPdfModalSrc').src = url;
                document.getElementById('dashboardPreviewPdfModalSrc').classList.remove('d-none');
            } else {
                document.getElementById('dashboardPreviewDownloadLink').href = url;
                document.getElementById('dashboardPreviewUnsupportedMessage').classList.remove('d-none');
            }

            bootstrap.Modal.getOrCreateInstance(document.getElementById('dashboardFilePreviewModal')).show();
        }
    }
</script>

<!-- File Preview Modal (Dashboard) -->
<x-modal id="dashboardFilePreviewModal" title="File Preview" title_en="File Preview" title_bm="Pratonton Fail" size="modal-xl modal-dialog-centered">
    <div class="text-center w-100 h-100" id="dashboardPreviewModalBody">
        <img id="dashboardPreviewImageModalSrc" src="" alt="Preview" class="img-fluid rounded d-none" style="max-height: 70vh;">
        <iframe id="dashboardPreviewPdfModalSrc" src="" class="w-100 d-none" style="height: 75vh; border: none;"></iframe>
        <div id="dashboardPreviewUnsupportedMessage" class="d-none py-5">
            <i class="ti ti-file-download text-primary mb-3" style="font-size: 48px;"></i>
            <h5>This file type cannot be previewed in the browser.</h5>
            <a id="dashboardPreviewDownloadLink" href="#" target="_blank" class="btn btn-primary mt-3" data-en="Download File" data-bm="Muat Turun Fail">Download File</a>
        </div>
    </div>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
    </x-slot>
</x-modal>
