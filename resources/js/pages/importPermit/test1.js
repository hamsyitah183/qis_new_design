/**
 * importPermitView.js
 * ------------------------------------------------------------------
 * Dummy data + rendering for import_permit_view.blade.php.
 *
 * Everything under APPLICATION / PERMITS / ACTIVITY_LOG is placeholder
 * data shaped after IpApplication / IpConsignmentPermit so it's a
 * straightforward swap later:
 *
 *   APPLICATION  ~ IpApplication (+ importer, exporter, entryPoint)
 *   PERMITS      ~ IpApplication->consignmentPermits (+ consignment_detail, attachments, condition)
 *   ACTIVITY_LOG ~ IpApplication->activity_log (ImportPermitLog)
 *
 * Import in your page entry:
 *   import './importPermitView.js';
 *   import '../../../css/pages/importPermit/importPermitView.css'; // adjust path
 */

import { initPermitDetailOffcanvas, openPermitDetail } from "./test2";
import { initScheduleCalendar } from "./test3";

// ---------------------------------------------------------------
// Config — label + color lookups. Color keys map to
// .ipv-badge.is-*, .ipv-stage-step.is-*, .ipv-timeline-icon.is-*
// classes in importPermitView.css, all driven by the root variables.
// ---------------------------------------------------------------

const STAGE_ORDER = [
    'submitted', 'doc_verification', 'technical_review',
    'awaiting_payment', 'payment_processing', 'completed',
];

export const STAGE_CONFIG = {
    submitted:           { en: 'Submitted',              bm: 'Dihantar',                  icon: 'bi-send-check',         color: 'info' },
    doc_verification:    { en: 'Document Verification',  bm: 'Semakan Dokumen',           icon: 'bi-file-earmark-check', color: 'secondary' },
    returned:            { en: 'Returned / Rejected',     bm: 'Dikembalikan / Ditolak',    icon: 'bi-arrow-return-left',  color: 'danger' },
    technical_review:    { en: 'Technical Review',        bm: 'Penilaian Pegawai',         icon: 'bi-clipboard-check',    color: 'primary' },
    awaiting_payment:    { en: 'Awaiting Payment',        bm: 'Menunggu Pembayaran',       icon: 'bi-hourglass-split',    color: 'warning' },
    payment_processing:  { en: 'Payment Processing',      bm: 'Proses Pengesahan Bayaran', icon: 'bi-credit-card',        color: 'orange' },
    completed:           { en: 'Completed',               bm: 'Selesai',                   icon: 'bi-check-circle',       color: 'success' },
    permit_approved:     { en: 'Permit Approved',         bm: 'Permit Diluluskan',         icon: 'bi-check-circle',       color: 'success' },
    permit_rejected:     { en: 'Permit Rejected',         bm: 'Permit Ditolak',            icon: 'bi-x-circle',           color: 'danger' },
    payment:             { en: 'Payment Update',          bm: 'Kemaskini Bayaran',         icon: 'bi-credit-card-2-back', color: 'orange' },
    email:               { en: 'Notification Sent',       bm: 'Notifikasi Dihantar',       icon: 'bi-envelope-check',     color: 'gray' },
};

export const PERMIT_STATUS_CONFIG = {
    queued:          { en: 'Queued for Review',            bm: 'Dalam Proses Semakan',         color: 'info' },
    approved:        { en: 'Approved',                      bm: 'Diluluskan',                   color: 'success' },
    rejected:        { en: 'Rejected',                      bm: 'Ditolak',                      color: 'danger' },
    pending_payment: { en: 'Pending Payment Authorization', bm: 'Menunggu Pengesahan Bayaran',  color: 'warning' },
    issued:          { en: 'Issued / Active',                bm: 'Dikeluarkan / Aktif',          color: 'teal' },
    payment_failed:  { en: 'Payment Failed',                 bm: 'Bayaran Gagal',                color: 'orange' },
};

