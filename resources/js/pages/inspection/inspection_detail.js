/**
 * inspection_detail.js
 * Renders the new inspection_detail.blade.php (card/sidebar/tabs design,
 * ported from the Consignment module) from real data.
 *
 * Unlike Consignment (split into consignment1.js / consignment2.js /
 * consignment-actions.js), this stays a single file — same shape as the
 * legacy inspection_detail.js, just restructured around the new UI and
 * carrying over the *real* endpoints/field names that file already used:
 *   - GET  /inspection_application/{id}/data
 *   - POST /internal/inspection/{id}/status        { status }
 *   - POST /public/inspection/{id}/status           { status }
 *   - POST /internal/inspection_item/{id}/accept    (bulk, id = application id)
 *   - POST /internal/inspection_item/{id}/reject    (bulk, id = application id)
 *   - POST /permit/print                            { type: 'Inspection', permit_number }
 *   - GET  /inspection/generate/{id}
 *   - POST /public/save-inspection/{permitId}
 *   - POST /payment/signed-url                      { type: 'inspection', application_id: APPLICATION.id }
 *
 * [TODO] A few things below are best-effort adaptations, not verified
 * against a real payload — see inline TODO markers, especially:
 *   - whether the API's /inspection_application/{id}/data response includes
 *     an application-level `attachment`/`attachments` array
 *   - the per-permit fee used in renderPendingPaymentTable / payBulk (the
 *     legacy code just hardcoded "RM 10.00" as a placeholder total)
 *   - permission names in hasPermission() calls (no permission gate existed
 *     in the old Inspection code — falls back to role checks either way)
 */

import $ from "jquery";
import Swal from "sweetalert2";
import Dropzone from "dropzone";
import "dropzone/dist/dropzone.css";
import select2 from "select2";
select2(window.jQuery);
import "select2/dist/css/select2.min.css";
import { applyTranslations } from "../../app";
import { loadProfile } from "../auth/profile";

Dropzone.autoDiscover = false;

// ---------------------------------------------------------------
// Config
// ---------------------------------------------------------------

const STAGE_ORDER = [
    'submitted', 'doc_verification', 'technical_review',
    'awaiting_payment', 'payment_processing', 'completed',
];

const STAGE_CONFIG = {
    submitted:           { en: 'Submitted',              bm: 'Dihantar',                  icon: 'bi-send-check',         color: 'info' },
    doc_verification:    { en: 'Clerk Review In-Progress', bm: 'Semakan Kerani Dalam Proses', icon: 'bi-file-earmark-check', color: 'secondary' },
    returned:            { en: 'Returned / Rejected',     bm: 'Dikembalikan / Ditolak',    icon: 'bi-arrow-return-left',  color: 'danger' },
    technical_review:    { en: 'Clerk Verified',          bm: 'Disahkan Kerani',           icon: 'bi-clipboard-check',    color: 'primary' },
    awaiting_payment:    { en: 'Awaiting Payment',        bm: 'Menunggu Pembayaran',       icon: 'bi-hourglass-split',    color: 'warning' },
    payment_processing:  { en: 'Payment Processing',      bm: 'Proses Pengesahan Bayaran', icon: 'bi-credit-card',        color: 'orange' },
    completed:           { en: 'Completed',               bm: 'Selesai',                   icon: 'bi-check-circle',       color: 'success' },
    permit_approved:     { en: 'Certificate Approved',    bm: 'Sijil Diluluskan',          icon: 'bi-check-circle',       color: 'success' },
    permit_rejected:     { en: 'Certificate Rejected',    bm: 'Sijil Ditolak',             icon: 'bi-x-circle',           color: 'danger' },
    payment:             { en: 'Payment Update',          bm: 'Kemaskini Bayaran',         icon: 'bi-credit-card-2-back', color: 'orange' },
    email:               { en: 'Notification Sent',       bm: 'Notifikasi Dihantar',       icon: 'bi-envelope-check',     color: 'gray' },
};

const PERMIT_STATUS_CONFIG = {
    processing:               { en: 'Processing',                bm: 'Sedang Diproses',    color: 'info' },
    reapplied:                { en: 'Reapplied',                  bm: 'Dipohon Semula',     color: 'info' },
    'pending for payment':    { en: 'Pending For Payment',        bm: 'Menunggu Bayaran',   color: 'warning' },
    'payment processing':     { en: 'Payment Processing',         bm: 'Bayaran Diproses',   color: 'orange' },
    paid:                     { en: 'Paid',                       bm: 'Telah Dibayar',      color: 'success' },
    completed:                { en: 'Completed',                  bm: 'Selesai',            color: 'success' },
    rejected:                 { en: 'Rejected',                   bm: 'Ditolak',            color: 'danger' },
    'payment failed':         { en: 'Payment Failed',             bm: 'Bayaran Gagal',      color: 'orange' },
    queued:                   { en: 'Queued for Review',          bm: 'Dalam Proses Semakan', color: 'info' },
};

// [TODO] Legacy code hardcoded a flat "RM 10.00" total regardless of permit
// count (see the old updateTotalValue()) — clearly an unfinished stub, not
// verified business logic. Using a configurable per-permit fee like
// Consignment's CONSIGNMENT_PERMIT_FEE instead; confirm the real amount.
const INSPECTION_PERMIT_FEE = 10;

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

// ---------------------------------------------------------------
// Live data
// ---------------------------------------------------------------

let APPLICATION = {};
let PERMITS = [];
let ACTIVITY_LOG = [];
let RAW_ACTIVITY_LOG = [];
let userData = null;

function getCurrentUserRoles() {
    if (!userData) return [];
    if (Array.isArray(userData.roles)) {
        return userData.roles.map((r) => (typeof r === 'string' ? r : r?.name)).filter(Boolean);
    }
    if (typeof userData.role === 'string') return [userData.role];
    return [];
}

function getCurrentUserType() {
    return userData?.type || null;
}

function getCurrentUserUuid() {
    return userData?.uuid || null;
}

function hasPermission(permissionName) {
    const user = window.fullUser;
    if (user && user.permissions) {
        return user.permissions.some(p => p.name === permissionName);
    }
    const roles = getCurrentUserRoles();
    return roles.some(r => ['superadmin', 'admin', 'officer', 'clerk'].includes(r));
}

function isOwnerApplicant() {
    return getCurrentUserType() === 'public' && APPLICATION.user_id === getCurrentUserUuid();
}

// ---------------------------------------------------------------
// Fetch + map real data
// ---------------------------------------------------------------

function getApplicationId() {
    if (window.APPLICATION_ID) return window.APPLICATION_ID;
    const parts = window.location.pathname.split('/');
    return parts[2];
}

async function loadApplicationData() {
    const applicationId = getApplicationId();
    const res = await fetch(`/inspection_application/${applicationId}/data`);
    const json = await res.json();
    console.log('inspection application', json);

    mapApplication(json);
    mapPermits(json);
    mapActivityLog(json);
}

function mapAttachment(f) {
    return {
        name: f.file_name || f.name,
        size: f.file_size || f.size || '',
        path: f.id ? `/inspection/attachment/${f.id}` : (f.file_path || f.path || ''),
        mime: f.file_type || f.mime,
    };
}

function deriveStageKey(status) {
    const s = (status || '').toLowerCase();
    if (s.includes('draft')) return 'submitted';
    if (s.includes('clerk review')) return 'doc_verification';
    if (s.includes('clerk verified')) return 'technical_review';
    if (s.includes('completed')) return 'completed';
    if (s.includes('rejected') || s.includes('not approved')) return 'returned';
    if (s.includes('pending for payment')) return 'awaiting_payment';
    if (s.includes('payment processing')) return 'payment_processing';
    return 'submitted';
}

