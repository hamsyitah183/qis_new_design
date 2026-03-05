<div class="wizard-step" data-title="PERMIT DETAILS" data-id="2e8WqSV3slGIpTbnjcJzmDwBQaHrfh0Z" data-step="1">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="register-page">
                <h6 class="mb-3">Permit Details :</h6>
                <div class="row gy-3 mb-3">
                    <div class="col-xl-6">
                        <label for="eta" class="form-label">Estimated Time
                            Arrival</label>
                        <input type="text" class="form-control " id="eta" name="eta"
                            value="{{ $application->eta ? \Carbon\Carbon::parse($application->eta)->format('d/m/Y') : '' }}"
                            disabled>
                    </div>
                </div>
                <div class="row gy-3">
                    <div class="col-xl-6">
                        <label for="trnptType" class="form-label">Transport Type</label>
                        <select class="form-select" id="trnptType" name="trnptType" data-route="/public/get_entry_point"
                            disabled>
                            <option value="">{{ $application->transport_type }}</option>
                            <option value="Air">Air</option>
                            <option value="Sea">Sea</option>
                            <option value="Land">Land</option>
                        </select>

                    </div>
                    <div class="col-xl-6">
                        <label for="entryPoint" class="form-label">Entry Point</label>
                        <input type="text" id="entryPoint" disabled class="form-control"
                            value="{{ $application->entryPoint?->entry_name }}">
                        <input type="hidden" id="descEntryPoint">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>