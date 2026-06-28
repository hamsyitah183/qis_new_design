/**
 * clerkDocVerification.js
 * ------------------------------------------------------------------
 * Same rendering approach as importPermitView.js / test1.js, trimmed
 * to what's relevant before anything's been approved (no Print
 * Permit, no download-permits offcanvas — nothing's issued yet at
 * Document Verification stage), plus the new Clerk Verification
 * panel and the Reject/Return-for-Amendment flow.
 *
 * Dummy data mirrors IpApplication / IpConsignmentPermit, with one
 * addition: every attachment carries a `verified` boolean — that's
 * the actual mechanism behind "the clerk needs to verify the
 * documents are valid." Toggling it lives in the attachment
 * offcanvas's Details tab (see renderDetails()).
 *
 * Verify / Reject both write straight into APPLICATION (status,
 * returned_reason) and re-run renderStageStepper() — which already
 * had returned-branch handling built in from the public view page,
 * just never exercised until now.
 */

// ---------------------------------------------------------------
// Config
// ---------------------------------------------------------------

const STAGE_ORDER = [
    'submitted', 'doc_verification', 'technical_review',
    'awaiting_payment', 'payment_processing', 'completed',
];

const STAGE_CONFIG = {
    submitted:           { en: 'Submitted',              icon: 'bi-send-check',         color: 'info' },
    doc_verification:    { en: 'Document Verification',  icon: 'bi-file-earmark-check', color: 'secondary' },
    returned:            { en: 'Returned / Rejected',     icon: 'bi-arrow-return-left',  color: 'danger' },
    technical_review:    { en: 'Technical Review',        icon: 'bi-clipboard-check',    color: 'primary' },
    awaiting_payment:    { en: 'Awaiting Payment',        icon: 'bi-hourglass-split',    color: 'warning' },
    payment_processing:  { en: 'Payment Processing',      icon: 'bi-credit-card',        color: 'orange' },
    completed:           { en: 'Completed',               icon: 'bi-check-circle',       color: 'success' },
    email:               { en: 'Notification Sent',       icon: 'bi-envelope-check',     color: 'gray' },
};

const PERMIT_STATUS_CONFIG = {
    queued: { en: 'Queued for Review', color: 'info' },
};

const CLERK_NAME = 'Nurul Aisyah';

// ---------------------------------------------------------------
// Dummy data — application currently sitting at Document Verification
// ---------------------------------------------------------------

const APPLICATION = {
    application_id: 'IP-2025-00456',
    type: 'Import Permit',
    status: 'doc_verification',
    status_duration: '3 hours',
    returned_reason: null,
    tags: [
        { label: 'Category 1', color: 'primary' },
        { label: 'Repeat Importer', color: 'secondary' },
    ],
    submitted_by: 'Tan Wei Ling',
    submitted_at: '12 May 2025, 9:02 AM',
    assigned_officer: CLERK_NAME, // labelled "Assigned Clerk" in this view
    sla_due: 'Due in 1 day 2 hours',
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
        { name: 'Invoice_IP-2025-00456.pdf', size: '420 KB', path: '/consignment/attachment/10', mime: 'application/pdf', verified: true },
        { name: 'Packing_List.xlsx', size: '88 KB', verified: false },
        { name: 'Letter_of_Authorization.docx', size: '156 KB', path: '/consignment/attachment/9', mime: 'application/pdf', verified: false },
        { name: 'Bill_of_Lading.pdf', size: '310 KB', path: '/consignment/attachment/8', mime: 'application/pdf', verified: true },
    ],
};