function mapApplication(json) {
    const importer = json.importer_detail || {};
    const exporter = json.exporter || json.user || {};
    const entryPoint = json.entry_point || {};
    const country = importer.country_info || {};
    const exporterCountry = exporter.country_info || {};

    const rawStatus = json.status || '';

    // print_calc lived on the first inspection_item in the legacy code
    // (`application.inspection_items[0].print_calc`) rather than the
    // application itself — carried over as-is.
    const firstItemPrintCalc =
        (json.inspection_items && json.inspection_items[0] && json.inspection_items[0].print_calc) ||
        json.print_calc || 0;

    APPLICATION = {
        // Old payment flow (`checkoutPage` handler) posted `application.id`
        // (numeric PK), not `application_id` (the string code) — kept
        // distinct so payBulk() doesn't silently send the wrong value.
        id: json.id,
        application_id: json.application_id,
        application_type: json.application_type || 'Inspection',
        type: 'Inspection Certificate',
        status: rawStatus,
        status_key: deriveStageKey(rawStatus),
        status_duration: json.status_duration || '',
        returned_reason: json.returned_reason || json.remark || null,
        tags: [],
        submitted_by: json.user?.fullname || exporter.fullname || exporter.name || '—',
        submitted_at: formatDateTime(json.created_at),
        downloaded_count: firstItemPrintCalc,
        eta: formatDate(json.eta),
        transport_type: json.transport_type || '—',
        entry_point: entryPoint.entry_name || json.entry_point_name || '—',
        entry_point_description: entryPoint.description || '',
        category_application: json.category_application,
        importer_verify: json.importer_verify || null,
        user_id: json.user_id || json.user?.uuid || null,
        importer: {
            name: importer.name || importer.fullname || '—',
            phone: importer.phone_no || importer.phone_number || '—',
            email: importer.email || '—',
            address: importer.address || [importer.address_1, importer.address_2, importer.postcode, importer.district]
                .filter(Boolean).join(', ') || '—',
            country: country.name || importer.country || '—',
        },
        exporter: {
            name: exporter.name || exporter.fullname || '—',
            phone: exporter.phone_no || exporter.phone_number || '—',
            email: exporter.email || '—',
            address: exporter.address || [exporter.address_1, exporter.address_2, exporter.postcode, exporter.district]
                .filter(Boolean).join(', ') || '—',
            country: exporterCountry.name || exporter.country || '—',
        },
        // [TODO] the legacy code never populated an application-level
        // attachments array — confirm the API actually returns one before
        // relying on the sidebar "Application Documents" section.
        attachments: (json.attachment || json.attachments || []).map(mapAttachment),
    };
}

function mapPermits(json) {
    const permits = json.inspection_items || [];
    PERMITS = permits.map((permit) => {
        const detail = permit.consignment_detail || {}; // field name kept as-is from the API
        const statusKey = (permit.status || 'processing').toLowerCase();
        return {
            id: permit.id,
            permit_number: permit.permit_number || '—',
            consignment_detail: {
                item_name: detail.item_name || '—',
                item_id: detail.item_id ?? null,
                purpose: detail.purpose || '—',
                uses: detail.uses || '—',
            },
            quantity: Number(detail.quantity || 0),
            unit_measurement: detail.measure || '',
            value: Number(detail.value || 0),
            status: statusKey,
            remark: permit.remark || '',
            attachments: (permit.attachments || []).map(mapAttachment),
            print_calc: permit.print_calc || 0,
            _raw: permit,
        };
    });
}

function mapActivityLog(json) {
    RAW_ACTIVITY_LOG = json.activity_log || [];
    ACTIVITY_LOG = RAW_ACTIVITY_LOG
        .slice()
        .sort((a, b) => new Date(a.time || a.created_at || 0) - new Date(b.time || b.created_at || 0))
        .map((entry) => ({
            stage: entry.stage || guessStage(entry.action || entry.title || ''),
            title: entry.action || entry.title || 'Update',
            // legacy applicationLog() read `log.causer.fullname` for the user
            user: entry.causer?.fullname || entry.user || entry.user_name || '—',
            description: entry.remark || entry.description || '',
            time: formatDateTime(entry.time || entry.created_at),
        }));
}

function guessStage(text) {
    const t = (text || '').toLowerCase();
    if (t.includes('reject')) return 'permit_rejected';
    if (t.includes('approve') || t.includes('accept')) return 'permit_approved';
    if (t.includes('payment')) return 'payment';
    if (t.includes('submit')) return 'submitted';
    if (t.includes('email') || t.includes('notif')) return 'email';
    return 'doc_verification';
}

function formatDate(value) {
    if (!value) return '—';
    const d = new Date(value);
    if (isNaN(d)) return value;
    return d.toLocaleDateString('en-GB');
}

function formatDateTime(value) {
    if (!value) return '—';
    const d = new Date(value);
    if (isNaN(d)) return value;
    return d.toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

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
    const ext = (filename || '').split('.').pop().toLowerCase();
    if (ext === 'pdf') return { icon: 'bi-file-earmark-pdf-fill', cls: 'is-pdf' };
    if (['xlsx', 'xls', 'csv'].includes(ext)) return { icon: 'bi-file-earmark-excel-fill', cls: 'is-excel' };
    if (['doc', 'docx'].includes(ext)) return { icon: 'bi-file-earmark-word-fill', cls: 'is-word' };
    if (['jpg', 'jpeg', 'png'].includes(ext)) return { icon: 'bi-file-earmark-image-fill', cls: 'is-image' };
    if (['ai', 'psd'].includes(ext)) return { icon: 'bi-file-earmark-richtext-fill', cls: 'is-design' };
    if (['zip', 'rar'].includes(ext)) return { icon: 'bi-file-earmark-zip-fill', cls: 'is-zip' };
    return { icon: 'bi-file-earmark-fill', cls: 'is-default' };
}

// ---------------------------------------------------------------
// Attachment chips + viewer
// ---------------------------------------------------------------

const attachmentRegistry = new Map();
let attachmentSeq = 0;
const attachmentDataMap = new Map();
let currentListId = null;
let currentIndex = 0;
let attachmentOffcanvas = null;

