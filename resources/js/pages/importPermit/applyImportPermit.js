/**
 * applyImportPermit.js
 * ------------------------------------------------------------------
 * Permit item form, now driven by the selected exporter's COUNTRY
 * instead of a free-standing category dropdown:
 *
 *   exporter selected → country known → ITEM_CATALOG_BY_COUNTRY[country]
 *   populates the Category select → Category populates Item Name.
 *
 * If no exporter is selected yet, the Category select stays locked
 * with a prompt, since there's no country to derive the catalog from.
 *
 * Also adds full-form validation: "Review Application" stays
 * disabled until every required field across Transportation,
 * Importer/Exporter, and at least one Added Item is filled in.
 */

// ---------------------------------------------------------------
// Reference data — catalog is now keyed by exporter COUNTRY
// ---------------------------------------------------------------

const ITEM_CATALOG_BY_COUNTRY = {
    Thailand: {
        'Fresh Produce':  ['Fresh Fruit — Durian', 'Fresh Vegetables — Thai Eggplant'],
        'Processed Food': ['Canned Pineapple', 'Instant Noodles'],
    },
    Indonesia: {
        'Agricultural Seedlings': ['Oil Palm Seedlings', 'Rubber Seedlings'],
        'Ornamental Plants':      ['Orchid Hybrids', 'Bonsai Trees'],
    },
    Singapore: {
        'Frozen Seafood': ['Frozen Seafood — Tilapia', 'Frozen Shrimp'],
        'Processed Food': ['Instant Noodles'],
    },
    China: {
        'Live Animals':   ['Live Ornamental Fish', 'Live Poultry Chicks'],
        'Processed Food': ['Canned Pineapple'],
    },
};

// Conditions keyed by category — unchanged, category is still the
// meaningful unit for import conditions regardless of country.
const CATEGORY_CONDITIONS = {
    'Fresh Produce': [
        'Must be accompanied by a valid phytosanitary certificate issued by the country of origin.',
        'Subject to inspection at the point of entry by a designated officer.',
        'Produce must be free from soil, pests, and plant diseases.',
    ],
    'Live Animals': [
        'Requires a valid health certificate from a licensed veterinarian in the country of origin.',
        'Subject to quarantine inspection on arrival for a minimum of 7 days.',
        'Annual import quota restrictions may apply to certain species.',
    ],
    'Frozen Seafood': [
        'Must maintain cold-chain temperature below −18°C throughout transit.',
        'Requires a valid catch certificate and health certificate.',
        'Products must comply with local food safety standards.',
    ],
    'Agricultural Seedlings': [
        'Restricted to approved research institutions and licensed nurseries only.',
        'Requires a valid phytosanitary certificate and import authorization letter.',
        'Subject to post-arrival monitoring for a period of 3 months.',
    ],
    'Processed Food': [
        'Must carry valid halal certification where applicable.',
        'Subject to laboratory analysis on a random sampling basis.',
        'Labelling must comply with Malaysian food labelling regulations.',
    ],
    'Ornamental Plants': [
        'Requires a valid CITES permit for protected or endangered species.',
        'Subject to phytosanitary inspection at the point of entry.',
        'Plants must be free from soil and growing media.',
    ],
};

const DEFAULT_CONDITIONS = [
    'Importer must hold a valid import licence for this category.',
    'All documents submitted must be authentic and verifiable.',
    'Subject to any additional conditions imposed by the approving officer.',
];

const UNIT_OPTIONS    = ['KG', 'Unit', 'Litre', 'Tonne', 'Box'];
const PURPOSE_OPTIONS = ['Commercial Sale', 'Research', 'Re-export', 'Personal Use', 'Breeding'];

const DOCUMENT_TYPE_OPTIONS = [
    'Commercial Invoice', 'Packing List', 'Bill of Lading', 'Authorization Letter',
    'Phytosanitary Certificate', 'Health Certificate', 'Halal Certificate',
    'CITES Permit', 'Lab Analysis Report', 'Catch Certificate', 'Other',
];

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

