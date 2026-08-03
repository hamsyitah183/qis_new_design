<div class="wizard-step" data-title="INSPECTION DETAILS" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="1">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="register-page ipa-card h-100">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-search-alt'></i></span>
                    <h6 data-en="Inspection Details" data-bm="Butiran Pemeriksaan">Inspection Details <a style="color:red"> * </a>
                        <span class="ipa-card-sub" data-en="When and how the goods arrive" data-bm="Bila dan bagaimana barangan tiba">When and how the goods arrive</span>
                    </h6>
                </div>
                <div class="row gy-3 mb-3">
                    <div class="col-xl-6">
                        <label for="eta" class="form-label" data-en="Expected Inspection Date" data-bm="Tarikh Jangkaan Pemeriksaan">Expected Inspection Date<a style="color:red"> * </a></label>
                        <input type="date" class="form-control " id="eta" name="eta" required>
                        <div class="invalid-feedback" id="etaError">Expected Inspection Date cannot be a past date.
                        </div>
                    </div>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-6">
                        <label for="trnptType" class="form-label" data-en="Transport Type" data-bm="Jenis Pengangkutan">Transport Type<a style="color:red"> * </a></label>
                        <select class="form-select" id="trnptType" name="trnptType" data-route="/public/get_entry_point"
                            required>
                            <option value="">-- Select Transport --</option>
                            <option value="Air">Air</option>
                            <option value="Sea">Sea</option>
                            <option value="Land">Land</option>
                        </select>
                    </div>
                    <div class="col-xl-6">
                        <label for="entryPoint" class="form-label" data-en="Entry Point" data-bm="Pintu Masuk">Entry Point<a style="color:red"> * </a></label>
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