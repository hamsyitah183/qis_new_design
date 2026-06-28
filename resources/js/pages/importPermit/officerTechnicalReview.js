/**
 * officerTechnicalReview.js
 * ------------------------------------------------------------------
 * Technical Review page — the officer reviews each permit item
 * individually, approving or rejecting with a mandatory reason for
 * rejections. Once every item has a decision the "Finalise" bar
 * appears and the officer can forward approved items to payment.
 *
 * Per-permit decision lives inside each accordion item's body,
 * in an action bar that replaces itself with a decided-state chip
 * once the officer has acted.
 *
 * Application status flow driven here:
 *   technical_review → awaiting_payment  (at least 1 approved)
 *   technical_review → completed         (all rejected — nothing to pay)
 */

// ---------------------------------------------------------------
// Config
// ---------------------------------------------------------------

const STAGE_ORDER = [
    'submitted', 'doc_verification', 'technical_review',
    'awaiting_payment', 'payment_processing', 'completed',
];

const STAGE_CONFIG = {
    submitted:           { en: 'Submitted',             icon: 'bi-send-check',         color: 'info'      },
    doc_verification:    { en: 'Document Verification', icon: 'bi-file-earmark-check', color: 'secondary' },
    returned:            { en: 'Returned / Rejected',   icon: 'bi-arrow-return-left',  color: 'danger'    },
    technical_review:    { en: 'Technical Review',      icon: 'bi-clipboard-check',    color: 'primary'   },
    awaiting_payment:    { en: 'Awaiting Payment',      icon: 'bi-hourglass-split',    color: 'warning'   },
    payment_processing:  { en: 'Payment Processing',    icon: 'bi-credit-card',        color: 'orange'    },
    completed:           { en: 'Completed',             icon: 'bi-check-circle',       color: 'success'   },
    email:               { en: 'Notification Sent',     icon: 'bi-envelope-check',     color: 'gray'      },
    permit_approved:     { en: 'Permit Approved',       icon: 'bi-check-circle',       color: 'success'   },
    permit_rejected:     { en: 'Permit Rejected',       icon: 'bi-x-circle',           color: 'danger'    },
};

const PERMIT_STATUS_CONFIG = {
    queued:    { en: 'Queued for Review',            color: 'info'    },
    approved:  { en: 'Approved',                     color: 'success' },
    rejected:  { en: 'Rejected',                     color: 'danger'  },
};

const OFFICER_NAME = 'Ahmad Zulkifli';

// ---------------------------------------------------------------
// Dummy data — application at Technical Review stage
// ---------------------------------------------------------------

const APPLICATION = {
    application_id: 'IP-2025-00456',
    type: 'Import Permit',
    status: 'technical_review',
    status_duration: '1 hour',
    returned_reason: null,
    tags: [
        { label: 'Category 1', color: 'primary' },
        { label: 'Repeat Importer', color: 'secondary' },
    ],
    submitted_by: 'Tan Wei Ling',
    submitted_at: '12 May 2025, 9:02 AM',
    assigned_officer: OFFICER_NAME,
    sla_due: 'Due in 2 days',
    eta: '20 May 2025',
    transport_type: 'Sea Freight',
    entry_point: 'Kota Kinabalu Port',
    entry_point_description: 'Main seaport entry point for Sabah-bound consignments.',
    importer: {
        name: 'Borneo Fresh Trading Sdn Bhd',
        phone: '(088) 244 511',
        email: 'admin@borneofresh.my',
        address: 'Lot 12, Kolombong Industrial Park, 88450 Kota Kinabalu',
        country: 'Malaysia',
    },
    exporter: {
        name: 'Golden Harvest Pte Ltd',
        phone: '+65 6221 4480',
        email: 'export@goldenharvest.sg',
        address: '21 Tanjong Penjuru Crescent',
        country: 'Singapore',
    },
    attachments: [
        { name: 'Invoice_IP-2025-00456.pdf', size: '420 KB', path: '/consignment/attachment/10', mime: 'application/pdf' },
        { name: 'Packing_List.xlsx',         size: '88 KB'  },
        { name: 'Bill_of_Lading.pdf',        size: '310 KB', path: '/consignment/attachment/8',  mime: 'application/pdf' },
    ],
};

