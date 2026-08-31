import { applyTranslations } from "../../app.js";
import {
    PERMITS,
    STAGE_CONFIG,
    PERMIT_STATUS_CONFIG,
    escapeHtml,
    money,
    renderAttachmentList,
} from "./test1.js";

// ---------------------------------------------------------------
// Per-permit activity log (keyed by permit_number)
// // TODO verify: replace with real per-permit activity once your API
// exposes it (e.g. permit._raw.activity_log) — see the fallback at the
// bottom of openPermitDetail() which already tries that first.
// ---------------------------------------------------------------
const PERMIT_ACTIVITY = {};

// ---------------------------------------------------------------
// Permit Detail Offcanvas
// ---------------------------------------------------------------
let permitDetailOffcanvas = null;

// Add this near the top of your module
function getCurrentLang() {
    try {
        return localStorage.getItem("qis_lang") || "en";
    } catch {
        return "en";
    }
}

function getStatusText(status) {
    const cfg = PERMIT_STATUS_CONFIG[status] || PERMIT_STATUS_CONFIG.queued;
    const lang = getCurrentLang();
    return lang === "bm" ? cfg.bm : cfg.en;
}

export function initPermitDetailOffcanvas() {
    const el = document.getElementById("permitDetailOffcanvas");
    if (el && !permitDetailOffcanvas) {
        permitDetailOffcanvas = new bootstrap.Offcanvas(el, {
            backdrop: true,
            keyboard: true,
            scroll: false,
        });
        el.addEventListener("show.bs.offcanvas", () => {
            const detailsTab = document.getElementById("pd-details-tab");
            if (detailsTab)
                bootstrap.Tab.getOrCreateInstance(detailsTab).show();
        });
    }
}