function initOffcanvas() {
    const el = document.getElementById('attachmentOffcanvas');
    if (el) {
        attachmentOffcanvas = new bootstrap.Offcanvas(el, { backdrop: true, keyboard: true, scroll: false, focus: false });
        el.addEventListener('hidden.bs.offcanvas', function () {
            document.querySelectorAll('.offcanvas-backdrop').forEach((b) => b.remove());
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

    const lang = getLang();
    document.getElementById('attachmentTitle').textContent = file.name;
    document.getElementById('attachmentCounter').textContent = `${currentIndex + 1} / ${files.length}`;
    renderViewer(file);
    renderDetails(file, lang);
    document.getElementById('attachmentPrevBtn').disabled = (currentIndex === 0);
    document.getElementById('attachmentNextBtn').disabled = (currentIndex === files.length - 1);

    if (attachmentOffcanvas) attachmentOffcanvas.show();
}

function renderViewer(file) {
    const container = document.getElementById('attachmentViewer');
    const path = file.path || '';

    if (!path) {
        container.innerHTML = `<div class="text-muted"><i class="bi bi-file-earmark-fill fs-1"></i><br>No file available</div>`;
        return;
    }

    let mime = (file.mime || '').toLowerCase();
    const isImage = mime.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(mime);
    const isVideo = mime.startsWith('video/') || ['mp4', 'webm', 'ogg', 'mov'].includes(mime);
    const isPdf = mime === 'application/pdf' || mime === 'pdf';

    let html = '';
    if (isImage) {
        html = `<img src="${escapeHtml(path)}" alt="${escapeHtml(file.name)}">`;
    } else if (isVideo) {
        html = `<video controls><source src="${escapeHtml(path)}" type="${escapeHtml(mime)}">Your browser does not support the video tag.</video>`;
    } else if (isPdf) {
        html = `<iframe src="${escapeHtml(path)}"></iframe>`;
    } else {
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

function renderDetails(file, lang) {
    const container = document.getElementById('attachmentDetails');
    const labels = {
        en: { name: 'File Name', size: 'File Size', type: 'File Type', path: 'Path' },
        bm: { name: 'Nama Fail', size: 'Saiz Fail', type: 'Jenis Fail', path: 'Laluan' }
    };
    const t = labels[lang] || labels.en;

    const fields = [
        { key: 'name', value: file.name },
        { key: 'size', value: file.size },
        { key: 'type', value: file.mime || 'Unknown' },
        { key: 'path', value: file.path || '—' },
    ];

    container.innerHTML = fields.map((f) => `
        <div class="detail-row">
            <span class="detail-label" data-en="${escapeHtml(labels.en[f.key])}" data-bm="${escapeHtml(labels.bm[f.key])}">${escapeHtml(t[f.key])}</span>
            <span class="detail-value">${escapeHtml(f.value)}</span>
        </div>
    `).join('');

    applyTranslations(container);
}

document.addEventListener('click', (e) => {
    const prevBtn = e.target.closest('#attachmentPrevBtn');
    const nextBtn = e.target.closest('#attachmentNextBtn');
    if (prevBtn && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex > 0) openAttachmentViewer(currentListId, currentIndex - 1);
    }
    if (nextBtn && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex < files.length - 1) openAttachmentViewer(currentListId, currentIndex + 1);
    }
});

function paintAttachmentList(containerEl, files, visibleCount) {
    const listId = containerEl.dataset.listId;
    if (!listId) return;
    attachmentDataMap.set(listId, files);

    const shown = files.slice(0, visibleCount);
    const remaining = files.length - shown.length;

    let html = shown.map((file, idx) => `
        <div class="ipv-attach-chip" data-list-id="${listId}" data-index="${idx}" style="cursor:pointer;">
            <div class="ipv-attach-icon ${fileMeta(file.name).cls}"><i class="bi ${fileMeta(file.name).icon}"></i></div>
            <div class="ipv-attach-info">
                <div class="ipv-attach-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                <div class="ipv-attach-size">${escapeHtml(file.size)} &middot;
                    <a href="#" class="ipv-download-link" data-en="Download" data-bm="Muat Turun" data-path="${escapeHtml(file.path)}" data-name="${escapeHtml(file.name)}">Download</a>
                </div>
            </div>
        </div>
    `).join('');

    if (remaining > 0) {
        html += `<div class="ipv-attach-more" data-list-id="${listId}">+${remaining}</div>`;
    }
    containerEl.innerHTML = html;
}

document.addEventListener('click', (e) => {
    if (e.target.closest('.ipv-download-link')) return;
    const chip = e.target.closest('.ipv-attach-chip');
    if (chip) {
        e.preventDefault();
        e.stopPropagation();
        const listId = chip.dataset.listId;
        const index = parseInt(chip.dataset.index, 10);
        if (listId !== undefined && !isNaN(index)) {
            openAttachmentViewer(listId, index);
        }
        return false;
    }
}, true);

document.getElementById('ipvDownloadAllApp')?.addEventListener('click', async function (e) {
    e.preventDefault();

    const attachments = APPLICATION.attachments || [];
    if (!attachments.length) {
        Swal.fire({
            icon: 'info',
            title: 'No Attachments',
            text: 'There are no application documents to download.',
            timer: 2000,
            showConfirmButton: false
        });
        return;
    }

    Swal.fire({
        title: 'Preparing download...',
        text: `Zipping ${attachments.length} file(s). Please wait.`,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        let JSZip;
        if (typeof window.JSZip !== 'undefined') {
            JSZip = window.JSZip;
        } else {
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
                script.onload = () => {
                    if (typeof window.JSZip !== 'undefined') {
                        JSZip = window.JSZip;
                        resolve();
                    } else {
                        reject(new Error('JSZip not available after loading'));
                    }
                };
                script.onerror = () => reject(new Error('Failed to load JSZip'));
                document.head.appendChild(script);
            });
        }

        const zip = new JSZip();
        let downloaded = 0;

        for (const file of attachments) {
            if (!file.path) continue;
            try {
                const response = await fetch(file.path, { credentials: 'include' });
                if (!response.ok) continue;
                const blob = await response.blob();
                zip.file(file.name || 'unnamed', blob);
                downloaded++;
            } catch (err) {
                console.error(`Error fetching ${file.name}:`, err);
            }
        }

        if (downloaded === 0) {
            Swal.fire({ icon: 'error', title: 'Download Failed', text: 'Could not retrieve any files. Check console for details.' });
            return;
        }

        const zipBlob = await zip.generateAsync({ type: 'blob' });
        const zipName = `Application_${APPLICATION.application_id}.zip`;
        const link = document.createElement('a');
        link.href = URL.createObjectURL(zipBlob);
        link.download = zipName;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(link.href);

        Swal.fire({ icon: 'success', title: 'Download Complete!', text: `${downloaded} file(s) downloaded as ${zipName}.`, timer: 2500, showConfirmButton: false });
    } catch (error) {
        console.error('Zip creation failed:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to create zip file. Please try again or contact support.' });
    }
});

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
    const lang = getLang();
    document.getElementById('ipvAppId').textContent = APPLICATION.application_id;
    document.getElementById('ipvSubmittedBy').textContent = APPLICATION.submitted_by;
    document.getElementById('ipvDownloadBadge').innerHTML = `<i class="bi bi-download"></i> ${APPLICATION.downloaded_count}`;

    const submittedLabel = lang === 'bm' ? 'Permohonan dihantar pada' : 'Application submitted on';
    document.getElementById('ipvCreatedAt').textContent = `${submittedLabel} ${APPLICATION.submitted_at}`;

    const total = PERMITS.reduce((sum, p) => sum + p.value, 0);
    document.getElementById('ipvTotalValue').textContent = `RM ${money(total)}`;

    const printBtn = document.getElementById('ipvPrintPermitBtn');
    if (printBtn) {
        printBtn.classList.add('generatePermit');
        printBtn.dataset.permit = APPLICATION.application_id;
        printBtn.dataset.type = APPLICATION.application_type;
    }
}

function partyBlockHtml(party, label) {
    const initial = (party.name || '?').charAt(0).toUpperCase();
    const lang = getLang();
    const labelText = lang === 'bm' ? (label === 'Importer' ? 'Pengimport' : 'Pengeksport') : label;
    return `
        <div class="ipv-party-header">
            <div class="ipv-party-avatar">${initial}</div>
            <div>
                <div class="ipv-party-name">${escapeHtml(party.name)}</div>
                <div class="ipv-party-sub">${escapeHtml(labelText)}</div>
            </div>
        </div>
        <div class="ipv-contact-row">
            <div class="ipv-contact-icon"><i class="bi bi-telephone"></i></div>
            <div><div class="ipv-contact-label">${lang === 'bm' ? 'Telefon' : 'Phone'}</div><div class="ipv-contact-value">${escapeHtml(party.phone)}</div></div>
        </div>
        <div class="ipv-contact-row">
            <div class="ipv-contact-icon"><i class="bi bi-envelope"></i></div>
            <div><div class="ipv-contact-label">Email</div><div class="ipv-contact-value">${escapeHtml(party.email)}</div></div>
        </div>
        <div class="ipv-contact-row">
            <div class="ipv-contact-icon"><i class="bi bi-geo-alt"></i></div>
            <div><div class="ipv-contact-label">${lang === 'bm' ? 'Alamat' : 'Address'}</div><div class="ipv-contact-value">${escapeHtml(party.address)}, ${escapeHtml(party.country)}</div></div>
        </div>
    `;
}

function renderParties() {
    document.getElementById('ipvImporterBlock').innerHTML = partyBlockHtml(APPLICATION.importer, 'Importer');
    document.getElementById('ipvExporterBlock').innerHTML = partyBlockHtml(APPLICATION.exporter, 'Exporter');
}

function renderAppAttachments() {
    renderAttachmentList(document.getElementById('ipvAppAttachments'), APPLICATION.attachments, 3);
}

// ---------------------------------------------------------------
// Render: status header + stage stepper
// ---------------------------------------------------------------

