import ApexCharts from 'apexcharts'
import $ from "jquery";

console.log('admin dashboard (dummy data mode)');

// ===================================================================
// Shared palette — matches the root CSS tokens used across the site
// (--primary-rgb, --primary-tint1-rgb, --primary-tint2-rgb, --primary-tint3-rgb)
// ===================================================================
const PALETTE = {
    primary: '#2D8F4F',
    tint1: '#6FBF44',
    tint2: '#14A885',
    tint3: '#9CCC65',
    warning: '#FFC658',
    info: '#0EA5E8',
    danger: '#FB4242',
};

// ===================================================================
// 1. STAT CARDS (finance_application_data) — dummy counts + trends
// ===================================================================
function loadStatCards() {
    const dummy = {
        total: 128450,
        ip: 342,
        ic: 118,
        cc: 76,
        ipTrend: { dir: 'up', pct: 8.4 },
        icTrend: { dir: 'down', pct: 3.1 },
        ccTrend: { dir: 'up', pct: 12.9 },
        revenueTrend: { dir: 'up', pct: 5.6 },
    };

    $('#amountRevenue').text(`RM ${dummy.total.toLocaleString()}`);
    $('#ipCount').text(dummy.ip.toLocaleString());
    $('#icCount').text(dummy.ic.toLocaleString());
    $('#ccCount').text(dummy.cc.toLocaleString());

    renderTrend('#revenueTrend', dummy.revenueTrend);
    renderTrend('#ipTrend', dummy.ipTrend);
    renderTrend('#icTrend', dummy.icTrend);
    renderTrend('#ccTrend', dummy.ccTrend);
}

function renderTrend(selector, trend) {
    const $el = $(selector);
    if (!$el.length) return;
    const isUp = trend.dir === 'up';
    $el
        .removeClass('adm-up adm-down')
        .addClass(isUp ? 'adm-up' : 'adm-down')
        .html(`<i class='bx ${isUp ? 'bx-trending-up' : 'bx-trending-down'}'></i> ${trend.pct}% vs last week`);
}

// ===================================================================
// 2. DAILY APPLICATION VOLUME CHART — dummy 7-day series
// ===================================================================
let dailyVolumeChart = null;

function buildDailyVolumeDummy() {
    const days = [];
    const ipData = [];
    const inspectionData = [];
    const consignmentData = [];
    const totalData = [];

    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        days.push(d.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short' }));

        const ip = Math.floor(Math.random() * 12) + 4;
        const inspection = Math.floor(Math.random() * 8) + 2;
        const consignment = Math.floor(Math.random() * 6) + 1;

        ipData.push(ip);
        inspectionData.push(inspection);
        consignmentData.push(consignment);
        totalData.push(ip + inspection + consignment);
    }

    return {
        days,
        series: [
            { name: 'Total Submissions', data: totalData },
            { name: 'Import Permit', data: ipData },
            { name: 'Inspection', data: inspectionData },
            { name: 'Consignment', data: consignmentData },
        ],
    };
}

function loadDailyVolumeChart() {
    const container = document.querySelector('#dailyVolumeChart');
    if (!container) return;

    const data = buildDailyVolumeDummy();

    $('#dailyVolumeChart .spinner-wrapper').remove();

    dailyVolumeChart = new ApexCharts(container, {
        chart: {
            id: 'dailyVolumeChart',
            type: 'area',
            height: 300,
            fontFamily: 'inherit',
            toolbar: { show: false },
        },
        title: { text: 'Daily Application Volume', style: { fontWeight: 700 } },
        subtitle: { text: 'Total submissions across all modules (last 7 days) — demo data' },
        series: data.series,
        xaxis: { categories: data.days },
        stroke: { curve: 'smooth', width: 2.5 },
        fill: {
            type: 'gradient',
            gradient: { opacityFrom: 0.35, opacityTo: 0.02 },
        },
        dataLabels: { enabled: false },
        legend: { position: 'top', horizontalAlign: 'right' },
        colors: [PALETTE.primary, PALETTE.tint2, PALETTE.tint3, PALETTE.info],
    });

    dailyVolumeChart.render().then(() => {
        // keep the sub-series hidden by default so "Total" reads clearly first,
        // same behaviour as before — user can click the legend to reveal them
        dailyVolumeChart.hideSeries('Import Permit');
        dailyVolumeChart.hideSeries('Inspection');
        dailyVolumeChart.hideSeries('Consignment');
    });
}

