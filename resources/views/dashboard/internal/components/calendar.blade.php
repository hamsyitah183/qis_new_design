<div class="card custom-card adm-calendar-card">
    <div class="card-body">
        <div class="adm-card-title-row">
            <div>
                <h6 data-en="Activity Calendar" data-bm="Kalendar Aktiviti">Activity Calendar</h6>
                <span class="adm-card-sub" data-en="Applications, notices &amp; system events" data-bm="Permohonan, notis &amp; acara sistem">Applications, notices &amp; system events</span>
            </div>
        </div>

        <div class="adm-cal-head">
            <button type="button" class="adm-cal-nav-btn" id="admCalPrev"><i class='bx bx-chevron-left'></i></button>
            <span class="adm-cal-month" id="admCalMonthLabel">&nbsp;</span>
            <button type="button" class="adm-cal-nav-btn" id="admCalNext"><i class='bx bx-chevron-right'></i></button>
        </div>

        <div class="adm-cal-grid" id="admCalGrid">
            <!-- day-of-week headers + day cells rendered by admindashboard.js -->
        </div>

        <div class="adm-cal-events">
            <div class="adm-cal-events-title" id="admCalEventsTitle">Today</div>
            <div id="admCalEventsList">
                <!-- selected day's events rendered by admindashboard.js -->
            </div>
        </div>
    </div>
</div>