<div class="wizard-step" data-title="INSPECTION DETAILS" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="1">
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
                    <span class="ipa-icon-badge"><i class='bx bx-search-alt'></i></span>
                    <h6 data-en="Inspection Details" data-bm="Butiran Pemeriksaan">Inspection Details
                        <span class="ipa-card-sub" data-en="When and how the goods arrive" data-bm="Bila dan bagaimana barangan tiba">When and how the goods arrive</span>
                    </h6> <a style="color:red"> * </a>
                </div>
                <div class="row gy-3 mb-3">
                    <div class="col-xl-12">
                        <label for="eta" class="form-label" data-en="Expected Inspection Date" data-bm="Tarikh Jangkaan Pemeriksaan">Expected Inspection Date</label><a style="color:red"> * </a>
                        <input type="date" class="form-control " id="eta" name="eta" required>
                        <div class="invalid-feedback" id="etaError">Expected Inspection Date cannot be a past date.
                        </div>
                    </div>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12">
                        <label for="trnptType" class="form-label" data-en="Transport Type" data-bm="Jenis Pengangkutan">Transport Type</label><a style="color:red"> * </a>
                        <select class="form-select" id="trnptType" name="trnptType" data-route="/public/get_entry_point"
                            required>
                            <option value="">-- Select Transport --</option>
                            <option value="Air">Air</option>
                            <option value="Sea">Sea</option>
                            <option value="Land">Land</option>
                        </select>
                    </div>
                    <div class="col-xl-12">
                        <label for="entryPoint" class="form-label" data-en="Entry Point" data-bm="Pintu Masuk">Entry Point</label><a style="color:red"> * </a>
                        <select class="form-select" id="entryPoint" name="entryPoint" required>
                            <option value="">-- Select Entry Point --</option>

                        </select>
                        <input type="hidden" id="descEntryPoint">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>