// ===================================================================
// 3. USER REGISTRATION CHART — dummy 12-month series
// ===================================================================
let userRegistrationChart = null;

function buildUserRegistrationDummy() {
    const months = [];
    const data = [];
    for (let m = 0; m < 12; m++) {
        months.push(new Date(2000, m, 1).toLocaleDateString('en-GB', { month: 'short' }));
        data.push(Math.floor(Math.random() * 80) + 20);
    }
    return { months, data };
}

function loadUserRegistrationChart() {
    const container = document.querySelector('#userLineChart');
    if (!container) return;

    const { months, data } = buildUserRegistrationDummy();

    userRegistrationChart = new ApexCharts(container, {
        chart: { type: 'bar', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
        title: { text: 'User Registrations', style: { fontWeight: 700 } },
        subtitle: { text: 'New public accounts per month — demo data' },
        series: [{ name: 'New Users', data }],
        xaxis: { categories: months },
        plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        colors: [PALETTE.tint2],
    });

    userRegistrationChart.render();
}

// ===================================================================
// 4. ACTIVITY CALENDAR — dummy events, pure JS month grid
// ===================================================================
const DUMMY_CAL_EVENTS = buildDummyCalendarEvents();

function buildDummyCalendarEvents() {
    const today = new Date();
    const key = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    const offset = (days) => {
        const d = new Date(today);
        d.setDate(d.getDate() + days);
        return d;
    };

    const events = {};
    const add = (date, type, title, meta) => {
        const k = key(date);
        if (!events[k]) events[k] = [];
        events[k].push({ type, title, meta });
    };

    add(today, 'success', '18 applications submitted', 'Across Import Permit, Inspection & Consignment');
    add(offset(-1), 'info', 'System maintenance completed', 'Scheduled 2:00–4:00 AM');
    add(offset(-2), 'success', '25 applications submitted', 'Peak volume day');
    add(offset(-4), 'warning', '3 applications flagged for review', 'Missing attachments');
    add(offset(2), 'warning', 'Scheduled maintenance', 'Planned 2:00–4:00 AM');
    add(offset(5), 'info', 'myPhyto sync check', 'Routine integration check');
    add(offset(-7), 'success', '19 applications submitted', '');

    return events;
}

let admCalCursor = new Date(); // month currently displayed
let admCalSelected = new Date(); // selected day

function calKey(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function renderCalendar() {
    const grid = document.getElementById('admCalGrid');
    const monthLabel = document.getElementById('admCalMonthLabel');
    if (!grid || !monthLabel) return;

    const year = admCalCursor.getFullYear();
    const month = admCalCursor.getMonth();

    monthLabel.textContent = admCalCursor.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });

    const dow = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
    let html = dow.map((d) => `<div class="adm-cal-dow">${d}</div>`).join('');

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();

    for (let i = 0; i < firstDay; i++) {
        html += `<div class="adm-cal-day adm-cal-empty"></div>`;
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const cellDate = new Date(year, month, day);
        const k = calKey(cellDate);
        const isToday = cellDate.toDateString() === today.toDateString();
        const isSelected = cellDate.toDateString() === admCalSelected.toDateString();
        const dayEvents = DUMMY_CAL_EVENTS[k] || [];

        const dots = dayEvents
            .slice(0, 3)
            .map((e) => `<span class="adm-cal-dot adm-${e.type}"></span>`)
            .join('');

        html += `
            <div class="adm-cal-day ${isToday ? 'adm-cal-today' : ''} ${isSelected ? 'adm-cal-selected' : ''}" data-date="${k}">
                <span>${day}</span>
                <div class="adm-cal-dots">${dots}</div>
            </div>
        `;
    }

    grid.innerHTML = html;

    grid.querySelectorAll('.adm-cal-day:not(.adm-cal-empty)').forEach((el) => {
        el.addEventListener('click', () => {
            const [y, m, d] = el.dataset.date.split('-').map(Number);
            admCalSelected = new Date(y, m - 1, d);
            renderCalendar();
            renderCalendarEvents();
        });
    });
}

