<div class="wizard-step active" data-title="IMPORTER & EXPORTER" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z"
    data-step="0">
    <div class="row justify-content-center">
        <div class="col-xl-6">
            <div class="register-page">
                <h6 class="mb-3">Importer :</h6>
                <div class="row gy-3">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <label for="selectimp" class="form-label">Select Assigning Importer</label>
                        <!-- <select id="selectimp" class="form-select xintra-select2" name="selectimp" style="width:100%;" >
                            <option value="">-- Select Importer --</option>
                            <option value="">--  Importer --</option>
                            <option value="">-- Select  --</option>
                            <option value="">-- SelectImporter --</option>
                        </select> -->
                        <input type="text" class="form-control mb-3" id="findImporter" name="findImporter" placeholder="Isert Company/Individu identiry number">
                        <button type="button" class="btn btn-md btn-info mb-3" id="btnFindImp"><i class="bx bx-search"></i> Find Importer</button>

                        <div class="alert alert-danger" id="searchresult" role="alert" style="display:none">
                            No Matching Identity Number!
                        </div>

                        <div class="alert alert-primary2" id="emailnotver" role="alert" style="display:none">
                            Email not verified!
                        </div>

                        <div class="alert alert-primary2" id="doanotver" role="alert" style="display:none">
                            Account is not verified by DOA!
                        </div>
                        
                    </div>
                    
                    <input type="hidden" id="app_cate" value="1">
                    <div class="col-xl-12">
                        <label for="impname" class="form-label">Name</label>
                        <input type="text" id="impid">
                        <input type="text" class="form-control " id="impname" name="impname" disabled >
                        <input type="hidden" id="impemail" name="impemail">
                    </div>
                    <div class="col-xl-12">
                        <label for="impfonno" class="form-label">Phone No</label>
                        <input type="text" class="form-control " id="impfonno" name="impfonno" disabled >
                    </div>
                    <div class="col-xl-12">
                        <label for="impaddress" class="form-label">Address</label>
                        <input type="text" class="form-control mb-2" id="impaddress1" name="impaddress1" disabled >
                        <input type="text" class="form-control " id="impaddress2" name="impaddress2" disabled >
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="register-page">
                <h6 class="mb-3">Exporter :</h6>
                <div class="row gy-3">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                        <label for="selectexp" class="form-label">Select Exporter</label>
                        <select id="selectexp" data-route="{{ route('public.getExporters') }}"
                            class="form-select xintra-select2" name="selectexp" style="width:100%;">
                            <option value="">-- Select Exporter --</option>
                        </select>
                    </div>
                    <div class="col-xl-12" class="">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addExporterModal">
                            <i class="bx bx-plus me-1"></i> Add Exporter
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
                        <input type="text" class="form-control mb-2" id="expaddress1" name="expaddress1" disabled>
                        <!-- <input type="text" class="form-control " id="expaddress2"  name="expaddress2"> -->
                    </div>
                    <div class="col-lg-12">
                        <label for="expcountry" class="form-label">Country</label>
                        <input type="hidden" class="form-control mb-2" id="expcountryCode" name="expcountryCode" >
                        <input type="text" class="form-control" id="expcountry" name="expcountry" disabled>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Exporter Modal -->
        <div class="modal fade" id="addExporterModal" tabindex="-1" aria-labelledby="addExporterModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <!-- Header -->
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addExporterModalLabel">
                            <i class="bx bx-user-plus me-2"></i> Add Exporter
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addexpName" class="form-label">Name</label>
                            <input type="text" id="addexpName" name="addexpName" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="addexpfonno" class="form-label">Phone No</label>
                            <input type="text" id="addexpfonno" name="addexpfonno" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="addexpaddress" class="form-label">Address</label>
                            <input type="text" id="addexpaddress1" name="addexpaddress1" class="form-control mb-2">
                            <input type="text" id="addexpaddress2" name="addexpaddress2" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="addexpcountry" class="form-label">Country</label>
                            <select class="form-select" id="addexpcountry" name="addexpcountry">
                                <option value="">-- Select Country --</option>
                                @foreach ($country as $coun)
                                    <option value="{{ $coun->code }}">{{ $coun->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i> Cancel
                        </button>
                        <button type="button" id="addExporterbtn" class="btn btn-primary"
                            data-route="{{ route('public.storeExp') }}">
                            <i class="bx bx-save me-1"></i> Save Exporter
                        </button>
                    </div>
                </div> <!-- end class:modal-content -->
            </div>
        </div> <!-- end modal -->
    </div>
</div>