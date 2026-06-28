/**
 * applicationSummary.js
 * ------------------------------------------------------------------
 * Summary & Declaration page for the import permit application.
 *
 * Reads draft data from sessionStorage (key: 'ipa_draft') that
 * applyImportPermit.js writes when the user clicks "Submit Application".
 * If no draft is found, falls back to safe placeholder data so the
 * page is still reviewable during development.
 *
 * Data shape expected in sessionStorage:
 * {
 *   eta, transportType, entryPoint,
 *   importer: { name, phone },
 *   exporter: { name, country },
 *   items: [{ itemName, category, usage, purpose, qty, unit, value,
 *              conditions: [], files: [{ name, docType, fileName, size }] }],
 *   appDocs: [{ name, docType, fileName, size }],
 * }
 */

// ---------------------------------------------------------------
// Fallback / demo data  (used when no sessionStorage draft exists)
// ---------------------------------------------------------------
const DEMO = {
    eta: '20 May 2025',
    transportType: 'Sea Freight',
    entryPoint: 'Kota Kinabalu Port',
    importer: { name: 'Borneo Fresh Trading Sdn Bhd', phone: '(088) 244 511' },
    exporter: { name: 'Golden Harvest Pte Ltd', country: 'Singapore' },
    items: [
        {
            itemName: 'Fresh Fruit — Corn', category: 'Fresh Produce',
            usage: 'Commercial Sale', purpose: 'Commercial Sale',
            qty: '1200', unit: 'KG', value: '2480',
            conditions: [
                'Must be accompanied by a valid phytosanitary certificate.',
                'Subject to inspection at point of entry.',
                'Produce must be free from soil, pests, and plant diseases.',
            ],
            files: [
                { name: 'Phytosanitary Certificate', docType: 'Phytosanitary Certificate', fileName: 'phyto_cert.pdf', size: '210 KB' },
                { name: 'Fumigation Certificate',    docType: 'Other',                     fileName: 'fumigation.pdf', size: '180 KB' },
            ],
        },
        {
            itemName: 'Frozen Seafood — Tilapia', category: 'Frozen Seafood',
            usage: 'Commercial Sale', purpose: 'Commercial Sale',
            qty: '4500', unit: 'KG', value: '9150',
            conditions: [
                'Must maintain cold-chain temperature below −18°C.',
                'Requires a valid catch certificate.',
                'Products must comply with local food safety standards.',
            ],
            files: [
                { name: 'Health Certificate', docType: 'Health Certificate', fileName: 'health_cert.pdf', size: '230 KB' },
            ],
        },
    ],
    appDocs: [
        { name: 'Commercial Invoice', docType: 'Commercial Invoice', fileName: 'invoice.pdf',        size: '420 KB' },
        { name: 'Packing List',       docType: 'Packing List',       fileName: 'packing_list.xlsx',  size: '88 KB'  },
        { name: 'Bill of Lading',     docType: 'Bill of Lading',     fileName: 'bill_of_lading.pdf', size: '310 KB' },
    ],
};

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function escapeHtml(v) {
    return String(v ?? '—').replace(/[&<>"']/g, c => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',
    }[c]));
}

