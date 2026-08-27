<div class="wizard-step" data-title="CONSIGNMENT ITEM DETAILS" data-id="H53WJiv9blN17MYTztq4g8U6eSVkaZDx" data-step="2">
    <div class="row justify-content-center summary-view">
        <div class="table-responsive">
            <table id="itemListTbl" class="table text-nowrap fs-12">
                <thead class="table-primary">
                    <tr>
                      
                        <th scope="col" data-en="Category" data-bm="Kategori">Category</th>
                        <th scope="col" data-en="Item Name" data-bm="Nama Barangan">Item Name</th>
              
                        <th scope="col" data-en="Quantity" data-bm="Kuantiti">Quantity</th>
                        {{-- <th scope="col" data-en="Item Price" data-bm="Harga Item">Item Price</th> --}}
                        <th scope="col" data-en="Action" data-bm="Tindakan">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- <tr>
                        <td>1</td>
                        <td scope="row">Durian - Fresh Fruit</td>
                        <td>500 KG</td>
                        <td>Commercial (Trade)</td>
                        <td>Fresh Produce</td>
                        <td>RM 10,000</td>
                        <td></td>
                        <td style="text-align: center">
                            <button type="button" class="btn btn-sm btn-primary-light">Remove</button>
                        </td>
                    </tr> -->
                </tbody>
            </table>
            <div class="d-flex justify-content-end align-items-end">
                <button id="mdlAddItemBtn" type="button" class="btn btn-md btn-info mt-3" data-bs-toggle="modal"
                    data-bs-target="#addItemModal">
                    <i class="bx bx-plus me-1"></i> <span data-en="Add Item" data-bm="Tambah Barangan">Add Item</span>
                </button>
            </div>
        </div>

    </div>
</div>


{{-- modal --}}
{{-- <x-modal id="ItemDetailsModal" title="Item Details">

    <div id="itemDetailsInfo"></div>

    <hr>

    <p class="p-1 mt-3">
        <strong class = "me-1">
            <span class = "avatar avatar-sm avatar-rounded  bd-gray-500">
                <i class="fa-solid fa-file"></i>
            </span> Attachment(s)
        </strong>
    </p>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="itemFilesTable">
            <thead class="">
                <tr>
                    <th style="width: 45%">File Name</th>
                    <th style="width: 25%">File Type</th>
                    <th style="width: 15%" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- JS inserts rows here -->
            </tbody>
        </table>
    </div>

    @slot('footer')
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    @endslot

</x-modal> --}}



