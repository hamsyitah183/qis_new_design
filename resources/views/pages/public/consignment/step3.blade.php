<div class="wizard-step" data-title="SUMMARY" data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu" data-limit="3" data-step="3">
    <div class="ipa-alert-note">
        <i class='bx bx-info-circle'></i>
        <span
            data-en="Please review everything below carefully. Once your application is submitted, changes can only be made by contacting the department."
            data-bm="Sila semak segala maklumat di bawah dengan teliti. Sebaik sahaja permohonan anda dihantar, sebarang perubahan hanya boleh dibuat dengan menghubungi pihak jabatan.">
            Please review everything below carefully. Once your application is submitted, changes can only be made by
            contacting the department.
        </span>
    </div>

    <div class="row">
        <!-- Importer & Exporter Details -->
        <div class="col-xl-6">
            <div class="ipa-card h-100 mb-3">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-user'></i></span>
                    <h6 data-en="Importer & Exporter Details" data-bm="Butiran Pengimport & Pengeksport">
                        Importer & Exporter Details
                        <span class="ipa-card-sub" data-en="Who this application is between"
                            data-bm="Siapa antara permohonan ini">Who this application is between</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table text-nowrap mt-3">
                            <tbody>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Importer Name"
                                            data-bm="Nama Pengimport"><strong>Importer</strong> Name</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="importerName"></td>
                                </tr>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Phone"
                                            data-bm="Telefon">&nbsp;&nbsp;&nbsp;&nbsp; Phone</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="importerPhoneno"></td>
                                </tr>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Address"
                                            data-bm="Alamat">&nbsp;&nbsp;&nbsp;&nbsp; Address</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="simpAdd"></td>
                                </tr>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Exporter Name"
                                            data-bm="Nama Pengeksport"><strong>Exporter</strong> Name</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="sexpName"></td>
                                </tr>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Phone"
                                            data-bm="Telefon">&nbsp;&nbsp;&nbsp;&nbsp; Phone</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="sexpfonno"></td>
                                </tr>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Address"
                                            data-bm="Alamat">&nbsp;&nbsp;&nbsp;&nbsp; Address</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="sexpAddress"></td>
                                </tr>
                                <tr class="d-none">
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Country"
                                            data-bm="Negara">&nbsp;&nbsp;&nbsp;&nbsp; Country</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="sexpCountry"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consignment Details (right column) -->
        <div class="col-xl-6">
            <div class="ipa-card h-100 mb-3">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-search-alt'></i></span>
                    <h6 data-en="Consignment Details" data-bm="Butiran Konsainan">
                        Consignment Details
                        <span class="ipa-card-sub" data-en="When and how the goods arrive"
                            data-bm="Bila dan bagaimana barangan tiba">When and how the goods arrive</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="summaryTable" class="table text-nowrap mt-3">
                            <tbody>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Estimated Time Arrival"
                                            data-bm="Anggaran Waktu Ketibaan">Estimated Time Arrival</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="seta"></td>
                                </tr>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Transport Type"
                                            data-bm="Jenis Pengangkutan">Transport Type</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="strty"></td>
                                </tr>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Entry Point"
                                            data-bm="Pintu Masuk">Entry Point</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="sentryp"></td>
                                </tr>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="PTN Number"
                                            data-bm="Nombor PTN">PTN number</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="sptnnumber"></td>
                                </tr>
                                <tr>
                                    <td class="w-25">
                                        <span class="d-block fw-semibold" data-en="Vehicles"
                                            data-bm="Kenderaan">Vehicles</span>
                                    </td>
                                    <td class="w-10">:</td>
                                    <td class="text-start text-muted" id="svehicle">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Attachments -->
        <div class="col-xl-12">
            <div class="ipa-items-card">
                <div class="ipa-card-header" style="margin-bottom:14px">
                    <span class="ipa-icon-badge"><i class='bx bx-paperclip'></i></span>
                    <h6 data-en="Application Attachments" data-bm="Lampiran Permohonan">
                        Application Attachments
                        <span class="ipa-card-sub" data-en="Supporting documents for this application"
                            data-bm="Dokumen sokongan untuk permohonan ini">Supporting documents for this
                            application</span>
                    </h6>
                </div>
                <div class="table-responsive card-body">
                    <table id="summaryAttachmentTable" class="table text-nowrap">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col" data-en="File Name" data-bm="Nama Fail">File Name</th>
                                <th scope="col" data-en="Size" data-bm="Saiz">Size</th>
                                <th scope="col" data-en="Type" data-bm="Jenis">Type</th>
                                <th scope="col" data-en="Action" data-bm="Tindakan">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Consignment Items (fixed with icon + subtitle header) -->
        <div class="col-xl-12 my-2">
            <div class="ipa-items-card">
                <div class="ipa-card-header" style="margin-bottom:14px">
                    <span class="ipa-icon-badge"><i class='bx bx-box'></i></span>
                    <h6 data-en="Consignment Items" data-bm="Barangan Konsainan">
                        Consignment Items
                        <span class="ipa-card-sub" data-en="List of items in this consignment"
                            data-bm="Senarai barangan dalam konsainan ini">List of items in this consignment</span>
                    </h6>
                </div>
                <div class="table-responsive card-body">
                    <table id="summaryTable3" class="table text-nowrap">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col" data-en="Item Name" data-bm="Nama Barangan">Item Name</th>
                                <th scope="col" data-en="Quantity" data-bm="Kuantiti">Quantity</th>
                                <th scope="col" data-en="Action" data-bm="Tindakan">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Consignment Application Prices -->
        <div class="col-xl-12 mt-2">
            <div class="ipa-items-card">
                <div class="ipa-card-header" style="margin-bottom:14px">
                    <span class="ipa-icon-badge"><i class='bx bx-dollar-circle'></i></span>
                    <h6 data-en="Consignment Application Prices" data-bm="Harga Permohonan Konsainan">
                        Consignment Application Prices
                        <span class="ipa-card-sub" data-en="Category‑wise pricing breakdown"
                            data-bm="Pecahan harga mengikut kategori">Category‑wise pricing breakdown</span>
                    </h6>
                </div>
                <div class="table-responsive card-body">
                    <table id="summaryTable4" class="table text-nowrap">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col" data-en="Category" data-bm="Kategori">Category</th>
                                <th scope="col" data-en="Quantity" data-bm="Kuantiti">Quantity</th>
                                <th scope="col" data-en="Price" data-bm="Harga">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- populated by JavaScript (tablePrice) -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit buttons -->
    <div class="row justify-content-center">
        <div class="d-flex gap-3 align-items-end justify-content-end">
            <button id="submitApps" type="button" class="btn btn-md btn-info">
                <i class="bx bx-send me-1"></i>
                <span data-en="Submit Application" data-bm="Hantar Permohonan">
                     Submit Application
                </span>
            </button>
        </div>
    </div>
</div>