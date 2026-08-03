<div class="wizard-step active" data-title="IMPORTER & EXPORTER" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="0">
    <div class="row justify-content-center">
        <div class="col-xl-6">
            <div class="register-page ipa-card h-100">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-user'></i></span>
                    <h6 data-en="Exporter" data-bm="Pengeksport">Exporter
                        <span class="ipa-card-sub" data-en="Assign an exporter for this application" data-bm="Tetapkan pengeksport untuk permohonan ini">Assign an exporter for this application</span>
                    </h6>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <label for="selectimp" class="form-label" data-en="Select Assigning Exporter" data-bm="Pilih Pengeksport yang Ditetapkan">Select Assigning Exporter</label>
                        <!-- <select id="selectimp" class="form-select xintra-select2" name="selectimp" style="width:100%;" >
                            <option value="">-- Select Importer --</option>
                            <option value="">--  Importer --</option>
                            <option value="">-- Select  --</option>
                            <option value="">-- SelectImporter --</option>
                        </select> -->
                        <input type="text" class="form-control mb-3" id="findImporter" name="findImporter"
                            placeholder="Company Number / Identification Number" data-bm="Nombor Syarikat / Nombor Pengenalan" data-en="Company Number / Identification Number" data-i18n-attr="placeholder">
                        <button type="button" class="btn btn-md btn-info mb-3" id="btnFindImp"><i
                                class="bx bx-search"></i> <span data-bm="Cari Pengimport" data-en="Find Importer">Find Importer</span></button>

                        <div class="alert alert-danger" id="searchresult" role="alert" style="display:none" data-bm="Tiada Nombor Pengenalan Sepadan!" data-en="No Matching Identity Number!">
                            No Matching Identity Number!
                        </div>

                        <div class="alert alert-primary2" id="emailnotver" role="alert" style="display:none" data-bm="E-mel tidak disahkan!" data-en="Email not verified!">
                            Email not verified!
                        </div>

                        <div class="alert alert-primary2" id="doanotver" role="alert" style="display:none" data-bm="Akaun tidak disahkan oleh DOA!" data-en="Account is not verified by DOA!">
                            Account is not verified by DOA!
                        </div>

                    </div>

                    <input type="hidden" id="app_cate" value="1">
                    <div class="col-xl-12">
                        <label for="impname" class="form-label" data-en="Name" data-bm="Nama">Name</label>
                        <input type="hidden" id="impid">
                        <input type="text" class="form-control " id="impname" name="impname" disabled>
                        <input type="hidden" id="impemail" name="impemail">
                    </div>
                    <div class="col-xl-12">
                        <label for="impfonno" class="form-label" data-en="Phone No" data-bm="No Telefon">Phone No</label>
                        <input type="text" class="form-control " id="impfonno" name="impfonno" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="impaddress" class="form-label" data-en="Address" data-bm="Alamat">Address</label>
                        <input type="text" class="form-control mb-2" id="impaddress1" name="impaddress1" disabled>
                        <input type="text" class="form-control " id="impaddress2" name="impaddress2" disabled>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="register-page ipa-card h-100">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-globe'></i></span>
                    <h6 data-en="Importer" data-bm="Pengimport">Importer <a style="color:red"> * </a>
                        <span class="ipa-card-sub" data-en="Who you're sending the goods to" data-bm="Siapa anda menghantar barangan kepada">Who you're sending the goods to</span>
                    </h6>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <label for="selectexp" class="form-label" data-en="Select Importer" data-bm="Pilih Pengimport">Select Importer</label>
                        <select id="selectexp" data-route="{{ route('public.getConsignmentImporters') }}"
                            class="form-select xintra-select2" name="selectexp" style="width:100%;" required>
                            <option value="" data-bm="-- Pilih Pengimport --" data-en="-- Select Importer --">-- Select Importer --</option>
                        </select>
                    </div>
                    <div class="col-xl-12" class="">
                        <button type="button" class="btn btn-primary" id="openExporterModalBtn">
                            <i class="bx bx-plus me-1"></i> <span data-en="Add Importer" data-bm="Tambah Pengimport">Add Importer</span>
                        </button>
                        <a style="color:red" data-en="*If importer is not in the selection list above" data-bm="*Jika pengimport tiada dalam senarai pilihan di atas"> *If importer is not in the selection list above</a>
                    </div>
                    <div class="col-xl-12">
                        <input type="hidden" id="expid">
                        <label for="expname" class="form-label" data-en="Name" data-bm="Nama">Name</label>
                        <input type="text" class="form-control " id="expname" name="expname" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="expfonno" class="form-label" data-en="Phone No" data-bm="No Telefon">Phone No</label>
                        <input type="text" class="form-control " id="expfonno" name="expfonno" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="expaddress" class="form-label" data-en="Address" data-bm="Alamat">Address</label>
                        {{-- <input type="text" class="form-control mb-2" id="expaddress1" name="expaddress1" disabled> --}}
                        <textarea name="expadress1" id="expaddress1" class="form-control" cols="30" rows="3" disabled></textarea>
                        <!-- <input type="text" class="form-control " id="expaddress2"  name="expaddress2"> -->
                    </div>
                    <div class="col-lg-12">
                        <label for="expcountry" class="form-label" data-en="Country" data-bm="Negara">Country</label>
                        <input type="hidden" class="form-control mb-2" id="expcountryCode" name="expcountryCode">
                        <input type="text" class="form-control" id="expcountry" name="expcountry" disabled>
                    </div>
                </div>
            </div>
        </div>


        <x-modal id="addExporterModal" title="Add Importer">
            <form id="addExporterForm">
                @csrf

                {{-- Name --}}
                <div class="mb-3">
                    <label for="addexpName" class="form-label" data-bm="Nama" data-en="Name">Name</label>
                    <input type="text" id="addexpName" name="addexpName" class="form-control">
                </div>

                {{-- Phone --}}
                <div class="mb-3">
                    <label for="addexpfonno" class="form-label" data-bm="No Telefon" data-en="Phone No">Phone No</label>
                    <input type="text" id="addexpfonno" name="addexpfonno" class="form-control">
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label for="addexpaddress" class="form-label" data-bm="Alamat" data-en="Address">Address</label>
                    <input type="text" id="addexpaddress1" name="addexpaddress1" class="form-control mb-2">
                    <input type="text" id="addexpaddress2" name="addexpaddress2" class="form-control">
                </div>

                {{-- Country --}}
                <div class="mb-3">
                    <label for="addexpcountry" class="form-label" data-bm="Negara" data-en="Country">Country</label>
                    <select class="form-select" id="addexpcountry" name="addexpcountry">
                        <option value="" data-bm="-- Pilih Negara --" data-en="-- Select Country --">-- Select Country --</option>
                        {{-- @foreach ($country as $coun)
                            <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                        @endforeach --}}
                        <option value="SWK">Sarawak, Malaysia</option>
                        <option value="BN">Brunei Darussalam</option>
                    </select>
                </div>

                @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
                <button type="button" class="btn btn-primary" id="saveExporterBtn" data-en="Save changes" data-bm="Simpan perubahan">Save changes</button>
                @endslot
            </form>
        </x-modal>
    </div>
</div>
