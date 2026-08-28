import ApexCharts from "apexcharts";
import $ from "jquery";

console.log("admin dashboard (dummy data mode)");

// ===================================================================
// Shared palette — matches the root CSS tokens used across the site
// (--primary-rgb, --primary-tint1-rgb, --primary-tint2-rgb, --primary-tint3-rgb)
// ===================================================================
const PALETTE = {
    primary: "#2D8F4F",
    tint1: "#6FBF44",
    tint2: "#14A885",
    tint3: "#9CCC65",
    warning: "#FFC658",
    info: "#0EA5E8",
    danger: "#FB4242",
};

// ===================================================================
// 1. STAT CARDS (finance_application_data) — dummy counts + trends
// ===================================================================
function loadStatCards() {
    $.ajax({
        url: "/application/count",
        method: "GET",
        success: function (res) {
            const data = res.data;
            $("#amountRevenue").text(`RM ${(data.total || 0).toLocaleString()}`);
            $("#ipCount").text((data.ipCount || 0).toLocaleString());
            $("#icCount").text((data.icCount || 0).toLocaleString());
            $("#ccCount").text((data.ccCount || 0).toLocaleString());

            // Trends are not provided by the API yet, so we can hide or omit them
        },
        error: function (err) {
            console.error("Error fetching stat cards data", err);
        }
    });
}

function renderTrend(selector, trend) {
    const $el = $(selector);
    if (!$el.length) return;
    const isUp = trend.dir === "up";
    $el.removeClass("adm-up adm-down")
        .addClass(isUp ? "adm-up" : "adm-down")
        .html(
            `<i class='bx ${isUp ? "bx-trending-up" : "bx-trending-down"}'></i> ${trend.pct}% vs last week`,
        );
}

// ===================================================================
// 2. DAILY APPLICATION VOLUME CHART — dummy 7-day series
// ===================================================================
let dailyVolumeChart = null;

function loadDailyVolumeChart() {
    const container = document.querySelector("#dailyVolumeChart");
    if (!container) return;

    $.ajax({
        url: "/internal/admin/dashboard/daily-volume",
        method: "GET",
        success: function (data) {
            $("#dailyVolumeChart").empty();

            dailyVolumeChart = new ApexCharts(container, {
                chart: {
                    id: "dailyVolumeChart",
                    type: "area",
                    height: 300,
                    fontFamily: "inherit",
                    toolbar: { show: false },
                },

                series: data.series,
                xaxis: { categories: data.days },
                stroke: { curve: "smooth", width: 2.5 },
                fill: {
                    type: "gradient",
                    gradient: { opacityFrom: 0.35, opacityTo: 0.02 },
                },
                dataLabels: { enabled: false },
                legend: { position: "top", horizontalAlign: "right" },
                colors: [PALETTE.primary, PALETTE.tint2, PALETTE.tint3, PALETTE.info],
            });

            dailyVolumeChart.render().then(() => {
                dailyVolumeChart.hideSeries("Import Permit");
                dailyVolumeChart.hideSeries("Inspection");
                dailyVolumeChart.hideSeries("Consignment");
            });
        },
        error: function (err) {
            console.error("Error fetching daily volume data", err);
        }
    });
}

// ===================================================================
// 3. USER REGISTRATION CHART — dummy 12-month series
// ===================================================================
let userRegistrationChart = null;

function loadUserRegistrationChart() {
    const container = document.querySelector("#userLineChart");
    if (!container) return;

    $.ajax({
        url: "/internal/admin/dashboard/user-registration",
        method: "GET",
        success: function (res) {
            const months = res.months;
            const data = res.data || Array(12).fill(0); // Use 0s if backend doesn't supply data yet

            $("#userLineChart").empty();

            userRegistrationChart = new ApexCharts(container, {
                chart: {
                    type: "bar",
                    height: 300,
                    fontFamily: "inherit",
                    toolbar: { show: false },
                },
                title: { text: "User Registrations", style: { fontWeight: 700 } },
                subtitle: { text: "New public accounts per month" },
                series: [{ name: "New Users", data }],
                xaxis: { categories: months },
                plotOptions: { bar: { borderRadius: 5, columnWidth: "55%" } },
                dataLabels: { enabled: false },
                colors: [PALETTE.tint2],
            });

            userRegistrationChart.render();
        },
        error: function (err) {
            console.error("Error fetching user registration data", err);
        }
    });
}

// ===================================================================
// 4. ACTIVITY CALENDAR — dummy events, pure JS month grid
// ===================================================================
const DUMMY_CAL_EVENTS = buildDummyCalendarEvents();

