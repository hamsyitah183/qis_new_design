<div class="wizard-step" data-title="CERTIFICATE DETAILS" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="1">
    <div class="row justify-content-center gy-3">
        <div class="col-xl-6">
            <div class="register-page ipa-card h-100">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-paperclip'></i></span>
                    <h6 data-en="Application Attachment" data-bm="Lampiran Permohonan">Application Attachment
                        <span class="ipa-card-sub" data-en="Upload supporting documents for this application"
                            data-bm="Muat naik dokumen sokongan untuk permohonan ini">Upload supporting documents for
                            this application</span>
                    </h6>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12">
                        <div class="col-xl-12">
                            @if ($consignmentDocuments->isEmpty())
                                <p class="text-muted">No document requirements configured for this application type.</p>
                            @else
                                <div class="d-flex flex-column gap-3" id="consignment-document-list">
                                    @foreach ($consignmentDocuments as $doc)
                                        <div class="border rounded p-3 consignment-doc-block"
                                            data-doc-id="{{ $doc->id }}" data-doc-name="{{ $doc->name }}">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <div class="fw-semibold fs-14">
                                                    {{ $doc->name }}
                                                    @if ($doc->is_required)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </div>
                                                @if ($doc->description)
                                                    <button type="button"
                                                        class="badge rounded-pill bg-light-primary text-primary border-0 doc-details-btn d-flex align-items-center gap-1"
                                                        data-doc-id="{{ $doc->id }}"
                                                        data-doc-name="{{ $doc->name }}"
                                                        data-description-target="doc-desc-{{ $doc->id }}">
                                                        <i class="ti ti-info-circle fs-14"></i>
                                                        <span data-en="Details" data-bm="Butiran">Details</span>
                                                    </button>

                                                    <div id="doc-desc-{{ $doc->id }}" class="d-none">
                                                        {!! $doc->description !!}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="d-flex justify-content-end my-2">
                                                <span class="badge rounded-pill bg-light text-muted doc-file-count"
                                                    data-doc-id="{{ $doc->id }}">
                                                    No files
                                                </span>
                                            </div>

                                            <form id="applicationAttachmentDropzone-{{ $doc->id }}"
                                                class="dz-clickable application-attachment-dropzone" method="post"
                                                enctype="multipart/form-data" data-doc-id="{{ $doc->id }}"
                                                data-doc-name="{{ $doc->name }}">
                                                @csrf
                                                <div class="dz-default dz-message">
                                                    <button class="dz-button p-4 border w-100 border-radius"
                                                        type="button">
                                                        <i class='bx bx-cloud-upload' style="font-size:20px"></i><br>
                                                        <span data-en="Drop files here to upload"
                                                            data-bm="Jatuhkan fail di sini untuk dimuat naik">
                                                            Drop files here to upload
                                                        </span>
                                                    </button>
                                                </div>
                                            </form>

                                            <div class="table-responsive mt-2">
                                                <table
                                                    class="table table-sm text-nowrap fs-12 application-attachment-table"
                                                    data-doc-id="{{ $doc->id }}">
                                                    <thead class="table-primary">
                                                        <tr>
                                                            <th data-en="File" data-bm="Fail">File</th>
                                                            <th data-en="Action" data-bm="Tindakan">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="empty-row">
                                                            <td colspan="2" class="text-center text-muted py-2"
                                                                data-en="No attachments uploaded yet."
                                                                data-bm="Tiada lampiran dimuat naik lagi.">
                                                                No attachments uploaded yet.
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- <div class="col-xl-12">
                        <div class="table-responsive mt-3">
                            <table class="table table-sm text-nowrap fs-12" id="applicationAttachmentTable">
                                <thead class="table-primary">
                                    <tr>
                                        <th data-en="File" data-bm="Fail">File</th>
                                        <th class="" data-en="Action" data-bm="Tindakan">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3"
                                            data-en="No attachments uploaded yet."
                                            data-bm="Tiada lampiran dimuat naik lagi.">No attachments uploaded yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="register-page ipa-card h-100">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-search-alt'></i></span>
                    <h6 data-en="Consignment Details" data-bm="Butiran Konsainan">Consignment Details 
                        <span class="ipa-card-sub" data-en="When and how the goods arrive"
                            data-bm="Bila dan bagaimana barangan tiba">When and how the goods arrive</span>
                    </h6><a
                            style="color:red"> * </a>
                </div>
                <div class="row gy-3 mb-3">
                    <div class="col-xl-12">
                        <label for="eta" class="form-label" data-en="Estimated Time Arrival"
                            data-bm="Anggaran Waktu Ketibaan">Estimated Time Arrival </label><a style="color:red"> *
                            </a>
                        <input type="date" class="form-control " id="eta" name="eta" required>
                        <div class="invalid-feedback" id="etaError">Estimated Time Arrival cannot be a past date.</div>
                    </div>
                    <div class="col-xl-12">
                        <label for="eta" class="form-label" data-en="Expired Date" data-bm="Tarikh Mansuh">Expired
                            Date<a style="color:red"> *
                            </a></label><a style="color:red"> *
                            </a>
                        <input type="date" class="form-control" id="expiredDate" name="expired_date" disabled>

                    </div>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12">
                        <label for="trnptType" class="form-label" data-en="Transport Type"
                            data-bm="Jenis Pengangkutan">Transport Type <a style="color:red"> * </a></label><a style="color:red"> *
                            </a>
                        <select class="form-select" id="trnptType" name="trnptType"
                            data-route="/public/get_entry_point" required>
                            <option value="Land" selected>Land</option>
                        </select>
                    </div>
                    <div class="col-xl-12">
                        <label for="entryPoint" class="form-label" data-en="Entry Point" data-bm="Pintu Masuk">Entry
                            Point<a style="color:red"> * </a></label><a style="color:red"> *
                            </a>
                        <select class="form-select" id="entryPoint" name="entryPoint" required>
                            <option value="">-- Select Entry Point --</option>

                        </select>
                        <input type="hidden" id="descEntryPoint">
                    </div>

                    <div class="col-xl-12">
                        <label for="entryPoint" class="form-label" data-en="PTN Number" data-bm="Nombor PTN">PTN
                            Number<a style="color:red"> * </a></label><a style="color:red"> *
                            </a>

                        <input type="text" name="ptnNumber" id="ptnNumber" class="form-control">
                    </div>

                    {{-- Vehicle Selection --}}
                    <div class="col-xl-12 mt-3">
                        <div class="border p-3 rounded bg-light">
                            <div class="row align-items-end">
                                <div class="col-xl-12">
                                    <label for="selectVehicle" class="form-label" data-en="Select Vehicle(s)"
                                        data-bm="Pilih Kenderaan">
                                        Select Vehicle(s) <a style="color:red"> * </a>
                                    </label><a style="color:red"> *
                            </a>
                                    <select id="selectVehicle" class="form-select xintra-select2"
                                        name="selectVehicle[]" style="width:100%;" multiple="multiple" required>
                                        <option value="" data-en="-- Select Vehicle(s) --"
                                            data-bm="-- Pilih Kenderaan --">-- Select Vehicle(s) --</option>
                                    </select>
                                    <small class="text-muted" data-en="You can select multiple vehicles."
                                        data-bm="Anda boleh pilih lebih daripada satu kenderaan.">
                                        You can select multiple vehicles.
                                    </small>
                                </div>
                                <div class="col-xl-12">
                                    <div class="d-flex ms-auto">
                                        <div class="">
                                            <button type="button" class="btn btn-primary ipa-btn-primary w-100"
                                                id="openVehicleModalBtn">
                                                <i class="bx bx-plus me-1"></i> <span data-en="Add Vehicle"
                                                    data-bm="Tambah Kenderaan">Add Vehicle</span>
                                            </button>
                                            <div class="ipa-hint-note mt-1">
                                                <i class='bx bx-info-circle'></i>
                                                <span data-en="If your vehicle isn't in the list, add it here."
                                                    data-bm="Jika kenderaan anda tiada dalam senarai, tambah di sini.">
                                                    If your vehicle isn't in the list, add it here.
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <input type="hidden" id="vehicleIds" name="vehicleIds" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



