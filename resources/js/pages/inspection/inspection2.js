import { applyTranslations } from "../../app.js";
import {
    PERMITS,
    STAGE_CONFIG,
    PERMIT_STATUS_CONFIG,
    escapeHtml,
    money,
    renderAttachmentList,
} from "./inspection1.js";

let permitDetailOffcanvas = null;

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

// Inspection certificates aren't paid/printed individually — payment and
// printing happen at the application level (bulk action bar + sidebar
// print button in inspection1.js). The only per-permit action here is
// Reapply, when the item was rejected.
function reapplyCtaHtml(permit) {
    const isOwner = window.authUser?.type === 'public';
    if (permit.status !== 'rejected' || !isOwner) return '';

    const lang = getCurrentLang();
    return `
        <div class="pd-payment-cta">
            <div class="pd-payment-cta-text">
                <i class="bi bi-arrow-repeat"></i>
                <div>
                    <strong data-en="Item rejected" data-bm="Item ditolak">Item rejected</strong>
                    <span data-en="You can correct and resubmit this item."
                          data-bm="Anda boleh membetulkan dan menghantar semula item ini.">
                        You can correct and resubmit this item.
                    </span>
                </div>
            </div>
            <button type="button" class="ipv-btn-primary is-warning reapply" data-permit="${permit.id}">
                <i class="bi bi-arrow-repeat"></i> <span data-en="Reapply" data-bm="Mohon Semula">Reapply</span>
            </button>
        </div>
    `;
}

export function openPermitDetail(permitId) {
    // permitId is now the numeric ID (not the permit number)
    const permit = PERMITS.find((p) => String(p.id) === String(permitId));
    if (!permit) {
        console.warn(`Permit with id ${permitId} not found.`);
        return;
    }

    const cfg = PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
    const detail = permit.consignment_detail;

    document.getElementById("permitDetailOffcanvasLabel").textContent = detail.item_name;
    const badge = document.getElementById("pdBadge");
    badge.textContent = getStatusText(permit.status);
    badge.className = `ipv-badge ms-2 is-${cfg.color}`;

    // ─── Use permit.id for the attachment list ID ──────────────────────
    const attachListId = `pd-attach-${permit.id}`;

    document.getElementById("pdDetailsContent").innerHTML = `
        ${reapplyCtaHtml(permit)}

        <div class="pd-section-label mb-2" data-en="Inspection Info" data-bm="Info Pemeriksaan">Inspection Info</div>
        <div class="p-2 row" style="background: var(--gray-1); border: 1px solid var(--default-border); border-radius: 0.6rem;">
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500 d-none"><i class="fa-solid fa-tag"></i></span>
                        <span data-en="Item Name:" data-bm="Nama Item:">Item Name:</span>
                    </strong>
                    <span class="text-break">${escapeHtml(detail.item_name)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500 d-none"><i class="fa-solid fa-scale-balanced"></i></span>
                        <span data-en="Quantity:" data-bm="Kuantiti:">Quantity:</span>
                    </strong>
                    <span class="text-break">${permit.quantity.toLocaleString()} ${escapeHtml(permit.unit_measurement)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500 d-none"><i class="fa-solid fa-money-bill"></i></span>
                        <span data-en="Value:" data-bm="Nilai:">Value:</span>
                    </strong>
                    <span class="text-break">RM ${money(permit.value)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500 d-none"><i class="fa-solid fa-pen-fancy"></i></span>
                        <span data-en="Purpose:" data-bm="Tujuan:">Purpose:</span>
                    </strong>
                    <span class="text-break">${escapeHtml(detail.purpose)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500 d-none"><i class="fa-solid fa-gear"></i></span>
                        <span data-en="Uses:" data-bm="Kegunaan:">Uses:</span>
                    </strong>
                    <span class="text-break">${escapeHtml(detail.uses)}</span>
                </p>
            </div>
            <div class="col-12">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500 d-none"><i class="fa-solid fa-file-contract"></i></span>
                        <span data-en="Permit Number:" data-bm="No. Permit:">Permit Number:</span>
                    </strong>
                    <span class="text-break">${escapeHtml(permit.permit_number)}</span>
                </p>
            </div>
        </div>

        ${permit.remark ? `
            <div class="pd-section-label mt-4" data-en="Remark" data-bm="Catatan">Remark</div>
            <div class="ipv-permit-remark is-${cfg.color}">
                <i class="bi bi-info-circle"></i>
                <span>${escapeHtml(permit.remark)}</span>
            </div>
        ` : ''}

        <div class="pd-section-label mt-4" data-en="Attachments" data-bm="Lampiran">Attachments (${permit.attachments.length})</div>
        <div class="ipv-attach-list" id="${attachListId}"></div>
    `;

    const attachContainer = document.getElementById(attachListId);
    renderAttachmentList(attachContainer, permit.attachments, permit.attachments.length);

    const detailsContainer = document.getElementById("pdDetailsContent");
    if (detailsContainer) applyTranslations(detailsContainer);

    // ---- Activity tab ----
    // [TODO] no confirmed per-permit activity log endpoint/field for
    // Inspection items yet — falls back to an empty timeline. Wire this to
    // permit._raw.activity_log (or equivalent) once the API exposes it.
    const log = permit._raw?.activity_log || [];
    const timelineEl = document.getElementById("pdActivityTimeline");
    if (!log.length) {
        timelineEl.innerHTML = '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity recorded yet.</p></div>';
    } else {
        timelineEl.innerHTML = log.map((entry) => {
            const stageCfg = STAGE_CONFIG[entry.stage] || STAGE_CONFIG.email;
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
        }).join("");
    }

    if (!permitDetailOffcanvas) initPermitDetailOffcanvas();
    const offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(
        document.getElementById("permitDetailOffcanvas"),
    );
    offcanvasInstance.show();
}

document.addEventListener("storage", (e) => {
    if (e.key === "qis_lang") {
        const offcanvasEl = document.getElementById("permitDetailOffcanvas");
        if (offcanvasEl && offcanvasEl.classList.contains("show")) {
            const detailsContainer = document.getElementById("pdDetailsContent");
            if (detailsContainer) applyTranslations(detailsContainer);
            const activityContainer = document.getElementById("pdActivityTimeline");
            if (activityContainer) applyTranslations(activityContainer);
        }
    }
});