const PERMITS = [
    {
        permit_number: 'PMT-1201',
        consignment_detail: { category: 'Fresh Produce', item_name: 'Fresh Fruit — Corn', usage: 'Commercial Sale' },
        quantity: 1200, unit_measurement: 'KG', purpose: 'Commercial Sale', value: 2480,
        status: 'queued',
        remark: 'Awaiting document verification. Not yet evaluated by an officer.',
        attachments: [
            { name: 'Phytosanitary_Cert.pdf', size: '210 KB', verified: true },
            { name: 'Fumigation_Cert.pdf', size: '180 KB', verified: false },
        ],
        conditions: [
            'Must be accompanied by a valid phytosanitary certificate.',
            'Subject to inspection at point of entry.',
        ],
    },
    {
        permit_number: 'PMT-1202',
        consignment_detail: { category: 'Live Animals', item_name: 'Live Ornamental Fish', usage: 'Re-export' },
        quantity: 300, unit_measurement: 'Unit', purpose: 'Re-export', value: 650,
        status: 'queued',
        remark: 'Awaiting document verification. Not yet evaluated by an officer.',
        attachments: [
            { name: 'Health_Cert.pdf', size: '195 KB', verified: false },
        ],
        conditions: [
            'Requires a valid health certificate from country of origin.',
            'Subject to quarantine inspection on arrival.',
        ],
    },
    {
        permit_number: 'PMT-1203',
        consignment_detail: { category: 'Frozen Seafood', item_name: 'Frozen Seafood — Tilapia', usage: 'Commercial Sale' },
        quantity: 4500, unit_measurement: 'KG', purpose: 'Commercial Sale', value: 9150,
        status: 'queued',
        remark: 'Awaiting document verification. Not yet evaluated by an officer.',
        attachments: [
            { name: 'Health_Cert_Seafood.pdf', size: '230 KB', verified: true },
            { name: 'Cold_Chain_Report.pdf', size: '142 KB', verified: true },
            { name: 'Catch_Certificate.pdf', size: '98 KB', verified: false },
        ],
        conditions: [
            'Must maintain cold-chain temperature below -18°C.',
            'Requires a valid catch certificate.',
        ],
    },
];

const ACTIVITY_LOG = [
    { stage: 'submitted',        title: 'Application Submitted',                   description: 'Application IP-2025-00456 was lodged by Tan Wei Ling.', time: '12 May 2025, 9:02 AM' },
    { stage: 'email',            title: 'Email Delivered: Submission Confirmation', description: 'A confirmation email was sent to the applicant.', time: '12 May 2025, 9:03 AM' },
    { stage: 'doc_verification', title: 'Stage: Submitted → Document Verification', description: `Assigned to clerk ${CLERK_NAME} for review.`, time: '12 May 2025, 10:15 AM' },
];

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