function renderCalendarEvents() {
    const title = document.getElementById('admCalEventsTitle');
    const list = document.getElementById('admCalEventsList');
    if (!title || !list) return;

    const today = new Date();
    const isToday = admCalSelected.toDateString() === today.toDateString();

    title.textContent = isToday
        ? 'Today'
        : admCalSelected.toLocaleDateString('en-GB', { weekday: 'long', day: '2-digit', month: 'long' });

    const events = DUMMY_CAL_EVENTS[calKey(admCalSelected)] || [];

    if (events.length === 0) {
        list.innerHTML = `<div class="adm-cal-empty-msg">No activity recorded for this day.</div>`;
        return;
    }

    list.innerHTML = events
        .map(
            (e) => `
            <div class="adm-cal-event">
                <span class="adm-cal-dot adm-${e.type}"></span>
                <div>
                    <b>${e.title}</b>
                    ${e.meta ? `<span>${e.meta}</span>` : ''}
                </div>
            </div>
        `
        )
        .join('');
}

function initCalendar() {
    if (!document.getElementById('admCalGrid')) return;

    renderCalendar();
    renderCalendarEvents();

    document.getElementById('admCalPrev').addEventListener('click', () => {
        admCalCursor = new Date(admCalCursor.getFullYear(), admCalCursor.getMonth() - 1, 1);
        renderCalendar();
    });

    document.getElementById('admCalNext').addEventListener('click', () => {
        admCalCursor = new Date(admCalCursor.getFullYear(), admCalCursor.getMonth() + 1, 1);
        renderCalendar();
    });
}

// ===================================================================
// 5. ANNOUNCEMENTS WIDGET — dummy list, demo-only "New" form
// ===================================================================
let announcements = [
    {
        title: 'Scheduled System Maintenance',
        body: 'QIS will be briefly unavailable for scheduled upgrades.',
        date: '10 Jul 2026',
        status: 'published',
    },
    {
        title: 'myPhyto Integration Now Live',
        body: 'Phytosanitary certificates now sync automatically with myPhyto.',
        date: '1 Jul 2026',
        status: 'published',
    },
    {
        title: 'Q3 Fee Schedule Review',
        body: 'Draft notice pending finance sign-off before publishing.',
        date: '18 Jul 2026',
        status: 'draft',
    },
];

function renderAnnouncements() {
    const list = document.getElementById('admAnnounceList');
    if (!list) return;

    if (announcements.length === 0) {
        list.innerHTML = `<div class="adm-cal-empty-msg">No announcements yet.</div>`;
        return;
    }

    list.innerHTML = announcements
        .map(
            (a) => `
            <div class="adm-announce-item">
                <span class="adm-icon"><i class='bx bx-bell'></i></span>
                <div>
                    <b>${a.title}</b>
                    <p>${a.body}</p>
                    <div class="adm-announce-meta">
                        <span>${a.date}</span>
                        <span class="adm-badge ${a.status === 'published' ? 'adm-published' : 'adm-draft'}">
                            ${a.status === 'published' ? 'Published' : 'Draft'}
                        </span>
                    </div>
                </div>
            </div>
        `
        )
        .join('');
}

function initAnnouncements() {
    if (!document.getElementById('admAnnounceList')) return;

    renderAnnouncements();

    const saveBtn = document.getElementById('admAnnounceSaveBtn');
    if (!saveBtn) return;

    saveBtn.addEventListener('click', () => {
        const title = document.getElementById('admAnnounceTitle').value.trim();
        const body = document.getElementById('admAnnounceBody').value.trim();
        const status = document.getElementById('admAnnounceStatus').value;

        if (!title) {
            document.getElementById('admAnnounceTitle').focus();
            return;
        }

        // demo only — prepended to the in-memory array, nothing is saved to a server
        announcements.unshift({
            title,
            body: body || 'No description provided.',
            date: new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
            status,
        });

        renderAnnouncements();

        document.getElementById('admAnnounceTitle').value = '';
        document.getElementById('admAnnounceBody').value = '';
        document.getElementById('admAnnounceStatus').value = 'published';

        const modalEl = document.getElementById('admAnnounceModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
    });
}

// ===================================================================
// INIT
// ===================================================================
export async function admin_dashboard() {
    loadStatCards();
    loadDailyVolumeChart();
    loadUserRegistrationChart();
    initCalendar();
    initAnnouncements();
}

admin_dashboard();