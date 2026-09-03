import "./bootstrap";
import { initInactivityTimeout } from "./inactivity_timeout";
import { IconHome, IconUser } from "tabler-icons";
import "@fortawesome/fontawesome-free/css/all.min.css";
import { getRemarkBm, getStatusBm } from "./appLog";

import $ from "jquery";
window.$ = window.jQuery = $; // make it global
import "select2";
import { internalUserEcho, publicUserEcho } from "./broadcast_user";
import { notification, notificationContent } from "./notification";

import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import "datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css";
import "datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css";
import { public_dashboard } from "./pages/dashboard/public_dashboard";
import "./pages/gallery.js";
// resources/js/app.js
import ApexCharts from "apexcharts";

// Make it globally available
window.ApexCharts = ApexCharts;

import DataTable from "datatables.net-bs5";
window.DataTable = DataTable;

export function getRoleBm(role) {
    const rolesMap = {
        "admin": "Pentadbir",
        "boundary officer": "Pegawai Sempadan",
        "clerk": "Kerani",
        "finance": "Kewangan",
        "officer": "Pegawai",
        "strict-clerk-tester": "Penguji-Kerani-Ketat",
        "strict-officer-tester": "Penguji-Pegawai-Ketat",
        "superadmin": "Pentadbir Sistem",
    };
    return rolesMap[role.toLowerCase()] || role;
}

export function getPermissionBm(permission) {
    const permMap = {
        "approve application": "luluskan permohonan",
        "approve permit": "luluskan permit",
        "approve public user": "luluskan pengguna awam",
        "control panel": "panel kawalan",
        "create internal user": "cipta pengguna dalaman",
        "create public user": "cipta pengguna awam",
        "delete application": "padam permohonan",
        "delete internal user": "padam pengguna dalaman",
        "delete public user": "padam pengguna awam",
        "generate financial report": "jana laporan kewangan",
        "generate operational report": "jana laporan operasi",
        "generate performance report": "jana laporan prestasi",
        "manage announcement": "urus pengumuman",
        "manage consignment item": "urus item konsainan",
        "manage import permit item": "urus item permit import",
        "manage role and permission": "urus peranan dan kebenaran",
        "manage settings": "urus tetapan",
        "print permit": "cetak permit",
        "read activity log": "papar log aktiviti",
        "read application": "papar permohonan",
        "read internal user": "papar pengguna dalaman",
        "read public user": "papar pengguna awam",
        "scan permit": "imbas permit",
        "update internal user": "kemas kini pengguna dalaman",
        "update public user": "kemas kini pengguna awam",
        "view dashboard": "lihat papan pemuka",
        "view exporter list": "papar senarai pengeksport",
        "view importer list": "papar senarai pengimport",
        "view notification": "papar notifikasi",
        "view orders invoices": "papar pesanan dan invois",
        "more...": "lebih..."
    };
    return permMap[permission.toLowerCase()] || permission;
}

