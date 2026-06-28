<div class="container-fluid px-0 px-md-3 mt-3">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <!-- Permit Details Card -->
            <div class="customer-card">
                <!-- Header -->
                <div class="card-header-custom">
                    <div class="avatar" style="background: var(--gray-5);">
                        <i class="bi bi-file-earmark-text" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="title-group">
                        <div class="name">Permit Details</div>
                        <div class="sub-label">
                            <i class="bi bi-info-circle me-1"></i> Application Information
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="card-body-custom">
                    <!-- ETA -->
                    <div class="detail-row">
                        <div class="icon-wrap"><i class="bi bi-calendar-event"></i></div>
                        <span class="label-text">ETA</span>
                        <span class="value-text">
                            {{ \Carbon\Carbon::parse($application->eta)->format('d/m/Y') }}
                        </span>
                    </div>

                    <!-- Transport Type -->
                    <div class="detail-row">
                        <div class="icon-wrap"><i class="bi bi-truck"></i></div>
                        <span class="label-text">Transport</span>
                        <span class="value-text">
                            {{ $application->transport_type }}
                        </span>
                    </div>

                    <!-- Entry Point -->
                    <div class="detail-row">
                        <div class="icon-wrap"><i class="bi bi-geo-alt"></i></div>
                        <span class="label-text">Entry Point</span>
                        <span class="value-text">
                            {{ $application->entry_point ?? '—' }}
                        </span>
                    </div>

                    <!-- Hidden inputs for form submission (if needed) -->
                    <input type="hidden" id="trnptType" name="trnptType" value="{{ $application->transport_type }}">
                    <input type="hidden" id="entryPoint" name="entryPoint" value="{{ $application->entry_point }}">
                    <input type="hidden" id="descEntryPoint" value="{{ $application->entry_point_description ?? '' }}">
                </div>
            </div>
        </div>
    </div>
</div>
