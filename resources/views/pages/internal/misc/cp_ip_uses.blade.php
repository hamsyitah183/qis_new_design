<div class="tab-pane p-0" id="ip-uses-settings" role="tabpanel">
    <ul class="list-group list-group-flush rounded">
        <li class="list-group-item">
            <div class="col-xxl-11">
                <div class="card custom-card shadow-none mb-0">
                    <div class="card-header justify-content-between d-sm-flex d-block">
                        <div class="card-title" data-en="IP Uses" data-bm="Kegunaan IP">IP Uses</div>
                        <div class="mt-sm-0 mt-2">
                            <button class="btn btn-sm btn-primary" onclick="openIpUsesModal()" data-type="ip_uses">
                                <i class="ri-add-line me-1"></i> <span data-en="Add IP Use" data-bm="Tambah Kegunaan">Add IP Use</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <div class="w-100">
                                    <table id="table-ip-uses" class="table table-striped text-nowrap table-hover table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th scope="col" data-en="Name" data-bm="Nama">Name</th>
                                                <th scope="col" data-en="Action" data-bm="Tindakan">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</div>

<!-- Add/Edit Modal for IP Uses -->
<div class="modal fade" id="ipUsesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ipUsesModalTitle" data-en="Add IP Use" data-bm="Tambah Kegunaan IP">Add IP Use</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ipUsesForm">
                @csrf
                <input type="hidden" id="ip_uses_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ip_uses_name" class="form-label">
                            <span data-en="Name" data-bm="Nama">Name</span> <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="ip_uses_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <span data-en="Close" data-bm="Tutup">Close</span>
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveIpUsesBtn">
                        <span data-en="Save" data-bm="Simpan">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