// Set up global DataTables bilingual defaults
function getGlobalDataTableLanguage() {
    return {
        "sEmptyTable": "<span data-en='No data available in table' data-bm='Tiada data tersedia dalam jadual'>No data available in table</span>",
        "sInfo": "<span data-en='Showing' data-bm='Menunjukkan'>Showing</span> _START_ <span data-en='to' data-bm='hingga'>to</span> _END_ <span data-en='of' data-bm='daripada'>of</span> _TOTAL_ <span data-en='entries' data-bm='entri'>entries</span>",
        "sInfoEmpty": "<span data-en='Showing 0 to 0 of 0 entries' data-bm='Menunjukkan 0 hingga 0 daripada 0 entri'>Showing 0 to 0 of 0 entries</span>",
        "sInfoFiltered": "<span data-en='(filtered from _MAX_ total entries)' data-bm='(ditapis daripada _MAX_ jumlah entri)'>(filtered from _MAX_ total entries)</span>",
        "sLengthMenu": "<span data-en='Show' data-bm='Papar'>Show</span> _MENU_ <span data-en='entries' data-bm='entri'>entries</span>",
        "sLoadingRecords": "<span data-en='Loading...' data-bm='Memuatkan...'>Loading...</span>",
        "sProcessing": "<span data-en='Processing...' data-bm='Sedang diproses...'>Processing...</span>",
        "sSearch": "<span data-en='Search:' data-bm='Cari:'>Search:</span>",
        "sZeroRecords": "<span data-en='No matching records found' data-bm='Tiada rekod yang sepadan'>No matching records found</span>",
        "oPaginate": {
            "sFirst": "First",
            "sLast": "Last",
            "sNext": "Next",
            "sPrevious": "Previous"
        },
        "oAria": {
            "sSortAscending": "<span data-en=': activate to sort column ascending' data-bm=': aktifkan untuk menyusun lajur menaik'>: activate to sort column ascending</span>",
            "sSortDescending": "<span data-en=': activate to sort column descending' data-bm=': aktifkan untuk menyusun lajur menurun'>: activate to sort column descending</span>"
        },
        "select": {
            "rows": {
                "_": "<span data-en='%d rows selected' data-bm='%d baris dipilih'>%d rows selected</span>",
                "0": "",
                "1": "<span data-en='1 row selected' data-bm='1 baris dipilih'>1 row selected</span>"
            },
            "print": "<span data-en='Print' data-bm='Cetak'>Print</span>"
        }
    };
}

if ($.fn.dataTable) {
    $.extend(true, $.fn.dataTable.defaults, {
        language: getGlobalDataTableLanguage()
    });
}

$(document).on('draw.dt', function(e, settings) {
    if (typeof applyTranslations === 'function' && settings.nTableWrapper) {
        applyTranslations(settings.nTableWrapper);
    }
});

$(document).on('preInit.dt', function(e, settings) {
    if (settings.oInit && (settings.oInit.responsive === true || typeof settings.oInit.responsive === 'object')) {
        settings.oInit.responsive = {
            details: {
                renderer: function (api, rowIdx, columns) {
                    var data = $.map(columns, function (col) {
                        if (!col.hidden) return '';
                        var header = api.column(col.columnIndex).header();
                        var title = col.title;
                        if (!title || title === "undefined") {
                            title = header ? (header.getAttribute('data-en') || header.textContent.trim()) : "";
                        }
                        return '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                            '<td class="dtr-title fw-bold pe-2" style="font-weight: 600;">' + title + '</td>' +
                            '<td class="dtr-data">' + col.data + '</td>' +
                            '</tr>';
                    }).join('');

                    return data ? $('<table class="table table-sm table-borderless mb-0"/>').append(data) : false;
                }
            }
        };
    }
});

// Forcefully translate DataTables pagination buttons when language changes
document.addEventListener("lang-changed", function(e) {
    const lang = e.detail.lang;

    // Apply unified translation sweep globally
    if (typeof applyTranslations === 'function') {
        applyTranslations(document);
    }

    setTimeout(() => {
        const dtLabels = {
            en: { first: "First", last: "Last", next: "Next", previous: "Previous" },
            bm: { first: "Pertama", last: "Terakhir", next: "Seterusnya", previous: "Sebelumnya" }
        };
        const t = dtLabels[lang] || dtLabels.en;

        document.querySelectorAll(".page-link").forEach(el => {
            const txt = el.textContent.trim();
            if (txt.includes("First") || txt.includes("Pertama")) el.textContent = t.first;
            if (txt.includes("Previous") || txt.includes("Sebelumnya")) el.textContent = t.previous;
            if (txt.includes("Next") || txt.includes("Seterusnya")) el.textContent = t.next;
            if (txt.includes("Last") || txt.includes("Terakhir")) el.textContent = t.last;
        });
    }, 50);
});

$("#redirectProfile").on("click", function (e) {
    e.preventDefault();

    console.log("redirect ");

    window.location.href = "/profile";
});

export function getAuthUser() {
    return window.authUser ?? null;
}

const user = getAuthUser();


if (user) {
    if (user.type === "internal") {
        internalUserEcho();
    } else {
        publicUserEcho(user.uuid);
    }
}

