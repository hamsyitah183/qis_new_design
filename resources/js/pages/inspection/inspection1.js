/**
 * inspection1.js
 * Renders inspection_detail.blade.php from real data.
 * Split from the single inspection_detail.js into 3 files to match the
 * pattern already used by Import Permit (test1/test2/importPermitActions)
 * and Consignment (consignment1/consignment2/consignment-actions).
 *
 * Field mapping below is corrected against a real payload from
 * GET /inspection_application/{id}/data — notably:
 *   - each inspection_item carries quantity/purpose/value/unit_measurement
 *     at the TOP LEVEL (already correctly typed), not just nested inside
 *     consignment_detail (which duplicates them as strings) — top-level is
 *     now preferred, consignment_detail is just the fallback.
 *   - importer_verify ("Verified" / etc.) is real data — now surfaced as a
 *     sidebar tag alongside the self/others category tag.
 *   - category_application comes back as a STRING ("0"/"1"), handled with
 *     String() comparison throughout.
 */

import { openPermitDetail, initPermitDetailOffcanvas } from "./inspection2";
import $ from "jquery";
import Swal from "sweetalert2";
import { applyTranslations } from "../../app";
import { loadProfile } from "../auth/profile";

// ---------------------------------------------------------------
// Config
// ---------------------------------------------------------------

const STAGE_ORDER = [
    'submitted',
    'doc_verification',
    'officer_verification_completed',
    'awaiting_payment',
    'payment_processing',
    'completed',
];

export const STAGE_CONFIG = {
    submitted:           { en: 'Submitted',              bm: 'Dihantar',                  icon: 'bi-send-check',         color: 'info' },
    doc_verification:    { en: 'Clerk Review In-Progress', bm: 'Semakan Kerani Dalam Proses', icon: 'bi-file-earmark-check', color: 'secondary' },
    returned:            { en: 'Returned / Rejected',     bm: 'Dikembalikan / Ditolak',    icon: 'bi-arrow-return-left',  color: 'danger' },
    officer_verification_completed: { en: 'Officer Verified', bm: 'Disahkan Pegawai',        icon: 'bi-check-circle',       color: 'success' },
    awaiting_payment:    { en: 'Awaiting Payment',        bm: 'Menunggu Pembayaran',       icon: 'bi-hourglass-split',    color: 'warning' },
    payment_processing:  { en: 'Payment Processing',      bm: 'Proses Pengesahan Bayaran', icon: 'bi-credit-card',        color: 'orange' },
    completed:           { en: 'Completed',               bm: 'Selesai',                   icon: 'bi-check-circle',       color: 'success' },
    permit_approved:     { en: 'Certificate Approved',    bm: 'Sijil Diluluskan',          icon: 'bi-check-circle',       color: 'success' },
    permit_rejected:     { en: 'Certificate Rejected',    bm: 'Sijil Ditolak',             icon: 'bi-x-circle',           color: 'danger' },
    payment:             { en: 'Payment Update',          bm: 'Kemaskini Bayaran',         icon: 'bi-credit-card-2-back', color: 'orange' },
    email:               { en: 'Notification Sent',       bm: 'Notifikasi Dihantar',       icon: 'bi-envelope-check',     color: 'gray' },
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

// ─── Flat application fee (RM 10 for the whole application) ───
export const INSPECTION_APPLICATION_FEE = 10;

// Fixed string expected by /permit/print and /public/save-inspection/{id}
export const INSPECTION_PRINT_TYPE = 'Inspection';

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
    if (s.includes('officer verification completed')) return 'officer_verification_completed';
    if (s.includes('completed')) return 'completed';
    if (s.includes('rejected') || s.includes('not approved')) return 'returned';
    if (s.includes('pending for payment')) return 'awaiting_payment';
    if (s.includes('payment processing')) return 'payment_processing';
    return 'submitted';
}

