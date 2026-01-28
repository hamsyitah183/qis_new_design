  <div class="wizard-step active" data-title="IMPORTER & EXPORTER" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z"
      data-step="0">
      <div class="row justify-content-center">
          <div class="col-xl-6 pt-sm-4 pt-lg-0">
              <div class="register-page">
                  <h6 class="mb-3">Exporter :</h6>
                  <div class="row gy-3">


                      <div class="col-xl-12">
                          <input type="hidden" id="expid" value="{{ $application->exporter_id }}">
                          <label for="expname" class="form-label">Name</label>
                          <input type="text" class="form-control " id="expname" name="expname"
                              value="{{ $application->exporter->fullname ?? '' }}" disabled>
                      </div>
                      <div class="col-xl-12">
                          <label for="expfonno" class="form-label">Phone No</label>
                          <input type="text" class="form-control " id="expfonno" name="expfonno"
                              value="{{ $application->exporter->phone_number ?? '' }}" disabled>
                      </div>
                      <div class="col-xl-12">
                          <label for="expaddress" class="form-label">Address</label>
                          <input type="text" class="form-control mb-2" id="expaddress1" name="expaddress1"
                              value="{{ $application->exporter->address_1 ?? '' }}" disabled>
                          <!-- <input type="text" class="form-control " id="expaddress2"  name="expaddress2"> -->
                      </div>

                  </div>
              </div>
          </div>
          <div class="col-xl-6">
              <div class="register-page">
                  <h6 class="mb-3">Importer :</h6>
                  <div class="row gy-3">
                      <input type="hidden" id="app_cate" value="0">
                      <div class="col-xl-12">
                          <label for="impname" class="form-label">Name</label>
                          <input type="hidden" id="impid" value="{{ $application->importer_id }}">
                          <input type="text" class="form-control " id="impname" name="impname"
                              value="{{ $application->importer->name ?? '' }}" disabled>
                      </div>
                      <div class="col-xl-12">
                          <label for="impfonno" class="form-label">Phone No</label>
                          <input type="text" class="form-control " id="impfonno" name="impfonno"
                              value="{{ $application->importer->phone_no ?? '' }}" disabled>
                      </div>
                      <div class="col-xl-12">
                          <label for="impaddress" class="form-label">Address</label>
                          <input type="text" class="form-control mb-2" id="impaddress1" name="impaddress1"
                              value="{{ $application->importer->address ?? '' }}" disabled>

                      </div>
                      <div class="col-lg-12">
                          <label for="expcountry" class="form-label">Country</label>
                          <input type="hidden" class="form-control mb-2" id="expcountryCode"
                              value="{{ $application->importer->countryInfo->code ?? '' }}" name="expcountryCode">
                          <input type="text" class="form-control" id="expcountry"
                              value="{{ $application->importer->countryInfo->name ?? '' }}" name="expcountry" disabled>
                      </div>
                  </div>
              </div>
          </div>

      </div>
  </div>