export function formatTime(timestamp) {
    const utcDate = new Date(timestamp);

    // Malaysia Time (UTC+8)
    const malaysiaOffset = 8 * 60; // minutes
    const localTime = new Date(utcDate.getTime() + malaysiaOffset * 60 * 1000);

    const options = {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    };

    const formatted = new Intl.DateTimeFormat("en-GB", options).format(
        localTime,
    );

    return formatted;
}

export function initTooltips() {
    // remove old tooltip instances to avoid duplicates
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        if (bootstrap.Tooltip.getInstance(el)) {
            bootstrap.Tooltip.getInstance(el).dispose();
        }
        new bootstrap.Tooltip(el);
    });
}

export async function getCountry(code) {
    const res = await fetch(`/country/${code}`);
    return await res.json(); // return the actual country data
}

export async function getEntryPoint(id) {
    const res = await fetch(`/entry_point/${id}`);
    return await res.json(); // return the actual country data
}

export function showToast(message) {
    const toastEl = document.getElementById("editToast");
    const toastMessage = document.getElementById("toastMessage");
    const toastTime = document.getElementById("toastTime");

    toastMessage.innerText = message;
    toastTime.innerText = "just now";

    const toast = new bootstrap.Toast(toastEl, {
        delay: 7000,
    });

    toast.show();
}

export function notifyUser(message, editor = null) {
    // 🔔 PLAY SOUND
    const sound = document.getElementById("notificationSound");

    // Required for browser autoplay policy
    sound.currentTime = 0;
    sound.play().catch(() => {
        console.warn("Sound blocked until user interacts with page");
    });

    // 🍞 SHOW TOAST
    showToast(editor ? `${message} (by ${editor})` : message);
    notificationContent();
}

// notification();

let notificationInterval = null;

function refreshApplicationSidebarCounts() {
    if (window.authUser?.type !== "internal") return;
    fetchApplicationCount();
    fetchVerificationCount();
}

export function startNotificationPolling() {
    if (notificationInterval) return; // prevent duplicates

    notification(); // run immediately
    refreshApplicationSidebarCounts();
    notificationInterval = setInterval(() => {
        notification();
        refreshApplicationSidebarCounts();
    }, 10000);
}

export function stopNotificationPolling() {
    if (!notificationInterval) return;

    clearInterval(notificationInterval);
    notificationInterval = null;
}

document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
        stopNotificationPolling();
    } else {
        startNotificationPolling();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    startNotificationPolling();
    initInactivityTimeout();
    if (window.authUser?.type === 'internal') {
        fetchVerificationCount();
    }
});

export function fetchVerificationCount() {
    if (!$("#verificationCount").length) return;

    $.ajax({
        url: `/internal/verification_count`,
        method: "GET",
        success: function (data) {
            if (data.count > 0) {
                $("#verificationCount").html(
                    `<span class="badge bg-success text-white sidebar-nav-count-badge">${data.count}</span>`,
                );
                $("#userMgmtParentBadge").show();
            } else {
                $("#verificationCount").text("");
                $("#userMgmtParentBadge").hide();
            }
        },
    });
}

export function fetchApplicationCount() {
    if (!$("#importPermitCount").length) return;

    $.ajax({
        url: `/internal/application_count`,
        method: "GET",
        success: function (data) {
            const total =
                (data.permit || 0) +
                (data.inspection || 0) +
                (data.consignment || 0);

            if (data.permit > 0) {
                $("#importPermitCount").html(
                    `<span class="sidebar-count-row"><span>Import Permit</span><span class="badge bg-success text-white sidebar-nav-count-badge">${data.permit}</span></span>`,
                );
            } else {
                $("#importPermitCount").text("Import Permit");
            }

            if (data.inspection > 0) {
                $("#inspectionAppCount").html(
                    `<span class="sidebar-count-row"><span>Inspection Certificate</span><span class="badge bg-success text-white sidebar-nav-count-badge">${data.inspection}</span></span>`,
                );
            } else {
                $("#inspectionAppCount").text("Inspection Certificate");
            }

            if (data.consignment > 0) {
                $("#consignmentAppCount").html(
                    `<span class="sidebar-count-row"><span>Consignment Certificate</span><span class="badge bg-success text-white sidebar-nav-count-badge">${data.consignment}</span></span>`,
                );
            } else {
                $("#consignmentAppCount").text("Consignment Certificate");
            }

            if (total > 0) {
                $("#appListParentBadge").show();
            } else {
                $("#appListParentBadge").hide();
            }
        },
    });
}