function buildTags(json) {
    const tags = [];
    const category = String(json.category_application ?? '');

    if (category === '0') {
        tags.push({ label_en: 'Self Import', label_bm: 'Import Sendiri', color: 'info' });
    } else {
        tags.push({ label_en: 'Apply for Others', label_bm: 'Mohon untuk Pihak Lain', color: 'primary' });
    }

    const verify = (json.importer_verify || '').toLowerCase();
    if (verify.includes('not')) {
        tags.push({ label_en: 'Importer Not Verified', label_bm: 'Pengimport Tidak Disahkan', color: 'danger' });
    } else if (verify.includes('wait')) {
        tags.push({ label_en: 'Awaiting Company Approval', label_bm: 'Menunggu Kelulusan Syarikat', color: 'warning' });
    } else if (verify.includes('verified')) {
        tags.push({ label_en: 'Importer Verified', label_bm: 'Pengimport Disahkan', color: 'success' });
    }

    return tags;
}

function mapApplication(json) {
    const importer = json.importer_detail || json.importer || {};
    const exporter = json.exporter || json.user || {};
    const entryPoint = json.entry_point || {};
    const importerCountry = importer.country_info || {};
    const exporterCountry = exporter.country_info || {};

    const rawStatus = json.status || '';

    const firstItemPrintCalc =
        (json.inspection_items && json.inspection_items[0] && json.inspection_items[0].print_calc) ||
        json.print_calc || 0;

    APPLICATION = {
        id: json.id,
        application_id: json.application_id,
        application_type: json.application_type || 'Inspection Certificate',
        type: json.application_type || 'Inspection Certificate',
        status: rawStatus,
        status_key: deriveStageKey(rawStatus),
        status_duration: json.status_duration || '',
        returned_reason: json.returned_reason || json.remark || null,
        tags: buildTags(json),
        submitted_by: importer.fullname || importer.name || json.user?.fullname || '—',
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
            name: importer.fullname || importer.name || '—',
            phone: importer.phone_number || importer.phone_no || '—',
            email: importer.email || '—',
            address: importer.address || [importer.address_1, importer.address_2, importer.postcode, importer.district]
                .filter(Boolean).join(', ') || '—',
            country: importerCountry.name || importer.country || 'Malaysia',
        },
        exporter: {
            name: exporter.name || exporter.fullname || '—',
            phone: exporter.phone_no || exporter.phone_number || '—',
            email: exporter.email || '—',
            address: exporter.address || [exporter.address_1, exporter.address_2, exporter.postcode, exporter.district]
                .filter(Boolean).join(', ') || '—',
            country: exporterCountry.name || exporter.country || '—',
        },
        attachments: (json.attachment || json.attachments || []).map(mapAttachment),
    };
}