<div class="offcanvas offcanvas-end" tabindex="-1" id="ItemDetailsOffcanvas" aria-labelledby="ItemDetailsOffcanvasLabel" style=" " aria-modal="true" role="dialog">
    <div class="offcanvas-header border-bottom px-4">
        <div class="d-flex align-items-center gap-3">
            <div class="ipv-permit-detail-icon"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="ipv-permit-detail-eyebrow" data-en="Permit Details" data-bm="Butiran Permit">Permit Details</div>
                <h5 class="offcanvas-title mb-0 fw-bold" id="ItemDetailsOffcanvasLabel" data-en="Item Details" data-bm="Butiran Item">Item Details</h5>
            </div>
            <span class="ipv-badge ms-2 is-success" id="pdBadge" data-en="Uploaded" data-bm="Dimuat Naik">Uploaded</span>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 68px); overflow: hidden;">
        <div class="pd-nav flex-shrink-0">
            <ul class="nav nav-pills flex-column" id="ItemDetailsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pd-details-tab" data-bs-toggle="tab" data-bs-target="#pd-details" type="button" role="tab" data-bs-placement="right" title="Details" aria-selected="true" >
                        <i class="bi bi-file-text"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pd-activity-tab" data-bs-toggle="tab" data-bs-target="#pd-activity" type="button" role="tab" data-bs-placement="right" title="Activity Log" aria-selected="false" tabindex="-1">
                        <i class="bi bi-clock-history"></i>
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content flex-grow-1 overflow-auto" id="ItemDetailsTabContent">
            <div class="tab-pane fade p-4 active show" id="pd-details" role="tabpanel" aria-labelledby="pd-details-tab">
                <div id="itemDetailsInfo"></div>
                
                {{-- <div class="pd-info-grid" id="pdInfoGrid"></div> --}}
                <div class="pd-section-label mt-4" data-en="Conditions" data-bm="Syarat">Conditions (0)</div>
                <div class="ipv-condition-item" id="pdConditionItem"><span data-en="No special conditions for this item." data-bm="Tiada syarat khas untuk item ini.">No special conditions for this item.</span></div>
                <div class="pd-section-label mt-4" data-en="Attachments" data-bm="Lampiran">Attachments (<span id="attachmentCount">0</span>)</div>
                <div class="ipv-attach-list" id="pdAttachList"></div>
            </div>
            <div class="tab-pane fade p-4" id="pd-activity" role="tabpanel" aria-labelledby="pd-activity-tab">
                <div class="ipv-timeline" id="pdActivityTimeline">
                    <div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p data-en="No activity recorded yet." data-bm="Tiada aktiviti direkodkan lagi.">No activity recorded yet.</p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Attachment Viewer Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="itemAttachmentOffcanvas"
    aria-labelledby="itemAttachmentOffcanvasLabel" style="width: 70%; max-width: 900px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="itemAttachmentOffcanvasLabel">
            <i class="bi bi-paperclip me-2"></i> <span id="itemAttachmentTitle" data-en="Attachment" data-bm="Lampiran">Attachment</span>
        </h5>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" id="itemAttachPrevBtn" title="Previous" >
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="badge bg-light text-dark" id="itemAttachCounter">0 / 0</span>
            <button class="btn btn-sm btn-outline-secondary" id="itemAttachNextBtn" title="Next">
                <i class="bi bi-chevron-right"></i>
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 60px);">
        <div class="pd-nav flex-shrink-0">
            <ul class="nav nav-pills flex-column" id="itemAttachmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="item-attach-view-tab" data-bs-toggle="tab"
                        data-bs-target="#item-attach-view" type="button" role="tab" aria-selected="true"
                        data-bs-placement="right" title="View" >
                        <i class="bi bi-eye"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="item-attach-details-tab" data-bs-toggle="tab"
                        data-bs-target="#item-attach-details" type="button" role="tab" aria-selected="false"
                        data-bs-placement="right" title="Details" >
                        <i class="bi bi-info-circle"></i>
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content flex-grow-1 p-3 overflow-auto" id="itemAttachmentTabContent">
            <div class="tab-pane fade show active" id="item-attach-view" role="tabpanel">
                <div id="itemAttachViewer" class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br><span data-en="Select an attachment" data-bm="Pilih lampiran">Select an attachment</span></div>
                </div>
            </div>
            <div class="tab-pane fade" id="item-attach-details" role="tabpanel">
                <div id="itemAttachDetails" class="py-2"></div>
                {{-- Edit Name Section --}}
                <div class="mt-4 pt-3 border-top">
                    <label class="form-label fw-semibold">
                        <span data-en="Edit File Name" data-bm="Edit Nama Fail">Edit File Name</span>
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="itemAttachEditName" placeholder="Enter new file name">
                        <button class="btn btn-primary" type="button" id="itemAttachSaveNameBtn">
                            <i class="ti ti-device-floppy me-1"></i>
                            <span data-en="Save" data-bm="Simpan">Save</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Item File Preview & Rename Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="itemFilePreviewOffcanvas" aria-labelledby="itemFilePreviewOffcanvasLabel" style="width: 70%; max-width: 900px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="itemFilePreviewOffcanvasLabel">
            <i class="bi bi-file-earmark me-2"></i> <span id="itemFileName" data-en="File Preview" data-bm="Pratonton Fail">File Preview</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex" style="height: calc(100% - 60px);">
        <div class="pd-nav flex-shrink-0">
            <ul class="nav nav-pills flex-column" id="itemFileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ifile-view-tab" data-bs-toggle="tab"
                        data-bs-target="#ifile-view" type="button" role="tab" aria-selected="true"
                        data-bs-placement="right" title="View" >
                        <i class="bi bi-eye"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ifile-details-tab" data-bs-toggle="tab"
                        data-bs-target="#ifile-details" type="button" role="tab" aria-selected="false"
                        data-bs-placement="right" title="Details" >
                        <i class="bi bi-info-circle"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ifile-edit-tab" data-bs-toggle="tab"
                        data-bs-target="#ifile-edit" type="button" role="tab" aria-selected="false"
                        data-bs-placement="right" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content flex-grow-1 p-3 overflow-auto" id="itemFileTabContent">
            <div class="tab-pane fade show active" id="ifile-view" role="tabpanel">
                <div id="itemFilePreviewContainer" class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br><span data-en="Select a file" data-bm="Pilih fail">Select a file</span></div>
                </div>
            </div>
            <div class="tab-pane fade" id="ifile-details" role="tabpanel">
                <div id="itemFileDetails" class="py-2"></div>
            </div>
            <div class="tab-pane fade" id="ifile-edit" role="tabpanel">
                <div class="p-3">
                    <div class="mb-3">
                        <label for="itemFileEditName" class="form-label" data-en="File Name" data-bm="Nama Fail">File Name</label>
                        <input type="text" class="form-control" id="itemFileEditName" data-en="Enter new file name" data-bm="Masukkan nama fail baharu" placeholder="Enter new file name">
                    </div>
                    <button type="button" class="btn btn-primary" id="itemFileSaveBtn" data-en="Save Changes" data-bm="Simpan Perubahan">
                        <i class="bi bi-check me-2"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>