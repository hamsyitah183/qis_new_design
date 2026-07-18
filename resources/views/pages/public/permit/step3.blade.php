<div class="wizard-step" data-title="SUMMARY" data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu" data-step="3">

    <div class="ipa-alert-note">
        <i class='bx bx-info-circle'></i>
        <span>Please review everything below carefully. Once your application is submitted, changes can only be made by contacting the department.</span>
    </div>

    <div class="row gy-3">
        <div class="col-xl-6">
            <div class="ipa-card">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-user'></i></span>
                    <h6>Importer &amp; Exporter
                        <span class="ipa-card-sub">Who this application is between</span>
                    </h6>
                </div>

                <div class="ipa-info-group-title">Importer</div>
                <div class="ipa-info-grid">
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Name</span>
                        <span class="ipa-info-value" id="importerName"></span>
                    </div>
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Phone</span>
                        <span class="ipa-info-value" id="importerPhoneno"></span>
                    </div>
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Address</span>
                        <span class="ipa-info-value" id="simpAdd"></span>
                    </div>
                </div>

                <div class="ipa-info-group-title mt-3">Exporter</div>
                <div class="ipa-info-grid">
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Name</span>
                        <span class="ipa-info-value" id="sexpName"></span>
                    </div>
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Phone</span>
                        <span class="ipa-info-value" id="sexpfonno"></span>
                    </div>
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Address</span>
                        <span class="ipa-info-value" id="sexpAddress"></span>
                    </div>
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Country</span>
                        <span class="ipa-info-value" id="sexpCountry"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="ipa-card">
                <div class="ipa-card-header">
                    <span class="ipa-icon-badge"><i class='bx bx-calendar-check'></i></span>
                    <h6>Permit Details
                        <span class="ipa-card-sub">Arrival and entry point</span>
                    </h6>
                </div>

                <div class="ipa-info-grid">
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Estimated Time Arrival</span>
                        <span class="ipa-info-value" id="seta"></span>
                    </div>
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Transport Type</span>
                        <span class="ipa-info-value" id="strty"></span>
                    </div>
                    <div class="ipa-info-row">
                        <span class="ipa-info-label">Entry Point</span>
                        <span class="ipa-info-value" id="sentryp"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="ipa-items-card">
                <div class="ipa-card-header" style="margin-bottom:14px">
                    <span class="ipa-icon-badge"><i class='bx bx-package'></i></span>
                    <h6>Consignment Details
                        <span class="ipa-card-sub">Every item included in this permit</span>
                    </h6>
                </div>
                <div class="table-responsive">
                    <table id="summaryTable3" class="table text-nowrap">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Purpose</th>
                                <th scope="col">Uses</th>
                                <th scope="col">Value</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="ipa-submit-row">
        {{-- <button id="generateSummary" type="button" class="btn btn-md btn-warning">Generate Summary</button> --}}
        <button id="submitApps" type="button" class="btn btn-md btn-info ipa-btn-primary">
            <i class="bx bx-send me-1"></i> Submit Application
        </button>
    </div>
</div>