{{-- Vehicle Add Modal --}}
{{-- Vehicle Add Modal --}}
<x-modal id="addVehicleModal" title="Add Vehicle">
    <form id="addVehicleForm">
        @csrf


        <div class="mb-3">
            <label for="addVehicleNumber" class="form-label" data-en="Vehicle Number (License Plate)"
                data-bm="Nombor Kenderaan (Plat)">
                Vehicle Number
            </label> <a style="color:red"> * </a>
            <input type="text" id="addVehicleNumber" name="addVehicleNumber" class="form-control" required>
        </div>




        <div class="row mb-3">
            <div class="col-md-6">
                <label for="addValidFrom" class="form-label" data-en="Valid From" data-bm="Berkuatkuasa Dari">Valid
                    From</label>
                <input type="date" id="addValidFrom" name="addValidFrom" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="addValidUntil" class="form-label" data-en="Valid Until"
                    data-bm="Berkuatkuasa Hingga">Valid Until</label>
                <input type="date" id="addValidUntil" name="addValidUntil" class="form-control">
            </div>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel"
                data-bm="Batal">Cancel</button>
            <button type="button" id="addVehicleBtn" class="btn btn-primary ipa-btn-primary" data-en="Save Vehicle"
                data-bm="Simpan Kenderaan">Save Vehicle</button>
        @endslot
    </form>
