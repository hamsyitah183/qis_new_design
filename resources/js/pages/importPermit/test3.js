/**
 * scheduleCalendar.js
 * ------------------------------------------------------------------
 * Dynamic calendar for application milestones. Displays:
 *   1. Application submitted date
 *   2. Document verification date (if available)
 *   3. Officer approval date (if available)
 *   4. SLA deadline (working days after submission)
 *   5. Permit printed date (if available)
 *
 * Also lists public holidays for the current month.
 * All dates are read from the application/permit data, no dummy data.
 */

// ---------------------------------------------------------------
// Public holidays — replace with your real list
// ---------------------------------------------------------------

export const PUBLIC_HOLIDAYS = [
    { date: '2026-01-01', name: "New Year's Day" },
    { date: '2026-02-17', name: 'Chinese New Year (Day 1)' },
    { date: '2026-02-18', name: 'Chinese New Year (Day 2)' },
    { date: '2026-05-01', name: 'Labour Day' },
    { date: '2026-06-03', name: 'Sample Public Holiday (placeholder)' },
];

const HOLIDAY_MAP = new Map(PUBLIC_HOLIDAYS.map((h) => [h.date, h.name]));

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

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
// Build events from real application data
// ---------------------------------------------------------------

/**
 * Builds an array of schedule events from application and permit data.
 * @param {Object} app - The APPLICATION object (from test1.js)
 * @param {Array} permits - The PERMITS array (from test1.js)
 * @param {Object} options - { slaWorkingDays, lang } // lang = 'en' or 'bm'
 * @returns {Array} events
 */
export function buildScheduleEvents(app, permits, options = {}) {
    const lang = options.lang || 'en';
    const slaWorkingDays = options.slaWorkingDays || 4; // default, but will be overridden by app.sla_days if present

    const events = [];

    // 1. Application submitted
    if (app.submitted_at) {
        const date = new Date(app.submitted_at);
        if (!isNaN(date)) {
            events.push({
                key: 'applied',
                label: lang === 'en' ? 'Application Submitted' : 'Permohonan Dihantar',
                date: date,
                color: 'info'
            });
        }
    }

    // 2. SLA deadline (compute from submitted_at + working days)
    let deadlineDate = null;
    if (app.submitted_at) {
        const submitted = new Date(app.submitted_at);
        if (!isNaN(submitted)) {
            // Use SLA days from app if available, else default
            const days = app.sla_days || slaWorkingDays;
            deadlineDate = addWorkingDays(submitted, days);
            events.push({
                key: 'deadline',
                label: lang === 'en' ? `SLA Deadline (${days} working days)` : `Tarikh Akhir SLA (${days} hari bekerja)`,
                date: deadlineDate,
                color: 'danger'
            });
        }
    }

    // 3. Document Verified by Clerk (look for 'clerk verified' status or custom date)
    // Could be derived from activity log or a dedicated field
    if (app.doc_verified_at) {
        const date = new Date(app.doc_verified_at);
        if (!isNaN(date)) {
            events.push({
                key: 'clerk',
                label: lang === 'en' ? 'Document Verified by Clerk' : 'Dokumen Disahkan oleh Kerani',
                date: date,
                color: 'secondary'
            });
        }
    } else {
        // Fallback: if app has status 'clerk verified', use status updated_at or a recent activity
        // For now, skip.
    }

    // 4. Officer Approval (look for officer approved status or date)
    if (app.officer_approved_at) {
        const date = new Date(app.officer_approved_at);
        if (!isNaN(date)) {
            events.push({
                key: 'officer',
                label: lang === 'en' ? 'Permit Approved by Officer' : 'Permit Diluluskan oleh Pegawai',
                date: date,
                color: 'primary'
            });
        }
    }

    // 5. Permit Printed by Boundary Officer (look for printed_at)
    if (app.printed_at) {
        const date = new Date(app.printed_at);
        if (!isNaN(date)) {
            events.push({
                key: 'print',
                label: lang === 'en' ? 'Permit Printed by Boundary Officer' : 'Permit Dicetak oleh Pegawai Sempadan',
                date: date,
                color: 'success'
            });
        }
    }

    // If there are permits with 'paid' or 'completed', we could also show a payment milestone
    // but for now, keep it simple.

    return events;
}