// ---------------------------------------------------------------
// Dummy data
// ---------------------------------------------------------------

const APPLICATION = {
    application_id: 'IP-2025-00456',
    type: 'Import Permit',
    status: 'payment_processing',
    status_duration: '6 hours',
    returned_reason: null,
    tags: [
        { label: 'Category 1', color: 'primary' },
        { label: 'Repeat Importer', color: 'secondary' },
    ],
    submitted_by: 'Tan Wei Ling',
    submitted_at: '12 May 2025, 9:02 AM',
    downloaded_count: 1,
    assigned_officer: 'Ahmad Zulkifli',
    sla_due: 'Due in 1 day 4 hours',
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
        { name: 'Invoice_IP-2025-00456.pdf', size: '420 KB', path: '/consignment/attachment/10', mime: 'application/image'  },
        { name: 'Packing_List.xlsx', size: '88 KB' },
        { name: 'Letter_of_Authorization.docx', size: '156 KB',  path: '/consignment/attachment/9', mime: 'application/image'  },
        { name: 'Bill_of_Lading.pdf', size: '310 KB' ,  path: '/consignment/attachment/8', mime: 'application/image' },
    ],
};

export const PERMITS = [
    {
        permit_number: 'PMT-1201',
        consignment_detail: { category: 'Fresh Produce', item_name: 'Fresh Fruit — Corn', usage: 'Commercial Sale' },
        quantity: 1200, unit_measurement: 'KG', purpose: 'Commercial Sale', value: 2480,
        status: 'issued',
        remark: 'Payment authorized on 16 May 2025. Permit is active until 16 Nov 2025.',
        attachments: [
            { name: 'Phytosanitary_Cert.pdf', size: '210 KB' },
            { name: 'Fumigation_Cert.pdf', size: '180 KB' },
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
        status: 'rejected',
        remark: 'Rejected — annual import quota for this species has been exhausted.',
        attachments: [
            { name: 'Health_Cert.pdf', size: '195 KB' },
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
        status: 'pending_payment',
        remark: 'Payment submitted by applicant — awaiting bank authorization.',
        attachments: [
            { name: 'Health_Cert_Seafood.pdf', size: '230 KB' },
            { name: 'Cold_Chain_Report.pdf', size: '142 KB' },
            { name: 'Catch_Certificate.pdf', size: '98 KB' },
        ],
        conditions: [
            'Must maintain cold-chain temperature below -18°C.',
            'Requires a valid catch certificate.',
        ],
    },
    {
        permit_number: 'PMT-1204',
        consignment_detail: { category: 'Agricultural Seedlings', item_name: 'Rubber Seedlings', usage: 'Research' },
        quantity: 80, unit_measurement: 'Unit', purpose: 'Research', value: 320,
        status: 'queued',
        remark: 'Awaiting officer evaluation. No action needed from applicant yet.',
        attachments: [
            { name: 'Research_Permit_Letter.pdf', size: '120 KB' },
        ],
        conditions: [
            'Restricted to approved research institutions only.',
        ],
    },
    {
        permit_number: 'PMT-1205',
        consignment_detail: { category: 'Processed Food', item_name: 'Canned Pineapple', usage: 'Commercial Sale' },
        quantity: 2000, unit_measurement: 'KG', purpose: 'Commercial Sale', value: 4100,
        status: 'approved',
        remark: 'Approved by officer. Invoice will be generated for payment shortly.',
        attachments: [
            { name: 'Halal_Cert.pdf', size: '165 KB' },
            { name: 'Lab_Analysis.pdf', size: '205 KB' },
        ],
        conditions: [
            'Must carry valid halal certification.',
            'Subject to laboratory analysis on a sampling basis.',
        ],
    },
    {
        permit_number: 'PMT-1206',
        consignment_detail: { category: 'Ornamental Plants', item_name: 'Orchid Hybrids', usage: 'Commercial Sale' },
        quantity: 600, unit_measurement: 'Unit', purpose: 'Commercial Sale', value: 1750,
        status: 'payment_failed',
        remark: 'Payment was declined by the issuing bank. Applicant has been notified to retry.',
        attachments: [
            { name: 'CITES_Permit.pdf', size: '175 KB' },
        ],
        conditions: [
            'Requires a valid CITES permit for protected species.',
        ],
    },
];

const ACTIVITY_LOG = [
    { stage: 'submitted',          title: 'Application Submitted',                       description: 'Application IP-2025-00456 was lodged by Tan Wei Ling.', time: '12 May 2025, 9:02 AM' },
    { stage: 'email',              title: 'Email Delivered: Submission Confirmation',     description: 'A confirmation email was sent to the applicant.', time: '12 May 2025, 9:03 AM' },
    { stage: 'doc_verification',   title: 'Stage: Submitted → Document Verification',     description: 'Application moved to clerk review for document checks.', time: '12 May 2025, 10:15 AM' },
    { stage: 'technical_review',   title: 'Stage: Document Verification → Technical Review', description: 'Documents verified. Forwarded to officer for permit-level evaluation.', time: '13 May 2025, 2:40 PM' },
    { stage: 'permit_approved',    title: 'Permit PMT-1201 Approved',                     description: 'Reviewed and approved by Ahmad Zulkifli.', time: '14 May 2025, 11:05 AM' },
    { stage: 'permit_rejected',    title: 'Permit PMT-1202 Rejected',                     description: 'Rejected — import quota for this species has been exhausted.', time: '14 May 2025, 11:20 AM' },
    { stage: 'awaiting_payment',   title: 'Stage: Technical Review → Awaiting Payment',    description: 'At least one permit approved. Invoice generated for payment.', time: '14 May 2025, 11:25 AM' },
    { stage: 'payment',            title: 'Payment Submitted',                            description: 'Applicant submitted payment for PMT-1201 and PMT-1203.', time: '15 May 2025, 4:05 PM' },
    { stage: 'payment_processing', title: 'Stage: Awaiting Payment → Payment Processing',  description: 'Payment is pending bank authorization.', time: '15 May 2025, 4:10 PM' },
    { stage: 'permit_approved',    title: 'Permit PMT-1201 Issued',                        description: 'Payment authorized. Permit is now active.', time: '16 May 2025, 9:00 AM' },
];

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

export function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

export function money(n) {
    return Number(n || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export function fileMeta(filename) {
    const ext = (filename.split('.').pop() || '').toLowerCase();
    if (ext === 'pdf') return { icon: 'bi-file-earmark-pdf-fill', cls: 'is-pdf' };
    if (['xlsx', 'xls', 'csv'].includes(ext)) return { icon: 'bi-file-earmark-excel-fill', cls: 'is-excel' };
    if (['doc', 'docx'].includes(ext)) return { icon: 'bi-file-earmark-word-fill', cls: 'is-word' };
    if (['jpg', 'jpeg', 'png'].includes(ext)) return { icon: 'bi-file-earmark-image-fill', cls: 'is-image' };
    if (['ai', 'psd'].includes(ext)) return { icon: 'bi-file-earmark-richtext-fill', cls: 'is-design' };
    if (['zip', 'rar'].includes(ext)) return { icon: 'bi-file-earmark-zip-fill', cls: 'is-zip' };
    return { icon: 'bi-file-earmark-fill', cls: 'is-default' };
}

// ---------------------------------------------------------------
// Attachment chips — shared by sidebar + each permit accordion item.
// Clicking the "+N" tile reveals the rest in place.
// ---------------------------------------------------------------

const attachmentRegistry = new Map();
let attachmentSeq = 0;

function attachmentChipHtml(file) {
    const meta = fileMeta(file.name);
    return `
        <div class="ipv-attach-chip">
            <div class="ipv-attach-icon ${meta.cls}"><i class="bi ${meta.icon}"></i></div>
            <div class="ipv-attach-info">
                <div class="ipv-attach-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                <div class="ipv-attach-size">${escapeHtml(file.size)} &middot; <a href="#" onclick="return false;">Download</a></div>
            </div>
        </div>
    `;
}

// ============================================================
// ATTACHMENT VIEWER – OFF CANVAS
// ============================================================

// Global map to store full attachment arrays keyed by listId
const attachmentDataMap = new Map();

// Current viewer state
let currentListId = null;
let currentIndex = 0;

let attachmentOffcanvas = null;

function initOffcanvas() {
    const el = document.getElementById('attachmentOffcanvas');
    if (el) {
        attachmentOffcanvas = new bootstrap.Offcanvas(el, {
            backdrop: true,
            keyboard: true,
            scroll: false
        });
        // When hidden, ensure backdrop is removed
        el.addEventListener('hidden.bs.offcanvas', function () {
            // Force-remove any leftover backdrop elements
            document.querySelectorAll('.offcanvas-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            // Also ensure body classes are removed
            document.body.classList.remove('offcanvas-open');
        });
    }
}

function openAttachmentViewer(listId, index) {
    const files = attachmentDataMap.get(listId);
    if (!files || !files.length) return;

    currentListId = listId;
    currentIndex = index;

    const file = files[currentIndex];
    if (!file) return;

    // Update content
    document.getElementById('attachmentTitle').textContent = file.name;
    document.getElementById('attachmentCounter').textContent = `${currentIndex + 1} / ${files.length}`;
    renderViewer(file);
    renderDetails(file);
    document.getElementById('attachmentPrevBtn').disabled = (currentIndex === 0);
    document.getElementById('attachmentNextBtn').disabled = (currentIndex === files.length - 1);

    // Show offcanvas
    if (attachmentOffcanvas) {
        attachmentOffcanvas.show();
    }
}

function renderViewer(file) {
    const container = document.getElementById('attachmentViewer');
    const mime = file.mime || '';
    const path = file.path || '';

    if (!path) {
        container.innerHTML = `<div class="text-muted"><i class="bi bi-file-earmark-fill fs-1"></i><br>No file available</div>`;
        return;
    }

    let html = '';
    if (mime.startsWith('image/')) {
        html = `<img src="${escapeHtml(path)}" alt="${escapeHtml(file.name)}" style="max-width:100%; max-height:70vh;">`;
    } else if (mime.startsWith('video/')) {
        html = `<video controls style="max-width:100%; max-height:70vh;"><source src="${escapeHtml(path)}" type="${escapeHtml(mime)}">Your browser does not support the video tag.</video>`;
    } else if (mime === 'application/pdf') {
        html = `<iframe src="${escapeHtml(path)}" style="width:100%; height:70vh; border:none;"></iframe>`;
    } else {
        // Fallback: show download link
        html = `
            <div class="text-center">
                <i class="bi bi-file-earmark-fill fs-1 d-block mb-3" style="color: var(--gray-5);"></i>
                <p class="text-muted">Preview not available for this file type.</p>
                <a href="${escapeHtml(path)}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </div>
        `;
    }
    container.innerHTML = html;
}

function renderDetails(file) {
    const container = document.getElementById('attachmentDetails');
    const fields = [
        { label: 'File Name', value: file.name },
        { label: 'File Size', value: file.size },
        { label: 'File Type', value: file.mime || 'Unknown' },
        { label: 'Path', value: file.path || '—' },
        // Add any extra metadata here
    ];
    container.innerHTML = fields.map(f => `
        <div class="detail-row">
            <span class="detail-label">${escapeHtml(f.label)}</span>
            <span class="detail-value">${escapeHtml(f.value)}</span>
        </div>
    `).join('');
}

// Navigation
document.addEventListener('click', (e) => {
    const prevBtn = e.target.closest('#attachmentPrevBtn');
    const nextBtn = e.target.closest('#attachmentNextBtn');
    if (prevBtn && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex > 0) {
            openAttachmentViewer(currentListId, currentIndex - 1);
        }
    }
    if (nextBtn && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex < files.length - 1) {
            openAttachmentViewer(currentListId, currentIndex + 1);
        }
    }
});

// Override attachment rendering to store data and make chips clickable
// Replace the paintAttachmentList function:

function paintAttachmentList(containerEl, files, visibleCount) {
    const listId = containerEl.dataset.listId;
    if (!listId) return;
    // Store full array
    attachmentDataMap.set(listId, files);

    const shown = files.slice(0, visibleCount);
    const remaining = files.length - shown.length;

    // Build chips with data attributes
    let html = shown.map((file, idx) => `
        <div class="ipv-attach-chip" data-list-id="${listId}" data-index="${idx}" style="cursor:pointer;">
            <div class="ipv-attach-icon ${fileMeta(file.name).cls}"><i class="bi ${fileMeta(file.name).icon}"></i></div>
            <div class="ipv-attach-info">
                <div class="ipv-attach-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                <div class="ipv-attach-size">${escapeHtml(file.size)} &middot; <a href="#" onclick="return false;">Download</a></div>
            </div>
        </div>
    `).join('');

    if (remaining > 0) {
        html += `<div class="ipv-attach-more" data-list-id="${listId}">+${remaining}</div>`;
    }
    containerEl.innerHTML = html;
}

// Add click listener on container for attachment chips (delegation)
document.addEventListener('click', (e) => {
    const chip = e.target.closest('.ipv-attach-chip');
    if (chip) {
        const listId = chip.dataset.listId;
        const index = parseInt(chip.dataset.index, 10);
        if (listId !== undefined && !isNaN(index)) {
            openAttachmentViewer(listId, index);
        }
        e.preventDefault();
    }
});



export function renderAttachmentList(containerEl, files, visibleCount) {
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
    if (!moreTile) return;
    const listId = moreTile.dataset.listId;
    const files = attachmentRegistry.get(listId);
    const containerEl = document.querySelector(`[data-list-id="${listId}"]`);
    if (containerEl && files) paintAttachmentList(containerEl, files, files.length);
});

// ---------------------------------------------------------------
// Render: sidebar
// ---------------------------------------------------------------

function renderHeaderInfo() {
    document.getElementById('ipvAppType').textContent = APPLICATION.type;
    document.getElementById('ipvAppId').textContent = APPLICATION.application_id;
    document.getElementById('ipvSubmittedBy').textContent = APPLICATION.submitted_by;
    document.getElementById('ipvDownloadBadge').innerHTML = `<i class="bi bi-download"></i> ${APPLICATION.downloaded_count}`;
    document.getElementById('ipvCreatedAt').textContent = `Application submitted on ${APPLICATION.submitted_at}`;

    document.getElementById('ipvTags').innerHTML = APPLICATION.tags.map((tag) =>
        `<span class="ipv-tag is-${tag.color}">${escapeHtml(tag.label)}</span>`
    ).join('');

    const total = PERMITS.reduce((sum, p) => sum + p.value, 0);
    document.getElementById('ipvTotalValue').textContent = `RM ${money(total)}`;
}

function partyBlockHtml(party, label, isExporter) {
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
    const importerEl = document.getElementById('ipvImporterBlock');
    const exporterEl = document.getElementById('ipvExporterBlock');
    importerEl.innerHTML = partyBlockHtml(APPLICATION.importer, 'Importer', false);
    exporterEl.innerHTML = partyBlockHtml(APPLICATION.exporter, 'Exporter', true);
    exporterEl.classList.add('is-exporter');
}

function renderAppAttachments() {
    renderAttachmentList(document.getElementById('ipvAppAttachments'), APPLICATION.attachments, 3);
}

// ---------------------------------------------------------------
// Render: status header + stage stepper + info row
// ---------------------------------------------------------------

function renderStageStepper() {
    const el = document.getElementById('ipvStageStepper');
    const currentIndex = STAGE_ORDER.indexOf(APPLICATION.status);

    el.innerHTML = STAGE_ORDER.map((key, i) => {
        const cfg = STAGE_CONFIG[key];
        let cls = 'is-pending';

        if (APPLICATION.status === 'returned') {
            if (key === 'submitted') cls = 'is-complete';
            else if (key === 'doc_verification') cls = 'is-returned';
        } else if (i < currentIndex) {
            cls = 'is-complete';
        } else if (i === currentIndex) {
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
// Render: Transportation Details tab
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
// Render: Permit List (accordion)
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
                        <i class="bi bi-eye"></i>
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

    // Fill in each permit's attachment chips now that containers exist in the DOM.
    PERMITS.forEach((permit) => {
        const container = document.getElementById(`attachList-${permit.permit_number}`);
        renderAttachmentList(container, permit.attachments, 2);
    });
}

function initAccordionToggle() {
    const accordion = document.getElementById('ipvPermitAccordion');
    if (!accordion) {
        console.error('❌ Accordion container (#ipvPermitAccordion) not found');
        return;
    }

    // Remove any previous listener to avoid duplicates (just in case)
    accordion.removeEventListener('click', accordion._toggleHandler);
    
    // Define the handler
    const handler = function(e) {
        // 1. "View full details" button → open offcanvas, don't toggle
        const viewBtn = e.target.closest('.ipv-view-detail-btn');
        if (viewBtn) {
            e.stopPropagation();
            const permitNumber = viewBtn.dataset.permitNumber;
            if (permitNumber) {
                try {
                    openPermitDetail(permitNumber);
                } catch (err) {
                    console.error('Error opening permit detail:', err);
                }
            }
            return;
        }

        // 2. Anywhere else on the header → toggle the row
        const header = e.target.closest('.ipv-permit-header');
        if (!header) return;

        const item = header.closest('.ipv-permit-item');
        if (item) {
            // Toggle the 'is-open' class
            const isOpen = item.classList.toggle('is-open');
            console.log(`Toggled ${item.dataset.permit} – now ${isOpen ? 'open' : 'closed'}`);
        }
    };

    // Store the handler so we can remove it later if needed
    accordion._toggleHandler = handler;
    accordion.addEventListener('click', handler);
    console.log('✅ Accordion toggle listener attached');
}

// ---------------------------------------------------------------
// Render: Condition tab
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
// Render: Activity tab
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

    // "View" on the value box jumps straight to the Permit List tab.
    const viewLink = document.getElementById('ipvViewPermitsLink');
    if (viewLink) {
        viewLink.addEventListener('click', () => {
            document.querySelector('.ipv-tabnav-item[data-ipv-tab="permits"]')?.click();
        });
    }
}

function renderPermitDownloadList() {
    const tbody = document.getElementById('permitDownloadTableBody');
    // Filter permits with status 'issued' (or 'active')
    const available = PERMITS.filter(p => p.status === 'issued' || p.status === 'active');

    if (!available.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">No permits available for download.</td></tr>`;
        document.getElementById('downloadSelectedPermitsBtn').disabled = true;
        return;
    }

    tbody.innerHTML = available.map((permit, idx) => {
        const statusCfg = PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
        return `
            <tr>
                <td><input type="checkbox" class="form-check-input permit-checkbox" data-index="${idx}" value="${permit.permit_number}"></td>
                <td><strong>${escapeHtml(permit.permit_number)}</strong></td>
                <td>${escapeHtml(permit.consignment_detail.item_name)}</td>
                <td><span class="ipv-badge is-${statusCfg.color}">${escapeHtml(statusCfg.en)}</span></td>
                <td style="text-align: right;">
                    <a href="/consignment/generate/${escapeHtml(permit.permit_number)}" target="_blank" class="btn btn-sm btn-info">
                        <i class="bi bi-eye me-1"></i> View
                    </a>
                </td>
            </tr>
        `;
    }).join('');

    // Reset selection counter
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.permit-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('downloadSelectedPermitsBtn').disabled = (count === 0);
}

// Event listeners
document.addEventListener('change', function(e) {
    if (e.target.matches('.permit-checkbox')) {
        updateSelectedCount();
        // Uncheck "Select All" if not all are checked
        const allCheckboxes = document.querySelectorAll('.permit-checkbox');
        const allChecked = document.querySelectorAll('.permit-checkbox:checked');
        const selectAll = document.getElementById('selectAllPermits');
        if (selectAll) {
            selectAll.checked = (allCheckboxes.length === allChecked.length && allCheckboxes.length > 0);
        }
    }
});

document.getElementById('selectAllPermits')?.addEventListener('change', function(e) {
    const checked = e.target.checked;
    document.querySelectorAll('.permit-checkbox').forEach(cb => cb.checked = checked);
    updateSelectedCount();
});

document.getElementById('downloadSelectedPermitsBtn')?.addEventListener('click', function() {
    const selected = document.querySelectorAll('.permit-checkbox:checked');
    if (!selected.length) return;

    // For demo, we'll open each permit in a new tab (or implement bulk download)
    // You can replace this with a form POST or API call.
    selected.forEach(cb => {
        const permitNumber = cb.value;
        // Example: open each permit's download link
        window.open(`/consignment/generate/${permitNumber}`, '_blank');
    });

    // Optionally close offcanvas
    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('permitListOffcanvas'));
    if (offcanvas) offcanvas.hide();
});

let permitListOffcanvas = null;

function initPermitOffcanvas() {
    const el = document.getElementById('permitListOffcanvas');
    if (el && !permitListOffcanvas) {
        // Ensure the offcanvas is hidden initially (remove leftover 'show' class)
        el.classList.remove('show');
        el.style.display = 'none'; // fallback
        permitListOffcanvas = new bootstrap.Offcanvas(el, {
            backdrop: true,
            keyboard: true,
            scroll: false
        });
        // Reset display after Bootstrap initialization (it will manage it)
        el.style.display = '';
        // Listen for hidden event to reset state if needed
        el.addEventListener('hidden.bs.offcanvas', function () {
            // optional cleanup
        });
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
    initOffcanvas();          // for attachment viewer
    initPermitOffcanvas();    // for permit list (now hidden)
    initPermitDetailOffcanvas()

    initScheduleCalendar()

    // Download badge click
    const badge = document.getElementById('ipvPrintPermitBtn');
    if (badge) {
        badge.addEventListener('click', function(e) {
            e.preventDefault();
            // Ensure offcanvas is initialized (should be already)
            if (!permitListOffcanvas) initPermitOffcanvas();
            if (permitListOffcanvas) {
                renderPermitDownloadList();
                permitListOffcanvas.show();
            } else {
                console.error('Permit offcanvas could not be initialized');
            }
        });
    } else {
        console.error('ipvDownloadBadge not found');
    }

    // Initialize Bootstrap tooltips for the permit detail tabs
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) {
        return new bootstrap.Tooltip(el);
    });
    }

document.addEventListener('DOMContentLoaded', init);

// Small API surface for swapping in real data later.
window.ImportPermitView = {
    setApplication(data) { Object.assign(APPLICATION, data); renderHeaderInfo(); renderParties(); renderAppAttachments(); renderStageStepper(); renderInfoRow(); renderTransportDetails(); },
    setPermits(data) { PERMITS.length = 0; PERMITS.push(...data); renderPermitAccordion(); renderConditionTab(); },
    setActivityLog(data) { ACTIVITY_LOG.length = 0; ACTIVITY_LOG.push(...data); renderActivityTimeline(); },
};