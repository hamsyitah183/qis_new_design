/**
 * scheduleCalendar.js
 * ------------------------------------------------------------------
 * Powers the calendar popover behind the "Schedule inspection" button
 * (#scheduleBtn). Click it to open a small floating calendar that
 * highlights five milestone dates for the application's SLA:
 *
 *   1. Date applied
 *   2. SLA deadline (N working days after applying — weekends and
 *      public holidays don't count)
 *   3. Date the clerk verified documents
 *   4. Date the officer approved the permit
 *   5. Date the boundary officer printed the permit
 *
 * It also lists out any public holiday that falls within whichever
 * month is currently in view, updating as you page through months.
 *
 * SCHEDULE_SCENARIO / SCHEDULE_EVENTS below are a standalone dummy
 * scenario (applied 1 June 2026, 4 working-day SLA) — separate from
 * the PERMITS/ACTIVITY_LOG dummy data in test1.js/test2.js. Wire it
 * up later with real dates from IpApplication / ImportPermitLog
 * (submitted_at, doc-verified_at, officer-approved_at, printed_at).
 *
 * PUBLIC_HOLIDAYS is a placeholder list — swap in the real Malaysia
 * public holiday calendar (e.g. from your public_code table) once
 * it's available. Each entry is { date: 'YYYY-MM-DD', name }.
 *
 * Import in test1.js:
 *   import { initScheduleCalendar } from './scheduleCalendar.js';
 *   // then inside init(): initScheduleCalendar();
 *
 * And add to the vite/css imports:
 *   import '../../../css/pages/importPermit/scheduleCalendar.css'; // adjust path
 *
 * NOTE: initScheduleCalendar() is idempotent — calling it more than
 * once (e.g. by accident from init()) is a no-op after the first
 * successful call, so listeners can never get double-attached to
 * #scheduleBtn.
 *
 * Blade markup needed (in addition to what was added previously):
 *   <div class="ipv-cal-holidays" id="ipvScheduleHolidays"></div>
 * placed between the #ipvScheduleGrid div and the #ipvScheduleLegend div.
 */

// ---------------------------------------------------------------
// Working-day / public-holiday helpers
// ---------------------------------------------------------------

// Placeholder holidays — replace with the real calendar later.
const PUBLIC_HOLIDAYS = [
    { date: '2026-01-01', name: "New Year's Day" },
    { date: '2026-02-17', name: 'Chinese New Year (Day 1)' },
    { date: '2026-02-18', name: 'Chinese New Year (Day 2)' },
    { date: '2026-05-01', name: 'Labour Day' },
    { date: '2026-06-03', name: 'Sample Public Holiday (placeholder)' },
];

const HOLIDAY_MAP = new Map(PUBLIC_HOLIDAYS.map((h) => [h.date, h.name]));

function isoDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function parseIsoDate(iso) {
    const [y, m, d] = iso.split('-').map(Number);
    return new Date(y, m - 1, d);
}

function isWeekend(date) {
    const day = date.getDay(); // 0 = Sun, 6 = Sat
    return day === 0 || day === 6;
}

function holidayNameFor(date) {
    return HOLIDAY_MAP.get(isoDate(date));
}

function isHoliday(date) {
    return HOLIDAY_MAP.has(isoDate(date));
}

function isWorkingDay(date) {
    return !isWeekend(date) && !isHoliday(date);
}

function holidaysInMonth(year, month) {
    return PUBLIC_HOLIDAYS
        .map((h) => ({ ...h, dateObj: parseIsoDate(h.date) }))
        .filter((h) => h.dateObj.getFullYear() === year && h.dateObj.getMonth() === month)
        .sort((a, b) => a.dateObj - b.dateObj);
}

/** Adds N working days on top of startDate, skipping weekends + holidays. */
function addWorkingDays(startDate, days) {
    const result = new Date(startDate);
    let added = 0;
    while (added < days) {
        result.setDate(result.getDate() + 1);
        if (isWorkingDay(result)) added++;
    }
    return result;
}