if (window.authUser?.type === "public") {
    public_dashboard();
}

export function generateUUID() {
    if (crypto.randomUUID) {
        return crypto.randomUUID();
    }

    // fallback for older browsers
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === "x" ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

function languange() {
    (function () {
        var STORAGE_KEY = "qis_lang";
        var buttons = document.querySelectorAll(".lang-btn");
        function setLang(lang) {
            document.querySelectorAll("[data-en]").forEach(function (el) {
                // --- Handle data-title ---
                if (
                    el.hasAttribute("data-title-en") &&
                    el.hasAttribute("data-title-bm")
                ) {
                    el.setAttribute("data-title", text);

                    // Update wizard navigation if this is a wizard step
                    var stepIndex = el.getAttribute("data-step");
                    if (stepIndex !== null) {
                        // Support both .wz-nav and .wizard-nav classes
                        var navSteps = document.querySelectorAll(
                            ".wz-nav .wz-step, .wizard-nav .wizard-step",
                        );
                        navSteps.forEach(function (navStep) {
                            var navIndex =
                                Array.from(navSteps).indexOf(navStep);
                            var stepNum = parseInt(stepIndex) - 1; // steps are 1-indexed
                            if (navIndex === stepNum) {
                                // Find the span that contains the text (second span, after the dot)
                                var spans = navStep.querySelectorAll("span");
                                if (spans.length > 1) {
                                    spans[1].innerHTML = text;
                                } else if (spans.length === 1) {
                                    spans[0].innerHTML = text;
                                }
                            }
                        });
                    }
                    var titleText =
                        el.getAttribute("data-" + lang + "-title") ||
                        el.getAttribute("data-title-en");
                    if (titleText) {
                        el.setAttribute("data-title", titleText);
                    }
                }

                // --- Handle placeholder ---
                var text = el.getAttribute("data-" + lang);
                if (text === null) return;

                if (el.getAttribute("data-i18n-attr") === "placeholder") {
                    el.setAttribute("placeholder", text);
                } else {
                    if (el.classList.contains("wizard-step")) {
                        var span = el.querySelector("span:last-child");
                        if (span) {
                            span.textContent = text;
                        }
                    }
                    el.textContent = text;
                }
            });

            // --- Sync wizard-nav dot labels from wizard-content step titles ---
            // Runs once per language switch (not per element), and works even
            // though these step divs have no data-en/data-bm of their own.
            document
                .querySelectorAll(".wizard-content .wizard-step")
                .forEach(function (stepEl) {
                    var step = stepEl.getAttribute("data-step");
                    var titleText =
                        stepEl.getAttribute("data-title-" + lang) ||
                        stepEl.getAttribute("data-title-en");
                    if (!titleText) return;

                    stepEl.setAttribute("data-title", titleText);

                    var navSpan = document.querySelector(
                        '.wizard-nav .wizard-step[data-step="' +
                            step +
                            '"] span:last-child',
                    );
                    if (navSpan) {
                        navSpan.textContent = titleText;
                    }
                });

            updateWizardButtons(lang);

            buttons.forEach(function (btn) {
                btn.classList.toggle(
                    "active",
                    btn.getAttribute("data-lang") === lang,
                );
            });

            document.documentElement.setAttribute(
                "lang",
                lang === "bm" ? "ms" : "en",
            );

            try {
                localStorage.setItem(STORAGE_KEY, lang);
            } catch (e) {}

            document.dispatchEvent(new CustomEvent("lang-changed", { detail: { lang: lang } }));
        }

        buttons.forEach(function (btn) {
            btn.addEventListener("click", function () {
                setLang(btn.getAttribute("data-lang"));
            });
        });

        var savedLang = "en";
        try {
            savedLang = localStorage.getItem(STORAGE_KEY) || "en";
        } catch (e) {}
        setLang(savedLang);
    })();
}
export function applyTranslations(container) {
    const lang = localStorage.getItem("qis_lang") || "en";
    const elements = container.querySelectorAll("[data-en]");

    elements.forEach(function (el) {
        // Handle data-title
        if (
            el.hasAttribute("data-title-en") &&
            el.hasAttribute("data-title-bm")
        ) {
            var titleText =
                el.getAttribute("data-" + lang + "-title") ||
                el.getAttribute("data-title-en");
            if (titleText) {
                el.setAttribute("data-title", titleText);
            }
        }

        const text = el.getAttribute("data-" + lang);
        if (text === null) return;

        if (el.getAttribute("data-i18n-attr") === "placeholder") {
            el.setAttribute("placeholder", text);
        } else {
            // If it's a wizard step, update the displayed label as well
            if (el.classList.contains("wizard-step")) {
                var span = el.querySelector("span:last-child");
                if (span) {
                    span.textContent = text;
                }
            }
            el.textContent = text;
        }
    });

    container.querySelectorAll(".ipv-role").forEach(el => {
        const text = el.getAttribute("data-original") || el.textContent.trim();
        if (!el.hasAttribute("data-original")) el.setAttribute("data-original", text);
        el.textContent = lang === "bm" ? getRoleBm(text) : text;
    });

    container.querySelectorAll(".ipv-permission, .ipv-more").forEach(el => {
        const text = el.getAttribute("data-original") || el.textContent.trim();
        if (!el.hasAttribute("data-original")) el.setAttribute("data-original", text);
        el.textContent = lang === "bm" ? getPermissionBm(text) : text;
    });

    // Translating timeline items without data attributes
    container.querySelectorAll(".ipv-timeline-title").forEach(el => {
        const text = el.getAttribute("data-original") || el.textContent.trim();
        if (!el.hasAttribute("data-original")) el.setAttribute("data-original", text);
        el.textContent = lang === "bm" ? getStatusBm(text) : text;
    });

    container.querySelectorAll(".ipv-timeline-desc").forEach(el => {
        const text = el.getAttribute("data-original") || el.textContent.trim();
        if (!el.hasAttribute("data-original")) el.setAttribute("data-original", text);
        el.textContent = lang === "bm" ? getRemarkBm(text) : text;
    });

    // Forcefully translate DataTables pagination buttons by matching their text content
    // Use setTimeout because DataTables v2 might render pagination *after* draw.dt fires.
    setTimeout(() => {
        const dtLabels = {
            en: { first: "First", last: "Last", next: "Next", previous: "Previous" },
            bm: { first: "Pertama", last: "Terakhir", next: "Seterusnya", previous: "Sebelumnya" }
        };
        const t = dtLabels[lang] || dtLabels.en;

        container.querySelectorAll(".page-link").forEach(el => {
            // Check includes() just to be safe against spaces or non-breaking chars
            const txt = el.textContent.trim();
            if (txt.includes("First") || txt.includes("Pertama")) el.textContent = t.first;
            if (txt.includes("Previous") || txt.includes("Sebelumnya")) el.textContent = t.previous;
            if (txt.includes("Next") || txt.includes("Seterusnya")) el.textContent = t.next;
            if (txt.includes("Last") || txt.includes("Terakhir")) el.textContent = t.last;
        });
    }, 50);
}

function updateWizardButtons(lang) {
    const labels = {
        en: { prev: "Prev", next: "Next", submit: "Submit" },
        bm: { prev: "Kembali", next: "Seterusnya", submit: "Hantar" },
    };
    const t = labels[lang] || labels.en;
    document
        .querySelectorAll(".wizard-btn.prev")
        .forEach((el) => (el.textContent = t.prev));
    document
        .querySelectorAll(".wizard-btn.next")
        .forEach((el) => (el.textContent = t.next));
    document
        .querySelectorAll(".wizard-btn.finish, .wizard-btn.submit")
        .forEach((el) => (el.textContent = t.submit));
}

languange();


// ─── Helper ──────────────────────────────────────────────
export function getCurrentLang() {
    try {
        return localStorage.getItem("qis_lang") || "en";
    } catch {
        return "en";
    }
}