function buildDummyCalendarEvents() {
    const today = new Date();
    const key = (d) =>
        `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
    const offset = (days) => {
        const d = new Date(today);
        d.setDate(d.getDate() + days);
        return d;
    };

    const events = {};
    const add = (date, type, title_en, title_bm, meta_en, meta_bm) => {
        const k = key(date);
        if (!events[k]) events[k] = [];
        events[k].push({ type, title_en, title_bm, meta_en, meta_bm });
    };

    add(
        today,
        "success",
        "18 applications submitted",
        "18 permohonan telah dihantar",
        "Across Import Permit, Inspection & Consignment",
        "Merentasi Permit Import, Pemeriksaan & Konsainan"
    );
    add(
        offset(-1),
        "info",
        "System maintenance completed",
        "Penyelenggaraan sistem selesai",
        "Scheduled 2:00–4:00 AM",
        "Dijadualkan 2:00–4:00 PG"
    );
    add(offset(-2), "success", "25 applications submitted", "25 permohonan telah dihantar", "Peak volume day", "Hari jumlah kemuncak");
    add(
        offset(-4),
        "warning",
        "3 applications flagged for review",
        "3 permohonan ditanda untuk semakan",
        "Missing attachments",
        "Lampiran tiada"
    );
    add(offset(2), "warning", "Scheduled maintenance", "Penyelenggaraan berjadual", "Planned 2:00–4:00 AM", "Dirancang 2:00–4:00 PG");
    add(offset(5), "info", "myPhyto sync check", "Semakan penyegerakan myPhyto", "Routine integration check", "Semakan integrasi rutin");
    add(offset(-7), "success", "19 applications submitted", "19 permohonan telah dihantar", "", "");

    return events;
}

let admCalCursor = new Date(); // month currently displayed
let admCalSelected = new Date(); // selected day

function calKey(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
}

function renderCalendar() {
    const grid = document.getElementById("admCalGrid");
    const monthLabel = document.getElementById("admCalMonthLabel");
    if (!grid || !monthLabel) return;

    const year = admCalCursor.getFullYear();
    const month = admCalCursor.getMonth();

    const lang = localStorage.getItem("qis_lang") || "en";
    monthLabel.textContent = admCalCursor.toLocaleDateString(lang === 'bm' ? "ms-MY" : "en-GB", {
        month: "long",
        year: "numeric",
    });

    const dowEn = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
    const dowBm = ["Ah", "Is", "Se", "Ra", "Kh", "Ju", "Sa"];
    let html = (lang === 'bm' ? dowBm : dowEn).map((d) => `<div class="adm-cal-dow">${d}</div>`).join("");

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
        const isSelected =
            cellDate.toDateString() === admCalSelected.toDateString();
        const dayEvents = DUMMY_CAL_EVENTS[k] || [];

        const dots = dayEvents
            .slice(0, 3)
            .map((e) => `<span class="adm-cal-dot adm-${e.type}"></span>`)
            .join("");

        html += `
            <div class="adm-cal-day ${isToday ? "adm-cal-today" : ""} ${isSelected ? "adm-cal-selected" : ""}" data-date="${k}">
                <span>${day}</span>
                <div class="adm-cal-dots">${dots}</div>
            </div>
        `;
    }

    grid.innerHTML = html;

    grid.querySelectorAll(".adm-cal-day:not(.adm-cal-empty)").forEach((el) => {
        el.addEventListener("click", () => {
            const [y, m, d] = el.dataset.date.split("-").map(Number);
            admCalSelected = new Date(y, m - 1, d);
            renderCalendar();
            renderCalendarEvents();
        });
    });
}

function renderCalendarEvents() {
    const title = document.getElementById("admCalEventsTitle");
    const list = document.getElementById("admCalEventsList");
    if (!title || !list) return;

    const today = new Date();
    const isToday = admCalSelected.toDateString() === today.toDateString();

    const lang = localStorage.getItem("qis_lang") || "en";

    title.textContent = isToday
        ? (lang === 'bm' ? "Hari ini" : "Today")
        : admCalSelected.toLocaleDateString(lang === 'bm' ? "ms-MY" : "en-GB", {
              weekday: "long",
              day: "2-digit",
              month: "long",
          });

    const events = DUMMY_CAL_EVENTS[calKey(admCalSelected)] || [];

    if (events.length === 0) {
        list.innerHTML = `<div class="adm-cal-empty-msg">${lang === 'bm' ? 'Tiada aktiviti direkodkan untuk hari ini.' : 'No activity recorded for this day.'}</div>`;
        return;
    }

    list.innerHTML = events
        .map(
            (e) => `
            <div class="adm-cal-event">
                <span class="adm-cal-dot adm-${e.type}"></span>
                <div>
                    <b>${lang === 'bm' ? e.title_bm : e.title_en}</b>
                    ${e.meta_en ? `<span>${lang === 'bm' ? e.meta_bm : e.meta_en}</span>` : ""}
                </div>
            </div>
        `,
        )
        .join("");
}

function initCalendar() {
    if (!document.getElementById("admCalGrid")) return;

    renderCalendar();
    renderCalendarEvents();

    window.addEventListener('lang-changed', function(e) {
        renderCalendar();
        renderCalendarEvents();
    });

    document.getElementById("admCalPrev").addEventListener("click", () => {
        admCalCursor = new Date(
            admCalCursor.getFullYear(),
            admCalCursor.getMonth() - 1,
            1,
        );
        renderCalendar();
    });

    document.getElementById("admCalNext").addEventListener("click", () => {
        admCalCursor = new Date(
            admCalCursor.getFullYear(),
            admCalCursor.getMonth() + 1,
            1,
        );
        renderCalendar();
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
}

admin_dashboard();
