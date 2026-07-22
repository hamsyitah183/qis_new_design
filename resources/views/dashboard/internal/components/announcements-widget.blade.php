<div class="card custom-card adm-announce-card">
    <div class="card-body">
        <div class="adm-card-title-row">
            <div>
                <h6>Announcements</h6>
                <span class="adm-card-sub">What's currently posted to applicants</span>
            </div>
            <button type="button" class="btn btn-sm btn-primary adm-announce-new-btn" data-bs-toggle="modal" data-bs-target="#admAnnounceModal">
                <i class='bx bx-plus me-1'></i> New
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
                <h5 class="modal-title" id="admAnnounceModalLabel">New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="admAnnounceTitle" class="form-label">Title</label>
                    <input type="text" class="form-control" id="admAnnounceTitle" placeholder="e.g. Scheduled system maintenance">
                </div>
                <div class="mb-3">
                    <label for="admAnnounceBody" class="form-label">Message</label>
                    <textarea class="form-control" id="admAnnounceBody" rows="3" placeholder="Short description shown to applicants"></textarea>
                </div>
                <div class="mb-1">
                    <label for="admAnnounceStatus" class="form-label">Status</label>
                    <select class="form-select" id="admAnnounceStatus">
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="admAnnounceSaveBtn" class="btn btn-primary adm-announce-new-btn">Save</button>
            </div>
        </div>
    </div>
</div>