<div class="wizard-step active" data-title="IMPORTER & EXPORTER" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="0">
    <div class="row justify-content-center gy-3">
        <div class="col-xl-6">
            <div class="register-page ipa-card">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-user-check'></i></span>
                    <h6><span data-en="Importer" data-bm="Pengimport">Importer</span> <span class="text-danger">*</span>
                        <span class="ipa-card-sub" data-en="Find the importer you're applying for"
                            data-bm="Cari pengimport yang anda pohon">Find the importer you're applying for</span>
                    </h6>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <label for="findImporter" class="form-label"><span data-en="Select Assigning Importer"
                                data-bm="Pilih Pengimport yang Ditetapkan">Select Assigning Importer</span></label>

                        <input type="text" class="form-control mb-3 required" id="findImporter" name="findImporter"
                            placeholder="Company Number / Identification Number" data-i18n-attr="placeholder"
                            data-en="Company Number / Identification Number"
                            data-bm="Nombor Syarikat / Nombor Pengenalan">

                        <button type="button" class="btn btn-md btn-info mb-3 ipa-btn-outline" id="btnFindImp">
                            <i class="bx bx-search"></i> <span data-en="Find Importer" data-bm="Cari Pengimport">Find
                                Importer</span>
                        </button>

                        <div class="alert alert-danger" id="searchresult" role="alert" style="display:none"
                            data-en="No Matching Identity Number!" data-bm="Tiada Nombor Pengenalan yang Sepadan!">
                            No Matching Identity Number!
                        </div>

                        <div class="alert alert-primary2" id="emailnotver" role="alert" style="display:none"
                            data-en="Email not verified!" data-bm="E-mel tidak disahkan!">
                            Email not verified!
                        </div>

                        <div class="alert alert-primary2" id="doanotver" role="alert" style="display:none"
                            data-en="Account is not verified by DOA!" data-bm="Akaun tidak disahkan oleh DOA!">
                            Account is not verified by DOA!
                        </div>
                    </div>

                    <input type="hidden" id="app_cate" value="1">
                    <div class="col-xl-12">
                        <label for="impname" class="form-label"><span data-en="Name" data-bm="Nama">Name</span></label>
                        <input type="hidden" id="impid" class="required">
                        <input type="text" class="form-control" id="impname" name="impname" disabled>
                        <input type="hidden" id="impemail" name="impemail">
                    </div>
                    <div class="col-xl-12">
                        <label for="impfonno" class="form-label"><span data-en="Phone No" data-bm="No. Telefon">Phone
                                No</span></label>
                        <input type="text" class="form-control" id="impfonno" name="impfonno" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="impaddress" class="form-label"><span data-en="Address"
                                data-bm="Alamat">Address</span></label>
                        <textarea type="text" class="form-control mb-2" id="impaddress1" name="impaddress1" disabled></textarea>
                        <input type="text" class="form-control d-none" id="impaddress2" name="impaddress2" disabled >
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="register-page ipa-card">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-globe'></i></span>
                    <h6><span data-en="Exporter" data-bm="Pengeksport">Exporter</span>
                        <span class="ipa-card-sub" data-en="Who the goods are coming from"
                            data-bm="Dari mana barang datang">Who the goods are coming from</span>
                    </h6>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <label for="selectexp" class="form-label"><span data-en="Select Exporter"
                                data-bm="Pilih Pengeksport">Select Exporter</span></label><a style="color:red">*</a>
                        <select id="selectexp" data-route="/public/get_exporters"
                            class="form-select xintra-select2 required" name="selectexp" style="width:100%;"
                            required>
                            <option value="" data-en="-- Select Exporter --" data-bm="-- Pilih Pengeksport --">
                                -- Select Exporter --</option>
                        </select>
                    </div>
                    <div class="col-xl-12">
                        <button type="button" class="btn btn-primary ipa-btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addExporterModal">
                            <i class="bx bx-plus me-1"></i> <span data-en="Add Exporter"
                                data-bm="Tambah Pengeksport">Add Exporter</span> 
                        </button>
                        <div class="ipa-hint-note">
                            <i class='bx bx-info-circle'></i>
                            <span data-en="If your exporter isn't in the list above, "
                                data-bm="Jika pengeksport anda tiada dalam senarai di atas, ">If your exporter isn't in
                                the list above, </span><a style="color:red" data-en="add them here"
                                data-bm="tambah mereka di sini">add them here</a><span>.</span>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <input type="hidden" id="expid">
                        <label for="expname" class="form-label"><span data-en="Name"
                                data-bm="Nama">Name</span></label>
                        <input type="text" class="form-control" id="expname" name="expname" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="expfonno" class="form-label"><span data-en="Phone No"
                                data-bm="No. Telefon">Phone No</span></label>
                        <input type="text" class="form-control" id="expfonno" name="expfonno" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="expaddress" class="form-label"><span data-en="Address"
                                data-bm="Alamat">Address</span></label>
                        <textarea type="text" class="form-control mb-2" id="expaddress1" name="expaddress1"
                            disabled></textarea>
                    </div>
                    <div class="col-lg-12">
                        <label for="expcountry" class="form-label"><span data-en="Country"
                                data-bm="Negara">Country</span></label>
                        <input type="hidden" class="form-control mb-2" id="expcountryCode" name="expcountryCode">
                        <input type="text" class="form-control" id="expcountry" name="expcountry" disabled>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Exporter Modal -->
        <x-modal id="addExporterModal" title="Add Exporter">
            <form id="addExporterForm">
                @csrf

                {{-- Name --}}
                <div class="mb-3">
                    <label for="addexpName" class="form-label" data-en="Name" data-bm="Nama">Name</label><a
                        style="color:red"> * </a>
                    <input type="text" id="addexpName" name="addexpName" class="form-control">
                </div>

                {{-- Phone --}}
                <div class="mb-3">
                    <label for="addexpfonno" class="form-label" data-en="Phone No" data-bm="No Telefon">Phone
                        No</label><a style="color:red"> * </a>
                    <input type="number" min="0" step="1" id="addexpfonno" name="addexpfonno"
                        class="form-control">
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label for="addexpaddress" class="form-label" data-en="Address"
                        data-bm="Alamat">Address</label><a style="color:red"> * </a>
                    <textarea type="text" id="addexpaddress1" name="addexpaddress1" class="form-control mb-2"> </textarea>

                </div>

                {{-- Country --}}
                <div class="mb-3">
                    <label for="addexpcountry" class="form-label" data-en="Country"
                        data-bm="Negara">Country</label><a style="color:red"> * </a>
                    <select class="form-select" id="addexpcountry" name="addexpcountry">
                        <option value="" data-en="-- Select Country --" data-bm="-- Pilih Negara --">-- Select
                            Country --</option>
                        @foreach ($country as $coun)
                            <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                        @endforeach
                    </select>
                </div>

                @slot('footer')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Cancel"
                        data-bm="Batal">Cancel</button>
                    <button type="button" id="addExporterbtn" class="btn btn-primary ipa-btn-primary"
                        data-en="Save Exporter" data-bm="Simpan Pengeksport">
                        Save Exporter
                    </button>
                @endslot
            </form>
        </x-modal>

    </div>
</div>