// decision: null | 'approved' | 'rejected'
// rejectionReason: string | null
const PERMITS = [
    {
        permit_number: 'PMT-1201',
        consignment_detail: { category: 'Fresh Produce', item_name: 'Fresh Fruit — Corn', usage: 'Commercial Sale' },
        quantity: 1200, unit_measurement: 'KG', purpose: 'Commercial Sale', value: 2480,
        status: 'queued', decision: null, rejectionReason: null,
        remark: 'Awaiting officer evaluation.',
        attachments: [
            { name: 'Phytosanitary_Cert.pdf', size: '210 KB', path: '/consignment/attachment/5', mime: 'application/pdf' },
            { name: 'Fumigation_Cert.pdf',    size: '180 KB' },
        ],
        conditions: [
            'Must be accompanied by a valid phytosanitary certificate.',
            'Subject to inspection at point of entry.',
        ],
        permitActivity: [
            { stage: 'doc_verification', title: 'Documents Verified', description: 'All documents verified by clerk Nurul Aisyah.', time: '13 May 2025, 8:50 AM' },
        ],
    },
    {
        permit_number: 'PMT-1202',
        consignment_detail: { category: 'Live Animals', item_name: 'Live Ornamental Fish', usage: 'Re-export' },
        quantity: 300, unit_measurement: 'Unit', purpose: 'Re-export', value: 650,
        status: 'queued', decision: null, rejectionReason: null,
        remark: 'Awaiting officer evaluation.',
        attachments: [
            { name: 'Health_Cert.pdf', size: '195 KB' },
        ],
        conditions: [
            'Requires a valid health certificate from country of origin.',
            'Subject to quarantine inspection on arrival.',
        ],
        permitActivity: [
            { stage: 'doc_verification', title: 'Documents Verified', description: 'All documents verified by clerk Nurul Aisyah.', time: '13 May 2025, 8:52 AM' },
        ],
    },
    {
        permit_number: 'PMT-1203',
        consignment_detail: { category: 'Frozen Seafood', item_name: 'Frozen Seafood — Tilapia', usage: 'Commercial Sale' },
        quantity: 4500, unit_measurement: 'KG', purpose: 'Commercial Sale', value: 9150,
        status: 'queued', decision: null, rejectionReason: null,
        remark: 'Awaiting officer evaluation.',
        attachments: [
            { name: 'Health_Cert_Seafood.pdf', size: '230 KB', path: '/consignment/attachment/3', mime: 'application/pdf' },
            { name: 'Cold_Chain_Report.pdf',   size: '142 KB' },
            { name: 'Catch_Certificate.pdf',   size: '98 KB'  },
        ],
        conditions: [
            'Must maintain cold-chain temperature below -18°C.',
            'Requires a valid catch certificate.',
        ],
        permitActivity: [
            { stage: 'doc_verification', title: 'Documents Verified', description: 'All documents verified by clerk Nurul Aisyah.', time: '13 May 2025, 8:54 AM' },
        ],
    },
    {
        permit_number: 'PMT-1204',
        consignment_detail: { category: 'Agricultural Seedlings', item_name: 'Rubber Seedlings', usage: 'Research' },
        quantity: 80, unit_measurement: 'Unit', purpose: 'Research', value: 320,
        status: 'queued', decision: null, rejectionReason: null,
        remark: 'Awaiting officer evaluation.',
        attachments: [
            { name: 'Research_Permit_Letter.pdf', size: '120 KB' },
        ],
        conditions: [
            'Restricted to approved research institutions only.',
        ],
        permitActivity: [
            { stage: 'doc_verification', title: 'Documents Verified', description: 'All documents verified by clerk Nurul Aisyah.', time: '13 May 2025, 8:55 AM' },
        ],
    },
];

const ACTIVITY_LOG = [
    { stage: 'submitted',        title: 'Application Submitted',                    description: 'Application IP-2025-00456 was lodged by Tan Wei Ling.', time: '12 May 2025, 9:02 AM' },
    { stage: 'doc_verification', title: 'Stage: Submitted → Document Verification', description: 'Assigned to clerk Nurul Aisyah for review.', time: '12 May 2025, 10:15 AM' },
    { stage: 'doc_verification', title: 'Documents Verified by Clerk',              description: 'Verified by Nurul Aisyah. Forwarded to Technical Review.', time: '13 May 2025, 9:00 AM' },
    { stage: 'technical_review', title: 'Stage: Document Verification → Technical Review', description: `Assigned to officer ${OFFICER_NAME}.`, time: '13 May 2025, 9:05 AM' },
];

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function escapeHtml(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',
    }[c]));
}

