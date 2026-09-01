<div class="wizard-step" data-title="APPLICATION DETAILS"
    data-title-en="APPLICATION DETAILS" 
    data-title-bm="MAKLUMAT PERMOHONAN" 
data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="1">
    <div class="row justify-content-center gy-3">
        <div class="col-xl-6">
            <div class="register-page ipa-card h-100">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-paperclip'></i></span>
                    <h6 data-en="Application Attachment" data-bm="Lampiran Permohonan">Application Attachment
                        <span class="ipa-card-sub" data-en="Upload supporting documents for this application" data-bm="Muat naik dokumen sokongan untuk permohonan ini">Upload supporting documents for this application</span>
                    </h6>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12">
                        <form id="applicationAttachmentDropzone" class="dz-clickable" method="post"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="dz-default dz-message">
                                <button class="dz-button p-5 border w-100 border-radius" type="button">
                                    <i class='bx bx-cloud-upload' style="font-size:22px"></i><br>
                                    <span data-en="Drop files here to upload" data-bm="Jatuhkan fail di sini untuk dimuat naik">Drop files here to upload</span>
                                </button>
                                
                            </div>
                        </form>
                    </div>
                    <div class="col-xl-12">
                        <div class="table-responsive mt-3">
                            <table class="table table-sm text-nowrap fs-12" id="applicationAttachmentTable">
                                <thead  class="table-primary">
                                    <tr>
                                        <th data-en="File" data-bm="Fail">File</th>
                                        <th class="" data-en="Action" data-bm="Tindakan">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3" data-en="No attachments uploaded yet." data-bm="Tiada lampiran dimuat naik lagi.">No attachments uploaded yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="register-page ipa-card h-100">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-calendar-check'></i></span>
                    <h6 data-en="Arrival" data-bm="Ketibaan">Arrival
                        <span class="ipa-card-sub" data-en="When application will arrive" data-bm="Bilakah permohonan akan tiba">When application will arrive</span>
                    </h6>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12">
                        <label for="eta" class="form-label" data-en="Estimated Time Arrival" data-bm="Anggaran Waktu Ketibaan">Estimated Time Arrival</label><a style="color:red"> *
                            </a>
                        <input type="date" class="form-control" id="eta" name="eta" required>
                        <div class="invalid-feedback" id="etaError" data-en="Estimated Time Arrival cannot be a past date." data-bm="Anggaran Waktu Ketibaan tidak boleh menjadi tarikh yang lepas.">Estimated Time Arrival cannot be a past date.</div>
                    </div>
                    <div class="col-xl-12">
                        <label for="trnptType" class="form-label" data-en="Transport Type" data-bm="Jenis Pengangkutan">Transport Type</label><a style="color:red"> * </a>
                        <select class="form-select" id="trnptType" name="trnptType" data-route="/public/get_entry_point"
                            required>
                            <option value="" data-en="-- Select Transport --" data-bm="-- Pilih Pengangkutan --">-- Select Transport --</option>
                            <option value="Air" data-en="Air" data-bm="Udara">Air</option>
                            <option value="Sea" data-en="Sea" data-bm="Laut">Sea</option>
                            <option value="Land" data-en="Land" data-bm="Darat">Land</option>
                        </select>
                    </div>
                    <div class="col-xl-12">
                        <label for="entryPoint" class="form-label" data-en="Entry Point" data-bm="Pintu Masuk">Entry Point</label><a style="color:red"> * </a>
                        <select class="form-select" id="entryPoint" name="entryPoint" required>
                            <option value="" data-en="-- Select Entry Point --" data-bm="-- Pilih Pintu Masuk --">-- Select Entry Point --</option>
                        </select>
                        <input type="hidden" id="descEntryPoint">
                    </div>

                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Application Attachment Viewer Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="attachmentOffcanvas"
    aria-labelledby="attachmentOffcanvasLabel" style="width: 70%; max-width: 900px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="attachmentOffcanvasLabel">
            <i class="bi bi-paperclip me-2"></i> <span id="attachmentTitle" data-en="Attachment" data-bm="Lampiran">Attachment</span>
        </h5>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" id="attachmentPrevBtn" title="Previous" >
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="badge bg-light text-dark" id="attachmentCounter">0 / 0</span>
            <button class="btn btn-sm btn-outline-secondary" id="attachmentNextBtn" title="Next" >
                <i class="bi bi-chevron-right"></i>
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 60px);">
        <div class="pd-nav flex-shrink-0">
            <ul class="nav nav-pills flex-column" id="attachmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="attach-view-tab" data-bs-toggle="tab"
                        data-bs-target="#attach-view" type="button" role="tab" aria-selected="true"
                        data-bs-placement="right" title="View" >
                        <i class="bi bi-eye"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attach-details-tab" data-bs-toggle="tab"
                        data-bs-target="#attach-details" type="button" role="tab" aria-selected="false"
                        data-bs-placement="right" title="Details" >
                        <i class="bi bi-info-circle"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attach-edit-tab" data-bs-toggle="tab"
                        data-bs-target="#attach-edit" type="button" role="tab" aria-selected="false"
                        data-bs-placement="right" title="Edit" >
                        <i class="bi bi-pencil"></i>
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content flex-grow-1 p-3 overflow-auto" id="attachmentTabContent">
            <div class="tab-pane fade show active" id="attach-view" role="tabpanel">
                <div id="attachmentViewer" class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br><span data-en="Select an attachment" data-bm="Pilih lampiran">Select an attachment</span></div>
                </div>
            </div>
            <div class="tab-pane fade" id="attach-details" role="tabpanel">
                <div id="attachmentDetails" class="py-2"></div>
            </div>
            <div class="tab-pane fade" id="attach-edit" role="tabpanel">
                <div class="p-3">
                    <div class="mb-3">
                        <label for="attachmentEditName" class="form-label" data-en="File Name" data-bm="Nama Fail">File Name</label>
                        <input type="text" class="form-control" id="attachmentEditName" placeholder="Enter new file name" data-en="Enter new file name" data-bm="Masukkan nama fail baharu">
                    </div>
                    <button type="button" class="btn btn-primary" id="attachmentSaveNameBtn">
                        <i class="bi bi-check me-2"></i> <span data-en="Save Changes" data-bm="Simpan Perubahan">Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
