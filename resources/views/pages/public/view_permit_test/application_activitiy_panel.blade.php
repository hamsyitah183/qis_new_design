{{--
    Activity / Permits tabbed panel
    -------------------------------
    Drop this in via:
        @include('pages.public.view_permit_test.application_activity_panel')

    All data shown here is rendered client-side from dummy arrays in
    activityPanel.js (window.ApplicationActivityData). Swap the dummy
    arrays for a real API/Blade-fed payload later — the render functions
    don't need to change, just the data shape they're fed.
--}}

<div class="row">
    <div class="col-xl-12">
        <div class="customer-card ap-card">

            <!-- ===== Tab Nav ===== -->
            <div class="ap-tabnav" role="tablist">
                <button type="button" class="ap-tabnav-item is-active" data-ap-tab="activity" role="tab">
                    Activity
                </button>
                <button type="button" class="ap-tabnav-item" data-ap-tab="permits" role="tab">
                    Permits
                    <span class="ap-tab-count" id="apPermitsCount">0</span>
                </button>
                <button type="button" class="ap-tabnav-item" data-ap-tab="documents" role="tab">
                    Documents
                </button>
                <button type="button" class="ap-tabnav-item" data-ap-tab="notes" role="tab">
                    Notes
                </button>
            </div>

            <div class="ap-card-body">

                <!-- ===== Pane: Activity ===== -->
                <div class="ap-tabpane is-active" data-ap-pane="activity">
                    <div class="ap-pane-header">
                        <h6 class="ap-pane-title">Latest Activity</h6>
                    </div>

                    <div class="ap-timeline" id="apActivityTimeline">
                        <!-- timeline items injected by activityPanel.js -->
                    </div>

                    <button type="button" class="ap-link-btn d-none" id="apShowMoreActivity">
                        Show more
                    </button>
                </div>

                <!-- ===== Pane: Permits ===== -->
                <div class="ap-tabpane" data-ap-pane="permits">
                    <div class="ap-pane-header">
                        <h6 class="ap-pane-title">Permit List</h6>
                        <span class="ap-pane-action">
                            <i class="bi bi-plus-circle me-1"></i> Create permit
                        </span>
                    </div>

                    <div class="ap-permit-list" id="apPermitList">
                        <!-- permit cards injected by activityPanel.js -->
                    </div>
                </div>

                <!-- ===== Pane: Documents (placeholder) ===== -->
                <div class="ap-tabpane" data-ap-pane="documents">
                    <div class="ap-empty-state">
                        <i class="bi bi-file-earmark-text"></i>
                        <p>No documents to show yet.</p>
                    </div>
                </div>

                <!-- ===== Pane: Notes (placeholder) ===== -->
                <div class="ap-tabpane" data-ap-pane="notes">
                    <div class="ap-empty-state">
                        <i class="bi bi-sticky"></i>
                        <p>No notes added yet.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>