function renderStageStepper() {
    const el = document.getElementById('ipvStageStepper');
    const key = APPLICATION.status_key;
    const currentIndex = STAGE_ORDER.indexOf(key);
    const lang = getLang();

    el.innerHTML = STAGE_ORDER.map((stepKey, i) => {
        const cfg = STAGE_CONFIG[stepKey];
        let cls = 'is-pending';

        if (key === 'returned') {
            if (stepKey === 'submitted') cls = 'is-complete';
            else if (stepKey === 'doc_verification') cls = 'is-returned';
        } else if (i < currentIndex) {
            cls = 'is-complete';
        } else if (i === currentIndex) {
            cls = 'is-current';
        }

        const label = cfg[lang] || cfg.en;
        return `<div class="ipv-stage-step ${cls}">${label}</div>`;
    }).join('');

    const statusCfg = STAGE_CONFIG[key] || { en: APPLICATION.status, bm: APPLICATION.status };
    document.getElementById('ipvStatusLabel').textContent = statusCfg[lang] || APPLICATION.status || '—';
    document.getElementById('ipvStatusDuration').textContent = APPLICATION.status_duration
        ? `In this status for ${APPLICATION.status_duration}` : '';

    const noteEl = document.getElementById('ipvReturnedNote');
    if (key === 'returned') {
        noteEl.classList.remove('d-none');
        noteEl.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i> ${APPLICATION.returned_reason ? escapeHtml(APPLICATION.returned_reason) : '<span data-en="Application returned for correction." data-bm="Permohonan dikembalikan untuk pembetulan.">Application returned for correction.</span>'}`;
    } else {
        noteEl.classList.add('d-none');
    }
}

function renderTransportDetails() {
    const el = document.getElementById('ipvTransportDetails');
    if (!el) return;
    const lang = getLang();
    const labels = {
        en: { eta: 'ETA', transport: 'Transport Type', entry: 'Entry Point', notes: 'Entry Point Notes', category: 'Application Category' },
        bm: { eta: 'ETA', transport: 'Jenis Pengangkutan', entry: 'Pintu Masuk', notes: 'Nota Pintu Masuk', category: 'Kategori Permohonan' }
    };
    const t = labels[lang] || labels.en;

    const categoryText = String(APPLICATION.category_application) === '0'
        ? (lang === 'bm' ? 'Permohonan Sendiri' : 'Self Application')
        : (lang === 'bm' ? 'Permohonan Bagi Pihak' : 'Application on Behalf of Others');

    const rows = [
        { icon: 'bi-person-badge', label: t.category, value: categoryText },
        { icon: 'bi-calendar-event', label: t.eta, value: APPLICATION.eta },
        { icon: 'bi-truck', label: t.transport, value: APPLICATION.transport_type },
        { icon: 'bi-geo-alt', label: t.entry, value: APPLICATION.entry_point },
        { icon: 'bi-info-circle', label: t.notes, value: APPLICATION.entry_point_description || '—' },
    ];

    el.innerHTML = rows.map(r => `
        <div class="ipv-detail-row">
            <div class="ipv-detail-icon"><i class="bi ${r.icon}"></i></div>
            <span class="ipv-detail-label">${r.label}</span>
            <span class="ipv-detail-value">${escapeHtml(r.value)}</span>
        </div>
    `).join('');
}

// ---------------------------------------------------------------
// Render: Bulk Action Bar (Approve/Reject, Pay, Print)
// ---------------------------------------------------------------

function renderBulkActionBar() {
    const wrapId = 'ipvBulkActionsWrap';
    let wrap = document.getElementById(wrapId);
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.id = wrapId;
        wrap.className = 'ipv-actions-bar mb-3';
        document.getElementById('ipvPermitAccordion')?.insertAdjacentElement('beforebegin', wrap);
    }

    const lang = getLang();
    const status = (APPLICATION.status || '').toLowerCase();
    const isOwner = isOwnerApplicant();
    const hasApprovePerm = hasPermission('approve permit');
    const hasPrintPerm = hasPermission('print permit');

    const hasProcessing = PERMITS.some(p => p.status === 'processing' || p.status === 'reapplied');
    const hasPendingPayment = PERMITS.some(p => ['pending for payment', 'payment failed'].includes(p.status));
    const isCompleted = status === 'completed' || status === 'paid';

    // ─── Approve / Reject All ────────────────────────────────────────
    if (status === 'clerk verified' && hasApprovePerm && hasProcessing) {
        wrap.style.display = '';
        wrap.innerHTML = `
            <div class="ipv-actions-bar-text">
                <i class="bi bi-info-circle"></i>
                <span>${lang === 'bm' ? 'Semua sijil dalam permohonan ini sedia untuk diluluskan atau ditolak.' : 'All certificates in this application are ready to be approved or rejected.'}</span>
            </div>
            <div class="ipv-actions-bar-buttons">
                <button type="button" class="ipv-btn-action is-success accept" data-application="${APPLICATION.application_id}">
                    <i class="bi bi-check-lg"></i> ${lang === 'bm' ? 'Lulus Semua' : 'Approve All'}
                </button>
                <button type="button" class="ipv-btn-action is-danger reject" data-application="${APPLICATION.application_id}">
                    <i class="bi bi-x-lg"></i> ${lang === 'bm' ? 'Tolak Semua' : 'Reject All'}
                </button>
            </div>
        `;
        return;
    }

    // ─── Pay All ──────────────────────────────────────────────────────
    if (isOwner && hasPendingPayment) {
        const pending = PERMITS.filter(p => ['pending for payment', 'payment failed'].includes(p.status));
        const total = pending.length * INSPECTION_PERMIT_FEE;
        wrap.style.display = '';
        wrap.innerHTML = `
            <div class="ipv-actions-bar-text">
                <i class="bi bi-credit-card"></i>
                <span>${lang === 'bm' ? `${pending.length} permit menunggu bayaran. Jumlah: RM ${money(total)}` : `${pending.length} permit${pending.length > 1 ? 's' : ''} awaiting payment. Total: RM ${money(total)}`}</span>
            </div>
            <div class="ipv-actions-bar-buttons">
                <button type="button" class="ipv-btn-action is-warning pay-bulk" data-application="${APPLICATION.application_id}">
                    <i class="bi bi-credit-card"></i> ${lang === 'bm' ? 'Bayar Semua' : 'Pay All'}
                </button>
            </div>
        `;
        return;
    }

    // ─── Download All ─────────────────────────────────────────────────
    if (isCompleted && (hasPrintPerm || isOwner)) {
        wrap.style.display = '';
        wrap.innerHTML = `
            <div class="ipv-actions-bar-text">
                <i class="bi bi-check-circle"></i>
                <span>${lang === 'bm' ? 'Permohonan ini telah selesai.' : 'This application is complete.'}</span>
            </div>
            <div class="ipv-actions-bar-buttons">
                <button type="button" class="ipv-btn-action is-info generatePermit" data-permit="${APPLICATION.application_id}" data-type="${APPLICATION.application_type}">
                    <i class="bi bi-download"></i> ${lang === 'bm' ? 'Muat Turun Semua Sijil' : 'Download All Certificates'}
                </button>
            </div>
        `;
        return;
    }

    wrap.style.display = 'none';
    wrap.innerHTML = '';
}

// ---------------------------------------------------------------
// Render: per‑permit actions (only Reapply)
// ---------------------------------------------------------------

function permitActionsHtml(permit) {
    const status = permit.status;
    const isOwner = getCurrentUserType() === 'public';
    const lang = getLang();
    let actions = '';

    if (status === 'rejected' && isOwner) {
        actions += `
            <button type="button" class="ipv-btn-action is-warning reapply" data-permit="${permit.id}">
                <i class="bi bi-arrow-repeat"></i> ${lang === 'bm' ? 'Mohon Semula' : 'Reapply'}
            </button>
        `;
    }

    return actions ? `<div class="ipv-permit-actions">${actions}</div>` : '';
}

// ---------------------------------------------------------------
// Render: Certificate List (accordion)
// ---------------------------------------------------------------

function renderPermitAccordion() {
    document.getElementById('ipvPermitCount').textContent = PERMITS.length;

    const el = document.getElementById('ipvPermitAccordion');

    if (!PERMITS.length) {
        el.innerHTML = '<div class="ipv-empty-state"><i class="bi bi-inbox"></i><p>No inspection items found.</p></div>';
        renderBulkActionBar();
        return;
    }

    const lang = getLang();

    el.innerHTML = PERMITS.map((permit) => {
        const cfg = PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
        const detail = permit.consignment_detail;
        const statusText = cfg[lang] || cfg.en;

        return `
            <div class="ipv-permit-item" data-permit="${escapeHtml(permit.permit_number)}">
                <div class="ipv-permit-header">
                    <div class="ipv-permit-icon"><i class="bi bi-box-seam"></i></div>
                    <div class="ipv-permit-id-group">
                        <div class="ipv-permit-id">#${escapeHtml(permit.permit_number)}</div>
                        <div class="ipv-permit-name">${escapeHtml(detail.item_name)}</div>
                    </div>
                    <span class="ipv-badge is-${cfg.color}">${escapeHtml(statusText)}</span>
                    <div class="ipv-permit-value">RM ${money(permit.value)}</div>
                    <button type="button" class="ipv-view-detail-btn" data-permit-number="${escapeHtml(permit.permit_number)}" title="View full details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <i class="bi bi-chevron-down ipv-chevron"></i>
                </div>
                <div class="ipv-permit-body">
                    <div class="pd-section-label mb-2" data-en="Inspection Info" data-bm="Info Pemeriksaan">Inspection Info</div>
                    <div class="p-2 row ipv-permit-details-grid" style="background: var(--gray-1); border: 1px solid var(--default-border); border-radius: 0.6rem;">
                        <div class="col-12 col-lg-6">
                            <p class="mb-2">
                                <strong class="me-1">
                                    <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-tag"></i></span>
                                    <span data-en="Item Name:" data-bm="Nama Item:">Item Name:</span>
                                </strong>
                                <span class="text-break">${escapeHtml(detail.item_name)}</span>
                            </p>
                        </div>
                        <div class="col-12 col-lg-6">
                            <p class="mb-2">
                                <strong class="me-1">
                                    <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-scale-balanced"></i></span>
                                    <span data-en="Quantity:" data-bm="Kuantiti:">Quantity:</span>
                                </strong>
                                <span class="text-break">${permit.quantity.toLocaleString()} ${escapeHtml(permit.unit_measurement)}</span>
                            </p>
                        </div>
                        <div class="col-12 col-lg-6">
                            <p class="mb-2">
                                <strong class="me-1">
                                    <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-pen-fancy"></i></span>
                                    <span data-en="Purpose:" data-bm="Tujuan:">Purpose:</span>
                                </strong>
                                <span class="text-break">${escapeHtml(detail.purpose)}</span>
                            </p>
                        </div>
                        <div class="col-12 col-lg-6">
                            <p class="mb-2">
                                <strong class="me-1">
                                    <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-gear"></i></span>
                                    <span data-en="Uses:" data-bm="Kegunaan:">Uses:</span>
                                </strong>
                                <span class="text-break">${escapeHtml(detail.uses)}</span>
                            </p>
                        </div>
                        <div class="col-12">
                            <p class="mb-2">
                                <strong class="me-1">
                                    <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-file-contract"></i></span>
                                    <span data-en="Permit Number:" data-bm="No. Permit:">Permit Number:</span>
                                </strong>
                                <span class="text-break">${escapeHtml(permit.permit_number)}</span>
                            </p>
                        </div>
                    </div>

                    <div class="ipv-permit-subsection-title" data-bm="Lampiran" data-en="Attachments">Attachments (${permit.attachments.length})</div>
                    <div class="ipv-attach-list" id="attachList-${escapeHtml(permit.permit_number)}"></div>

                    ${permit.remark ? `
                        <div class="ipv-permit-remark is-${cfg.color}">
                            <i class="bi bi-info-circle"></i>
                            <span>${escapeHtml(permit.remark)}</span>
                        </div>
                    ` : ''}

                    ${permitActionsHtml(permit)}
                </div>
            </div>
        `;
    }).join('');

    PERMITS.forEach((permit) => {
        const container = document.getElementById(`attachList-${permit.permit_number}`);
        renderAttachmentList(container, permit.attachments, 2);
    });

    applyTranslations(el);
    renderBulkActionBar();
}

function initAccordionToggle() {
    const accordion = document.getElementById('ipvPermitAccordion');
    if (!accordion) return;

    accordion.removeEventListener('click', accordion._toggleHandler);

    const handler = function (e) {
        const viewBtn = e.target.closest('.ipv-view-detail-btn');
        if (viewBtn) {
            e.stopPropagation();
            const permitNumber = viewBtn.dataset.permitNumber;
            if (permitNumber) openPermitDetail(permitNumber);
            return;
        }

        if (e.target.closest('.ipv-permit-actions')) return;

        const header = e.target.closest('.ipv-permit-header');
        if (!header) return;
        header.closest('.ipv-permit-item')?.classList.toggle('is-open');
    };

    accordion._toggleHandler = handler;
    accordion.addEventListener('click', handler);
}

// ---------------------------------------------------------------
// Render: Pending Payment tab
// ---------------------------------------------------------------

function renderPendingPaymentTable() {
    const tableBody = $("#summaryTable4 tbody");
    if (!tableBody.length) return;
    tableBody.empty();

    const pending = PERMITS.filter((p) => ['pending for payment', 'payment failed'].includes(p.status));

    document.getElementById('ipvPendingPaymentCount') &&
        (document.getElementById('ipvPendingPaymentCount').textContent = pending.length);

    if (!pending.length) {
        tableBody.append(`<tr><td colspan="3" class="text-center text-muted">No permits pending payment.</td></tr>`);
        $("#summaryTable4 tfoot").html('');
        return;
    }

    pending.forEach((permit) => {
        tableBody.append(`
            <tr>
                <td>${escapeHtml(permit.permit_number)}</td>
                <td class="text-wrap">${escapeHtml(permit.consignment_detail.item_name)}</td>
                <td class="text-end">RM ${INSPECTION_PERMIT_FEE.toFixed(2)}</td>
            </tr>
        `);
    });

    const total = pending.length * INSPECTION_PERMIT_FEE;
    const $tfoot = $("#summaryTable4 tfoot");
    $tfoot.html(`
        <tr>
            <td colspan="2" class="text-end fw-bold">${getLang() === 'bm' ? 'Jumlah:' : 'Total:'}</td>
            <td class="text-end fw-bold">RM ${money(total)}</td>
        </tr>
        <tr>
            <td colspan="3" class="text-end">
                <button class="ipv-btn-primary pay-bulk" data-application="${APPLICATION.application_id}">
                    <i class="bi bi-credit-card"></i> ${getLang() === 'bm' ? 'Bayar Semua' : 'Pay All'}
                </button>
            </td>
        </tr>
    `);
}

// ---------------------------------------------------------------
// Payment awareness banner
// ---------------------------------------------------------------

function renderPaymentAwarenessBanner() {
    const wrap = document.getElementById('ipvPaymentBannerWrap');
    const el = document.getElementById('ipvPaymentBanner');
    if (!wrap || !el) return;

    const isOwner = isOwnerApplicant();
    const pending = PERMITS.filter((p) => ['pending for payment', 'payment failed'].includes(p.status));
    const lang = getLang();

    if (!isOwner || !pending.length) {
        wrap.style.display = 'none';
        return;
    }

    const total = pending.length * INSPECTION_PERMIT_FEE;
    const hasFailed = pending.some((p) => p.status === 'payment failed');

    el.className = `ipv-payment-banner${hasFailed ? ' is-danger' : ''}`;
    const label = lang === 'bm' ? `${pending.length} permit menunggu bayaran` : `${pending.length} permit${pending.length > 1 ? 's' : ''} awaiting payment`;
    const failMsg = hasFailed
        ? (lang === 'bm' ? 'Percubaan bayaran sebelum ini gagal — sila cuba semula. ' : 'A previous payment attempt failed — please retry. ')
        : '';
    const due = lang === 'bm' ? 'Jumlah perlu dibayar' : 'Total due';

    el.innerHTML = `
        <div class="ipv-payment-banner-text">
            <i class="bi ${hasFailed ? 'bi-exclamation-octagon' : 'bi-credit-card'}"></i>
            <div>
                <strong>${label}</strong>
                <span>${failMsg}${due}: RM ${money(total)}</span>
            </div>
        </div>
        <button type="button" class="ipv-btn-primary is-pay" id="ipvGoToPaymentTab">
            <i class="bi bi-arrow-right-circle"></i> ${lang === 'bm' ? 'Bayar Sekarang' : 'Pay Now'}
        </button>
    `;
    wrap.style.display = 'block';

    const btn = document.getElementById('ipvGoToPaymentTab');
    if (btn) {
        btn.onclick = function (e) {
            e.preventDefault();
            const count = pending.length;
            const amount = total;
            const lang = getLang();

            Swal.fire({
                title: lang === 'bm' ? 'Teruskan ke Pembayaran?' : 'Proceed to Payment?',
                text: lang === 'bm'
                    ? `Anda akan membayar RM ${money(amount)} untuk ${count} permit.`
                    : `You are about to pay RM ${money(amount)} for ${count} permit${count > 1 ? 's' : ''}.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: lang === 'bm' ? 'Ya, teruskan ke pembayaran' : 'Yes, proceed to payment',
                cancelButtonText: lang === 'bm' ? 'Batal' : 'Cancel',
            }).then((result) => {
                if (!result.isConfirmed) return;

                const paymentTab = document.querySelector('.ipv-tabnav-item[data-ipv-tab="payment"]');
                if (paymentTab) {
                    paymentTab.click();
                    paymentTab.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }

                $('.pay-bulk').first().click();
            });
        };
    }
}