// Builds the payment CTA block shown right under the status badge —
// this is the whole point: whoever opens a permit's details should
// immediately see "this needs paying" or "this is ready to print",
// not have to go hunting in a separate tab.
function paymentCtaHtml(permit) {
    const status = permit.status;
    const isOwner = window.authUser?.type === 'public';
    const isStaff = (window.authUser?.roles || []).some((r) =>
        ['admin', 'officer', 'superadmin'].includes(r.name)
    );

    if (['pending for payment', 'payment failed'].includes(status) && isOwner) {
        const failed = status === 'payment failed';
        return `
            <div class="pd-payment-cta ${failed ? 'is-danger' : ''}">
                <div class="pd-payment-cta-text">
                    <i class="bi ${failed ? 'bi-exclamation-octagon' : 'bi-credit-card'}"></i>
                    <div>
                        <strong data-en="${failed ? 'Payment failed — please retry' : 'Payment required'}" 
                                data-bm="${failed ? 'Pembayaran gagal — sila cuba semula' : 'Pembayaran diperlukan'}">
                            ${failed ? 'Payment failed — please retry' : 'Payment required'}
                        </strong>
                        <span data-en="This permit will not be issued until payment is completed." 
                              data-bm="Permit ini tidak akan dikeluarkan sehingga pembayaran selesai.">
                            This permit will not be issued until payment is completed.
                        </span>
                    </div>
                </div>
                <button type="button" class="ipv-btn-primary is-pay pd-pay-now" data-permit="${permit.id}" data-value="12">
                    <i class="bi bi-credit-card"></i> <span data-en="Pay" data-bm="Bayar">Pay</span> RM 12 <span data-en="Now" data-bm="Sekarang">Now</span>
                </button>
            </div>
        `;
    }

    if (['paid', 'completed'].includes(status) && (isOwner || isStaff)) {
        const slug = (permit.permit_number || '').replaceAll('/', '');
        return `
            <div class="pd-payment-cta is-success">
                <div class="pd-payment-cta-text">
                    <i class="bi bi-check-circle"></i>
                    <div>
                        <strong data-en="Payment complete" data-bm="Pembayaran selesai">Payment complete</strong>
                        <span data-en="This permit is active and ready to print." 
                              data-bm="Permit ini aktif dan sedia untuk dicetak.">
                            This permit is active and ready to print.
                        </span>
                    </div>
                </div>
                <button type="button" class="ipv-btn-primary generatePermit" data-permit="${slug}">
                    <i class="bi bi-download"></i> <span data-en="Print / Download Permit" data-bm="Muat Turun Permit">Print / Download Permit</span>
                </button>
            </div>
        `;
    }

    return '';
}
export function openPermitDetail(permitId) {
    const permit = PERMITS.find((p) => String(p.id) === String(permitId));
    if (!permit) {
        console.warn(`Permit with id ${permitId} not found.`);
        return;
    }

    const cfg =
        PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
    const detail = permit.consignment_detail;

    console.log("permits details", permit);

    // Header
    document.getElementById("permitDetailOffcanvasLabel").textContent =
        permit.consignment_detail.item_name;
    const badge = document.getElementById("pdBadge");
    badge.textContent = getStatusText(permit.status);
    badge.className = `ipv-badge ms-2 is-${cfg.color}`;

   const agreementBanner = permit.agreedAt
    ? `<div class="alert alert-success mb-3 d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2"></i>
        <div>
            <strong>
                <span data-en="Declaration Confirmed" data-bm="Pengisytiharan Disahkan">Declaration Confirmed</span>
            </strong>
            <div class="small text-muted">
                <span data-en="Agreed on:" data-bm="Dipersetujui pada:">Agreed on:</span> ${permit.agreedAt}
            </div>
        </div>
    </div>`
    : `<div class="alert alert-warning mb-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>
            <span data-en="Pending Agreement" data-bm="Menunggu Persetujuan">Pending Agreement</span>
        </strong>
        - <span data-en="User has not confirmed this item yet." data-bm="Pengguna belum mengesahkan item ini lagi.">User has not confirmed this item yet.</span>
    </div>`;

    // ---- Details tab ----
    const attachListId = `pd-attach-${permit.permit_number}`;

    document.getElementById("pdDetailsContent").innerHTML = `
        ${paymentCtaHtml(permit)}

        ${agreementBanner}

        <div class="pd-section-label mb-2" data-en="Consignment Info" data-bm="Info Konsainan">Consignment Info</div>
        <div class="p-2 row" style="background: var(--gray-1); border: 1px solid var(--default-border); border-radius: 0.6rem;">
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm d-none  avatar-rounded bd-gray-500"><i class="fa-solid fa-tag"></i></span>
                        <span data-en="Item Name:" data-bm="Nama Item:">Item Name:</span>
                    </strong> 
                    <span class="text-break">${escapeHtml(detail.item_name)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm d-none  avatar-rounded bd-gray-500"><i class="fa-solid fa-layer-group"></i></span>
                        <span data-en="Category:" data-bm="Kategori:">Category:</span>
                    </strong> 
                    <span class="text-break">${escapeHtml(detail.category)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm d-none  avatar-rounded bd-gray-500"><i class="fa-solid fa-scale-balanced"></i></span>
                        <span data-en="Quantity:" data-bm="Kuantiti:">Quantity:</span>
                    </strong> 
                    <span class="text-break">${permit.quantity.toLocaleString()} ${escapeHtml(permit.unit_measurement)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm d-none  avatar-rounded bd-gray-500"><i class="fa-solid fa-money-bill"></i></span>
                        <span data-en="Value:" data-bm="Nilai:">Value:</span>
                    </strong> 
                    <span class="text-break">RM ${money(permit.value)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm d-none  avatar-rounded bd-gray-500"><i class="fa-solid fa-pen-fancy"></i></span>
                        <span data-en="Purpose:" data-bm="Tujuan:">Purpose:</span>
                    </strong> 
                    <span class="text-break">${escapeHtml(permit.purpose)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm d-none  avatar-rounded bd-gray-500"><i class="fa-solid fa-gear"></i></span>
                        <span data-en="Usage:" data-bm="Kegunaan:">Usage:</span>
                    </strong> 
                    <span class="text-break">${escapeHtml(detail.usage)}</span>
                </p>
            </div>
            <div class="col-12">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm d-none  d-none  avatar-rounded bd-gray-500"><i class="fa-solid fa-file-contract"></i></span>
                        <span data-en="Permit Number:" data-bm="No. Permit:">Permit Number:</span>
                    </strong> 
                    <span class="text-break">${escapeHtml(permit.permit_number)}</span>
                </p>
            </div>
        </div>

        ${
            permit.remark
                ? `
            <div class="pd-section-label mt-4" data-en="Permit Remark" data-bm="Catatan Permit">Permit Remark</div>
            <div class="ipv-permit-remark is-${cfg.color}">
                <i class="bi bi-info-circle"></i>
                <span>${escapeHtml(permit.remark)}</span>
            </div>
        `
                : ""
        }

       ${ 
            permit.conditions 
                ? `
            <div class="pd-section-label mt-4" data-en="Conditions" data-bm="Syarat">Conditions</div>
            <div class="ipv-condition-item">
                <span>${permit.conditions}</span>
            </div>
        `
                : ""
        }

        <div class="pd-section-label mt-4" data-en="Attachments" data-bm="Lampiran">Attachments (${permit.attachments.length})</div>
        <div class="ipv-attach-list" id="${attachListId}"></div>
    `;

    const attachContainer = document.getElementById(attachListId);
    renderAttachmentList(
        attachContainer,
        permit.attachments,
        permit.attachments.length,
    );

    // Apply translations to the newly rendered details
    const detailsContainer = document.getElementById("pdDetailsContent");
    if (detailsContainer) {
        applyTranslations(detailsContainer);
    }

    // ---- Activity tab ----
    // Prefer real per-permit activity from the API if it's there, fall
    // back to the (currently empty) static map above.
    const log =
        permit._raw?.activity_log ||
        PERMIT_ACTIVITY[permit.permit_number] ||
        [];
    const timelineEl = document.getElementById("pdActivityTimeline");
    if (!log.length) {
        timelineEl.innerHTML =
            '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity recorded yet.</p></div>';
    } else {
        timelineEl.innerHTML = log
            .map((entry) => {
                console.log('Rendering activity entry:', entry);
                const stageCfg =
                    STAGE_CONFIG[entry.stage] || STAGE_CONFIG.email;
                return `
                <div class="ipv-timeline-item">
                    <div class="ipv-timeline-icon is-${stageCfg.color}"><i class="bi ${stageCfg.icon}"></i></div>
                    <div class="ipv-timeline-body">
                        <div>
                            <div class="ipv-timeline-title">${escapeHtml(entry.title)}</div>
                            <p class="ipv-timeline-desc">${escapeHtml(entry.description)}</p>
                        </div>
                        <span class="ipv-timeline-time">${escapeHtml(entry.time)}</span>
                    </div>
                </div>
            `;
            })
            .join("");
    }

    // Reinitialize offcanvas to ensure it's properly set up
    if (!permitDetailOffcanvas) {
        initPermitDetailOffcanvas();
    }
    // Always get a fresh instance to ensure it can be shown again
    const offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(
        document.getElementById("permitDetailOffcanvas"),
    );
    offcanvasInstance.show();
}

// Listen for language changes and re-apply translations to offcanvas if visible
document.addEventListener("storage", (e) => {
    if (e.key === "qis_lang") {
        const offcanvasEl = document.getElementById("permitDetailOffcanvas");
        if (offcanvasEl && offcanvasEl.classList.contains("show")) {
            // Re-apply translations to the currently visible offcanvas
            const detailsContainer =
                document.getElementById("pdDetailsContent");
            if (detailsContainer) {
                applyTranslations(detailsContainer);
            }
            const activityContainer =
                document.getElementById("pdActivityTimeline");
            if (activityContainer) {
                applyTranslations(activityContainer);
            }
        }
    }
});

// Import these functions from test1.js or define them here
const attachmentRegistry = new Map();
let attachmentSeq = 0;
