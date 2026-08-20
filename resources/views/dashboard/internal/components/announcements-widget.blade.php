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
                        <b>{{ $a->title }}</b>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($a->content), 80) }}</p>
                        <div class="adm-announce-meta mb-2">
                            <span>{{ $a->created_at->format('d M Y') }}</span>
                            <span class="adm-badge {{ $a->is_active ? 'adm-published' : 'adm-draft' }}">
                                {{ $a->is_active ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        @if($a->attachments->isNotEmpty())
                            <div class="mt-2" style="cursor: pointer;" 
                                 onclick="document.getElementById('dashboard_modal_image_src').src=this.querySelector('img').dataset.src; bootstrap.Modal.getOrCreateInstance(document.getElementById('dashboardImageViewModal')).show();">
                                <img src="{{ asset('storage/' . $a->attachments->first()->file_path) }}" 
                                     alt="attachment" 
                                     data-src="{{ asset('storage/' . $a->attachments->first()->file_path) }}"
                                     class="rounded border"
                                     style="max-width: 100%; max-height: 200px; object-fit: contain;">
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="adm-cal-empty-msg" data-en="No announcements yet." data-bm="Tiada pengumuman lagi.">No announcements yet.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- Image View Modal (Dashboard) -->
<x-modal id="dashboardImageViewModal" title="View Image" title_en="View Image" title_bm="Lihat Gambar" size="modal-lg modal-dialog-centered">
    <div class="text-center">
        <img id="dashboard_modal_image_src" src="" alt="Attachment Image" class="img-fluid rounded" style="max-height: 80vh;">
    </div>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
    </x-slot>
</x-modal>
