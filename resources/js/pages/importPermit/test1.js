/**
 * test1.js - Bilingual version
 * Renders import_permit_view.blade.php from real data.
 * All dynamic text now switches between English and Bahasa Malaysia.
 */

import { initPermitDetailOffcanvas, openPermitDetail } from "./test2";
import { buildScheduleEvents, initScheduleCalendar } from "./test3";
import $ from "jquery";
import Swal from "sweetalert2";
import { applyTranslations, getAuthUser } from "../../app"; // <-- added
import { loadProfile } from "../auth/profile";

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
    doc_verification:    { en: 'Clerk Review In-Progress', bm: 'Semakan Kerani Dalam Proses', icon: 'bi-file-earmark-check', color: 'secondary' },
    returned:            { en: 'Returned / Rejected',     bm: 'Dikembalikan / Ditolak',    icon: 'bi-arrow-return-left',  color: 'danger' },
    technical_review:    { en: 'Officer Verification',    bm: 'Pengesahan Pegawai',        icon: 'bi-clipboard-check',    color: 'primary' },
    awaiting_payment:    { en: 'Awaiting Payment',        bm: 'Menunggu Pembayaran',       icon: 'bi-hourglass-split',    color: 'warning' },
    payment_processing:  { en: 'Payment Processing',      bm: 'Proses Pengesahan Bayaran', icon: 'bi-credit-card',        color: 'orange' },
    completed:           { en: 'Completed',               bm: 'Selesai',                   icon: 'bi-check-circle',       color: 'success' },
    permit_approved:     { en: 'Permit Approved',         bm: 'Permit Diluluskan',         icon: 'bi-check-circle',       color: 'success' },
    permit_rejected:     { en: 'Permit Rejected',         bm: 'Permit Ditolak',            icon: 'bi-x-circle',           color: 'danger' },
    payment:             { en: 'Payment Update',          bm: 'Kemaskini Bayaran',         icon: 'bi-credit-card-2-back', color: 'orange' },
    email:               { en: 'Notification Sent',       bm: 'Notifikasi Dihantar',       icon: 'bi-envelope-check',     color: 'gray' },
    qr_scan:             { en: 'QR Scan',                 bm: 'Imbasan QR',                icon: 'bi-qr-code-scan',       color: 'secondary' },
};

