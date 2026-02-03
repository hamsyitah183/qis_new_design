<div class="wizard-step active" data-title="IMPORTER & EXPORTER" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z"
    data-step="0">
    <div class="row justify-content-center">
        <div class="col-xl-6">
            <div class="register-page">
                <h6 class="mb-3">Exporter :</h6>
                <div class="row gy-3">
                    <input type="hidden" id="app_cate" value="0">
                    <input type="hidden" id="impemail">
                    <div class="col-xl-12">
                        <label for="impname" class="form-label">Name</label>
                        <input type="hidden" id="impid" value="{{ Auth::user()->id ?? '' }}">
                        <input type="text" class="form-control " id="impname" name="impname"
                            value="{{ Auth::user()->fullname ?? '' }}" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="impfonno" class="form-label">Phone No</label>
                        <input type="text" class="form-control " id="impfonno" name="impfonno"
                            value="{{ Auth::user()->phone_number ?? '' }}" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="impaddress" class="form-label">Address</label>
                        <input type="text" class="form-control mb-2" id="impaddress1" name="impaddress1"
                            value="{{ Auth::user()->address_1 ?? '' }}" disabled>
                        <input type="text" class="form-control " id="impaddress2" name="impaddress2"
                            value="{{ Auth::user()->address_2 ?? '' }}" disabled>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="register-page">
                <h6 class="mb-3">Importer :<a style="color:red"> * </a></h6>
                <div class="row gy-3">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <label for="selectexp" class="form-label">Select Importer</label>
                        <select id="selectexp" data-route="{{ route('public.getConsignmentImporters') }}"
                            class="form-select xintra-select2" name="selectexp" style="width:100%;" required>
                            <option value="">-- Select Importer --</option>
                        </select>
                    </div>
                    <div class="col-xl-12" class="">
                        <button type="button" class="btn btn-primary" id="openExporterModalBtn">
                            <i class="bx bx-plus me-1"></i> Add Importer
                        </button>
                        <a style="color:red"> *If exporter is not in the selection list above</a>
                    </div>
                    <div class="col-xl-12">
                        <input type="hidden" id="expid">
                        <label for="expname" class="form-label">Name</label>
                        <input type="text" class="form-control " id="expname" name="expname" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="expfonno" class="form-label">Phone No</label>
                        <input type="text" class="form-control " id="expfonno" name="expfonno" disabled>
                    </div>
                    <div class="col-xl-12">
                        <label for="expaddress" class="form-label">Address</label>
                        {{-- <input type="text" class="form-control mb-2" id="expaddress1" name="expaddress1" disabled>
                        --}}
                        <textarea name="expadress1" id="expaddress1" class="form-control" cols="30" rows="3"
                            disabled></textarea>
                        <!-- <input type="text" class="form-control " id="expaddress2"  name="expaddress2"> -->
                    </div>
                    <div class="col-lg-12">
                        <label for="expcountry" class="form-label">Country</label>
                        <input type="hidden" class="form-control mb-2" id="expcountryCode" name="expcountryCode">
                        <input type="text" class="form-control" id="expcountry" name="expcountry" disabled>
                    </div>
                </div>
            </div>
        </div>

        {{-- exporter modal --}}
        <x-modal id="addExporterModal" title="Add Importer">
            <form id="addExporterForm">
                @csrf

                {{-- Name --}}
                <div class="mb-3">
                    <label for="addexpName" class="form-label">Name<a style="color:red"> * </a></label>
                    <input type="text" id="addexpName" name="addexpName" class="form-control">
                </div>

                {{-- Phone --}}
                <div class="mb-3">
                    <label for="addexpfonno" class="form-label">Phone No<a style="color:red"> * </a></label>
                    <input type="text" id="addexpfonno" name="addexpfonno" class="form-control">
                </div>

                {{-- Address --}}
                <div class="mb-3">
                    <label for="addexpaddress" class="form-label">Address<a style="color:red"> * </a></label>
                    <input type="text" id="addexpaddress1" name="addexpaddress1" class="form-control mb-2">
                    <input type="text" id="addexpaddress2" name="addexpaddress2" class="form-control">
                </div>

                {{-- Country --}}
                <div class="mb-3">
                    <label for="addexpcountry" class="form-label">Country<a style="color:red"> * </a></label>
                    <select class="form-select" id="addexpcountry" name="addexpcountry">
                        <option value="">-- Select Country --</option>
                        {{-- @foreach ($country as $coun)
                        <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                        @endforeach --}}
                        <option value="SWK">Sarawak, Malaysia</option>
                        <option value="BN">Brunei Darussalam</option>
                    </select>
                </div>

                @slot('footer')
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <button type="button" id="addExporterbtn" class="btn btn-primary"
                    data-route="{{ route('public.storeImporter') }}">
                    Save Importer
                </button>
                @endslot
            </form>
        </x-modal>

    </div>
</div>