function money(n) {
    return Number(n || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fileMeta(filename) {
    const ext = (filename.split('.').pop() || '').toLowerCase();
    if (ext === 'pdf') return { icon: 'bi-file-earmark-pdf-fill', cls: 'is-pdf' };
    if (['xlsx', 'xls', 'csv'].includes(ext)) return { icon: 'bi-file-earmark-excel-fill', cls: 'is-excel' };
    if (['doc', 'docx'].includes(ext)) return { icon: 'bi-file-earmark-word-fill', cls: 'is-word' };
    if (['jpg', 'jpeg', 'png'].includes(ext)) return { icon: 'bi-file-earmark-image-fill', cls: 'is-image' };
    if (['zip', 'rar'].includes(ext)) return { icon: 'bi-file-earmark-zip-fill', cls: 'is-zip' };
    return { icon: 'bi-file-earmark-fill', cls: 'is-default' };
}

function nowString() {
    return new Date().toLocaleString('en-MY', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

// ---------------------------------------------------------------
// Attachment chips + viewer offcanvas (same pattern as the view page)
// ---------------------------------------------------------------

const attachmentRegistry = new Map();
const attachmentDataMap = new Map();
let attachmentSeq = 0;
let currentListId = null;
let currentIndex = 0;
let attachmentOffcanvas = null;

function paintAttachmentList(containerEl, files, visibleCount) {
    const listId = containerEl.dataset.listId;
    if (!listId) return;
    attachmentDataMap.set(listId, files);

    const shown = files.slice(0, visibleCount);
    const remaining = files.length - shown.length;

    let html = shown.map((file, idx) => {
        const meta = fileMeta(file.name);
        return `
            <div class="ipv-attach-chip" data-list-id="${listId}" data-index="${idx}" style="cursor:pointer;">
                <div class="ipv-attach-icon ${meta.cls}"><i class="bi ${meta.icon}"></i></div>
                <div class="ipv-attach-info">
                    <div class="ipv-attach-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                    <div class="ipv-attach-size">
                        ${file.verified ? '<i class="bi bi-patch-check-fill text-success" title="Verified"></i>' : '<i class="bi bi-exclamation-circle text-warning" title="Pending verification"></i>'}
                        ${escapeHtml(file.size)}
                    </div>
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

document.addEventListener('click', (e) => {
    const moreTile = e.target.closest('.ipv-attach-more');
    if (moreTile) {
        const listId = moreTile.dataset.listId;
        const files = attachmentRegistry.get(listId);
        const containerEl = document.querySelector(`[data-list-id="${listId}"]`);
        if (containerEl && files) paintAttachmentList(containerEl, files, files.length);
        return;
    }

    const chip = e.target.closest('.ipv-attach-chip');
    if (chip) {
        const listId = chip.dataset.listId;
        const index = parseInt(chip.dataset.index, 10);
        if (listId !== undefined && !isNaN(index)) openAttachmentViewer(listId, index);
        return;
    }

    const prevBtn = e.target.closest('#attachmentPrevBtn');
    if (prevBtn && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex > 0) openAttachmentViewer(currentListId, currentIndex - 1);
    }
    const nextBtn = e.target.closest('#attachmentNextBtn');
    if (nextBtn && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex < files.length - 1) openAttachmentViewer(currentListId, currentIndex + 1);
    }
});

function initOffcanvas() {
    const el = document.getElementById('attachmentOffcanvas');
    if (el) {
        attachmentOffcanvas = new bootstrap.Offcanvas(el, { backdrop: true, keyboard: true, scroll: false });
    }
}

function openAttachmentViewer(listId, index) {
    const files = attachmentDataMap.get(listId);
    if (!files || !files.length) return;

    currentListId = listId;
    currentIndex = index;
    const file = files[currentIndex];
    if (!file) return;

    document.getElementById('attachmentTitle').textContent = file.name;
    document.getElementById('attachmentCounter').textContent = `${currentIndex + 1} / ${files.length}`;
    renderViewer(file);
    renderDetails(file);
    document.getElementById('attachmentPrevBtn').disabled = (currentIndex === 0);
    document.getElementById('attachmentNextBtn').disabled = (currentIndex === files.length - 1);

    attachmentOffcanvas?.show();
}

function renderViewer(file) {
    const container = document.getElementById('attachmentViewer');
    const mime = file.mime || '';
    const path = file.path || '';

    if (!path) {
        container.innerHTML = '<div class="text-muted"><i class="bi bi-file-earmark-fill fs-1"></i><br>No file available</div>';
        return;
    }
    let html = '';
    if (mime.startsWith('image/')) {
        html = `<img src="${escapeHtml(path)}" alt="${escapeHtml(file.name)}" style="max-width:100%; max-height:70vh;">`;
    } else if (mime === 'application/pdf') {
        html = `<iframe src="${escapeHtml(path)}" style="width:100%; height:70vh; border:none;"></iframe>`;
    } else {
        html = `
            <div class="text-center">
                <i class="bi bi-file-earmark-fill fs-1 d-block mb-3" style="color: var(--gray-5);"></i>
                <p class="text-muted">Preview not available for this file type.</p>
            </div>
        `;
    }
    container.innerHTML = html;
}

// This is the per-document verification control — the actual
// mechanism behind "is the document valid." Toggling it flips
// file.verified and re-renders both this panel and the chip list
// it came from.
function renderDetails(file) {
    const container = document.getElementById('attachmentDetails');
    const fields = [
        { label: 'File Name', value: file.name },
        { label: 'File Size', value: file.size },
        { label: 'File Type', value: file.mime || 'Unknown' },
    ];

    container.innerHTML = `
        ${fields.map((f) => `
            <div class="detail-row">
                <span class="detail-label">${escapeHtml(f.label)}</span>
                <span class="detail-value">${escapeHtml(f.value)}</span>
            </div>
        `).join('')}
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value">
                ${file.verified
                    ? '<span class="ipv-badge is-success">Verified</span>'
                    : '<span class="ipv-badge is-warning">Pending Verification</span>'}
            </span>
        </div>
        <button type="button" class="ipv-verify-toggle-btn" id="ipvVerifyToggleBtn">
            ${file.verified
                ? '<i class="bi bi-x-circle"></i> Unmark Verified'
                : '<i class="bi bi-check-circle"></i> Mark as Verified'}
        </button>
    `;

    document.getElementById('ipvVerifyToggleBtn')?.addEventListener('click', () => {
        file.verified = !file.verified;
        renderDetails(file);
        const files = attachmentDataMap.get(currentListId);
        const containerEl = document.querySelector(`[data-list-id="${currentListId}"]`);
        if (files && containerEl) paintAttachmentList(containerEl, files, files.length);
    });
}

// ---------------------------------------------------------------
// Sidebar
// ---------------------------------------------------------------

function renderHeaderInfo() {
    document.getElementById('ipvAppType').textContent = APPLICATION.type;
    document.getElementById('ipvAppId').textContent = APPLICATION.application_id;
    document.getElementById('ipvSubmittedBy').textContent = APPLICATION.submitted_by;
    document.getElementById('ipvCreatedAt').textContent = `Application submitted on ${APPLICATION.submitted_at}`;

    document.getElementById('ipvTags').innerHTML = APPLICATION.tags.map((tag) =>
        `<span class="ipv-tag is-${tag.color}">${escapeHtml(tag.label)}</span>`
    ).join('');

    const total = PERMITS.reduce((sum, p) => sum + p.value, 0);
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
            <div><div class="ipv-contact-label">Address</div><div class="ipv-contact-value">${escapeHtml(party.address)}, ${escapeHtml(party.country)}</div></div>
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
// Status header + stage stepper
// ---------------------------------------------------------------

function renderStageStepper() {
    const el = document.getElementById('ipvStageStepper');
    const currentIdx = STAGE_ORDER.indexOf(APPLICATION.status);

    el.innerHTML = STAGE_ORDER.map((key, i) => {
        const cfg = STAGE_CONFIG[key];
        let cls = 'is-pending';

        if (APPLICATION.status === 'returned') {
            if (key === 'submitted') cls = 'is-complete';
            else if (key === 'doc_verification') cls = 'is-returned';
        } else if (i < currentIdx) {
            cls = 'is-complete';
        } else if (i === currentIdx) {
            cls = 'is-current';
        }

        return `<div class="ipv-stage-step ${cls}">${cfg.en}</div>`;
    }).join('');

    document.getElementById('ipvStatusLabel').textContent =
        (STAGE_CONFIG[APPLICATION.status] || {}).en || APPLICATION.status;
    document.getElementById('ipvStatusDuration').textContent =
        `In this status for ${APPLICATION.status_duration}`;

    const noteEl = document.getElementById('ipvReturnedNote');
    if (APPLICATION.status === 'returned') {
        noteEl.classList.remove('d-none');
        noteEl.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i> ${escapeHtml(APPLICATION.returned_reason || 'Application returned for correction.')}`;
    } else {
        noteEl.classList.add('d-none');
    }
}

function renderInfoRow() {
    document.getElementById('ipvAssignedOfficer').textContent = APPLICATION.assigned_officer;
    document.getElementById('ipvSlaDue').textContent = APPLICATION.sla_due;
}

// ---------------------------------------------------------------
// Transportation Details tab
// ---------------------------------------------------------------

function renderTransportDetails() {
    const el = document.getElementById('ipvTransportDetails');
    const rows = [
        { icon: 'bi-calendar-event', label: 'ETA', value: APPLICATION.eta },
        { icon: 'bi-truck', label: 'Transport Type', value: APPLICATION.transport_type },
        { icon: 'bi-geo-alt', label: 'Entry Point', value: APPLICATION.entry_point },
        { icon: 'bi-info-circle', label: 'Entry Point Notes', value: APPLICATION.entry_point_description },
    ];
    el.innerHTML = rows.map((r) => `
        <div class="ipv-detail-row">
            <div class="ipv-detail-icon"><i class="bi ${r.icon}"></i></div>
            <span class="ipv-detail-label">${r.label}</span>
            <span class="ipv-detail-value">${escapeHtml(r.value)}</span>
        </div>
    `).join('');
}

// ---------------------------------------------------------------
// Permit List (accordion)
// ---------------------------------------------------------------

function renderPermitAccordion() {
    document.getElementById('ipvPermitCount').textContent = PERMITS.length;

    const el = document.getElementById('ipvPermitAccordion');
    el.innerHTML = PERMITS.map((permit) => {
        const cfg = PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
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
                    <button type="button" class="ipv-view-detail-btn" data-permit-number="${escapeHtml(permit.permit_number)}" title="View full details">
                        <i class="bi bi-arrow-up-right-square"></i>
                    </button>
                    <i class="bi bi-chevron-down ipv-chevron"></i>
                </div>
                <div class="ipv-permit-body">
                    <div class="ipv-permit-grid">
                        <div class="ipv-permit-meta"><span class="meta-label">Category</span><span class="meta-value">${escapeHtml(detail.category)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Usage</span><span class="meta-value">${escapeHtml(detail.usage)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Purpose</span><span class="meta-value">${escapeHtml(permit.purpose)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Quantity</span><span class="meta-value">${permit.quantity.toLocaleString()} ${escapeHtml(permit.unit_measurement)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Value</span><span class="meta-value">RM ${money(permit.value)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Permit Number</span><span class="meta-value">${escapeHtml(permit.permit_number)}</span></div>
                    </div>

                    <div class="ipv-permit-subsection-title">Attachments (${permit.attachments.length})</div>
                    <div class="ipv-attach-list" id="attachList-${escapeHtml(permit.permit_number)}"></div>

                    <div class="ipv-permit-subsection-title">Further Details</div>
                    ${permit.conditions.map((c) => `
                        <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
                    `).join('')}

                    <div class="ipv-permit-remark is-${cfg.color}">
                        <i class="bi bi-info-circle"></i>
                        <span>${escapeHtml(permit.remark)}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    PERMITS.forEach((permit) => {
        const container = document.getElementById(`attachList-${permit.permit_number}`);
        renderAttachmentList(container, permit.attachments, 2);
    });
}

function initAccordionToggle() {
    const accordion = document.getElementById('ipvPermitAccordion');
    if (!accordion) return;

    accordion.addEventListener('click', (e) => {
        const viewBtn = e.target.closest('.ipv-view-detail-btn');
        if (viewBtn) {
            e.stopPropagation();
            openPermitDetail(viewBtn.dataset.permitNumber);
            return;
        }
        const header = e.target.closest('.ipv-permit-header');
        if (!header) return;
        header.closest('.ipv-permit-item')?.classList.toggle('is-open');
    });
}

let permitDetailOffcanvas = null;

function initPermitDetailOffcanvas() {
    const el = document.getElementById('permitDetailOffcanvas');
    if (el) permitDetailOffcanvas = new bootstrap.Offcanvas(el, { backdrop: true, keyboard: true, scroll: false });
}

function openPermitDetail(permitNumber) {
    const permit = PERMITS.find((p) => p.permit_number === permitNumber);
    if (!permit) return;

    const cfg = PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
    const detail = permit.consignment_detail;

    document.getElementById('permitDetailOffcanvasLabel').textContent = permit.permit_number;
    const badge = document.getElementById('pdBadge');
    badge.textContent = cfg.en;
    badge.className = `ipv-badge ms-2 is-${cfg.color}`;

    const rows = [
        { label: 'Category', value: detail.category },
        { label: 'Item', value: detail.item_name },
        { label: 'Usage', value: detail.usage },
        { label: 'Purpose', value: permit.purpose },
        { label: 'Quantity', value: `${permit.quantity.toLocaleString()} ${permit.unit_measurement}` },
        { label: 'Declared Value', value: `RM ${money(permit.value)}` },
    ];

    document.getElementById('pdDetailsContent').innerHTML = `
        <div class="pd-section-label">Consignment Info</div>
        ${rows.map((r) => `
            <div class="ipv-detail-row">
                <span class="ipv-detail-label">${escapeHtml(r.label)}</span>
                <span class="ipv-detail-value">${escapeHtml(r.value)}</span>
            </div>
        `).join('')}

        <div class="pd-section-label mt-4">Conditions (${permit.conditions.length})</div>
        ${permit.conditions.map((c) => `
            <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
        `).join('')}

        <div class="pd-section-label mt-4">Attachments (${permit.attachments.length})</div>
        <div class="ipv-attach-list" id="pd-attach-${permit.permit_number}"></div>
    `;

    renderAttachmentList(document.getElementById(`pd-attach-${permit.permit_number}`), permit.attachments, permit.attachments.length);

    permitDetailOffcanvas?.show();
}

// ---------------------------------------------------------------
// Condition tab
// ---------------------------------------------------------------

function renderConditionTab() {
    const el = document.getElementById('ipvConditionList');
    el.innerHTML = PERMITS.map((permit) => `
        <div class="ipv-condition-card">
            <div class="ipv-condition-card-head">
                <div>
                    <div class="ipv-condition-card-title">${escapeHtml(permit.consignment_detail.item_name)}</div>
                    <div class="ipv-condition-card-sub">${escapeHtml(permit.consignment_detail.category)} &middot; #${escapeHtml(permit.permit_number)}</div>
                </div>
            </div>
            ${permit.conditions.length ? permit.conditions.map((c) => `
                <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
            `).join('') : '<div class="ipv-condition-item"><span>No special conditions for this item.</span></div>'}
        </div>
    `).join('');
}

// ---------------------------------------------------------------
// Activity tab
// ---------------------------------------------------------------

function renderActivityTimeline() {
    const el = document.getElementById('ipvActivityTimeline');
    if (!ACTIVITY_LOG.length) {
        el.innerHTML = '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity recorded yet.</p></div>';
        return;
    }
    el.innerHTML = ACTIVITY_LOG.map((entry) => {
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
// Tabs
// ---------------------------------------------------------------

function initTabs() {
    const tabs = document.querySelectorAll('.ipv-tabnav-item');
    const panes = document.querySelectorAll('.ipv-tabpane');

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.ipvTab;
            tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
            panes.forEach((pane) => pane.classList.toggle('is-active', pane.dataset.ipvPane === target));
        });
    });

    document.getElementById('ipvViewPermitsLink')?.addEventListener('click', () => {
        document.querySelector('.ipv-tabnav-item[data-ipv-tab="permits"]')?.click();
    });
}

// ---------------------------------------------------------------
// Clerk Verification panel + Reject modal
// ---------------------------------------------------------------

function initClerkPanel() {
    const checkboxes = document.querySelectorAll('[data-clerk-check]');
    const verifyBtn = document.getElementById('ipvVerifyBtn');

    function updateVerifyBtnState() {
        verifyBtn.disabled = !Array.from(checkboxes).every((cb) => cb.checked);
    }
    checkboxes.forEach((cb) => cb.addEventListener('change', updateVerifyBtnState));

    verifyBtn.addEventListener('click', () => {
        APPLICATION.status = 'technical_review';
        APPLICATION.status_duration = 'Just now';
        renderStageStepper();
        renderClerkDecidedState('verified');

        ACTIVITY_LOG.push({
            stage: 'doc_verification',
            title: 'Documents Verified by Clerk',
            description: `Verified by ${CLERK_NAME}. Forwarded to Technical Review.`,
            time: nowString(),
        });
        renderActivityTimeline();
    });

    const rejectModalEl = document.getElementById('ipvRejectModal');
    const reasonInput = document.getElementById('ipvRejectReason');
    const confirmBtn = document.getElementById('ipvRejectConfirmBtn');

    document.getElementById('ipvRejectBtn').addEventListener('click', () => {
        reasonInput.value = '';
        confirmBtn.disabled = true;
        bootstrap.Modal.getOrCreateInstance(rejectModalEl).show();
    });

    document.querySelectorAll('.ipv-reject-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            reasonInput.value = chip.dataset.reason;
            confirmBtn.disabled = false;
        });
    });

    reasonInput.addEventListener('input', (e) => {
        confirmBtn.disabled = e.target.value.trim().length === 0;
    });

    confirmBtn.addEventListener('click', () => {
        const reason = reasonInput.value.trim();
        if (!reason) return;

        APPLICATION.status = 'returned';
        APPLICATION.returned_reason = reason;
        renderStageStepper();
        renderClerkDecidedState('rejected', reason);

        ACTIVITY_LOG.push({
            stage: 'returned',
            title: 'Application Returned for Amendment',
            description: reason,
            time: nowString(),
        });
        renderActivityTimeline();

        bootstrap.Modal.getInstance(rejectModalEl)?.hide();
    });
}

function renderClerkDecidedState(type, reason) {
    const panel = document.getElementById('ipvClerkPanel');
    panel.classList.add('is-decided');

    if (type === 'verified') {
        panel.innerHTML = `
            <div class="ipv-clerk-decided is-verified">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <div class="ipv-clerk-decided-title">Verified by ${escapeHtml(CLERK_NAME)}</div>
                    <div class="ipv-clerk-decided-sub">Forwarded to Technical Review — ${nowString()}</div>
                </div>
            </div>
        `;
    } else {
        panel.innerHTML = `
            <div class="ipv-clerk-decided is-rejected">
                <i class="bi bi-x-circle-fill"></i>
                <div>
                    <div class="ipv-clerk-decided-title">Returned for Amendment by ${escapeHtml(CLERK_NAME)}</div>
                    <div class="ipv-clerk-decided-sub">"${escapeHtml(reason)}" — ${nowString()}</div>
                </div>
            </div>
        `;
    }
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

function init() {
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
    initClerkPanel();
}

document.addEventListener('DOMContentLoaded', init);

window.ClerkDocVerification = {
    setApplication(data) { Object.assign(APPLICATION, data); renderHeaderInfo(); renderParties(); renderAppAttachments(); renderStageStepper(); renderInfoRow(); renderTransportDetails(); },
    setPermits(data) { PERMITS.length = 0; PERMITS.push(...data); renderPermitAccordion(); renderConditionTab(); },
};