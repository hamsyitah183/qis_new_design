import {
    PERMITS,
    STAGE_CONFIG,
    PERMIT_STATUS_CONFIG,
    escapeHtml,
    money,
    renderAttachmentList,
} from './test1.js';
// ---------------------------------------------------------------
// Per-permit activity log (keyed by permit_number)
// ---------------------------------------------------------------
const PERMIT_ACTIVITY = {
    'PMT-1201': [
        { stage: 'technical_review', title: 'Permit Queued for Review',    description: 'PMT-1201 entered the officer\'s review queue.', time: '13 May 2025, 2:42 PM' },
        { stage: 'permit_approved',  title: 'Permit Approved',             description: 'Reviewed and approved by Ahmad Zulkifli.', time: '14 May 2025, 11:05 AM' },
        { stage: 'awaiting_payment', title: 'Invoice Generated',           description: 'Invoice issued for RM 2,480.00.', time: '14 May 2025, 11:26 AM' },
        { stage: 'payment',          title: 'Payment Submitted',           description: 'Applicant submitted payment.', time: '15 May 2025, 4:05 PM' },
        { stage: 'permit_approved',  title: 'Permit Issued',               description: 'Payment authorized. Permit is now active until 16 Nov 2025.', time: '16 May 2025, 9:00 AM' },
    ],
    'PMT-1202': [
        { stage: 'technical_review', title: 'Permit Queued for Review',    description: 'PMT-1202 entered the officer\'s review queue.', time: '13 May 2025, 2:43 PM' },
        { stage: 'permit_rejected',  title: 'Permit Rejected',             description: 'Rejected — annual import quota for this species has been exhausted.', time: '14 May 2025, 11:20 AM' },
        { stage: 'email',            title: 'Rejection Notice Sent',       description: 'Applicant notified of rejection via email.', time: '14 May 2025, 11:22 AM' },
    ],
    'PMT-1203': [
        { stage: 'technical_review', title: 'Permit Queued for Review',    description: 'PMT-1203 entered the officer\'s review queue.', time: '13 May 2025, 2:44 PM' },
        { stage: 'permit_approved',  title: 'Permit Approved',             description: 'Reviewed and approved by Ahmad Zulkifli.', time: '14 May 2025, 3:10 PM' },
        { stage: 'awaiting_payment', title: 'Invoice Generated',           description: 'Invoice issued for RM 9,150.00.', time: '14 May 2025, 3:15 PM' },
        { stage: 'payment',          title: 'Payment Submitted',           description: 'Applicant submitted payment for PMT-1203.', time: '15 May 2025, 4:06 PM' },
        { stage: 'payment_processing', title: 'Payment Pending Authorization', description: 'Awaiting bank authorization.', time: '15 May 2025, 4:11 PM' },
    ],
    'PMT-1204': [
        { stage: 'doc_verification', title: 'Permit Queued for Review',    description: 'PMT-1204 entered the officer\'s review queue.', time: '13 May 2025, 2:45 PM' },
    ],
    'PMT-1205': [
        { stage: 'technical_review', title: 'Permit Queued for Review',    description: 'PMT-1205 entered the officer\'s review queue.', time: '13 May 2025, 2:46 PM' },
        { stage: 'permit_approved',  title: 'Permit Approved',             description: 'Reviewed and approved. Awaiting payment.', time: '14 May 2025, 4:00 PM' },
    ],
    'PMT-1206': [
        { stage: 'technical_review', title: 'Permit Queued for Review',    description: 'PMT-1206 entered the officer\'s review queue.', time: '13 May 2025, 2:47 PM' },
        { stage: 'permit_approved',  title: 'Permit Approved',             description: 'Reviewed and approved. Invoice issued.', time: '14 May 2025, 4:20 PM' },
        { stage: 'payment',          title: 'Payment Submitted',           description: 'Applicant submitted payment.', time: '15 May 2025, 5:00 PM' },
        { stage: 'payment_processing', title: 'Payment Declined',          description: 'Payment declined by issuing bank. Applicant notified.', time: '15 May 2025, 5:30 PM' },
    ],
};

// ---------------------------------------------------------------
// Permit Detail Offcanvas
// ---------------------------------------------------------------
let permitDetailOffcanvas = null;

export function initPermitDetailOffcanvas() {
    const el = document.getElementById('permitDetailOffcanvas');
    if (el && !permitDetailOffcanvas) {
        permitDetailOffcanvas = new bootstrap.Offcanvas(el, {
            backdrop: true,
            keyboard: true,
            scroll: false,
        });
        // Reset Bootstrap tab to Details whenever offcanvas opens
        el.addEventListener('show.bs.offcanvas', () => {
            const detailsTab = document.getElementById('pd-details-tab');
            if (detailsTab) bootstrap.Tab.getOrCreateInstance(detailsTab).show();
        });
    }
}

export function openPermitDetail(permitNumber) {
    const permit = PERMITS.find(p => p.permit_number === permitNumber);
    if (!permit) return;

    const cfg = PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
    const detail = permit.consignment_detail;

    // Header
    document.getElementById('permitDetailOffcanvasLabel').textContent = permit.permit_number;
    const badge = document.getElementById('pdBadge');
    badge.textContent = cfg.en;
    badge.className = `ipv-badge ms-2 is-${cfg.color}`;

    // ---- Details tab ----
    const rows = [
        { label: 'Category',         value: detail.category },
        { label: 'Item',             value: detail.item_name },
        { label: 'Usage',            value: detail.usage },
        { label: 'Purpose',          value: permit.purpose },
        { label: 'Quantity',         value: `${permit.quantity.toLocaleString()} ${permit.unit_measurement}` },
        { label: 'Declared Value',   value: `RM ${money(permit.value)}` },
    ];

    // Use a listId scoped to this permit for the attachment viewer
    const attachListId = `pd-attach-${permit.permit_number}`;

    document.getElementById('pdDetailsContent').innerHTML = `
        <div class="pd-section-label">Consignment Info</div>
        <div class="pd-info-grid">
            ${rows.map(r => `
                <div class="pd-info-cell">
                    <div class="pd-cell-label">${escapeHtml(r.label)}</div>
                    <div class="pd-cell-value">${escapeHtml(r.value)}</div>
                </div>
            `).join('')}
        </div>

        <div class="pd-section-label mt-4">Permit Remark</div>
        <div class="ipv-permit-remark is-${cfg.color}">
            <i class="bi bi-info-circle"></i>
            <span>${escapeHtml(permit.remark)}</span>
        </div>

        <div class="pd-section-label mt-4">Conditions (${permit.conditions.length})</div>
        ${permit.conditions.map(c => `
            <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
        `).join('')}

        <div class="pd-section-label mt-4">Attachments (${permit.attachments.length})</div>
        <div class="ipv-attach-list" id="${attachListId}"></div>
    `;

    // Render attachment chips — they'll auto-wire to the attachment viewer via delegation
    const attachContainer = document.getElementById(attachListId);
    renderAttachmentList(attachContainer, permit.attachments, permit.attachments.length);

    // ---- Activity tab ----
    const log = PERMIT_ACTIVITY[permit.permit_number] || [];
    const timelineEl = document.getElementById('pdActivityTimeline');
    if (!log.length) {
        timelineEl.innerHTML = '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity recorded yet.</p></div>';
    } else {
        timelineEl.innerHTML = log.map(entry => {
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
        }).join('');
    }

    if (!permitDetailOffcanvas) initPermitDetailOffcanvas();
    permitDetailOffcanvas.show();
}