function mapPermits(json) {
    const permits = json.inspection_items || [];
    PERMITS = permits.map((permit) => {
        const detail = permit.consignment_detail || {};
        const quantity = permit.quantity ?? detail.quantity ?? 0;
        const purpose = permit.purpose ?? detail.purpose ?? '—';
        const value = permit.value ?? detail.value ?? 0;
        const unitMeasurement = permit.unit_measurement ?? detail.measure ?? '';

        const statusKey = (permit.status || 'processing').toLowerCase();

        return {
            id: permit.id,
            permit_number: permit.permit_number || '—',
            consignment_detail: {
                item_name: detail.item_name || '—',
                item_id: detail.item_id ?? null,
                purpose,
                uses: detail.uses || '—',
            },
            quantity: Number(quantity) || 0,
            unit_measurement: unitMeasurement,
            value: Number(value) || 0,
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

export function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

export function money(n) {
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
    document.getElementById('ipvAppId').textContent = APPLICATION.application_id;
    document.getElementById('ipvSubmittedBy').textContent = APPLICATION.submitted_by;
    document.getElementById('ipvDownloadBadge').innerHTML = `<i class="bi bi-download"></i> ${APPLICATION.downloaded_count}`;

    const submittedLabel = lang === 'bm' ? 'Permohonan dihantar pada' : 'Application submitted on';
    document.getElementById('ipvCreatedAt').textContent = `${submittedLabel} ${APPLICATION.submitted_at}`;

    const tagsEl = document.getElementById('ipvTags');
    if (tagsEl) {
        tagsEl.innerHTML = (APPLICATION.tags || []).map((tag) => {
            const label = lang === 'bm' ? tag.label_bm : tag.label_en;
            return `<span class="ipv-tag btn-primary is-${tag.color}">${escapeHtml(label)}</span>`;
        }).join('');
    }

    const total = PERMITS.reduce((sum, p) => sum + p.value, 0);
    document.getElementById('ipvTotalValue').textContent = `RM ${money(total)}`;

    const printBtn = document.getElementById('ipvPrintPermitBtn');
    if (printBtn) {
        printBtn.classList.add('generatePermit');
        printBtn.dataset.permit = APPLICATION.application_id;
        printBtn.dataset.type = INSPECTION_PRINT_TYPE;
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
        noteEl.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i> ${escapeHtml(APPLICATION.returned_reason || 'Application returned for correction.')}`;
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
    const wrapId = document.getElementById('ipvBulkActionsWrap')
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

    // ─── Pay All – flat fee per application ────────────────────────
    if (isOwner && hasPendingPayment) {
        const pending = PERMITS.filter(p => ['pending for payment', 'payment failed'].includes(p.status));
        const total = INSPECTION_APPLICATION_FEE; // flat fee
        wrap.style.display = '';
        wrap.innerHTML = `
            <div class="ipv-actions-bar-text">
                <i class="bi bi-credit-card"></i>
                <span>${lang === 'bm' ? `${pending.length} permit menunggu bayaran. Jumlah: RM ${money(total)}` : `${pending.length} permit${pending.length > 1 ? 's' : ''} awaiting payment. Total: RM ${money(total)}`}</span>
            </div>
            <div class="ipv-actions-bar-buttons">
                <button type="button" class="ipv-btn-action is-warning pay-bulk" data-application="${APPLICATION.application_id}">
                    <i class="bi bi-credit-card"></i> ${lang === 'bm' ? 'Bayar' : 'Pay'}
                </button>
            </div>
        `;
        return;
    }

    if (isCompleted && (hasPrintPerm || isOwner)) {
        wrap.style.display = '';
        wrap.innerHTML = `
            <div class="ipv-actions-bar-text">
                <i class="bi bi-check-circle"></i>
                <span>${lang === 'bm' ? 'Permohonan ini telah selesai.' : 'This application is complete.'}</span>
            </div>
            <div class="ipv-actions-bar-buttons">
                <button type="button" class="ipv-btn-action is-info generatePermit" data-permit="${APPLICATION.application_id}" data-type="${INSPECTION_PRINT_TYPE}">
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
// Render: Pending Payment tab (flat fee per application)
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

    // Show each permit with a dash in the fee column (since fee is flat per application)
    pending.forEach((permit) => {
        tableBody.append(`
            <tr>
                <td>${escapeHtml(permit.permit_number)}</td>
                <td class="text-wrap">${escapeHtml(permit.consignment_detail.item_name)}</td>
                <td class="text-end">—</td>
            </tr>
        `);
    });

    const total = INSPECTION_APPLICATION_FEE;
    const $tfoot = $("#summaryTable4 tfoot");
    $tfoot.html(`
        <tr>
            <td colspan="2" class="text-end fw-bold">${getLang() === 'bm' ? 'Jumlah Yuran:' : 'Total Fee:'}</td>
            <td class="text-end fw-bold">RM ${money(total)}</td>
        </tr>
        <tr>
            <td colspan="3" class="text-end">
                <button class="ipv-btn-primary pay-bulk" data-application="${APPLICATION.application_id}">
                    <i class="bi bi-credit-card"></i> ${getLang() === 'bm' ? 'Bayar' : 'Pay'}
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

    const total = INSPECTION_APPLICATION_FEE;
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
    initPermitDetailOffcanvas();
    initApplicationLogModal();

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

// Public API
window.ImportPermitView = window.ImportPermitView || {};
window.ImportPermitView.reload = async function () {
    await loadApplicationData();
    await renderAll();
};
window.ImportPermitView.getApplication = () => APPLICATION;
window.ImportPermitView.getPermits = () => PERMITS;

export { PERMITS, APPLICATION, renderViewer, renderDetails };