// ---------------------------------------------------------------
// Render: Activity tab + Application Log modal
// ---------------------------------------------------------------

function renderActivityTimeline() {
    const el = document.getElementById('ipvActivityTimeline');
    if (!ACTIVITY_LOG.length) {
        el.innerHTML = '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity recorded yet.</p></div>';
        return;
    }
    const lang = getLang();
    el.innerHTML = ACTIVITY_LOG.map((entry) => {
        const cfg = STAGE_CONFIG[entry.stage] || STAGE_CONFIG.email;
        const title = entry.title || cfg[lang] || cfg.en;
        return `
            <div class="ipv-timeline-item">
                <div class="ipv-timeline-icon is-${cfg.color}"><i class="bi ${cfg.icon}"></i></div>
                <div class="ipv-timeline-body">
                    <div>
                        <div class="ipv-timeline-title">${escapeHtml(title)}</div>
                        <p class="ipv-timeline-desc">${escapeHtml(entry.description)}</p>
                    </div>
                    <span class="ipv-timeline-time">${escapeHtml(entry.time)}</span>
                </div>
            </div>
        `;
    }).join('');
}

function renderApplicationLogTable() {
    const tbody = $('#applicationLogTable tbody');
    tbody.empty();
    const lang = getLang();

    if (!RAW_ACTIVITY_LOG.length) {
        tbody.append('<tr><td colspan="5" class="text-center text-muted">No log entries found.</td></tr>');
        return;
    }

    RAW_ACTIVITY_LOG.forEach((entry) => {
        let statusLabel = entry.status || '—';
        const statusKey = (entry.status || '').toLowerCase();
        if (PERMIT_STATUS_CONFIG[statusKey]) {
            statusLabel = PERMIT_STATUS_CONFIG[statusKey][lang] || PERMIT_STATUS_CONFIG[statusKey].en;
        }
        tbody.append(`
            <tr>
                <td>${escapeHtml(entry.action || entry.title || '—')}</td>
                <td>${escapeHtml(entry.causer?.fullname || entry.user || entry.user_name || '—')}</td>
                <td>${escapeHtml(entry.remark || entry.description || '—')}</td>
                <td>${escapeHtml(statusLabel)}</td>
                <td>${escapeHtml(formatDateTime(entry.time || entry.created_at))}</td>
            </tr>
        `);
    });
}