// ---------------------------------------------------------------
// Calendar rendering (unchanged logic, now uses passed events)
// ---------------------------------------------------------------

let currentEvents = [];
let calYear, calMonth;

function eventsByIsoDate(events) {
    const map = new Map();
    events.forEach((ev) => {
        const key = isoDate(ev.date);
        if (!map.has(key)) map.set(key, []);
        map.get(key).push(ev);
    });
    return map;
}

function renderCalendar(year, month, events) {
    const labelEl = document.getElementById('ipvScheduleMonthLabel');
    const gridEl = document.getElementById('ipvScheduleGrid');
    if (!labelEl || !gridEl) return;

    labelEl.textContent = monthLabelText(year, month);

    const firstOfMonth = new Date(year, month, 1);
    const startWeekday = firstOfMonth.getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const cells = [];
    for (let i = 0; i < startWeekday; i++) cells.push(null);
    for (let d = 1; d <= daysInMonth; d++) cells.push(new Date(year, month, d));

    const eventsMap = eventsByIsoDate(events);

    gridEl.innerHTML = cells.map((date) => {
        if (!date) return '<div class="ipv-cal-cell is-empty"></div>';

        const evs = eventsMap.get(isoDate(date)) || [];
        const holidayName = holidayNameFor(date);
        const classes = ['ipv-cal-cell'];
        if (isWeekend(date)) classes.push('is-weekend');
        if (holidayName) classes.push('is-holiday');
        if (evs.length) classes.push('has-event');

        const dots = evs.map((ev) =>
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
    if (!el) return;

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

function renderMonth(year, month, events) {
    renderCalendar(year, month, events);
    renderHolidayList(year, month);
}

function renderLegend(events) {
    const el = document.getElementById('ipvScheduleLegend');
    if (!el) return;
    el.innerHTML = events.map((ev) => `
        <div class="ipv-cal-legend-item">
            <span class="ipv-cal-dot is-${ev.color}"></span>
            <span>${escapeHtml(ev.label)} — ${formatShort(ev.date)}</span>
        </div>
    `).join('');
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
}

function closeCalendarPopover() {
    getPopoverEl()?.classList.remove('is-open');
    document.getElementById('scheduleBtn')?.classList.remove('is-active');
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

let isInitialized = false;

export function initScheduleCalendar(events = [], holidays = PUBLIC_HOLIDAYS) {
    if (isInitialized) {
        console.warn('[scheduleCalendar] Already initialized — ignoring repeat call.');
        return;
    }

    const btn = document.getElementById('scheduleBtn');
    const popover = getPopoverEl();

    if (!btn) {
        console.warn('[scheduleCalendar] #scheduleBtn not found.');
        return;
    }
    if (!popover) {
        console.warn('[scheduleCalendar] #ipvSchedulePopover not found.');
        return;
    }

    // Store events and set initial month to the earliest event date (or today)
    currentEvents = events;
    if (events.length) {
        const sorted = [...events].sort((a, b) => a.date - b.date);
        const earliest = sorted[0].date;
        calYear = earliest.getFullYear();
        calMonth = earliest.getMonth();
    } else {
        const now = new Date();
        calYear = now.getFullYear();
        calMonth = now.getMonth();
    }

    // Update holiday map (in case holidays differ from default)
    if (holidays) {
        HOLIDAY_MAP.clear();
        holidays.forEach((h) => HOLIDAY_MAP.set(h.date, h.name));
    }

    renderLegend(events);
    renderMonth(calYear, calMonth, events);

    // Nav buttons
    document.getElementById('ipvScheduleCalPrev')?.addEventListener('click', () => {
        calMonth--;
        if (calMonth < 0) { calMonth = 11; calYear--; }
        renderMonth(calYear, calMonth, events);
    });
    document.getElementById('ipvScheduleCalNext')?.addEventListener('click', () => {
        calMonth++;
        if (calMonth > 11) { calMonth = 0; calYear++; }
        renderMonth(calYear, calMonth, events);
    });

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

    isInitialized = true;
}

// Expose helpers for external use
export { addWorkingDays, isWorkingDay, isHoliday, isWeekend };