<div class="wizard-step" data-title="PERMIT DETAILS" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="1">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="register-page ipa-card">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-calendar-check'></i></span>
                    <h6>Permit Details
                        <span class="ipa-card-sub">When and how the consignment will arrive</span>
                    </h6>
                </div>
                <div class="row gy-3 mb-3">
                    <div class="col-xl-6">
                        <label for="eta" class="form-label">Estimated Time Arrival<a style="color:red"> * </a></label>
                        <input type="date" class="form-control " id="eta" name="eta" required>
                        <div class="invalid-feedback" id="etaError">Estimated Time Arrival cannot be a past date.</div>
                    </div>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-12">
                        <label for="trnptType" class="form-label">Transport Type<a style="color:red"> * </a></label>
                        <select class="form-select" id="trnptType" name="trnptType" data-route="/public/get_entry_point"
                            required>
                            <option value="">-- Select Transport --</option>
                            <option value="Air">Air</option>
                            <option value="Sea">Sea</option>
                            <option value="Land">Land</option>
                        </select>
                    </div>
                    <div class="col-xl-12">
                        <label for="entryPoint" class="form-label">Entry Point<a style="color:red"> * </a></label>
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