function initApplicationLogModal() {
    $('#applicationModal').off('click').on('click', function (e) {
        e.preventDefault();
        renderApplicationLogTable();
        const modalEl = document.getElementById('activityLogModal');
        new bootstrap.Modal(modalEl).show();
    });
}

// ---------------------------------------------------------------
// Permit detail offcanvas (per-permit deep view)
// ---------------------------------------------------------------

let permitDetailOffcanvas = null;

function getStatusText(status) {
    const cfg = PERMIT_STATUS_CONFIG[status] || PERMIT_STATUS_CONFIG.queued;
    const lang = getLang();
    return lang === 'bm' ? cfg.bm : cfg.en;
}

function initPermitDetailOffcanvas() {
    const el = document.getElementById('permitDetailOffcanvas');
    if (el && !permitDetailOffcanvas) {
        permitDetailOffcanvas = new bootstrap.Offcanvas(el, { backdrop: true, keyboard: true, scroll: false });
        el.addEventListener('show.bs.offcanvas', () => {
            const detailsTab = document.getElementById('pd-details-tab');
            if (detailsTab) bootstrap.Tab.getOrCreateInstance(detailsTab).show();
        });
    }
}

// Inspection certificates aren't paid/printed individually — same as
// Consignment, payment/printing happens at the application level. Only
// per-permit action is Reapply when the item was rejected.
function reapplyCtaHtml(permit) {
    const isOwner = window.authUser?.type === 'public';
    if (permit.status !== 'rejected' || !isOwner) return '';

    const lang = getLang();
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

function openPermitDetail(permitNumber) {
    const permit = PERMITS.find((p) => p.permit_number === permitNumber);
    if (!permit) return;

    const cfg = PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
    const detail = permit.consignment_detail;

    document.getElementById('permitDetailOffcanvasLabel').textContent = detail.item_name;
    const badge = document.getElementById('pdBadge');
    badge.textContent = getStatusText(permit.status);
    badge.className = `ipv-badge ms-2 is-${cfg.color}`;

    const attachListId = `pd-attach-${permit.permit_number}`;

    document.getElementById('pdDetailsContent').innerHTML = `
        ${reapplyCtaHtml(permit)}

        <div class="pd-section-label mb-2" data-en="Inspection Info" data-bm="Info Pemeriksaan">Inspection Info</div>
        <div class="p-2 row" style="background: var(--gray-1); border: 1px solid var(--default-border); border-radius: 0.6rem;">
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-tag"></i></span>
                        <span data-en="Item Name:" data-bm="Nama Item:">Item Name:</span>
                    </strong>
                    <span class="text-break">${escapeHtml(detail.item_name)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-scale-balanced"></i></span>
                        <span data-en="Quantity:" data-bm="Kuantiti:">Quantity:</span>
                    </strong>
                    <span class="text-break">${permit.quantity.toLocaleString()} ${escapeHtml(permit.unit_measurement)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-money-bill"></i></span>
                        <span data-en="Value:" data-bm="Nilai:">Value:</span>
                    </strong>
                    <span class="text-break">RM ${money(permit.value)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-pen-fancy"></i></span>
                        <span data-en="Purpose:" data-bm="Tujuan:">Purpose:</span>
                    </strong>
                    <span class="text-break">${escapeHtml(detail.purpose)}</span>
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-gear"></i></span>
                        <span data-en="Uses:" data-bm="Kegunaan:">Uses:</span>
                    </strong>
                    <span class="text-break">${escapeHtml(detail.uses)}</span>
                </p>
            </div>
            <div class="col-12">
                <p class="mb-2">
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-file-contract"></i></span>
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

    const detailsContainer = document.getElementById('pdDetailsContent');
    if (detailsContainer) applyTranslations(detailsContainer);

    // ---- Activity tab ----
    // [TODO] no confirmed per-permit activity log endpoint/field for
    // Inspection items — falls back to an empty timeline, same as
    // Consignment's placeholder before its API exposed permit._raw.activity_log.
    const log = permit._raw?.activity_log || [];
    const timelineEl = document.getElementById('pdActivityTimeline');
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
        }).join('');
    }

    if (!permitDetailOffcanvas) initPermitDetailOffcanvas();
    const offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(
        document.getElementById('permitDetailOffcanvas'),
    );
    offcanvasInstance.show();
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

    const viewLink = document.getElementById('ipvViewPermitsLink');
    if (viewLink) {
        viewLink.addEventListener('click', () => {
            document.querySelector('.ipv-tabnav-item[data-ipv-tab="permits"]')?.click();
        });
    }
}

// =================================================================
// WORKFLOW ACTIONS
// (equivalent of consignment-actions.js, using Inspection's real
// endpoints from the legacy inspection_detail.js)
// =================================================================

function applicationId() {
    return window.APPLICATION_ID || APPLICATION.application_id;
}

function csrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

function reload() {
    return window.ImportPermitView?.reload();
}

// ─── Application-level status transitions ──────────────────────────
// (POST /internal/inspection/{id}/status or /public/inspection/{id}/status,
// body: { status }) — real endpoints/values carried over from the legacy
// inspection_detail.js.

function acceptApplication() {
    $('#acceptAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Accept Application?',
            text: 'Are you sure you want to accept this application?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, accept it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/internal/inspection/${applicationId()}/status`,
                method: 'POST',
                data: { _token: csrfToken(), status: 'Clerk Verified' },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Application Accepted!',
                        text: res.message || 'The application has been successfully accepted.',
                        showConfirmButton: false,
                        position: 'center',
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                },
            });
        });
    });
}