export const PERMIT_STATUS_CONFIG = {
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

export const PUBLIC_HOLIDAYS = [
    { date: '2026-01-01', name: "New Year's Day" },
    { date: '2026-02-17', name: 'Chinese New Year (Day 1)' },
    { date: '2026-02-18', name: 'Chinese New Year (Day 2)' },
    { date: '2026-05-01', name: 'Labour Day' },
    { date: '2026-06-03', name: 'Sample Public Holiday (placeholder)' },
];

// ---------------------------------------------------------------
// Helper: get current language
// ---------------------------------------------------------------

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

// ---------------------------------------------------------------
// Live data — populated by loadApplicationData(). Renderer functions
// below read from these, same as the old dummy version.
// userData is populated once by loadProfile() in init() and is now the
// single source of truth for "who is the current user" — nothing in
// this file reads window.authUser anymore.
// ---------------------------------------------------------------

let APPLICATION = {};
let PERMITS = [];
let ACTIVITY_LOG = [];
let RAW_ACTIVITY_LOG = [];
let userData = null;

// ---------------------------------------------------------------
// Current-user helpers — normalize userData (from loadProfile()) into
// the shapes the render functions need, in one place.
// ---------------------------------------------------------------

function getCurrentUserRoles() {
    if (!userData) return [];
    if (Array.isArray(userData.roles)) {
        // roles could be an array of strings or an array of { name } objects
        return userData.roles
            .map((r) => (typeof r === 'string' ? r : r?.name))
            .filter(Boolean);
    }
    if (typeof userData.role === 'string') return [userData.role];
    return [];
}

function getCurrentUserType() {
    // profile.js sets user["type"] = response.type ('public' | 'internal')
    return userData?.type || null;
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
    const res = await fetch(`/application/${applicationId}/data`);
    const json = await res.json();
    console.log('application', json);

    mapApplication(json);
    mapPermits(json);
    mapActivityLog(json);
}

function mapApplication(json) {
    const importer = json.importer || {};
    const exporter = json.exporter || {};
    const entryPoint = json.entry_point || {};
    const country = exporter.country_info || {};

    const rawStatus = json.status || '';
    const importerVerify = json.importer_verify || '';

    APPLICATION = {
        application_id: json.application_id,
        type: 'Import Permit',
        status: rawStatus,
        status_key: deriveStageKey(rawStatus, importerVerify),
        status_duration: json.status_duration || '',
        returned_reason: json.returned_reason || json.remark || null,
        tags: buildTags(json),
        submitted_by: importer.fullname || '—',
        submitted_at: formatDateTime(json.created_at),
        downloaded_count: json.print_calc || 0,
        assigned_officer: json.assigned_officer?.name || json.officer?.name || '—',
        sla_due: json.sla_due || '—',
        eta: formatDate(json.eta),
        transport_type: json.transport_type || '—',
        entry_point: entryPoint.entry_name || json.entry_point_name || '—',
        entry_point_description: entryPoint.description || '',
        importer: {
            name: importer.fullname || '—',
            phone: importer.phone_number || '—',
            email: importer.email || '—',
            address: [importer.address_1, importer.address_2, importer.postcode, importer.district]
                .filter(Boolean).join(', '),
            country: 'Malaysia',
        },
        exporter: {
            name: exporter.name || '—',
            phone: exporter.phone_no || '—',
            email: exporter.email || '—',
            address: exporter.address || '—',
            country: country.name || exporter.country || '—',
        },
        attachments: (json.attachment || []).map((f) => ({
            name: f.file_name || f.name,
            size: f.file_size || f.size || '',
            path: f.file_path || f.path,
            mime: f.file_type || f.mime,
        })),
    };
}

function deriveStageKey(status, importerVerify) {
    const s = (status || '').toLowerCase();
    const iv = (importerVerify || '').toLowerCase();

    if (s.includes('draft')) return 'submitted';
    if (s.includes('clerk review')) return 'doc_verification';
    if (iv.includes('wait for company approval')) return 'doc_verification';
    if (s.includes('clerk verified')) return 'technical_review';
    
    // ⚠️ Officer Verification Completed → Awaiting Payment (not Completed)
    if (s.includes('officer verification completed')) return 'awaiting_payment';
    
    if (s.includes('completed')) return 'completed';
    if (s.includes('rejected') || s.includes('not approved')) return 'returned';
    if (s.includes('pending for payment')) return 'awaiting_payment';
    if (s.includes('payment processing')) return 'payment_processing';

    return 'submitted';
}

function buildTags(json) {
    const tags = [];
    const category = json.category_application;
    if (category === 0 || category === '0') {
        tags.push({
            label: 'Self Import',
            label_en: 'Self Import',
            label_bm: 'Import Sendiri',
            color: 'info'
        });
    } else {
        tags.push({
            label: 'Apply for Others',
            label_en: 'Apply for Others',
            label_bm: 'Mohon untuk Pihak Lain',
            color: 'primary'
        });
    }
    return tags;
}

function mapPermits(json) {
    const permits = json.consignment_permits || [];
    PERMITS = permits.map((permit) => {
        const detail = permit.consignment_detail || {};
        const statusKey = (permit.status || '').toLowerCase();
        return {
            id: permit.id,
            permit_number: permit.permit_number || ' ',
            consignment_detail: {
                
                item_name: detail.item_name || '—',
                usage: detail.uses || detail.usage || '—',
            },
            quantity: Number(detail.quantity || 0),
            unit_measurement: detail.measure || '',
            purpose: detail.purpose || '—',
            value: Number(detail.value || 0),
            status: statusKey,
            remark: permit.remark || '',
            attachments: (permit.attachments || []).map((f) => ({
                name: f.file_name || f.name,
                size: f.file_size || f.size || '',
                path: f.file_path || f.path,
                mime: f.file_type || f.mime,
            })),
            conditions: detail.condition || [],
            agreedAt: detail.agreedAt,
            isCustom: detail.isCustom,
            _raw: permit,
        };
    });
}

function mapActivityLog(json) {
    RAW_ACTIVITY_LOG = json.activity_log || [];
    const qrLogs = (json.qr_scan_logs || []).map((q) => ({
        action: 'QR Scan',
        user: q.scanned_by || q.user || '—',
        remark: q.location || q.remark || '',
        status: q.status || '',
        time: q.created_at || q.time,
        stage: 'qr_scan',
    }));

    const combined = [...RAW_ACTIVITY_LOG, ...qrLogs].sort((a, b) =>
        new Date(a.time || a.created_at || 0) - new Date(b.time || b.created_at || 0)
    );

    ACTIVITY_LOG = combined.map((entry) => ({
        stage: entry.stage || guessStage(entry.action || entry.title || ''),
        title: entry.action || entry.title || 'Update',
        description: entry.remark || entry.description || '',
        time: formatDateTime(entry.time || entry.created_at),
    }));
}

function guessStage(text) {
    const t = (text || '').toLowerCase();
    if (t.includes('reject')) return 'permit_rejected';
    if (t.includes('approve')) return 'permit_approved';
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

export function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

export function money(n) {
    return Number(n || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export function fileMeta(filename) {
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
// Attachment chips + viewer (unchanged from the dummy version)
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
    const ext = path.split('.').pop().toLowerCase();

    const isImage = mime.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(mime) || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext);
    const isVideo = mime.startsWith('video/') || ['mp4', 'webm', 'ogg', 'mov'].includes(mime) || ['mp4', 'webm', 'ogg', 'mov'].includes(ext);
    const isPdf = mime === 'application/pdf' || mime === 'pdf' || ext === 'pdf';

    let html = '';
    if (isImage) {
        html = `<img src="${escapeHtml(path)}" alt="${escapeHtml(file.name)}">`;
    } else if (isVideo) {
        const videoMime = mime.startsWith('video/') ? mime : `video/${ext}`;
        html = `<video controls><source src="${escapeHtml(path)}" type="${escapeHtml(videoMime)}">Your browser does not support the video tag.</video>`;
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
                    <a href="#" class="ipv-download-link" data-en ="Download" data-bm = "Muat Turun" data-path="${escapeHtml(file.path)}" data-name="${escapeHtml(file.name)}">Download</a>
                </div>
            </div>
        </div>
    `).join('');

    if (remaining > 0) {
        html += `<div class="ipv-attach-more" data-list-id="${listId}">+${remaining}</div>`;
    }
    containerEl.innerHTML = html;
}

// Inside the DOMContentLoaded or after paintAttachmentList is defined
document.addEventListener('click', (e) => {
    // If the click is on the download link or its children, do nothing (let the link handler work)
    if (e.target.closest('.ipv-download-link')) {
        return;
    }
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
}, true); // capture phase

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

    // Show loading alert
    Swal.fire({
        title: 'Preparing download...',
        text: `Zipping ${attachments.length} file(s). Please wait.`,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        // Ensure JSZip is loaded
        let JSZip;
        if (typeof window.JSZip !== 'undefined') {
            JSZip = window.JSZip;
        } else {
            // Load JSZip from CDN
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
            if (!file.path) {
                console.warn('Missing path for attachment:', file.name);
                continue;
            }

            try {
                const response = await fetch(file.path, {
                    credentials: 'include',
                });
                if (!response.ok) {
                    console.warn(`Failed to fetch ${file.name}: ${response.status}`);
                    continue;
                }
                const blob = await response.blob();
                const fileName = file.name || 'unnamed';
                zip.file(fileName, blob);
                downloaded++;
            } catch (err) {
                console.error(`Error fetching ${file.name}:`, err);
            }
        }

        if (downloaded === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Download Failed',
                text: 'Could not retrieve any files. Check console for details.',
            });
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

        Swal.fire({
            icon: 'success',
            title: 'Download Complete!',
            text: `${downloaded} file(s) downloaded as ${zipName}.`,
            timer: 2500,
            showConfirmButton: false
        });

    } catch (error) {
        console.error('Zip creation failed:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to create zip file. Please try again or contact support.',
        });
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
    const lang = getLang();
    document.getElementById('ipvAppType').textContent = APPLICATION.type;
    document.getElementById('ipvAppId').textContent = APPLICATION.application_id;
    document.getElementById('ipvSubmittedBy').textContent = APPLICATION.submitted_by;
    document.getElementById('ipvDownloadBadge').innerHTML = `<i class="bi bi-download"></i> ${APPLICATION.downloaded_count}`;

    // Bilingual "Application submitted on"
    const submittedLabel = lang === 'bm' ? 'Permohonan dihantar pada' : 'Application submitted on';
    document.getElementById('ipvCreatedAt').textContent = `${submittedLabel} ${APPLICATION.submitted_at}`;

    const tagLabels = {
        en: { self: 'Self Import', other: 'Apply for Others' },
        bm: { self: 'Import Sendiri', other: 'Mohon untuk Pihak Lain' }
    };
    document.getElementById('ipvTags').innerHTML = APPLICATION.tags.map((tag) => {
        // The tag object might have label_en and label_bm already; use them.
        const label = lang === 'bm' ? tag.label_bm : tag.label_en;
        return `<span class="ipv-tag btn-primary is-${tag.color}">${escapeHtml(label)}</span>`;
    }).join('');

    const total = PERMITS.reduce((sum, p) => sum + p.value, 0);
    document.getElementById('ipvTotalValue').textContent = `RM ${money(total)}`;
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
    const exporterEl = document.getElementById('ipvExporterBlock');
    exporterEl.innerHTML = partyBlockHtml(APPLICATION.exporter, 'Exporter');
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

    // Status label
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

function renderInfoRow() {
    document.getElementById('ipvAssignedOfficer').textContent = APPLICATION.assigned_officer;
    document.getElementById('ipvSlaDue').textContent = APPLICATION.sla_due;
}

function renderTransportDetails() {
    const el = document.getElementById('ipvTransportDetails');
    const lang = getLang();
    const labels = {
        en: { eta: 'ETA', transport: 'Transport Type', entry: 'Entry Point', },
        bm: { eta: 'ETA', transport: 'Jenis Pengangkutan', entry: 'Pintu Masuk' }
    };
    const t = labels[lang] || labels.en;

    const rows = [
        { icon: 'bi-calendar-event', label: t.eta, value: APPLICATION.eta },
        { icon: 'bi-truck', label: t.transport, value: APPLICATION.transport_type },
        { icon: 'bi-geo-alt', label: t.entry, value: APPLICATION.entry_point },
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
// Render: Permit List (accordion) + per-permit action buttons
// ---------------------------------------------------------------

function permitActionsHtml(permit) {
    const status = permit.status;
    const applicationStatus = (APPLICATION.status || '').toLowerCase();

    // ─── Permission helper ────────────────────────────────
    function hasPermission(permissionName) {
        const user = window.fullUser;
        if (!user || !user.permissions) return false;
        return user.permissions.some(p => p.name === permissionName);
    }

    const isOwner = getCurrentUserType() === 'public';
    const lang = getLang();

    let user =  window.authUser ;

    let actions = '';

    // ─── Accept Custom Item to List ──────────────────────────
    // Only for internal users with 'approve permit' permission.
    if (
        permit.isCustom === true &&
        user.type === 'internal'
    ) {
        actions += `
            <button type="button" class="ipv-btn-action is-success accept-custom" data-permit="${permit.id}">
                <i class="bi bi-check-lg"></i> ${lang === 'bm' ? 'Terima Item ke Senarai' : 'Accept Item to List'}
            </button>
        `;
    }

    // ─── Standard Approve / Reject ──────────────────────────
    if (
        applicationStatus === 'clerk verified' &&
        (status === 'processing' || status === 'reapplied') &&
        hasPermission('approve permit')
    ) {
        // Standard approve/reject for non-custom permits only
        if (!permit.isCustom) {
            actions += `
                <button type="button" class="ipv-btn-action is-success accept" data-permit="${permit.id}">
                    <i class="bi bi-check-lg"></i> ${lang === 'bm' ? 'Lulus' : 'Approve'}
                </button>
                <button type="button" class="ipv-btn-action is-danger reject" data-permit="${permit.id}">
                    <i class="bi bi-x-lg"></i> ${lang === 'bm' ? 'Tolak' : 'Reject'}
                </button>
            `;
        } else {
            // For custom permits, keep the standard Reject button
            actions += `
                <button type="button" class="ipv-btn-action is-danger reject" data-permit="${permit.id}">
                    <i class="bi bi-x-lg"></i> ${lang === 'bm' ? 'Tolak' : 'Reject'}
                </button>
            `;
        }
    }

    // ─── Reapply (owner only) ──────────────────────────────
    if (status === 'rejected' && isOwner) {
        actions += `
            <button type="button" class="ipv-btn-action is-warning reapply" data-permit="${permit.id}">
                <i class="bi bi-arrow-repeat"></i> ${lang === 'bm' ? 'Mohon Semula' : 'Reapply'}
            </button>
        `;
    }

    // ─── Pay Now (owner only) ──────────────────────────────
    if (['pending for payment', 'payment failed'].includes(status) && isOwner) {
        actions += `
            <button type="button" class="ipv-btn-action is-warning pd-pay-now" data-permit="${permit.id}" data-value="12">
                <i class="bi bi-credit-card"></i> ${lang === 'bm' ? 'Bayar Sekarang' : 'Pay Now'} — RM 12.00
            </button>
        `;
    }

    // ─── Print / Download ──────────────────────────────────
    if (
        ['paid', 'completed'].includes(status) &&
        (hasPermission('print permit') || isOwner)
    ) {
        const slug = (permit.permit_number || '').replaceAll('/', '');
        actions += `
            <button type="button" class="ipv-btn-action is-info generatePermit" data-permit="${slug}">
                <i class="bi bi-download"></i> ${lang === 'bm' ? 'Cetak / Muat Turun Permit' : 'Print / Download Permit'}
            </button>
        `;
    }

    return actions ? `<div class="ipv-permit-actions">${actions}</div>` : '';
}

function renderPermitAccordion() {
    document.getElementById('ipvPermitCount').textContent = PERMITS.length;

    const el = document.getElementById('ipvPermitAccordion');

    if (!PERMITS.length) {
        el.innerHTML = '<div class="ipv-empty-state"><i class="bi bi-inbox"></i><p>No consignment items found.</p></div>';
        return;
    }

    const lang = getLang();

    el.innerHTML = PERMITS.map((permit) => {
        const cfg = PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
        const detail = permit.consignment_detail;
        const statusText = cfg[lang] || cfg.en;

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
                    ${agreementBanner}
                    <div class="pd-section-label mb-2" data-en="Consignment Info" data-bm="Info Konsainan">Consignment Info</div>
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
                                <span class="text-break">${escapeHtml(permit.purpose)}</span>
                            </p>
                        </div>
                        <div class="col-12 col-lg-6">
                            <p class="mb-2">
                                <strong class="me-1">
                                    <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-gear"></i></span>
                                    <span data-en="Usage:" data-bm="Kegunaan:">Usage:</span>
                                </strong> 
                                <span class="text-break">${escapeHtml(detail.usage)}</span>
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

                    <div class="ipv-permit-subsection-title" data-bm = "Lampiran" data-en = "Attachments">Attachments (${permit.attachments.length})</div>
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

    // Render attachment lists and apply translations to the whole container
    PERMITS.forEach((permit) => {
        const container = document.getElementById(`attachList-${permit.permit_number}`);
        renderAttachmentList(container, permit.attachments, 2);
    });

    // Apply translations to the accordion (for data-en/data-bm labels)
    applyTranslations(el);
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

    const pending = PERMITS.filter((p) =>
        ['pending for payment', 'payment failed'].includes(p.status)
    );

    document.getElementById('ipvPendingPaymentCount') &&
        (document.getElementById('ipvPendingPaymentCount').textContent = pending.length);

    if (!pending.length) {
        tableBody.append(`<tr><td colspan="4" class="text-center text-muted">No permits pending payment.</td></tr>`);
        return;
    }

    pending.forEach((permit) => {
        tableBody.append(`
            <tr>
                <td>
                    <div class="form-check">
                        <input class="form-check-input permit-checkbox" type="checkbox"
                            value="${permit.id}" data-permit-value="12"
                            ${permit.status === 'payment processing' ? 'disabled' : ''}>
                    </div>
                </td>
                <td>${escapeHtml(permit.permit_number)}</td>
                <td class="text-wrap">${escapeHtml(permit.consignment_detail.item_name)}</td>
                <td class="text-end">RM 12</td>
            </tr>
        `);
    });

    $("#checkAllPermits").prop("checked", false);
}

// ---------------------------------------------------------------
// Payment awareness banner
// ---------------------------------------------------------------

function renderPaymentAwarenessBanner() {
    const wrap = document.getElementById('ipvPaymentBannerWrap');
    const el = document.getElementById('ipvPaymentBanner');
    if (!wrap || !el) return;

    // Read from userData (loadProfile()) instead of window.authUser.
    const isOwner = getCurrentUserType() === 'public';
    const pending = PERMITS.filter((p) => ['pending for payment', 'payment failed'].includes(p.status));
    const lang = getLang();

    if (!isOwner || !pending.length) {
        wrap.style.display = 'none';
        return;
    }

    // Fixed fee: RM 12 per permit
    const total = pending.length * 12;
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

    // --- Click handler with confirmation ---
    const btn = document.getElementById('ipvGoToPaymentTab');
    if (btn) {
        btn.onclick = function(e) {
            e.preventDefault();

            const count = pending.length;
            const amount = total; // total is already computed
            const lang = getLang();

            const titleText = lang === 'bm' ? 'Teruskan ke Pembayaran?' : 'Proceed to Payment?';
            const confirmText = lang === 'bm' ? 'Ya, teruskan ke pembayaran' : 'Yes, proceed to payment';
            const cancelText = lang === 'bm' ? 'Batal' : 'Cancel';
            const paymentText = lang === 'bm'
                ? `Anda akan membayar RM ${money(amount)} untuk ${count} permit.`
                : `You are about to pay RM ${money(amount)} for ${count} permit${count > 1 ? 's' : ''}.`;

            Swal.fire({
                title: titleText,
                text: paymentText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
            }).then((result) => {
                if (!result.isConfirmed) return;

                // 1. Switch to payment tab
                const paymentTab = document.querySelector('.ipv-tabnav-item[data-ipv-tab="payment"]');
                if (paymentTab) {
                    paymentTab.click();
                    paymentTab.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }

                // 2. Select all pending permits (checkboxes in the payment table)
                $('.permit-checkbox').prop('checked', true);

                // 3. Update the total value display
                updateTotalValue();

                // 4. Enable and trigger the checkout button
                $('#checkoutPage').prop('disabled', false).click();
            });
        };
    }
}

function updateTotalValue() {
    let total = 0;
    $(".permit-checkbox:checked").each(function () {
        total += parseFloat($(this).attr("data-permit-value")) || 0;
    });
    $("#totalValue").text(`RM ${money(total)}`);
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
        console.log('Rendering activity entry:', entry);
        const cfg = STAGE_CONFIG[entry.stage] || STAGE_CONFIG.email;
        const title = cfg[lang] || cfg.en;
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
        // Try to map status to bilingual label
        let statusLabel = entry.status || '—';
        const statusKey = (entry.status || '').toLowerCase();
        if (PERMIT_STATUS_CONFIG[statusKey]) {
            statusLabel = PERMIT_STATUS_CONFIG[statusKey][lang] || PERMIT_STATUS_CONFIG[statusKey].en;
        }
        tbody.append(`
            <tr>
                <td>${escapeHtml(entry.action || entry.title || '—')}</td>
                <td>${escapeHtml(entry.user || entry.user_name || entry.user?.name || '—')}</td>
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

// ---------------------------------------------------------------
// Download-permits offcanvas
// ---------------------------------------------------------------

function renderPermitDownloadList() {
    const tbody = document.getElementById('permitDownloadTableBody');
    const available = PERMITS.filter((p) => p.status === 'paid' || p.status === 'completed');
    const lang = getLang();

    if (!available.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">No permits available for download.</td></tr>`;
        document.getElementById('downloadSelectedPermitsBtn').disabled = true;
        return;
    }

    tbody.innerHTML = available.map((permit, idx) => {
        const cfg = PERMIT_STATUS_CONFIG[permit.status] || PERMIT_STATUS_CONFIG.queued;
        const statusText = cfg[lang] || cfg.en;
        const slug = (permit.permit_number || '').replaceAll('/', '');
        return `
            <tr>
                <td><input type="checkbox" class="form-check-input permit-download-checkbox" data-index="${idx}" value="${slug}"></td>
                <td><strong>${escapeHtml(permit.permit_number)}</strong></td>
                <td>${escapeHtml(permit.consignment_detail.item_name)}</td>
                <td><span class="ipv-badge is-${cfg.color}">${escapeHtml(statusText)}</span></td>
                <td style="text-align: right;">
                    <a href="/permit/generate/${escapeHtml(slug)}" target="_blank" class="btn btn-sm btn-info">
                        <i class="bi bi-eye me-1"></i> View
                    </a>
                </td>
            </tr>
        `;
    }).join('');

    updateSelectedDownloadCount();
}

function updateSelectedDownloadCount() {
    const count = document.querySelectorAll('.permit-download-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('downloadSelectedPermitsBtn').disabled = (count === 0);
}

document.addEventListener('change', function (e) {
    if (e.target.matches('.permit-download-checkbox')) {
        updateSelectedDownloadCount();
        const all = document.querySelectorAll('.permit-download-checkbox');
        const checked = document.querySelectorAll('.permit-download-checkbox:checked');
        const selectAll = document.getElementById('selectAllPermits');
        if (selectAll) selectAll.checked = (all.length === checked.length && all.length > 0);
    }
});

document.getElementById('selectAllPermits')?.addEventListener('change', function (e) {
    document.querySelectorAll('.permit-download-checkbox').forEach((cb) => (cb.checked = e.target.checked));
    updateSelectedDownloadCount();
});

document.getElementById('downloadSelectedPermitsBtn')?.addEventListener('click', function () {
    document.querySelectorAll('.permit-download-checkbox:checked').forEach((cb) => {
        window.open(`/permit/generate/${cb.value}`, '_blank');
    });
    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('permitListOffcanvas'));
    if (offcanvas) offcanvas.hide();
});

let permitListOffcanvas = null;

function initPermitOffcanvas() {
    const el = document.getElementById('permitListOffcanvas');
    if (el && !permitListOffcanvas) {
        el.classList.remove('show');
        permitListOffcanvas = new bootstrap.Offcanvas(el, { backdrop: true, keyboard: true, scroll: false });
    }
}

// ---------------------------------------------------------------
// Payment checkbox + checkout wiring
// ---------------------------------------------------------------

function initPaymentCheckboxes() {
    $(document).on('change', '#checkAllPermits', function () {
        const isChecked = $(this).is(':checked');
        $('.permit-checkbox').prop('checked', isChecked);
        $('#checkoutPage').prop('disabled', !isChecked);
        updateTotalValue();
    });

    $(document).on('change', '.permit-checkbox', function () {
        const total = $('.permit-checkbox').length;
        const checked = $('.permit-checkbox:checked').length;
        $('#checkoutPage').prop('disabled', checked === 0);
        $('#checkAllPermits').prop('checked', total > 0 && total === checked);
        updateTotalValue();
    });

    let checkoutLocked = false;

    $(document).on('click', '#checkoutPage', function (e) {
        e.preventDefault();
        if (checkoutLocked) return;
        checkoutLocked = true;

        const $btn = $(this);
        const lang = getLang();
        $btn.prop('disabled', true).text(lang === 'bm' ? 'Memproses...' : 'Processing...');

        const selectedPermits = $('.permit-checkbox:checked').map(function () { return $(this).val(); }).get();

        if (!selectedPermits.length) {
            Swal.fire({
                icon: 'error',
                title: lang === 'bm' ? 'Ralat!' : 'Error!',
                text: lang === 'bm' ? 'Pilih permit untuk diteruskan.' : 'Choose the permit to continue.'
            });
            checkoutLocked = false;
            $btn.prop('disabled', false).text(lang === 'bm' ? 'Pergi Ke Bayaran' : 'Go To Checkout');
            return;
        }

        Swal.fire({ title: lang === 'bm' ? 'Memuat...' : 'Loading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        let calculatedTotal = 0;
        $('.permit-checkbox:checked').each(function () {
            calculatedTotal += parseFloat($(this).attr('data-permit-value')) || 0;
        });

        $.ajax({
            url: '/payment/signed-url',
            method: 'POST',
            data: {
                application_id: APPLICATION.application_id,
                permit_ids: selectedPermits,
                total: Number(calculatedTotal).toFixed(2),
                type: 'import_permit',
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (res) { window.location.href = res.url; },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: lang === 'bm' ? 'Ralat!' : 'Error!',
                    text: lang === 'bm' ? 'Tidak dapat meneruskan ke bayaran.' : 'Unable to proceed to checkout.'
                });
                checkoutLocked = false;
                $btn.prop('disabled', false).text(lang === 'bm' ? 'Pergi Ke Bayaran' : 'Go To Checkout');
            },
        });
    });
}

// ---------------------------------------------------------------
// Refresh UI on language change
// ---------------------------------------------------------------

function refreshUI() {
    // Re-render all dynamic parts without re-fetching data
    renderHeaderInfo();
    renderParties();
    renderAppAttachments();
    renderStageStepper();
    renderInfoRow();
    renderTransportDetails();
    renderPermitAccordion();
    renderPendingPaymentTable();
    renderActivityTimeline();
    renderPaymentAwarenessBanner();
    initAccordionToggle();
    // Re-apply translations to any static elements inside dynamic content
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
    renderInfoRow();
    renderTransportDetails();
    renderPermitAccordion();
    renderPendingPaymentTable();
    renderActivityTimeline();
    renderPaymentAwarenessBanner();
    initAccordionToggle();
    // Apply translations to static parts inside the dynamic content
    const container = document.querySelector('.ipv-wrapper');
    if (container) applyTranslations(container);

    const events = buildScheduleEvents(APPLICATION, PERMITS, { 
        lang: getLang(), 
        slaWorkingDays: 4 // default, or read from app.sla_days
    });
    initScheduleCalendar(events, PUBLIC_HOLIDAYS);

}

async function init() {
    if (!document.getElementById('ipvAppId')) return;

    Swal.fire({
        title: 'Loading...',
        text: 'Please wait while we fetch the application details.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // userData is the single source of truth for "who is the current
    // user" throughout this file — permitActionsHtml() and
    // renderPaymentAwarenessBanner() read it via getCurrentUserRoles() /
    // getCurrentUserType() instead of window.authUser.
    userData = await loadProfile();
    console.log('await', userData);

    if (!userData) {
        console.warn('init: profile failed to load — role/owner-based UI (permit action buttons, payment banner) will be hidden.');
    }

    await loadApplicationData();
    await renderAll();

    initTabs();
    initOffcanvas();
    initPermitOffcanvas();
    initPermitDetailOffcanvas();
    initApplicationLogModal();
    initPaymentCheckboxes();
    initScheduleCalendar();

    Swal.close();

    const printBtn = document.getElementById('ipvPrintPermitBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (!permitListOffcanvas) initPermitOffcanvas();
            renderPermitDownloadList();
            permitListOffcanvas.show();
        });
    }

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));

    // --- Listen to language changes via MutationObserver on <html> lang attribute ---
    const observer = new MutationObserver(() => {
        refreshUI();
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

    // --- Download individual attachment ---
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

        // If the path is relative, construct absolute URL using baseUrl
        if (!filePath.startsWith('http') && !filePath.startsWith('/')) {
            const base = window.baseUrl || '';
            filePath = (base.endsWith('/') ? base.slice(0, -1) : base) + '/' + filePath;
        }

        try {
            const response = await fetch(filePath, {
                credentials: 'include',
            });
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

// Public API — used by test4-actions.js to refresh the view after an
// action completes without a full reload.
window.ImportPermitView = {
    reload: async function () {
        await loadApplicationData();
        await renderAll();
    },
    getApplication: () => APPLICATION,
    getPermits: () => PERMITS,
};

export { PERMITS, STAGE_CONFIG as _STAGE_CONFIG, APPLICATION, renderViewer, renderDetails };