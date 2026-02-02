<div class="wizard-step" data-title="PERMIT DETAILS" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="1">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="register-page">
                <h6 class="mb-3">Permit Details :</h6>
                <div class="row gy-3 mb-3">
                    <div class="col-xl-6">
                        <label for="eta" class="form-label">Estimated Time Arrival</label>
                        <input type="date" class="form-control " id="eta" name="eta" required>
                    </div>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-6">
                        <label for="trnptType" class="form-label">Transport Type</label>
                        <select class="form-select" id="trnptType" name="trnptType" data-route="/public/get_entry_point"
                            required>
                            <option value="">-- Select Transport --</option>
                            <option value="Air">Air</option>
                            <option value="Sea">Sea</option>
                            <option value="Land">Land</option>
                        </select>
                    </div>
                    <div class="col-xl-6">
                        <label for="entryPoint" class="form-label">Entry Point</label>
                        <select class="form-select" id="entryPoint" name="entryPoint"required>
                            <option value="">-- Select Entry Point --</option>

                        </select>
                        <input type="hidden" id="descEntryPoint">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