function adminRejectApplication() {
    $('#rejectAdminAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Reject Application',
            html: `
                <p class="mb-2">Please provide a reason for rejection:</p>
                <textarea id="rejectReason" class="swal2-textarea" placeholder="Enter rejection reason..."></textarea>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            focusConfirm: false,
            preConfirm: () => {
                const reason = document.getElementById('rejectReason').value;
                if (!reason.trim()) {
                    Swal.showValidationMessage('Rejection reason is required');
                    return false;
                }
                return reason;
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/internal/inspection/${applicationId()}/status`,
                method: 'POST',
                data: { _token: csrfToken(), status: 'Rejected', reason: result.value },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Application Rejected!',
                        text: 'The application has been rejected.',
                        showConfirmButton: false,
                        timer: 2000,
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                },
            });
        });
    });
}

function verifyApplication() {
    $('#verifyAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Verify Application?',
            text: 'Are you sure you want to verify this application?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, verify it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (!result.isConfirmed) return;

            // legacy endpoint deliberately used the public guard here
            $.ajax({
                url: `/public/inspection/${applicationId()}/status`,
                method: 'POST',
                data: { _token: csrfToken(), status: 'Clerk review in-progress' },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Application Verified!',
                        text: res.message || 'The application has been successfully verified.',
                        showConfirmButton: false,
                        position: 'center',
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                },
            });
        });
    });
}

function rejectApplication() {
    $('#rejectAppl').off('click').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Reject Application?',
            text: 'Are you sure you want to reject this application?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reject it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/internal/inspection/${applicationId()}/status`,
                method: 'POST',
                data: { _token: csrfToken(), status: 'Rejected' },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Application Not Approved!',
                        text: 'The application has been successfully marked as not verified.',
                        showConfirmButton: false,
                        position: 'center',
                    });
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                },
            });
        });
    });
}

// ─── Bulk item accept/reject ────────────────────────────────────────
// (POST /internal/inspection_item/{id}/accept|reject — id is the
// *application* id here, matching the legacy acceptPermit()/rejectPermit()
// which bulk-actions every item on the application at once.)

function acceptCertificates() {
    $(document).off('click', '.accept').on('click', '.accept', function (e) {
        e.preventDefault();
        const id = $(this).data('application');

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to accept all these inspection items?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
        }).then((firstResult) => {
            if (!firstResult.isConfirmed) return;

            Swal.fire({
                title: 'Please Confirm Again',
                text: 'This action cannot be undone. Accept all the inspection items?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, accept it',
                cancelButtonText: 'Cancel',
            }).then((secondResult) => {
                if (!secondResult.isConfirmed) return;

                $.ajax({
                    url: `/internal/inspection_item/${id}/accept`,
                    method: 'POST',
                    data: { _token: csrfToken() },
                    success: function () {
                        Swal.fire('Accepted!', 'The inspection items have been accepted.', 'success');
                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                    },
                });
            });
        });
    });
}

function rejectCertificates() {
    $(document).off('click', '.reject').on('click', '.reject', function (e) {
        e.preventDefault();
        const id = $(this).data('application');

        Swal.fire({
            title: 'Reject Inspection Items',
            text: 'Please provide a reason for rejecting these inspection items:',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason...',
            showCancelButton: true,
            confirmButtonText: 'Reject Items',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value || value.trim().length < 5) {
                    return 'Rejection reason is required (min 5 characters).';
                }
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/internal/inspection_item/${id}/reject`,
                method: 'POST',
                data: { _token: csrfToken(), reason: result.value },
                success: function () {
                    Swal.fire('Rejected!', 'The inspection items have been rejected successfully.', 'success');
                    window.location.reload();
                },
                error: function (err) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                },
            });
        });
    });
}

// ─── Certificate download ────────────────────────────────────────────
// (POST /permit/print { type: 'Inspection', permit_number }, then opens
// /inspection/generate/{id} — id here is whatever value was clicked,
// application_id for the bulk/sidebar button.)

function generatePermit() {
    $(document).off('click', '.generatePermit').on('click', '.generatePermit', function (e) {
        e.preventDefault();
        const id = $(this).data('permit');

        $.ajax({
            url: `/permit/print`,
            method: 'POST',
            data: { _token: csrfToken(), type: 'Inspection', permit_number: id },
            success: function (res) {
                if (res.message === 'Need Response') {
                    Swal.fire({
                        title: 'This Permit has been downloaded more than once',
                        text: 'Please provide a reason for downloading it:',
                        icon: 'warning',
                        input: 'textarea',
                        inputPlaceholder: 'Enter reason...',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        inputValidator: (value) => {
                            if (!value || value.trim().length < 5) return 'Reason is required (min 5 characters).';
                        },
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                        $.ajax({
                            url: `/permit/print`,
                            method: 'POST',
                            data: { _token: csrfToken(), type: 'Inspection', permit_number: id, reason: result.value },
                            success: function () {
                                Swal.close();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Submitted!',
                                    text: 'The reason submitted successfully.',
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                                setTimeout(() => {
                                    window.open(`/inspection/generate/${id}`, '_blank');
                                }, 500);
                            },
                            error: function (err) {
                                Swal.close();
                                Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
                            },
                        });
                    });
                } else {
                    window.open(`/inspection/generate/${id}`, '_blank');
                }
            },
            error: function (err) {
                Swal.fire({ icon: 'error', title: 'Error!', text: err.responseJSON?.message || 'Something went wrong.' });
            },
        });
    });
}

// ─── Bulk payment ─────────────────────────────────────────────────────
// (POST /payment/signed-url { type: 'inspection', application_id }. Legacy
// code sent `application.id` — the numeric PK — not `application_id`, kept
// exactly as-is via APPLICATION.id.)

function payBulk() {
    $(document).off('click', '.pay-bulk').on('click', '.pay-bulk', function (e) {
        e.preventDefault();

        const pending = PERMITS.filter(p => ['pending for payment', 'payment failed'].includes(p.status));

        if (!pending.length) {
            Swal.fire({
                icon: 'info',
                title: 'No pending permits',
                text: 'There are no permits awaiting payment.',
            });
            return;
        }

        const total = pending.length * INSPECTION_PERMIT_FEE;
        const permitIds = pending.map(p => p.id);
        const amountText = money(total);

        Swal.fire({
            title: 'Proceed to Payment?',
            text: `You are about to pay RM ${amountText} for ${pending.length} permit(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed to payment',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '/payment/signed-url',
                method: 'POST',
                data: {
                    application_id: APPLICATION.id,
                    permit_ids: permitIds,
                    total: Number(total).toFixed(2),
                    type: 'inspection',
                    _token: csrfToken(),
                },
                success: function (res) {
                    window.location.href = res.url;
                },
                error: function () {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Error!', text: 'Unable to proceed to checkout.' });
                },
            });
        });
    });
}

// ─── Reapply ──────────────────────────────────────────────────────────
// (POST /public/save-inspection/{permitId}). The legacy modal tried to
// pre-select #itemSelect by item *name* against an empty, never-populated
// <select> — a no-op bug. Fixed here by seeding the select with the
// existing item as its only option (the item itself isn't meant to change
// on reapply, only quantity/value/purpose/uses/attachments), instead of
// inventing an items-by-country endpoint that was never evidenced for
// Inspection.

let itemDropzone = null;
let updateItem = null;