function money(n) {
    return Number(n || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fileMeta(fileName) {
    const ext = (fileName.split('.').pop() || '').toLowerCase();
    if (ext === 'pdf')                        return { icon: 'bi-file-earmark-pdf-fill',   cls: 'is-pdf'   };
    if (['xlsx','xls','csv'].includes(ext))   return { icon: 'bi-file-earmark-excel-fill', cls: 'is-excel' };
    if (['doc','docx'].includes(ext))         return { icon: 'bi-file-earmark-word-fill',  cls: 'is-word'  };
    if (['jpg','jpeg','png'].includes(ext))   return { icon: 'bi-file-earmark-image-fill', cls: 'is-image' };
    if (['zip','rar'].includes(ext))          return { icon: 'bi-file-earmark-zip-fill',   cls: 'is-zip'   };
    return { icon: 'bi-file-earmark-fill', cls: 'is-default' };
}

function draftId() {
    return 'DRAFT-' + Math.random().toString(36).slice(2,8).toUpperCase();
}

function today() {
    return new Date().toLocaleDateString('en-MY', { day: 'numeric', month: 'long', year: 'numeric' });
}

// ---------------------------------------------------------------
// Load data
// ---------------------------------------------------------------

function loadDraft() {
    try {
        const raw = sessionStorage.getItem('ipa_draft');
        if (raw) return JSON.parse(raw);
    } catch {}
    return DEMO;
}

// ---------------------------------------------------------------
// Render helpers
// ---------------------------------------------------------------

function renderInfoGrid(el, rows) {
    el.innerHTML = rows.map(r => `
        <div class="ips-info-cell">
            <div class="ips-info-label">${escapeHtml(r.label)}</div>
            <div class="ips-info-value">${escapeHtml(r.value)}</div>
        </div>
    `).join('');
}

function partyBlockHtml(label, name, detail, detailIcon) {
    const initial = (name || '?').charAt(0).toUpperCase();
    return `
        <div class="ips-party-label">${escapeHtml(label)}</div>
        <div class="ips-party-head">
            <div class="ips-party-avatar">${initial}</div>
            <div>
                <div class="ips-party-name">${escapeHtml(name)}</div>
                <div class="ips-party-detail">
                    <i class="bi ${detailIcon}"></i> ${escapeHtml(detail)}
                </div>
            </div>
        </div>
    `;
}

function docChipsHtml(files) {
    if (!files || !files.length) {
        return '<div class="ips-no-docs">No documents attached.</div>';
    }
    return `<div class="ips-doc-chips">${files.map(f => {
        const meta = fileMeta(f.fileName || f.name);
        return `
            <div class="ips-doc-chip">
                <div class="ips-doc-chip-icon ${meta.cls}"><i class="bi ${meta.icon}"></i></div>
                <div class="ips-doc-chip-info">
                    <div class="ips-doc-chip-name" title="${escapeHtml(f.name)}">${escapeHtml(f.name)}</div>
                    <div class="ips-doc-chip-meta">
                        <span class="ips-doc-type-badge">${escapeHtml(f.docType)}</span>
                        <span>${escapeHtml(f.size)}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('')}</div>`;
}

// ---------------------------------------------------------------
// Render sections
// ---------------------------------------------------------------

function renderRefStrip(draft) {
    document.getElementById('ipsRefId').textContent           = draftId();
    document.getElementById('ipsRefPreparedBy').textContent   = draft.importer?.name || '—';
    document.getElementById('ipsRefDate').textContent         = today();
    document.getElementById('ipsRefItemCount').textContent    = `${draft.items.length} item${draft.items.length !== 1 ? 's' : ''}`;
    const total = draft.items.reduce((s, i) => s + parseFloat(i.value || 0), 0);
    document.getElementById('ipsRefTotalValue').textContent   = `RM ${money(total)}`;
}

function renderTransport(draft) {
    renderInfoGrid(document.getElementById('ipsTransportGrid'), [
        { label: 'ETA',            value: draft.eta           || '—' },
        { label: 'Transport Type', value: draft.transportType || '—' },
        { label: 'Entry Point',    value: draft.entryPoint    || '—' },
    ]);
}

function renderParties(draft) {
    document.getElementById('ipsImporterBlock').innerHTML =
        partyBlockHtml('Importer', draft.importer?.name, draft.importer?.phone || '—', 'bi-telephone');
    document.getElementById('ipsExporterBlock').innerHTML =
        partyBlockHtml('Exporter', draft.exporter?.name, draft.exporter?.country || '—', 'bi-globe2');
}

function renderItems(draft) {
    const el = document.getElementById('ipsItemsAccordion');
    document.getElementById('ipsItemsSubtitle').textContent =
        `${draft.items.length} item${draft.items.length !== 1 ? 's' : ''} added to this application`;

    el.innerHTML = draft.items.map((item, idx) => {
        const total = money(parseFloat(item.value || 0));
        return `
            <div class="ips-item-row" data-idx="${idx}">
                <div class="ips-item-row-header">
                    <div class="ips-item-num">${idx + 1}</div>
                    <div class="ips-item-row-info">
                        <div class="ips-item-row-name">${escapeHtml(item.itemName)}</div>
                        <div class="ips-item-row-meta">
                            <span>${escapeHtml(item.category)}</span>
                            <span class="ips-sep">·</span>
                            <span>${escapeHtml(item.qty)} ${escapeHtml(item.unit)}</span>
                            <span class="ips-sep">·</span>
                            <span>RM ${total}</span>
                            <span class="ips-sep">·</span>
                            <span>${item.files?.length || 0} doc${(item.files?.length || 0) !== 1 ? 's' : ''}</span>
                        </div>
                    </div>
                    <button type="button" class="ips-item-toggle" data-idx="${idx}" title="Expand">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                <div class="ips-item-body" id="ipsItemBody-${idx}">
                    <div class="ips-info-grid" style="margin-bottom:1.1rem;">
                        ${[
                            { label: 'Category', value: item.category },
                            { label: 'Item Name', value: item.itemName },
                            { label: 'Usage',    value: item.usage || '—' },
                            { label: 'Purpose',  value: item.purpose },
                            { label: 'Quantity', value: `${item.qty} ${item.unit}` },
                            { label: 'Declared Value', value: `RM ${total}` },
                        ].map(r => `
                            <div class="ips-info-cell">
                                <div class="ips-info-label">${escapeHtml(r.label)}</div>
                                <div class="ips-info-value">${escapeHtml(r.value)}</div>
                            </div>
                        `).join('')}
                    </div>

                    <div class="ips-sub-section-label">Import Conditions</div>
                    <div class="ips-conditions-list">
                        ${(item.conditions || []).map(c => `
                            <div class="ips-condition-row">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>${escapeHtml(c)}</span>
                            </div>
                        `).join('')}
                    </div>

                    <div class="ips-sub-section-label" style="margin-top:1rem;">Supporting Documents</div>
                    ${docChipsHtml(item.files)}
                </div>
            </div>
        `;
    }).join('');

    // Accordion toggle
    el.addEventListener('click', e => {
        const btn = e.target.closest('.ips-item-toggle');
        if (!btn) return;
        const idx  = btn.dataset.idx;
        const body = document.getElementById(`ipsItemBody-${idx}`);
        const row  = btn.closest('.ips-item-row');
        const open = row.classList.toggle('is-open');
        btn.querySelector('i').className = open ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
    });
}

function renderAppDocs(draft) {
    document.getElementById('ipsAppDocs').innerHTML = docChipsHtml(draft.appDocs);
}

// ---------------------------------------------------------------
// Declaration checkboxes
// ---------------------------------------------------------------

function initDeclaration() {
    const checks   = document.querySelectorAll('.ips-decl-check');
    const fillEl   = document.getElementById('ipsDeclProgressFill');
    const labelEl  = document.getElementById('ipsDeclProgressLabel');
    const submitBtn = document.getElementById('ipsSubmitBtn');
    const hintEl   = document.getElementById('ipsSubmitHint');

    function update() {
        const checked = document.querySelectorAll('.ips-decl-check:checked').length;
        const total   = checks.length;
        const pct     = Math.round((checked / total) * 100);

        fillEl.style.width     = pct + '%';
        labelEl.textContent    = `${checked} of ${total} confirmed`;

        const allDone = checked === total;
        submitBtn.disabled     = !allDone;
        hintEl.textContent     = allDone
            ? 'All declarations confirmed. You may now submit.'
            : `Please confirm all ${total} declarations above to submit.`;
        hintEl.classList.toggle('is-ready', allDone);

        // highlight unchecked items
        checks.forEach(cb => {
            cb.closest('.ips-decl-item').classList.toggle('is-checked', cb.checked);
        });
    }

    checks.forEach(cb => cb.addEventListener('change', update));
    update();
}

// ---------------------------------------------------------------
// Submit
// ---------------------------------------------------------------

function wireSubmit() {
    document.getElementById('ipsSubmitBtn').addEventListener('click', () => {
        const btn = document.getElementById('ipsSubmitBtn');
        btn.disabled   = true;
        btn.innerHTML  = '<span class="ips-spinner"></span> Submitting…';

        // Simulate API call — replace with real fetch/axios POST
        setTimeout(() => {
            sessionStorage.removeItem('ipa_draft');
            // Redirect to confirmation or application list
            window.location.href = '/public/view_import_permit';
        }, 1800);
    });
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

function init() {
    if (!document.getElementById('ipsRefId')) return;

    const draft = loadDraft();

    renderRefStrip(draft);
    renderTransport(draft);
    renderParties(draft);
    renderItems(draft);
    renderAppDocs(draft);
    initDeclaration();
    wireSubmit();
}

document.addEventListener('DOMContentLoaded', init);