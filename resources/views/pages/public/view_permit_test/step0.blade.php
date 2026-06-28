<div class="container-fluid px-0 px-md-3">

    <!-- ===== Page Header ===== -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h5 class="fw-bold mb-0" style="color: var(--default-text-color);">
            <i class="bi bi-people me-2" style="color: var(--primary-color);"></i>
            Importer and Exporter Details
        </h5>
    </div>

    <!-- ===== Two-column Grid ===== -->
    <div class="details-grid">

        <!-- ============================================================ -->
        <!-- IMPORTER CARD -->
        <!-- ============================================================ -->
        <div class="customer-card">
            <!-- Header -->
            <div class="card-header-custom">
                <div class="avatar importer-avatar">
                    {{ substr($application->importer->fullname ?? 'I', 0, 1) }}
                </div>
                <div class="title-group">
                    <div class="name">{{ $application->importer->fullname ?? '—' }}</div>
                    <div class="sub-label">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Importer
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="card-body-custom">

                <!-- Details -->
                <div class="detail-row">
                    <div class="icon-wrap"><i class="bi bi-telephone"></i></div>
                    <span class="label-text">Phone</span>
                    <span class="value-text">{{ $application->importer->phone_number ?? '—' }}</span>
                </div>

                <div class="detail-row">
                    <div class="icon-wrap"><i class="bi bi-envelope"></i></div>
                    <span class="label-text">Email</span>
                    <span class="value-text">{{ $application->importer->email ?? '—' }}</span>
                </div>

                <div class="detail-row">
                    <div class="icon-wrap"><i class="bi bi-geo-alt"></i></div>
                    <span class="label-text">Address</span>
                    <span class="value-text">
                        {{ $application->importer->address_1 ?? '—' }}
                        @if($application->importer->address_2)
                            <span class="sub-address">{{ $application->importer->address_2 }}</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <div class="icon-wrap"><i class="bi bi-globe2"></i></div>
                    <span class="label-text">Country</span>
                    <span class="value-text">
                        <span class="country-badge">{{ $application->importer->country ?? '—' }}</span>
                    </span>
                </div>

            </div>
        </div>

        <!-- ============================================================ -->
        <!-- EXPORTER CARD -->
        <!-- ============================================================ -->
        <div class="customer-card">
            <!-- Header -->
            <div class="card-header-custom">
                <div class="avatar exporter-avatar">
                    {{ substr($application->exporter->name ?? 'E', 0, 1) }}
                </div>
                <div class="title-group">
                    <div class="name">{{ $application->exporter->name ?? '—' }}</div>
                    <div class="sub-label">
                        <i class="bi bi-box-arrow-right me-1"></i> Exporter
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="card-body-custom">

                <!-- Details -->
                <div class="detail-row">
                    <div class="icon-wrap"><i class="bi bi-telephone"></i></div>
                    <span class="label-text">Phone</span>
                    <span class="value-text">{{ $application->exporter->phone_no ?? '—' }}</span>
                </div>

                <div class="detail-row">
                    <div class="icon-wrap"><i class="bi bi-envelope"></i></div>
                    <span class="label-text">Email</span>
                    <span class="value-text">{{ $application->exporter->email ?? '—' }}</span>
                </div>

                <div class="detail-row">
                    <div class="icon-wrap"><i class="bi bi-geo-alt"></i></div>
                    <span class="label-text">Address</span>
                    <span class="value-text">{{ $application->exporter->address ?? '—' }}</span>
                </div>

                <div class="detail-row">
                    <div class="icon-wrap"><i class="bi bi-flag"></i></div>
                    <span class="label-text">Country</span>
                    <span class="value-text">
                        <span class="country-badge">{{ $application->exporter->country ?? '—' }}</span>
                    </span>
                </div>

            </div>
        </div>

    </div><!-- /details-grid -->

    <!-- ===== Hidden inputs (preserved) ===== -->
    <input type="hidden" id="app_cate" value="0">
    <input type="hidden" id="impid" value="{{ $application->importer_id }}">
    <input type="hidden" id="expid" value="{{ $application->exporter_id }}">
    <input type="hidden" id="expcountryCode" value="{{ $application->exporter->country ?? '' }}" name="expcountryCode">

</div><!-- /container -->