function formatBytes(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function fileMeta(fileName) {
    const ext = (fileName.split('.').pop() || '').toLowerCase();
    if (ext === 'pdf')                         return { icon: 'bi-file-earmark-pdf-fill',     cls: 'is-pdf' };
    if (['xlsx','xls','csv'].includes(ext))    return { icon: 'bi-file-earmark-excel-fill',   cls: 'is-excel' };
    if (['doc','docx'].includes(ext))          return { icon: 'bi-file-earmark-word-fill',    cls: 'is-word' };
    if (['jpg','jpeg','png'].includes(ext))    return { icon: 'bi-file-earmark-image-fill',   cls: 'is-image' };
    if (['zip','rar'].includes(ext))           return { icon: 'bi-file-earmark-zip-fill',     cls: 'is-zip' };
    return { icon: 'bi-file-earmark-fill', cls: 'is-default' };
}

function money(n) {
    return Number(n || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ---------------------------------------------------------------
// Attachment card system — 1 file per card (unchanged)
// ---------------------------------------------------------------

let attachSeq = 0;
let fileSeq   = 0;

function createAttachmentList(container) {
    const listId = 'alist-' + (attachSeq++);
    const cards  = [];

    container.innerHTML = `
        <div class="ipa-attachment-list" id="${listId}-list"></div>
        <button type="button" class="ipa-btn-add-attachment" id="${listId}-addbtn">
            <i class="bi bi-paperclip"></i> Add Attachment
        </button>
    `;

    const listEl  = container.querySelector(`#${listId}-list`);
    const addBtn  = container.querySelector(`#${listId}-addbtn`);

    function rerenderCard(card) {
        const el = listEl.querySelector(`[data-card-id="${card.cardId}"]`);
        if (!el) return;

        const bodyEl = el.querySelector('.ipa-attachment-card-body');

        if (!card.fileObj) {
            bodyEl.innerHTML = `
                <label class="ipa-dropzone" data-card-drop="${card.cardId}">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <div class="ipa-dropzone-title">Choose a file or drag &amp; drop</div>
                    <div class="ipa-dropzone-sub">PDF, JPG, PNG, DOCX, XLSX — up to 10 MB</div>
                    <span class="ipa-dropzone-browse">Browse</span>
                    <input type="file" class="ipa-card-file-input" hidden>
                </label>
            `;
            wireDropzone(card, bodyEl);
        } else {
            const meta   = fileMeta(card.fileName);
            const isDone = card.status === 'completed';
            bodyEl.innerHTML = `
                <div class="ipa-attachment-file-row">
                    <div class="ipa-file-icon ${meta.cls}"><i class="bi ${meta.icon}"></i></div>
                    <div class="ipa-file-info">
                        <div class="ipa-file-name" title="${escapeHtml(card.fileName)}">${escapeHtml(card.fileName)}</div>
                        <div class="ipa-file-meta">${escapeHtml(card.size)}</div>
                        ${isDone
                            ? `<div class="ipa-file-status-done"><i class="bi bi-check-circle-fill"></i> Completed</div>
                               <button type="button" class="ipa-file-replace-btn" data-card-replace="${card.cardId}">Replace file</button>`
                            : `<div class="ipa-file-progress-track">
                                   <div class="ipa-file-progress-fill" style="width:${Math.min(card.progress,100)}%"></div>
                               </div>
                               <div class="ipa-file-status-progress">${Math.round(card.progress)}% uploading…</div>`
                        }
                    </div>
                </div>
            `;
            if (isDone) {
                el.classList.add('has-file');
                const replaceBtn = bodyEl.querySelector(`[data-card-replace="${card.cardId}"]`);
                replaceBtn?.addEventListener('click', () => {
                    card.fileObj = null; card.fileName = ''; card.size = '';
                    card.progress = 0;  card.status = 'idle';
                    el.classList.remove('has-file');
                    rerenderCard(card);
                });
            }
        }
    }

    function wireDropzone(card, bodyEl) {
        const dropzone  = bodyEl.querySelector(`[data-card-drop="${card.cardId}"]`);
        const fileInput = bodyEl.querySelector('.ipa-card-file-input');
        if (!dropzone || !fileInput) return;

        dropzone.addEventListener('click', e => {
            if (e.target !== fileInput) { e.preventDefault(); fileInput.click(); }
        });
        fileInput.addEventListener('change', () => {
            if (fileInput.files[0]) pickFile(card, fileInput.files[0]);
        });
        ['dragover','dragleave','drop'].forEach(evt => {
            dropzone.addEventListener(evt, e => {
                e.preventDefault();
                dropzone.classList.toggle('is-dragover', evt === 'dragover');
                if (evt === 'drop' && e.dataTransfer?.files[0]) pickFile(card, e.dataTransfer.files[0]);
            });
        });
    }

    function pickFile(card, file) {
        card.fileObj   = file;
        card.fileName  = file.name;
        card.size      = formatBytes(file.size);
        card.progress  = 0;
        card.status    = 'uploading';
        rerenderCard(card);
        simulateUpload(card);
    }

    function simulateUpload(card) {
        const interval = setInterval(() => {
            card.progress += Math.random() * 25 + 10;
            if (card.progress >= 100) {
                card.progress = 100;
                card.status   = 'completed';
                clearInterval(interval);
            }
            rerenderCard(card);
            document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
        }, 350);
    }

    function addCard() {
        const cardId = listId + '-card-' + cards.length;
        const card   = {
            cardId,
            name: '', docType: '',
            fileObj: null, fileName: '', size: '',
            progress: 0, status: 'idle',
        };
        cards.push(card);

        const el = document.createElement('div');
        el.className = 'ipa-attachment-card';
        el.dataset.cardId = cardId;
        el.innerHTML = `
            <div class="ipa-attachment-card-head">
                <div class="ipa-field">
                    <label>Document Name</label>
                    <input type="text" class="ipa-input ipa-card-name" placeholder="e.g. Phytosanitary Certificate" data-card-id="${cardId}">
                </div>
                <div class="ipa-field">
                    <label>Document Type</label>
                    <select class="ipa-input ipa-card-type" data-card-id="${cardId}">
                        <option value="">-- Select type --</option>
                        ${DOCUMENT_TYPE_OPTIONS.map(t => `<option value="${escapeHtml(t)}">${escapeHtml(t)}</option>`).join('')}
                    </select>
                </div>
                <button type="button" class="ipa-attachment-card-remove" data-card-remove="${cardId}" title="Remove attachment">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="ipa-attachment-card-body"></div>
        `;

        el.querySelector('.ipa-card-name').addEventListener('input', function() {
            card.name = this.value.trim();
            document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
        });
        el.querySelector('.ipa-card-type').addEventListener('change', function() {
            card.docType = this.value;
            document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
        });

        el.querySelector(`[data-card-remove="${cardId}"]`).addEventListener('click', () => {
            el.remove();
            const idx = cards.findIndex(c => c.cardId === cardId);
            if (idx > -1) cards.splice(idx, 1);
            document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
        });

        listEl.appendChild(el);
        rerenderCard(card);
        document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
    }

    addBtn.addEventListener('click', addCard);

    return {
        getFiles: () => cards
            .filter(c => c.status === 'completed')
            .map(c => ({
                id:      c.cardId,
                name:    c.name || c.fileName,
                docType: c.docType || 'Other',
                fileName: c.fileName,
                size:    c.size,
                fileObj: c.fileObj,
                status:  c.status,
            })),
        getCardCount: () => cards.length,
        clear: () => {
            cards.length = 0;
            listEl.innerHTML = '';
            document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
        },
    };
}

let appUploader = null; // exposed at module scope so validation can check it

// ---------------------------------------------------------------
// Category / Item cascade — now driven by exporter COUNTRY
// ---------------------------------------------------------------

function getCatalogForCurrentExporter() {
    const country = document.getElementById('exporterCountry')?.value.trim();
    return country ? (ITEM_CATALOG_BY_COUNTRY[country] || {}) : {};
}

function populateCategorySelectForExporter() {
    const catSelect  = document.getElementById('ipaItemCategory');
    const nameSelect = document.getElementById('ipaItemName');
    const catalog    = getCatalogForCurrentExporter();
    const categories = Object.keys(catalog);

    // Reset item name select regardless — category is changing under it
    nameSelect.innerHTML = '<option value="">-- Select category first --</option>';
    nameSelect.disabled = true;

    if (!categories.length) {
        catSelect.innerHTML = '<option value="">-- Select an exporter first --</option>';
        catSelect.disabled = true;
        return;
    }

    catSelect.disabled = false;
    catSelect.innerHTML = '<option value="">-- Select category --</option>' +
        categories.map(cat => `<option value="${escapeHtml(cat)}">${escapeHtml(cat)}</option>`).join('');

    document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
}

function initCategoryNameCascade() {
    const catSelect  = document.getElementById('ipaItemCategory');
    const nameSelect = document.getElementById('ipaItemName');

    // Start locked — no exporter selected yet on page load
    catSelect.disabled = true;
    catSelect.innerHTML = '<option value="">-- Select an exporter first --</option>';

    catSelect.addEventListener('change', () => {
        const catalog = getCatalogForCurrentExporter();
        const items   = catalog[catSelect.value] || [];
        nameSelect.innerHTML = '<option value="">-- Select item --</option>' +
            items.map(i => `<option value="${escapeHtml(i)}">${escapeHtml(i)}</option>`).join('');
        nameSelect.disabled = items.length === 0;
        document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
    });

    // Whenever the exporter changes (selected from search, or a new
    // exporter is being added), re-derive the category list.
    document.addEventListener('ipa:exporter-changed', populateCategorySelectForExporter);
}

// ---------------------------------------------------------------
// Populate the rest of the form selects (purpose, unit — unaffected)
// ---------------------------------------------------------------

function populateFormSelects() {
    const purposeSelect = document.getElementById('ipaItemPurpose');
    PURPOSE_OPTIONS.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p; opt.textContent = p;
        purposeSelect.appendChild(opt);
    });

    const unitSelect = document.getElementById('ipaItemUnit');
    UNIT_OPTIONS.forEach(u => {
        const opt = document.createElement('option');
        opt.value = u; opt.textContent = u;
        unitSelect.appendChild(opt);
    });

    initCategoryNameCascade();
}

// ---------------------------------------------------------------
// Added items store
// ---------------------------------------------------------------

let itemSeq = 0;
const addedItems = [];

function getFormData() {
    return {
        category: document.getElementById('ipaItemCategory').value.trim(),
        itemName: document.getElementById('ipaItemName').value.trim(),
        usage:    document.getElementById('ipaItemUsage').value.trim(),
        purpose:  document.getElementById('ipaItemPurpose').value.trim(),
        qty:      document.getElementById('ipaItemQty').value.trim(),
        unit:     document.getElementById('ipaItemUnit').value.trim(),
        value:    document.getElementById('ipaItemValue').value.trim(),
    };
}

function validateForm(data) {
    const exporterChosen = document.getElementById('exporterId').value.trim() ||
                            document.getElementById('exporterSearch').value.trim();
    if (!exporterChosen) return false;
    return data.category && data.itemName && data.purpose && data.qty && data.unit && data.value;
}

function resetItemForm() {
    document.getElementById('ipaItemCategory').value = '';
    const nameSelect = document.getElementById('ipaItemName');
    nameSelect.innerHTML = '<option value="">-- Select category first --</option>';
    nameSelect.disabled = true;
    document.getElementById('ipaItemUsage').value   = '';
    document.getElementById('ipaItemPurpose').value = '';
    document.getElementById('ipaItemQty').value     = '';
    document.getElementById('ipaItemUnit').value    = '';
    document.getElementById('ipaItemValue').value   = '';

    const uploaderContainer = document.getElementById('ipaItemUploaderContainer');
    uploaderContainer.innerHTML = '';
    currentItemUploader = createAttachmentList(uploaderContainer);
}

// ---------------------------------------------------------------
// Condition modal
// ---------------------------------------------------------------

let pendingItemData     = null;
let currentItemUploader = null;
let conditionModal      = null;

function openConditionModal(data) {
    pendingItemData = data;

    document.getElementById('ipaModalItemSummary').innerHTML = `
        <div class="ipa-modal-summary-cell">
            <div class="ipa-modal-summary-label">Category</div>
            <div class="ipa-modal-summary-value">${escapeHtml(data.category)}</div>
        </div>
        <div class="ipa-modal-summary-cell">
            <div class="ipa-modal-summary-label">Item</div>
            <div class="ipa-modal-summary-value">${escapeHtml(data.itemName)}</div>
        </div>
        <div class="ipa-modal-summary-cell">
            <div class="ipa-modal-summary-label">Quantity</div>
            <div class="ipa-modal-summary-value">${escapeHtml(data.qty)} ${escapeHtml(data.unit)}</div>
        </div>
        <div class="ipa-modal-summary-cell">
            <div class="ipa-modal-summary-label">Declared Value</div>
            <div class="ipa-modal-summary-value">RM ${money(parseFloat(data.value))}</div>
        </div>
    `;

    const conditions = CATEGORY_CONDITIONS[data.category] || DEFAULT_CONDITIONS;
    document.getElementById('ipaModalConditions').innerHTML = conditions.map(c => `
        <div class="ipa-modal-condition-item">
            <i class="bi bi-check-circle-fill"></i>
            <span>${escapeHtml(c)}</span>
        </div>
    `).join('');

    const agreeCheck  = document.getElementById('ipaAgreeCheck');
    const confirmBtn  = document.getElementById('ipaConfirmAddBtn');
    agreeCheck.checked = false;
    confirmBtn.disabled = true;

    conditionModal.show();
}

function confirmAddItem() {
    if (!pendingItemData) return;

    const files = currentItemUploader ? currentItemUploader.getFiles() : [];
    const conditions = CATEGORY_CONDITIONS[pendingItemData.category] || DEFAULT_CONDITIONS;

    const item = {
        id: 'item-' + (itemSeq++),
        ...pendingItemData,
        conditions,
        files,
    };

    addedItems.push(item);
    renderAddedList();
    conditionModal.hide();
    resetItemForm();
    pendingItemData = null;
    document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
}

// ---------------------------------------------------------------
// Added items list rendering
// ---------------------------------------------------------------

function renderAddedList() {
    const listEl = document.getElementById('ipaAddedList');
    const card   = document.getElementById('ipaAddedItemsCard');
    const badge  = document.getElementById('ipaItemCountBadge');

    badge.textContent = addedItems.length;
    card.style.display = addedItems.length > 0 ? '' : 'none';

    listEl.innerHTML = addedItems.map((item, idx) => `
        <div class="ipa-added-row" data-item-id="${item.id}">
            <div class="ipa-added ips-item-num">
                ${idx + 1}
            </div>
            <div class="ipa-added-row-info">
                <div class="ipa-added-row-name">${escapeHtml(item.itemName)}</div>
                <div class="ipa-added-row-meta">
                    <span>${escapeHtml(item.category)}</span>
                    <span class="sep">·</span>
                    <span>${escapeHtml(item.qty)} ${escapeHtml(item.unit)}</span>
                    <span class="sep">·</span>
                    <span>RM ${money(parseFloat(item.value))}</span>
                    <span class="sep">·</span>
                    <span>${item.files.length} doc${item.files.length !== 1 ? 's' : ''}</span>
                </div>
            </div>
            <div class="ipa-added-row-actions">
                <button type="button" class="ipa-row-btn is-view" data-item-id="${item.id}" title="View details">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" class="ipa-row-btn is-delete" data-item-id="${item.id}" title="Remove item">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function deleteAddedItem(itemId) {
    const idx = addedItems.findIndex(i => i.id === itemId);
    if (idx > -1) addedItems.splice(idx, 1);
    renderAddedList();
    document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
}

// ---------------------------------------------------------------
// Item detail offcanvas
// ---------------------------------------------------------------

let itemDetailOffcanvas = null;

function openItemDetail(itemId) {
    const item = addedItems.find(i => i.id === itemId);
    if (!item) return;

    const detailsTab = document.getElementById('ipa-oc-details-tab');
    if (detailsTab) bootstrap.Tab.getOrCreateInstance(detailsTab).show();

    document.getElementById('ipaItemDetailOffcanvasLabel').textContent = item.itemName;

    const fields = [
        { label: 'Category',       value: item.category },
        { label: 'Item',           value: item.itemName },
        { label: 'Usage',          value: item.usage || '—' },
        { label: 'Purpose',        value: item.purpose },
        { label: 'Quantity',       value: `${item.qty} ${item.unit}` },
        { label: 'Declared Value', value: `RM ${money(parseFloat(item.value))}` },
    ];

    document.getElementById('ipaOcDetailsContent').innerHTML = `
        <div class="ipa-oc-section-label">Consignment Info</div>
        <div class="ipa-oc-info-grid">
            ${fields.map(f => `
                <div>
                    <div class="ipa-oc-cell-label">${escapeHtml(f.label)}</div>
                    <div class="ipa-oc-cell-value">${escapeHtml(f.value)}</div>
                </div>
            `).join('')}
        </div>

        <div class="ipa-oc-section-label">Conditions (${item.conditions.length})</div>
        ${item.conditions.map(c => `
            <div class="ipa-modal-condition-item">
                <i class="bi bi-check-circle-fill"></i>
                <span>${escapeHtml(c)}</span>
            </div>
        `).join('')}
    `;

    const docsEl = document.getElementById('ipaOcDocsContent');
    if (!item.files.length) {
        docsEl.innerHTML = `
            <div class="ipa-empty-state">
                <i class="bi bi-paperclip"></i>
                <p>No documents attached to this item.</p>
            </div>
        `;
    } else {
        docsEl.innerHTML = `
            <div class="ipa-oc-section-label">Attached Documents (${item.files.length})</div>
            ${item.files.map(f => {
                const meta = fileMeta(f.fileName);
                return `
                    <div class="ipa-oc-doc-item">
                        <div class="ipa-oc-doc-icon ${meta.cls}"><i class="bi ${meta.icon}"></i></div>
                        <div class="ipa-oc-doc-name" title="${escapeHtml(f.name)}">${escapeHtml(f.name)}</div>
                        <span class="ipa-oc-doc-type">${escapeHtml(f.docType)}</span>
                        <div class="ipa-oc-doc-meta">${escapeHtml(f.size)}</div>
                    </div>
                `;
            }).join('')}
        `;
    }

    if (!itemDetailOffcanvas) {
        itemDetailOffcanvas = new bootstrap.Offcanvas(
            document.getElementById('ipaItemDetailOffcanvas'),
            { backdrop: true, keyboard: true, scroll: false }
        );
    }
    itemDetailOffcanvas.show();
}

// ---------------------------------------------------------------
// Delegated click on added items list
// ---------------------------------------------------------------

function initAddedListDelegation() {
    document.getElementById('ipaAddedList').addEventListener('click', e => {
        const viewBtn = e.target.closest('.ipa-row-btn.is-view');
        if (viewBtn) { openItemDetail(viewBtn.dataset.itemId); return; }

        const delBtn = e.target.closest('.ipa-row-btn.is-delete');
        if (delBtn) { deleteAddedItem(delBtn.dataset.itemId); }
    });
}

// ---------------------------------------------------------------
// Draft / submit status + FULL FORM VALIDATION
// ---------------------------------------------------------------

function setStatus(state, text) {
    document.querySelectorAll('#ipaDraftStatus, #ipaFooterStatus').forEach(el => {
        const dot   = el.querySelector('.ipa-draft-dot');
        const label = el.querySelector('span:last-child');
        if (dot)   dot.className       = `ipa-draft-dot is-${state}`;
        if (label) label.textContent   = text;
    });
}

/**
 * isApplicationReadyToReview()
 * Gate for the "Review Application" button — every required field
 * across Transportation, Importer/Exporter, and at least one
 * confirmed Added Item must be present.
 */
function isApplicationReadyToReview() {
    const eta            = document.getElementById('ipaEta')?.value.trim();
    const transportType  = document.getElementById('ipaTransportType')?.value.trim();
    const entryPoint      = document.getElementById('ipaEntryPoint')?.value.trim();

    const importerName   = document.getElementById('importerName')?.value.trim();
    const importerPhone  = document.getElementById('importerPhone')?.value.trim();

    const exporterId      = document.getElementById('exporterId')?.value.trim();
    const exporterSearch  = document.getElementById('exporterSearch')?.value.trim();
    const exporterCountry = document.getElementById('exporterCountry')?.value.trim();
    const exporterAddress = document.getElementById('exporterAddress')?.value.trim();

    if (!eta || !transportType || !entryPoint) return false;
    if (!importerName || !importerPhone) return false;
    // An exporter must be either selected from search (has exporterId)
    // OR being added new (has a typed name + country + address filled in).
    if (!exporterSearch || !exporterCountry || !exporterAddress) return false;

    if (!addedItems.length) return false;

    // Application-level documents — require at least one completed upload.
    if (!appUploader || appUploader.getFiles().length === 0) return false;

    return true;
}

function updateReviewButtonState() {
    const reviewBtn = document.getElementById('ipaSubmitBtn');
    if (!reviewBtn) return;

    const ready = isApplicationReadyToReview();
    reviewBtn.disabled = !ready;
    reviewBtn.classList.toggle('is-disabled-look', !ready);
}

function wireFooterActions() {
    document.getElementById('ipaSaveDraftBtn')?.addEventListener('click', () => {
        const time = new Date().toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit' });
        setStatus('saved', `Saved as draft — ${time}`);
    });

    document.getElementById('ipaSubmitBtn')?.addEventListener('click', () => {
        if (!isApplicationReadyToReview()) {
            alert('Please complete all required fields, add at least one permit item, and upload at least one application document before reviewing.');
            return;
        }
        setStatus('submitted', 'Ready for review');
        // Navigate to summary/review page here, e.g.:
        // window.location.href = '/public/apply_import_permit/review';
    });

    document.addEventListener('ipa:form-dirty', () => {
        setStatus('unsaved', 'Unsaved changes');
        updateReviewButtonState();
    });
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

let _initialized = false;

function init() {
    if (_initialized) return;
    _initialized = true;

    const appUploaderContainer = document.getElementById('ipaAppUploader');
    if (!appUploaderContainer) return;

    appUploader = createAttachmentList(appUploaderContainer);

    const itemUploaderContainer = document.getElementById('ipaItemUploaderContainer');
    currentItemUploader = createAttachmentList(itemUploaderContainer);

    populateFormSelects();

    conditionModal = new bootstrap.Modal(document.getElementById('ipaConditionModal'), {
        backdrop: 'static',
        keyboard: false,
    });

    document.getElementById('ipaAgreeCheck').addEventListener('change', function () {
        document.getElementById('ipaConfirmAddBtn').disabled = !this.checked;
    });

    document.getElementById('ipaConfirmAddBtn').addEventListener('click', confirmAddItem);

    document.getElementById('ipaAddItemBtn').addEventListener('click', () => {
        const data = getFormData();
        if (!validateForm(data)) {
            const hasExporter = document.getElementById('exporterId').value.trim() ||
                                 document.getElementById('exporterSearch').value.trim();
            if (!hasExporter) {
                alert('Please select an exporter first — the item catalog depends on the exporter\'s country.');
            } else {
                alert('Please fill in all required fields (Category, Item, Purpose, Quantity, Unit, Declared Value).');
            }
            return;
        }
        openConditionModal(data);
    });

    document.getElementById('ipaResetItemBtn').addEventListener('click', resetItemForm);

    initAddedListDelegation();

    wireFooterActions();

    // Mark dirty on any field input, then re-check Review button gate
    document.querySelector('.ipa-wrapper')?.addEventListener('input', () => {
        document.dispatchEvent(new CustomEvent('ipa:form-dirty'));
    });

    // Initial state — Review button starts disabled
    updateReviewButtonState();
}

document.addEventListener('DOMContentLoaded', init);