// ---------------------------------------------------------------
// Dummy SLA scenario — applied 1 June 2026, 4 working-day SLA
// ---------------------------------------------------------------

const APPLIED_DATE = new Date(2026, 5, 1); // 1 June 2026 (Monday)
const SLA_WORKING_DAYS = 4;
const DEADLINE_DATE = addWorkingDays(APPLIED_DATE, SLA_WORKING_DAYS);

const SCHEDULE_EVENTS = [
    { key: 'applied',  label: 'Application Submitted',             date: APPLIED_DATE,             color: 'info' },
    { key: 'clerk',    label: 'Document Verified by Clerk',        date: new Date(2026, 5, 2),     color: 'secondary' },
    { key: 'officer',  label: 'Permit Approved by Officer',        date: new Date(2026, 5, 5),     color: 'primary' },
    { key: 'deadline', label: `SLA Deadline (${SLA_WORKING_DAYS} working days)`, date: DEADLINE_DATE, color: 'danger' },
    { key: 'print',    label: 'Permit Printed by Boundary Officer', date: new Date(2026, 5, 8),    color: 'success' },
];

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

function formatShort(date) {
    return date.toLocaleDateString('en-MY', { day: 'numeric', month: 'short', year: 'numeric' });
}

function monthLabelText(year, month) {
    return new Date(year, month, 1).toLocaleDateString('en-MY', { month: 'long', year: 'numeric' });
}

// ---------------------------------------------------------------
// Calendar grid rendering
// ---------------------------------------------------------------

let calYear = APPLIED_DATE.getFullYear();
let calMonth = APPLIED_DATE.getMonth(); // 0-indexed

function buildMonthGrid(year, month) {
    const firstOfMonth = new Date(year, month, 1);
    const startWeekday = firstOfMonth.getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const cells = [];
    for (let i = 0; i < startWeekday; i++) cells.push(null);
    for (let d = 1; d <= daysInMonth; d++) cells.push(new Date(year, month, d));
    return cells;
}

function eventsByIsoDate() {
    const map = new Map();
    SCHEDULE_EVENTS.forEach((ev) => {
        const key = isoDate(ev.date);
        if (!map.has(key)) map.set(key, []);
        map.get(key).push(ev);
    });
    return map;
}

function renderCalendar(year, month) {
    const labelEl = document.getElementById('ipvScheduleMonthLabel');
    const gridEl = document.getElementById('ipvScheduleGrid');
    if (!labelEl || !gridEl) return;

    labelEl.textContent = monthLabelText(year, month);

    const grid = buildMonthGrid(year, month);
    const eventsMap = eventsByIsoDate();

    gridEl.innerHTML = grid.map((date) => {
        if (!date) return '<div class="ipv-cal-cell is-empty"></div>';

        const events = eventsMap.get(isoDate(date)) || [];
        const holidayName = holidayNameFor(date);
        const classes = ['ipv-cal-cell'];
        if (isWeekend(date)) classes.push('is-weekend');
        if (holidayName) classes.push('is-holiday');
        if (events.length) classes.push('has-event');

        const dots = events.map((ev) =>
            `<span class="ipv-cal-dot is-${ev.color}" title="${escapeHtml(ev.label)} — ${formatShort(date)}"></span>`
        ).join('');

        const tooltip = holidayName ? `Public Holiday: ${holidayName}` : '';

        return `
            <div class="${classes.join(' ')}" title="${escapeHtml(tooltip)}">
                <span class="ipv-cal-daynum">${date.getDate()}</span>
                <div class="ipv-cal-dots">${dots}</div>
            </div>
        `;
    }).join('');
}

function renderHolidayList(year, month) {
    const el = document.getElementById('ipvScheduleHolidays');
    if (!el) return; // optional block — skip quietly if not added to the blade yet

    const holidays = holidaysInMonth(year, month);

    if (!holidays.length) {
        el.innerHTML = `
            <div class="ipv-cal-holidays-title">Public Holidays — ${monthLabelText(year, month)}</div>
            <div class="ipv-cal-holidays-empty">No public holidays this month.</div>
        `;
        return;
    }

    el.innerHTML = `
        <div class="ipv-cal-holidays-title">Public Holidays — ${monthLabelText(year, month)}</div>
        ${holidays.map((h) => `
            <div class="ipv-cal-holiday-item">
                <span class="ipv-cal-dot"></span>
                <span>${escapeHtml(h.name)} — ${formatShort(h.dateObj)}</span>
            </div>
        `).join('')}
    `;
}