</x-modal>


<div class="offcanvas offcanvas-end" tabindex="-1" id="attachmentOffcanvas"
    aria-labelledby="attachmentOffcanvasLabel" style="width: 70%; max-width: 900px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="attachmentOffcanvasLabel">
            <i class="bi bi-paperclip me-2"></i> <span id="attachmentTitle" data-en="Attachment"
                data-bm="Lampiran">Attachment</span>
        </h5>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" id="attachmentPrevBtn" title="Previous">
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="badge bg-light text-dark" id="attachmentCounter">0 / 0</span>
            <button class="btn btn-sm btn-outline-secondary" id="attachmentNextBtn" title="Next">
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
                        data-bs-placement="right" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attach-details-tab" data-bs-toggle="tab"
                        data-bs-target="#attach-details" type="button" role="tab" aria-selected="false"
                        data-bs-placement="right" title="Details">
                        <i class="bi bi-info-circle"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attach-edit-tab" data-bs-toggle="tab" data-bs-target="#attach-edit"
                        type="button" role="tab" aria-selected="false" data-bs-placement="right"
                        title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content flex-grow-1 p-3 overflow-auto" id="attachmentTabContent">
            <div class="tab-pane fade show active" id="attach-view" role="tabpanel">
                <div id="attachmentViewer" class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br><span
                            data-en="Select an attachment" data-bm="Pilih lampiran">Select an attachment</span></div>
                </div>
            </div>
            <div class="tab-pane fade" id="attach-details" role="tabpanel">
                <div id="attachmentDetails" class="py-2"></div>
            </div>
            <div class="tab-pane fade" id="attach-edit" role="tabpanel">
                <div class="p-3">
                    <div class="mb-3">
                        <label for="attachmentEditName" class="form-label" data-en="File Name"
                            data-bm="Nama Fail">File Name</label>
                        <input type="text" class="form-control" id="attachmentEditName"
                            placeholder="Enter new file name" data-en="Enter new file name"
                            data-bm="Masukkan nama fail baharu">
                    </div>
                    <button type="button" class="btn btn-primary" id="attachmentSaveNameBtn">
                        <i class="bi bi-check me-2"></i> <span data-en="Save Changes" data-bm="Simpan Perubahan">Save
                            Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Requirement Description Modal -->
<div class="modal fade" id="docDescriptionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="docDescriptionModalLabel">Document Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body doc-description-modal-body"></div>
        </div>
    </div>
</div>
