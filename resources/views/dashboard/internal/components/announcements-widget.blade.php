<div class="card custom-card adm-announce-card">
    <div class="card-body">
        <div class="adm-card-title-row">
            <div>
                <h6 data-en="Announcements" data-bm="Pengumuman">Announcements</h6>
                <span class="adm-card-sub" data-en="What's currently posted to applicants" data-bm="Apa yang sedang disiarkan kepada pemohon">What's currently posted to applicants</span>
            </div>
            <button type="button" class="btn btn-sm btn-primary adm-announce-new-btn" data-bs-toggle="modal" data-bs-target="#admAnnounceModal">
                <i class='bx bx-plus me-1'></i> <span data-en="New" data-bm="Baru">New</span>
            </button>
        </div>

        <div class="adm-announce-list" id="admAnnounceList">
            <!-- rendered by admindashboard.js -->
        </div>
    </div>
</div>

{{-- New Announcement modal — demo only: it just prepends to the in-memory list, nothing is persisted --}}
<div class="modal fade" id="admAnnounceModal" tabindex="-1" aria-labelledby="admAnnounceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="admAnnounceModalLabel" data-en="New Announcement" data-bm="Pengumuman Baru">New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="admAnnounceTitle" class="form-label" data-en="Title" data-bm="Tajuk">Title</label>
                    <input type="text" class="form-control" id="admAnnounceTitle" placeholder="e.g. Scheduled system maintenance" data-en="e.g. Scheduled system maintenance" data-bm="cth. Penyelenggaraan sistem berjadual" data-i18n-attr="placeholder">
                </div>
                <div class="mb-3">
                    <label for="admAnnounceBody" class="form-label" data-en="Message" data-bm="Mesej">Message</label>
                    <textarea class="form-control" id="admAnnounceBody" rows="3" placeholder="Short description shown to applicants" data-en="Short description shown to applicants" data-bm="Penerangan ringkas yang dipaparkan kepada pemohon" data-i18n-attr="placeholder"></textarea>
                </div>
                <div class="mb-1">
                    <label for="admAnnounceStatus" class="form-label" data-en="Status" data-bm="Status">Status</label>
                    <select class="form-select" id="admAnnounceStatus">
                        <option value="published" data-en="Published" data-bm="Telah Diterbitkan">Published</option>
                        <option value="draft" data-en="Draft" data-bm="Draf">Draft</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>
                <button type="button" id="admAnnounceSaveBtn" class="btn btn-primary adm-announce-new-btn" data-en="Save" data-bm="Simpan">Save</button>
            </div>
        </div>
    </div>
</div>