function renderMonth(year, month) {
    renderCalendar(year, month);
    renderHolidayList(year, month);
}

function renderLegend() {
    const el = document.getElementById('ipvScheduleLegend');
    if (!el) return;
    el.innerHTML = SCHEDULE_EVENTS.map((ev) => `
        <div class="ipv-cal-legend-item">
            <span class="ipv-cal-dot is-${ev.color}"></span>
            <span>${escapeHtml(ev.label)} — ${formatShort(ev.date)}</span>
        </div>
    `).join('');
}

function initMonthNav() {
    document.getElementById('ipvScheduleCalPrev')?.addEventListener('click', () => {
        calMonth--;
        if (calMonth < 0) { calMonth = 11; calYear--; }
        renderMonth(calYear, calMonth);
    });
    document.getElementById('ipvScheduleCalNext')?.addEventListener('click', () => {
        calMonth++;
        if (calMonth > 11) { calMonth = 0; calYear++; }
        renderMonth(calYear, calMonth);
    });
}

// ---------------------------------------------------------------
// Popover open / close / position
// ---------------------------------------------------------------

function getPopoverEl() {
    return document.getElementById('ipvSchedulePopover');
}

function isPopoverOpen() {
    return getPopoverEl()?.classList.contains('is-open') ?? false;
}

function openCalendarPopover() {
    const popover = getPopoverEl();
    const btn = document.getElementById('scheduleBtn');
    if (!popover || !btn) return;

    popover.classList.add('is-open');
    btn.classList.add('is-active');

    // Positioning next to the button is disabled for now — the popover
    // currently renders wherever .ipv-cal-popover's CSS places it.
    // Uncomment to anchor it under the button again:
    //
    // const rect = btn.getBoundingClientRect();
    // const popW = popover.offsetWidth || 320;
    // let left = rect.left;
    // if (left + popW > window.innerWidth - 16) {
    //     left = window.innerWidth - popW - 16;
    // }
    // popover.style.top = `${rect.bottom + 8}px`;
    // popover.style.left = `${Math.max(16, left)}px`;
}

function closeCalendarPopover() {
    getPopoverEl()?.classList.remove('is-open');
    document.getElementById('scheduleBtn')?.classList.remove('is-active');
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

let isInitialized = false;

export function initScheduleCalendar() {
    if (isInitialized) {
        console.warn('[scheduleCalendar] initScheduleCalendar() was called again — ignoring the repeat call so listeners don\'t get attached twice.');
        return;
    }

    const btn = document.getElementById('scheduleBtn');
    const popover = getPopoverEl();

    if (!btn) {
        console.warn('[scheduleCalendar] #scheduleBtn not found in the DOM — nothing to attach the calendar to.');
        return;
    }
    if (!popover) {
        console.warn('[scheduleCalendar] #ipvSchedulePopover not found in the DOM — the calendar popover markup is missing from the blade.');
        return;
    }

    isInitialized = true;

    renderLegend();
    renderMonth(calYear, calMonth);
    initMonthNav();

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        isPopoverOpen() ? closeCalendarPopover() : openCalendarPopover();
    });

    document.getElementById('ipvScheduleClose')?.addEventListener('click', closeCalendarPopover);

    document.addEventListener('click', (e) => {
        if (!isPopoverOpen()) return;
        if (popover.contains(e.target) || e.target.closest('#scheduleBtn')) return;
        closeCalendarPopover();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeCalendarPopover();
    });
}

// Exported in case you want to reuse the SLA math / holiday data elsewhere.
export { addWorkingDays, isWorkingDay, isHoliday, isWeekend, PUBLIC_HOLIDAYS };