function initItemDropzone($modal) {
    const dropzoneEl = $modal.find('#itemDropzone')[0];
    if (!dropzoneEl) return;

    if (dropzoneEl.dropzone) dropzoneEl.dropzone.destroy();

    itemDropzone = new Dropzone(dropzoneEl, {
        url: '/',
        autoProcessQueue: false,
        maxFilesize: 10,
        acceptedFiles: '.jpg,.jpeg,.png,.pdf',
        addRemoveLinks: true,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        processing: function () {
            Swal.fire({
                title: 'Uploading...',
                html: 'Please wait while your file is being uploaded.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });
            groupPreview();
        },
    });

    itemDropzone.on('addedfile', function () {
        groupPreview();
    });
}

function groupPreview() {
    setTimeout(function () {
        const $dropzone = $('#itemDropzone');
        const $previews = $dropzone.find('.dz-preview');
        const $deleteBtns = $previews.find('.dz-remove');

        let $group = $dropzone.find('.dz-preview-group');
        if ($group.length === 0) {
            $group = $('<div class="dz-preview-group"></div>');
            $dropzone.find('.dz-message').after($group);
        }
        $previews.appendTo($group);

        if (itemDropzone) {
            for (const file of itemDropzone.getAcceptedFiles()) {
                if (file.type === 'application/pdf') {
                    const $preview = $(file.previewElement);
                    const $img = $preview.find('.dz-image img[data-dz-thumbnail]');
                    $img.attr('src', '/images/pdf-logo.png');
                    $img.css({ 'object-fit': 'contain', width: '100%', height: '100%' });
                }
            }
        }

        $deleteBtns.html('<i class="ti ti-trash"></i>');
        Swal.close();
    }, 100);
}

function reapply() {
    $(document).off('click', '.reapply').on('click', '.reapply', async function (e) {
        e.preventDefault();

        const id = $(this).data('permit');
        const permit = PERMITS.find((p) => p.id == id);
        if (!permit) {
            console.warn('Permit not found!');
            return;
        }

        const rawDetail = permit._raw?.consignment_detail || {};

        $('#saveBtn').data('id', id).attr('data-id', id);

        const modalEl = document.getElementById('addItemModal');
        const modal = new bootstrap.Modal(modalEl);

        modalEl.addEventListener('shown.bs.modal', async () => {
            const $modal = $(modalEl);
            initItemDropzone($modal);

            const $select = $modal.find('#itemSelect');
            $select.empty();
            $select.append(`<option value="${rawDetail.item_id ?? ''}">${escapeHtml(rawDetail.item_name || permit.consignment_detail.item_name)}</option>`);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.trigger('change');
            } else {
                $select.select2({ width: '100%', dropdownParent: $modal });
            }

            $modal.find('#itemValue').val(rawDetail.value);
            $modal.find('#itemQuantity').val(rawDetail.quantity);

            $modal.find('#itemPurpose option').each(function () {
                if ($(this).data('description') === rawDetail.purpose) {
                    $(this).prop('selected', true);
                }
            });
            $modal.find('#itemPurpose').trigger('change');

            $modal.find('#itemMeasure').val(rawDetail.measure).trigger('change');

            const $itemUses = $modal.find('#itemUses');
            $itemUses.empty().append(`<option value="">-- Select Uses --</option>`);
            if (rawDetail.uses) {
                $itemUses.append(`<option value="${escapeHtml(rawDetail.uses)}">${escapeHtml(rawDetail.uses)}</option>`);
                $itemUses.val(rawDetail.uses);
            }
            if ($itemUses.hasClass('select2-hidden-accessible')) {
                $itemUses.trigger('change');
            } else {
                $itemUses.select2({ width: '100%', dropdownParent: $modal });
            }

            wireReapplySave();
        }, { once: true });

        modal.show();
    });
}

function wireReapplySave() {
    $(document).off('click', '#saveBtn').on('click', '#saveBtn', function (e) {
        e.preventDefault();

        const $modal = $('#addItemModal');
        const id = $(this).data('id');

        const itemSelectValue = $modal.find('#itemSelect').val();
        const itemSelectText = $modal.find('#itemSelect option:selected').text();
        const itemValue = $modal.find('#itemValue').val().trim();
        const itemQuantity = $modal.find('#itemQuantity').val().trim();
        const itemMeasure = $modal.find('#itemMeasure').val();
        const itemPurpose = $modal.find('#itemPurpose option:selected').text();
        const itemUsesValue = $modal.find('#itemUses').val();

        if (!itemSelectValue || !itemValue || !itemQuantity || !itemMeasure || !itemPurpose || !itemUsesValue) {
            Swal.fire('Error', 'Please fill all required fields', 'error');
            return;
        }

        const files = itemDropzone?.getAcceptedFiles() || [];

        updateItem = {
            item_id: itemSelectValue,
            item_name: itemSelectText,
            value: itemValue,
            quantity: itemQuantity,
            measure: itemMeasure,
            purpose: itemPurpose,
            uses: itemUsesValue,
            files,
        };

        saveReapplyItem(id);

        $('#itemValue, #itemQuantity').val('');
        $('#itemSelect').val(null).trigger('change');
        $('#itemMeasure, #itemPurpose').val('').trigger('change');
        $('#itemUses').val(null).trigger('change');
        if (itemDropzone) itemDropzone.removeAllFiles(true);

        bootstrap.Modal.getInstance($modal[0])?.hide();
    });
}

function saveReapplyItem(permitId) {
    if (!updateItem) {
        Swal.fire('Error', 'No item to save', 'error');
        return;
    }

    const form = document.querySelector('#wizardForm') || document.querySelector('#addItemModal form');
    const formData = new FormData(form || undefined);

    const { files, ...otherData } = updateItem;
    formData.append('items[0][data]', JSON.stringify(otherData));

    if (files && files.length > 0) {
        files.forEach((file) => {
            formData.append('files[]', file);
            formData.append('file_item_index[]', 0);
        });
    }

    Swal.fire({ title: 'Submitting...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: '/public/save-inspection/' + permitId,
        type: 'POST',
        data: formData,
        headers: { 'X-CSRF-TOKEN': csrfToken() },
        processData: false,
        contentType: false,
        success: function () {
            Swal.fire({ icon: 'success', title: 'Permit Reapply!', timer: 1500, showConfirmButton: false });
            reload();
        },
        error: function () {
            Swal.fire('Error', 'Failed to save permit', 'error');
        },
    });
}

function initActions() {
    acceptApplication();
    adminRejectApplication();
    verifyApplication();
    rejectApplication();

    acceptCertificates();
    rejectCertificates();
    generatePermit();
    reapply();
    payBulk();
}

// ---------------------------------------------------------------
// Refresh UI on language change
// ---------------------------------------------------------------

function refreshUI() {
    renderHeaderInfo();
    renderParties();
    renderAppAttachments();
    renderStageStepper();
    renderTransportDetails();
    renderPermitAccordion();
    renderPendingPaymentTable();
    renderActivityTimeline();
    renderPaymentAwarenessBanner();
    initAccordionToggle();
    const container = document.querySelector('.ipv-wrapper');
    if (container) applyTranslations(container);
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

async function renderAll() {
    renderHeaderInfo();
    renderParties();
    renderAppAttachments();
    renderStageStepper();
    renderTransportDetails();
    renderPermitAccordion();
    renderPendingPaymentTable();
    renderActivityTimeline();
    renderPaymentAwarenessBanner();
    initAccordionToggle();
    const container = document.querySelector('.ipv-wrapper');
    if (container) applyTranslations(container);
}

async function init() {
    if (!document.getElementById('ipvAppId')) return;

    Swal.fire({
        title: 'Loading...',
        text: 'Please wait while we fetch the application details.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    userData = await loadProfile();
    console.log('await', userData);

    if (!userData) {
        console.warn('init: profile failed to load — role/owner-based UI will be hidden.');
    }

    await loadApplicationData();
    await renderAll();

    initTabs();
    initOffcanvas();
    initApplicationLogModal();
    initActions();

    Swal.close();

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));

    const observer = new MutationObserver(() => {
        refreshUI();
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

    document.addEventListener('click', async function (e) {
        const link = e.target.closest('.ipv-download-link');
        if (!link) return;
        e.preventDefault();

        let filePath = link.dataset.path;
        const fileName = link.dataset.name || 'download';

        if (!filePath) {
            Swal.fire('Error', 'File path is missing.', 'error');
            return;
        }

        if (!filePath.startsWith('http') && !filePath.startsWith('/')) {
            const base = window.baseUrl || '';
            filePath = (base.endsWith('/') ? base.slice(0, -1) : base) + '/' + filePath;
        }

        try {
            const response = await fetch(filePath, { credentials: 'include' });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        } catch (err) {
            console.error('Download failed:', err);
            Swal.fire('Error', 'Failed to download the file. Please try again.', 'error');
        }
    });
}

document.addEventListener('DOMContentLoaded', init);

// Public API — same shape as consignment1.js's window.ImportPermitView
window.ImportPermitView = window.ImportPermitView || {};
window.ImportPermitView.reload = async function () {
    await loadApplicationData();
    await renderAll();
};
window.ImportPermitView.getApplication = () => APPLICATION;
window.ImportPermitView.getPermits = () => PERMITS;