function money(n) {
    return Number(n || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fileMeta(filename) {
    const ext = (filename.split('.').pop() || '').toLowerCase();
    if (ext === 'pdf')                        return { icon: 'bi-file-earmark-pdf-fill',   cls: 'is-pdf'   };
    if (['xlsx','xls','csv'].includes(ext))   return { icon: 'bi-file-earmark-excel-fill', cls: 'is-excel' };
    if (['doc','docx'].includes(ext))         return { icon: 'bi-file-earmark-word-fill',  cls: 'is-word'  };
    if (['jpg','jpeg','png'].includes(ext))   return { icon: 'bi-file-earmark-image-fill', cls: 'is-image' };
    if (['zip','rar'].includes(ext))          return { icon: 'bi-file-earmark-zip-fill',   cls: 'is-zip'   };
    return { icon: 'bi-file-earmark-fill', cls: 'is-default' };
}

function nowString() {
    return new Date().toLocaleString('en-MY', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

// ---------------------------------------------------------------
// Attachment chips + viewer (same pattern as clerk page)
// ---------------------------------------------------------------

const attachmentRegistry = new Map();
const attachmentDataMap  = new Map();
let attachmentSeq  = 0;
let currentListId  = null;
let currentIndex   = 0;
let attachmentOffcanvas = null;

function paintAttachmentList(containerEl, files, visibleCount) {
    const listId = containerEl.dataset.listId;
    if (!listId) return;
    attachmentDataMap.set(listId, files);

    const shown     = files.slice(0, visibleCount);
    const remaining = files.length - shown.length;

    let html = shown.map((file, idx) => {
        const meta = fileMeta(file.name);
        return `
            <div class="ipv-attach-chip" data-list-id="${listId}" data-index="${idx}" style="cursor:pointer;">
                <div class="ipv-attach-icon ${meta.cls}"><i class="bi ${meta.icon}"></i></div>
                <div class="ipv-attach-info">
                    <div class="ipv-attach-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                    <div class="ipv-attach-size">${escapeHtml(file.size)}</div>
                </div>
            </div>
        `;
    }).join('');

    if (remaining > 0) {
        html += `<div class="ipv-attach-more" data-list-id="${listId}">+${remaining}</div>`;
    }
    containerEl.innerHTML = html;
}

function renderAttachmentList(containerEl, files, visibleCount) {
    if (!containerEl) return;
    if (!files || !files.length) {
        containerEl.innerHTML = '<span class="ipv-attach-size" style="padding:0.4rem 0;">No attachments.</span>';
        return;
    }
    const listId = 'attach-' + (attachmentSeq++);
    attachmentRegistry.set(listId, files);
    containerEl.dataset.listId = listId;
    paintAttachmentList(containerEl, files, visibleCount);
}

document.addEventListener('click', e => {
    const more = e.target.closest('.ipv-attach-more');
    if (more) {
        const listId = more.dataset.listId;
        const files  = attachmentRegistry.get(listId);
        const el     = document.querySelector(`[data-list-id="${listId}"]`);
        if (el && files) paintAttachmentList(el, files, files.length);
        return;
    }
    const chip = e.target.closest('.ipv-attach-chip');
    if (chip) {
        openAttachmentViewer(chip.dataset.listId, parseInt(chip.dataset.index, 10));
        return;
    }
    if (e.target.closest('#attachmentPrevBtn') && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex > 0) openAttachmentViewer(currentListId, currentIndex - 1);
    }
    if (e.target.closest('#attachmentNextBtn') && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex < files.length - 1) openAttachmentViewer(currentListId, currentIndex + 1);
    }
});

function initOffcanvas() {
    const el = document.getElementById('attachmentOffcanvas');
    if (el) attachmentOffcanvas = new bootstrap.Offcanvas(el, { backdrop: true, keyboard: true, scroll: false });
}

function openAttachmentViewer(listId, index) {
    const files = attachmentDataMap.get(listId);
    if (!files?.length) return;
    currentListId = listId;
    currentIndex  = index;
    const file    = files[index];
    if (!file) return;

    document.getElementById('attachmentTitle').textContent   = file.name;
    document.getElementById('attachmentCounter').textContent = `${index + 1} / ${files.length}`;
    renderViewer(file);
    renderAttachmentDetails(file);
    document.getElementById('attachmentPrevBtn').disabled = index === 0;
    document.getElementById('attachmentNextBtn').disabled = index === files.length - 1;
    attachmentOffcanvas?.show();
}

function renderViewer(file) {
    const container = document.getElementById('attachmentViewer');
    const { mime = '', path = '' } = file;
    if (!path) {
        container.innerHTML = '<div class="text-muted"><i class="bi bi-file-earmark-fill fs-1"></i><br>No file available</div>';
        return;
    }
    if (mime.startsWith('image/')) {
        container.innerHTML = `<img src="${escapeHtml(path)}" style="max-width:100%;max-height:70vh;">`;
    } else if (mime === 'application/pdf') {
        container.innerHTML = `<iframe src="${escapeHtml(path)}" style="width:100%;height:70vh;border:none;"></iframe>`;
    } else {
        container.innerHTML = `
            <div class="text-center">
                <i class="bi bi-file-earmark-fill fs-1 d-block mb-3" style="color:var(--gray-5);"></i>
                <p class="text-muted">Preview not available.</p>
            </div>`;
    }
}

function renderAttachmentDetails(file) {
    document.getElementById('attachmentDetails').innerHTML = `
        ${[
            { label: 'File Name', value: file.name },
            { label: 'File Size', value: file.size },
            { label: 'File Type', value: file.mime || 'Unknown' },
        ].map(f => `
            <div class="detail-row">
                <span class="detail-label">${escapeHtml(f.label)}</span>
                <span class="detail-value">${escapeHtml(f.value)}</span>
            </div>
        `).join('')}
    `;
}

// ---------------------------------------------------------------
// Sidebar rendering
// ---------------------------------------------------------------

function renderHeaderInfo() {
    document.getElementById('ipvAppType').textContent     = APPLICATION.type;
    document.getElementById('ipvAppId').textContent       = APPLICATION.application_id;
    document.getElementById('ipvSubmittedBy').textContent = APPLICATION.submitted_by;
    document.getElementById('ipvCreatedAt').textContent   = `Application submitted on ${APPLICATION.submitted_at}`;

    document.getElementById('ipvTags').innerHTML = APPLICATION.tags.map(tag =>
        `<span class="ipv-tag is-${tag.color}">${escapeHtml(tag.label)}</span>`
    ).join('');

    const total = PERMITS.reduce((s, p) => s + p.value, 0);
    document.getElementById('ipvTotalValue').textContent = `RM ${money(total)}`;
}

function partyBlockHtml(party, label) {
    const initial = (party.name || '?').charAt(0).toUpperCase();
    return `
        <div class="ipv-party-header">
            <div class="ipv-party-avatar">${initial}</div>
            <div>
                <div class="ipv-party-name">${escapeHtml(party.name)}</div>
                <div class="ipv-party-sub">${label}</div>
            </div>
        </div>
        <div class="ipv-contact-row">
            <div class="ipv-contact-icon"><i class="bi bi-telephone"></i></div>
            <div><div class="ipv-contact-label">Phone</div><div class="ipv-contact-value">${escapeHtml(party.phone)}</div></div>
        </div>
        <div class="ipv-contact-row">
            <div class="ipv-contact-icon"><i class="bi bi-envelope"></i></div>
            <div><div class="ipv-contact-label">Email</div><div class="ipv-contact-value">${escapeHtml(party.email)}</div></div>
        </div>
        <div class="ipv-contact-row">
            <div class="ipv-contact-icon"><i class="bi bi-geo-alt"></i></div>
            <div><div class="ipv-contact-label">Address</div>
            <div class="ipv-contact-value">${escapeHtml(party.address)}, ${escapeHtml(party.country)}</div></div>
        </div>
    `;
}

function renderParties() {
    document.getElementById('ipvImporterBlock').innerHTML = partyBlockHtml(APPLICATION.importer, 'Importer');
    const exporterEl = document.getElementById('ipvExporterBlock');
    exporterEl.innerHTML = partyBlockHtml(APPLICATION.exporter, 'Exporter');
    exporterEl.classList.add('is-exporter');
}

function renderAppAttachments() {
    renderAttachmentList(document.getElementById('ipvAppAttachments'), APPLICATION.attachments, 3);
}

// ---------------------------------------------------------------
// Stage stepper
// ---------------------------------------------------------------

function renderStageStepper() {
    const el         = document.getElementById('ipvStageStepper');
    const currentIdx = STAGE_ORDER.indexOf(APPLICATION.status);

    el.innerHTML = STAGE_ORDER.map((key, i) => {
        const cfg = STAGE_CONFIG[key];
        let cls = 'is-pending';
        if (i < currentIdx)       cls = 'is-complete';
        else if (i === currentIdx) cls = 'is-current';
        return `<div class="ipv-stage-step ${cls}">${cfg.en}</div>`;
    }).join('');

    document.getElementById('ipvStatusLabel').textContent    = (STAGE_CONFIG[APPLICATION.status] || {}).en || APPLICATION.status;
    document.getElementById('ipvStatusDuration').textContent = `In this status for ${APPLICATION.status_duration}`;
}

function renderInfoRow() {
    document.getElementById('ipvAssignedOfficer').textContent = APPLICATION.assigned_officer;
    document.getElementById('ipvSlaDue').textContent          = APPLICATION.sla_due;
}

// ---------------------------------------------------------------
// Transport + Condition tabs
// ---------------------------------------------------------------

function renderTransportDetails() {
    document.getElementById('ipvTransportDetails').innerHTML = [
        { icon: 'bi-calendar-event', label: 'ETA',               value: APPLICATION.eta },
        { icon: 'bi-truck',          label: 'Transport Type',     value: APPLICATION.transport_type },
        { icon: 'bi-geo-alt',        label: 'Entry Point',        value: APPLICATION.entry_point },
        { icon: 'bi-info-circle',    label: 'Entry Point Notes',  value: APPLICATION.entry_point_description },
    ].map(r => `
        <div class="ipv-detail-row">
            <div class="ipv-detail-icon"><i class="bi ${r.icon}"></i></div>
            <span class="ipv-detail-label">${r.label}</span>
            <span class="ipv-detail-value">${escapeHtml(r.value)}</span>
        </div>
    `).join('');
}

function renderConditionTab() {
    document.getElementById('ipvConditionList').innerHTML = PERMITS.map(permit => `
        <div class="ipv-condition-card">
            <div class="ipv-condition-card-head">
                <div>
                    <div class="ipv-condition-card-title">${escapeHtml(permit.consignment_detail.item_name)}</div>
                    <div class="ipv-condition-card-sub">${escapeHtml(permit.consignment_detail.category)} &middot; #${escapeHtml(permit.permit_number)}</div>
                </div>
            </div>
            ${permit.conditions.map(c => `
                <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
            `).join('')}
        </div>
    `).join('');
}

// ---------------------------------------------------------------
// Activity tab
// ---------------------------------------------------------------

function renderActivityTimeline() {
    const el = document.getElementById('ipvActivityTimeline');
    if (!ACTIVITY_LOG.length) {
        el.innerHTML = '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity yet.</p></div>';
        return;
    }
    el.innerHTML = ACTIVITY_LOG.map(entry => {
        const cfg = STAGE_CONFIG[entry.stage] || STAGE_CONFIG.email;
        return `
            <div class="ipv-timeline-item">
                <div class="ipv-timeline-icon is-${cfg.color}"><i class="bi ${cfg.icon}"></i></div>
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

// ---------------------------------------------------------------
// Progress box (sidebar)
// ---------------------------------------------------------------

function updateProgressBox() {
    const total    = PERMITS.length;
    const approved = PERMITS.filter(p => p.decision === 'approved').length;
    const rejected = PERMITS.filter(p => p.decision === 'rejected').length;
    const pending  = total - approved - rejected;

    document.getElementById('otrCountTotal').textContent    = total;
    document.getElementById('otrCountPending').textContent  = pending;
    document.getElementById('otrCountApproved').textContent = approved;
    document.getElementById('otrCountRejected').textContent = rejected;

    document.getElementById('otrFillApproved').style.width = `${(approved / total) * 100}%`;
    document.getElementById('otrFillRejected').style.width = `${(rejected / total) * 100}%`;

    const hint = document.getElementById('otrProgressHint');
    if (pending > 0) {
        hint.textContent = `${pending} item${pending > 1 ? 's' : ''} still need${pending === 1 ? 's' : ''} review.`;
        hint.className   = 'otr-progress-hint';
    } else if (approved > 0) {
        hint.textContent = 'All items reviewed. Ready to finalise.';
        hint.className   = 'otr-progress-hint is-ready';
    } else {
        hint.textContent = 'All items rejected.';
        hint.className   = 'otr-progress-hint is-rejected';
    }
}

// ---------------------------------------------------------------
// Permit accordion — the main review surface
// ---------------------------------------------------------------

function permitDecisionBarHtml(permit) {
    if (permit.decision === 'approved') {
        return `
            <div class="otr-decision-chip is-approved">
                <i class="bi bi-check-circle-fill"></i>
                <span>Approved by ${escapeHtml(OFFICER_NAME)}</span>
                <button type="button" class="otr-undo-btn" data-permit-number="${escapeHtml(permit.permit_number)}" title="Undo decision">
                    <i class="bi bi-arrow-counterclockwise"></i> Undo
                </button>
            </div>
        `;
    }
    if (permit.decision === 'rejected') {
        return `
            <div class="otr-decision-chip is-rejected">
                <i class="bi bi-x-circle-fill"></i>
                <div>
                    <span>Rejected by ${escapeHtml(OFFICER_NAME)}</span>
                    <div class="otr-rejection-reason">"${escapeHtml(permit.rejectionReason)}"</div>
                </div>
                <button type="button" class="otr-undo-btn" data-permit-number="${escapeHtml(permit.permit_number)}" title="Undo decision">
                    <i class="bi bi-arrow-counterclockwise"></i> Undo
                </button>
            </div>
        `;
    }
    // Pending
    return `
        <div class="otr-action-bar">
            <div class="otr-action-bar-label">
                <i class="bi bi-person-badge"></i> Officer Decision
            </div>
            <div class="otr-action-btns">
                <button type="button" class="otr-btn-approve"
                    data-permit-number="${escapeHtml(permit.permit_number)}">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
                <button type="button" class="otr-btn-reject-open"
                    data-permit-number="${escapeHtml(permit.permit_number)}">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
            </div>
        </div>
    `;
}

function renderPermitAccordion() {
    document.getElementById('ipvPermitCount').textContent = PERMITS.length;
    const el = document.getElementById('ipvPermitAccordion');

    el.innerHTML = PERMITS.map(permit => {
        const cfg    = PERMIT_STATUS_CONFIG[permit.decision || 'queued'] || PERMIT_STATUS_CONFIG.queued;
        const detail = permit.consignment_detail;

        return `
            <div class="ipv-permit-item" data-permit="${escapeHtml(permit.permit_number)}">
                <div class="ipv-permit-header">
                    <div class="ipv-permit-icon"><i class="bi bi-box-seam"></i></div>
                    <div class="ipv-permit-id-group">
                        <div class="ipv-permit-id">#${escapeHtml(permit.permit_number)}</div>
                        <div class="ipv-permit-name">${escapeHtml(detail.item_name)}</div>
                    </div>
                    <span class="ipv-badge is-${cfg.color}">${escapeHtml(cfg.en)}</span>
                    <div class="ipv-permit-value">RM ${money(permit.value)}</div>
                    <button type="button" class="ipv-view-detail-btn"
                        data-permit-number="${escapeHtml(permit.permit_number)}" title="Full details">
                        <i class="bi bi-arrow-up-right-square"></i>
                    </button>
                    <i class="bi bi-chevron-down ipv-chevron"></i>
                </div>

                <div class="ipv-permit-body">
                    <!-- Details grid -->
                    <div class="ipv-permit-grid">
                        <div class="ipv-permit-meta"><span class="meta-label">Category</span><span class="meta-value">${escapeHtml(detail.category)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Usage</span><span class="meta-value">${escapeHtml(detail.usage)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Purpose</span><span class="meta-value">${escapeHtml(permit.purpose)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Quantity</span><span class="meta-value">${permit.quantity.toLocaleString()} ${escapeHtml(permit.unit_measurement)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Value</span><span class="meta-value">RM ${money(permit.value)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Permit No.</span><span class="meta-value">${escapeHtml(permit.permit_number)}</span></div>
                    </div>

                    <!-- Conditions -->
                    <div class="ipv-permit-subsection-title">Import Conditions</div>
                    ${permit.conditions.map(c => `
                        <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
                    `).join('')}

                    <!-- Attachments -->
                    <div class="ipv-permit-subsection-title">Attachments (${permit.attachments.length})</div>
                    <div class="ipv-attach-list" id="attachList-${escapeHtml(permit.permit_number)}"></div>

                    <!-- ================================================ -->
                    <!-- OFFICER DECISION BAR — key interaction point       -->
                    <!-- ================================================ -->
                    <div class="otr-decision-zone" id="otrDecision-${escapeHtml(permit.permit_number)}">
                        ${permitDecisionBarHtml(permit)}
                    </div>
                </div>
            </div>
        `;
    }).join('');

    // Render attachment chips for each permit
    PERMITS.forEach(permit => {
        renderAttachmentList(
            document.getElementById(`attachList-${permit.permit_number}`),
            permit.attachments,
            2
        );
    });

    updateProgressBox();
    checkFinaliseBar();
}

// Refresh just the decision zone for a single permit (no full re-render)
function refreshPermitDecisionZone(permit) {
    const zone = document.getElementById(`otrDecision-${permit.permit_number}`);
    if (zone) zone.innerHTML = permitDecisionBarHtml(permit);

    // Also update the badge in the header
    const item   = document.querySelector(`[data-permit="${permit.permit_number}"]`);
    const badge  = item?.querySelector('.ipv-badge');
    const cfg    = PERMIT_STATUS_CONFIG[permit.decision || 'queued'] || PERMIT_STATUS_CONFIG.queued;
    if (badge) {
        badge.textContent  = cfg.en;
        badge.className    = `ipv-badge is-${cfg.color}`;
    }
}

// ---------------------------------------------------------------
// Accordion toggle + decision button delegation
// ---------------------------------------------------------------

let _accordionInit = false;

function initAccordionToggle() {
    if (_accordionInit) return;
    _accordionInit = true;

    document.getElementById('ipvPermitAccordion').addEventListener('click', e => {

        // Full-detail offcanvas
        const viewBtn = e.target.closest('.ipv-view-detail-btn');
        if (viewBtn) {
            e.stopPropagation();
            openPermitDetail(viewBtn.dataset.permitNumber);
            return;
        }

        // Approve
        const approveBtn = e.target.closest('.otr-btn-approve');
        if (approveBtn) {
            e.stopPropagation();
            applyDecision(approveBtn.dataset.permitNumber, 'approved', null);
            return;
        }

        // Open reject modal
        const rejectOpenBtn = e.target.closest('.otr-btn-reject-open');
        if (rejectOpenBtn) {
            e.stopPropagation();
            openRejectModal(rejectOpenBtn.dataset.permitNumber);
            return;
        }

        // Undo
        const undoBtn = e.target.closest('.otr-undo-btn');
        if (undoBtn) {
            e.stopPropagation();
            applyDecision(undoBtn.dataset.permitNumber, null, null);
            return;
        }

        // Accordion header toggle
        const header = e.target.closest('.ipv-permit-header');
        if (header) {
            header.closest('.ipv-permit-item')?.classList.toggle('is-open');
        }
    });
}

// ---------------------------------------------------------------
// Approve / Reject logic
// ---------------------------------------------------------------

function applyDecision(permitNumber, decision, reason) {
    const permit = PERMITS.find(p => p.permit_number === permitNumber);
    if (!permit) return;

    const wasDecided = permit.decision !== null;
    permit.decision       = decision;
    permit.rejectionReason = reason;
    permit.status         = decision || 'queued';

    // Push activity log entry
    if (decision === 'approved') {
        permit.permitActivity.push({
            stage: 'permit_approved',
            title: 'Permit Approved',
            description: `Approved by officer ${OFFICER_NAME}.`,
            time: nowString(),
        });
        ACTIVITY_LOG.push({
            stage: 'permit_approved',
            title: `Permit ${permitNumber} Approved`,
            description: `Approved by ${OFFICER_NAME}.`,
            time: nowString(),
        });
    } else if (decision === 'rejected') {
        permit.permitActivity.push({
            stage: 'permit_rejected',
            title: 'Permit Rejected',
            description: `Rejected by officer ${OFFICER_NAME}: "${reason}"`,
            time: nowString(),
        });
        ACTIVITY_LOG.push({
            stage: 'permit_rejected',
            title: `Permit ${permitNumber} Rejected`,
            description: `Rejected by ${OFFICER_NAME}: "${reason}"`,
            time: nowString(),
        });
    } else if (wasDecided) {
        // Undo
        permit.permitActivity.push({
            stage: 'technical_review',
            title: 'Decision Undone',
            description: `Decision on ${permitNumber} reversed by ${OFFICER_NAME}.`,
            time: nowString(),
        });
    }

    refreshPermitDecisionZone(permit);
    updateProgressBox();
    renderActivityTimeline();
    checkFinaliseBar();
}

// ---------------------------------------------------------------
// Reject modal
// ---------------------------------------------------------------

let rejectModal     = null;
let pendingRejectNo = null;

function initRejectModal() {
    const modalEl   = document.getElementById('otrRejectModal');
    const reasonEl  = document.getElementById('otrRejectReason');
    const confirmEl = document.getElementById('otrRejectConfirmBtn');

    rejectModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });

    // Quick-reason chips
    document.querySelectorAll('.ipv-reject-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            reasonEl.value    = chip.dataset.reason;
            confirmEl.disabled = false;
        });
    });

    reasonEl.addEventListener('input', () => {
        confirmEl.disabled = reasonEl.value.trim().length === 0;
    });

    confirmEl.addEventListener('click', () => {
        const reason = reasonEl.value.trim();
        if (!reason || !pendingRejectNo) return;
        applyDecision(pendingRejectNo, 'rejected', reason);
        pendingRejectNo = null;
        rejectModal.hide();
    });

    // Reset on close
    modalEl.addEventListener('hidden.bs.modal', () => {
        reasonEl.value     = '';
        confirmEl.disabled = true;
        pendingRejectNo    = null;
    });
}

function openRejectModal(permitNumber) {
    pendingRejectNo = permitNumber;
    document.getElementById('otrRejectModalPermitNo').textContent = permitNumber;
    document.getElementById('otrRejectReason').value = '';
    document.getElementById('otrRejectConfirmBtn').disabled = true;
    rejectModal?.show();
}

// ---------------------------------------------------------------
// Finalise bar
// ---------------------------------------------------------------

function checkFinaliseBar() {
    const bar     = document.getElementById('otrFinaliseBar');
    const pending = PERMITS.filter(p => p.decision === null).length;

    if (pending > 0) {
        bar.classList.add('d-none');
        return;
    }

    bar.classList.remove('d-none');

    const approved = PERMITS.filter(p => p.decision === 'approved').length;
    const rejected = PERMITS.filter(p => p.decision === 'rejected').length;
    const summary  = document.getElementById('otrFinaliseSummary');

    summary.innerHTML = `
        <span class="otr-finalise-stat is-approved">
            <i class="bi bi-check-circle-fill"></i> ${approved} Approved
        </span>
        <span class="otr-finalise-stat is-rejected">
            <i class="bi bi-x-circle-fill"></i> ${rejected} Rejected
        </span>
        ${approved > 0
            ? `<span class="otr-finalise-note">
                   Invoice will be generated for ${approved} approved permit${approved > 1 ? 's' : ''}.
               </span>`
            : `<span class="otr-finalise-note is-warning">
                   All permits rejected — application will be closed.
               </span>`
        }
    `;
}

document.getElementById('otrFinaliseBtn')?.addEventListener('click', () => {
    const approved = PERMITS.filter(p => p.decision === 'approved').length;

    APPLICATION.status           = approved > 0 ? 'awaiting_payment' : 'completed';
    APPLICATION.status_duration  = 'Just now';

    const label = approved > 0 ? 'Awaiting Payment' : 'Completed (all rejected)';

    ACTIVITY_LOG.push({
        stage: approved > 0 ? 'awaiting_payment' : 'completed',
        title: `Technical Review Finalised — ${label}`,
        description: `${approved} permit${approved !== 1 ? 's' : ''} approved, ` +
                     `${PERMITS.length - approved} rejected. ` +
                     `Actioned by ${OFFICER_NAME}.`,
        time: nowString(),
    });

    renderStageStepper();
    renderActivityTimeline();
    updateProgressBox();

    // Replace finalise bar with a decided state
    const bar = document.getElementById('otrFinaliseBar');
    bar.innerHTML = `
        <div class="otr-finalised-chip">
            <i class="bi bi-check2-all"></i>
            <div>
                <div class="otr-finalised-title">Technical Review Finalised</div>
                <div class="otr-finalised-sub">
                    ${label} — ${nowString()}
                </div>
            </div>
        </div>
    `;
});

// ---------------------------------------------------------------
// Permit detail offcanvas
// ---------------------------------------------------------------

let permitDetailOffcanvas = null;

function initPermitDetailOffcanvas() {
    const el = document.getElementById('permitDetailOffcanvas');
    if (el) permitDetailOffcanvas = new bootstrap.Offcanvas(el, { backdrop: true, keyboard: true, scroll: false });
}

function openPermitDetail(permitNumber) {
    const permit = PERMITS.find(p => p.permit_number === permitNumber);
    if (!permit) return;

    const cfg    = PERMIT_STATUS_CONFIG[permit.decision || 'queued'] || PERMIT_STATUS_CONFIG.queued;
    const detail = permit.consignment_detail;

    document.getElementById('permitDetailOffcanvasLabel').textContent = permit.permit_number;
    const badge = document.getElementById('pdBadge');
    badge.textContent = cfg.en;
    badge.className   = `ipv-badge ms-2 is-${cfg.color}`;

    // Details tab
    document.getElementById('pdDetailsContent').innerHTML = `
        <div class="pd-section-label">Consignment Info</div>
        <div class="pd-info-grid">
            ${[
                { label: 'Category',       value: detail.category },
                { label: 'Item',           value: detail.item_name },
                { label: 'Usage',          value: detail.usage },
                { label: 'Purpose',        value: permit.purpose },
                { label: 'Quantity',       value: `${permit.quantity.toLocaleString()} ${permit.unit_measurement}` },
                { label: 'Declared Value', value: `RM ${money(permit.value)}` },
            ].map(r => `
                <div class="pd-info-cell">
                    <div class="pd-cell-label">${escapeHtml(r.label)}</div>
                    <div class="pd-cell-value">${escapeHtml(r.value)}</div>
                </div>
            `).join('')}
        </div>

        <div class="pd-section-label mt-4">Import Conditions</div>
        ${permit.conditions.map(c => `
            <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
        `).join('')}

        <div class="pd-section-label mt-4">Attachments (${permit.attachments.length})</div>
        <div class="ipv-attach-list" id="pd-attach-${permit.permit_number}"></div>

        ${permit.decision ? `
            <div class="pd-section-label mt-4">Officer Decision</div>
            <div class="ipv-permit-remark is-${cfg.color}">
                <i class="bi bi-${permit.decision === 'approved' ? 'check-circle' : 'x-circle'}"></i>
                <span>${permit.decision === 'approved'
                    ? `Approved by ${escapeHtml(OFFICER_NAME)}`
                    : `Rejected: "${escapeHtml(permit.rejectionReason)}"`}
                </span>
            </div>
        ` : ''}
    `;

    renderAttachmentList(
        document.getElementById(`pd-attach-${permit.permit_number}`),
        permit.attachments,
        permit.attachments.length
    );

    // Activity tab
    const timelineEl = document.getElementById('pdActivityTimeline');
    timelineEl.innerHTML = (permit.permitActivity || []).map(entry => {
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
    }).join('') || '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity yet.</p></div>';

    // Reset to details tab
    bootstrap.Tab.getOrCreateInstance(document.getElementById('pd-details-tab'))?.show();

    permitDetailOffcanvas?.show();
}

// ---------------------------------------------------------------
// Tabs
// ---------------------------------------------------------------

function initTabs() {
    const tabs  = document.querySelectorAll('.ipv-tabnav-item');
    const panes = document.querySelectorAll('.ipv-tabpane');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.ipvTab;
            tabs.forEach(t  => t.classList.toggle('is-active', t === tab));
            panes.forEach(p => p.classList.toggle('is-active', p.dataset.ipvPane === target));
        });
    });
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

let _initialized = false;

function init() {
    if (_initialized) return;
    _initialized = true;
    if (!document.getElementById('ipvAppId')) return;

    renderHeaderInfo();
    renderParties();
    renderAppAttachments();
    renderStageStepper();
    renderInfoRow();
    renderTransportDetails();
    renderPermitAccordion();
    renderConditionTab();
    renderActivityTimeline();
    initAccordionToggle();
    initTabs();
    initOffcanvas();
    initPermitDetailOffcanvas();
    initRejectModal();
}

document.addEventListener('DOMContentLoaded', init);

window.OfficerTechnicalReview = {
    setApplication(data) { Object.assign(APPLICATION, data); renderHeaderInfo(); renderStageStepper(); renderInfoRow(); },
    setPermits(data) { PERMITS.length = 0; PERMITS.push(...data); renderPermitAccordion(); renderConditionTab(); },
};