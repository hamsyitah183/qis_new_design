/**
 * ============================================================
 * Control Panel — District Entry, Purpose, Unit, Condition
 * ============================================================
 * District Entry keeps its own nested editor (district + entry
 * points together) since it's a parent/child structure.
 *
 * Purpose, Unit Measurement, and Condition Category are all
 * flat code+name lists from public_code — they share one
 * generic "simple list" table renderer and one shared offcanvas,
 * driven by SIMPLE_LIST_CONFIG below. Adding a 5th simple list
 * later just means adding one config entry + one panel section
 * in the blade, no new JS logic.
 */

// ============================================================
// 0. PANEL SWITCHING
// ============================================================
function initPanelNav() {
    const navItems = document.querySelectorAll('.cp-nav-item[data-cp-panel]');
    const panels = document.querySelectorAll('.cp-panel[data-cp-panel-content]');

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const target = item.dataset.cpPanel;
            navItems.forEach(n => n.classList.toggle('is-active', n === item));
            panels.forEach(p => p.classList.toggle('is-active', p.dataset.cpPanelContent === target));
        });
    });
}

// ============================================================
// PART A — DISTRICT ENTRY (nested district + entry points)
// ============================================================

let districts = [];
let filteredDistricts = [];
let editingDistrictId = null;
let entryPointSeq = 0;

const tableBody = document.getElementById('districtTableBody');
const searchInput = document.getElementById('searchDistrict');
const transportFilter = document.getElementById('cpTransportFilter');

const summaryDistricts = document.getElementById('summaryDistricts');
const summaryEntryPoints = document.getElementById('summaryEntryPoints');
const summaryEmptyDistricts = document.getElementById('summaryEmptyDistricts');

const addDistrictBtn = document.getElementById('addDistrictBtn');
const districtOffcanvasEl = document.getElementById('districtOffcanvas');
const districtOffcanvasLabel = document.getElementById('districtOffcanvasLabel');
const districtOffcanvasSub = document.getElementById('districtOffcanvasSub');
const districtNameInput = document.getElementById('districtNameInput');
const entryPointList = document.getElementById('entryPointList');
const entryPointEmpty = document.getElementById('entryPointEmpty');
const addEntryPointBtn = document.getElementById('addEntryPointBtn');
const saveDistrictBtn = document.getElementById('saveDistrictBtn');

let districtOffcanvasInstance = null;
let districtSearchTimeout = null;

const TRANSPORT_OPTIONS = ['Air', 'Sea', 'Land'];

function escapeHtml(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',
    }[c]));
}

function transportBadgeClass(type) {
    const map = { Air: 'bg-info', Sea: 'bg-primary', Land: 'bg-warning' };
    return map[type] || 'bg-light';
}

function transportIcon(type) {
    const map = { Air: 'bi-airplane', Sea: 'bi-water', Land: 'bi-truck' };
    return map[type] || 'bi-geo-alt';
}

function loadDistrictData() {
    districts = [
        { id: 1, cate_code: '1', name: 'Kota Kinabalu', entryPoints: [
            { id: 'ep1', name: 'Kota Kinabalu International Airport (KKIA)', transport_type: 'Air' },
            { id: 'ep2', name: 'MASB Cargo Complex Kota Kinabalu', transport_type: 'Air' },
            { id: 'ep3', name: 'Sepanggar Container Port', transport_type: 'Sea' },
            { id: 'ep4', name: 'Bulk Cargo Port Kota Kinabalu', transport_type: 'Sea' },
            { id: 'ep5', name: 'Jesselton Point Ferry Terminal, Kota Kinabalu', transport_type: 'Sea' },
            { id: 'ep6', name: 'Kota Kinabalu General Post Office', transport_type: 'Air' },
            { id: 'ep7', name: 'Post Office DC (Distribution Centre) Kolombong', transport_type: 'Air' },
        ]},
        { id: 2, cate_code: '2', name: 'Kudat', entryPoints: [
            { id: 'ep8', name: 'Ferry Terminal', transport_type: 'Sea' },
            { id: 'ep9', name: 'Kudat Barter Trade Centre', transport_type: 'Sea' },
        ]},
        { id: 3, cate_code: '3', name: 'Sandakan', entryPoints: [
            { id: 'ep10', name: 'Sandakan Airport', transport_type: 'Air' },
            { id: 'ep11', name: 'Sandakan Port', transport_type: 'Sea' },
            { id: 'ep12', name: 'Ferry Terminal', transport_type: 'Sea' },
            { id: 'ep13', name: 'Sandakan Barter Trade Centre', transport_type: 'Sea' },
        ]},
        { id: 4, cate_code: '4', name: 'Lahad Datu', entryPoints: [
            { id: 'ep14', name: 'Lahad Datu Port', transport_type: 'Sea' },
            { id: 'ep15', name: 'POIC Port, Lahad Datu', transport_type: 'Sea' },
        ]},
        { id: 5, cate_code: '5', name: 'Tawau', entryPoints: [
            { id: 'ep16', name: 'Tawau Airport', transport_type: 'Air' },
            { id: 'ep17', name: 'Tawau Port', transport_type: 'Sea' },
            { id: 'ep18', name: 'Ferry Terminal', transport_type: 'Sea' },
            { id: 'ep19', name: 'Tawau Barter Trade Centre', transport_type: 'Sea' },
        ]},
        { id: 6, cate_code: '6', name: 'Kunak', entryPoints: [
            { id: 'ep20', name: 'Kunak Port', transport_type: 'Sea' },
        ]},
        { id: 7, cate_code: '7', name: 'Semporna', entryPoints: [
            { id: 'ep21', name: 'Ferry Terminal, Semporna', transport_type: 'Sea' },
        ]},
        { id: 8, cate_code: '8', name: 'Kuala Penyu', entryPoints: [
            { id: 'ep22', name: 'Ferry Terminal, Menumbok', transport_type: 'Sea' },
        ]},
        { id: 9, cate_code: '9', name: 'Sipitang', entryPoints: [
            { id: 'ep23', name: 'ICQS Sindumin/Merapok', transport_type: 'Land' },
        ]},
    ];
    filteredDistricts = [...districts];
}

function renderDistrictSummary() {
    const totalEntryPoints = districts.reduce((sum, d) => sum + d.entryPoints.length, 0);
    const emptyDistricts = districts.filter(d => d.entryPoints.length === 0).length;

    if (summaryDistricts) summaryDistricts.innerText = districts.length;
    if (summaryEntryPoints) summaryEntryPoints.innerText = totalEntryPoints;
    if (summaryEmptyDistricts) summaryEmptyDistricts.innerText = emptyDistricts;
}

function renderDistrictTable(data) {
    if (!tableBody) return;
    tableBody.innerHTML = '';

    if (!data.length) {
        tableBody.innerHTML = `
            <tr><td colspan="3" class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-4 d-block mb-2"></i>No districts found
            </td></tr>`;
        return;
    }

    data.forEach(district => {
        const row = document.createElement('tr');
        row.className = 'ipv-row';
        row.setAttribute('data-id', district.id);

        const entryChips = district.entryPoints.length
            ? district.entryPoints.map(ep => `
                <span class="cp-entry-chip" title="${escapeHtml(ep.name)}">
                    <i class="bi ${transportIcon(ep.transport_type)}"></i>
                    ${escapeHtml(ep.name)}
                    <span class="badge ${transportBadgeClass(ep.transport_type)} cp-entry-chip-badge">${ep.transport_type}</span>
                </span>
            `).join('')
            : `<span class="cp-no-entry">No entry points yet</span>`;

        row.innerHTML = `
            <td>
                <div class="fw-semibold text-nowrap">${escapeHtml(district.name)}</div>
                <div class="cp-district-code">Code: ${escapeHtml(district.cate_code)}</div>
            </td>
            <td><div class="cp-entry-chip-list">${entryChips}</div></td>
            <td>
                <div class="ipv-action-group">
                    <button class="ipv-action-btn text-primary edit-btn" data-id="${district.id}" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="ipv-action-btn text-danger delete-btn" data-id="${district.id}" title="Delete">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </td>
        `;

        row.querySelector('.edit-btn').addEventListener('click', () => openEditDistrict(district.id));
        row.querySelector('.delete-btn').addEventListener('click', () => deleteDistrict(district.id));

        tableBody.appendChild(row);
    });
}

function deleteDistrict(id) {
    const district = districts.find(d => d.id === id);
    if (!district) return;

    const msg = district.entryPoints.length
        ? `Delete "${district.name}"? This will also remove its ${district.entryPoints.length} entry point(s). This action cannot be undone.`
        : `Delete "${district.name}"? This action cannot be undone.`;

    if (!confirm(msg)) return;

    districts = districts.filter(d => d.id !== id);
    applyDistrictFilters();
    renderDistrictSummary();
}

function applyDistrictFilters() {
    const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const transportType = transportFilter ? transportFilter.value : '';

    filteredDistricts = districts.filter(d => {
        if (keyword) {
            const matchDistrict = d.name.toLowerCase().includes(keyword);
            const matchEntry = d.entryPoints.some(ep => ep.name.toLowerCase().includes(keyword));
            if (!matchDistrict && !matchEntry) return false;
        }
        if (transportType) {
            const hasType = d.entryPoints.some(ep => ep.transport_type === transportType);
            if (!hasType) return false;
        }
        return true;
    });

    renderDistrictTable(filteredDistricts);
}

function bindDistrictSearchAndFilter() {
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(districtSearchTimeout);
            districtSearchTimeout = setTimeout(applyDistrictFilters, 250);
        });
    }
    if (transportFilter) {
        transportFilter.addEventListener('change', applyDistrictFilters);
    }
}

function initDistrictOffcanvas() {
    if (!districtOffcanvasInstance && districtOffcanvasEl) {
        districtOffcanvasInstance = new bootstrap.Offcanvas(districtOffcanvasEl);
    }
}

function resetDistrictForm() {
    editingDistrictId = null;
    if (districtNameInput) districtNameInput.value = '';
    entryPointList.innerHTML = '';
    entryPointSeq = 0;
    toggleEntryEmptyState();
}

function openAddDistrict() {
    resetDistrictForm();
    districtOffcanvasLabel.textContent = 'Add District';
    districtOffcanvasSub.textContent = 'Define a new district and its entry points';
    initDistrictOffcanvas();
    districtOffcanvasInstance.show();
}

function openEditDistrict(id) {
    const district = districts.find(d => d.id === id);
    if (!district) return;

    resetDistrictForm();
    editingDistrictId = id;

    districtOffcanvasLabel.textContent = 'Edit District';
    districtOffcanvasSub.textContent = `${district.name} · Code ${district.cate_code}`;
    districtNameInput.value = district.name;

    district.entryPoints.forEach(ep => addEntryPointRow(ep.name, ep.transport_type, ep.id));
    toggleEntryEmptyState();

    initDistrictOffcanvas();
    districtOffcanvasInstance.show();
}

function toggleEntryEmptyState() {
    const hasRows = entryPointList.children.length > 0;
    entryPointEmpty.classList.toggle('d-none', hasRows);
}

function addEntryPointRow(name = '', transportType = 'Sea', existingId = null) {
    const tempId = existingId || ('new-' + (entryPointSeq++));

    const row = document.createElement('div');
    row.className = 'cp-entry-row';
    row.dataset.tempId = tempId;

    row.innerHTML = `
        <div class="cp-entry-row-fields">
            <input type="text" class="form-control cp-entry-name-input"
                placeholder="e.g. Sepanggar Container Port" value="${escapeHtml(name)}">
            <select class="form-select cp-entry-type-select">
                ${TRANSPORT_OPTIONS.map(t => `
                    <option value="${t}" ${t === transportType ? 'selected' : ''}>${t}</option>
                `).join('')}
            </select>
        </div>
        <button type="button" class="cp-entry-remove-btn" title="Remove entry point">
            <i class="bi bi-trash3"></i>
        </button>
    `;

    row.querySelector('.cp-entry-remove-btn').addEventListener('click', () => {
        row.remove();
        toggleEntryEmptyState();
    });

    entryPointList.appendChild(row);
    toggleEntryEmptyState();
    return row;
}

function collectEntryPointsFromForm() {
    const rows = Array.from(entryPointList.querySelectorAll('.cp-entry-row'));
    return rows.map(row => ({
        id: row.dataset.tempId,
        name: row.querySelector('.cp-entry-name-input').value.trim(),
        transport_type: row.querySelector('.cp-entry-type-select').value,
    })).filter(ep => ep.name.length > 0);
}

function saveDistrict() {
    const name = districtNameInput.value.trim();

    if (!name) {
        alert('Please enter a district name.');
        districtNameInput.focus();
        return;
    }

    const entryPoints = collectEntryPointsFromForm();

    if (editingDistrictId !== null) {
        const district = districts.find(d => d.id === editingDistrictId);
        if (district) {
            district.name = name;
            district.entryPoints = entryPoints;
        }
    } else {
        const nextCode = String(
            districts.reduce((max, d) => Math.max(max, parseInt(d.cate_code, 10) || 0), 0) + 1
        );
        districts.push({ id: Date.now(), cate_code: nextCode, name, entryPoints });
    }

    applyDistrictFilters();
    renderDistrictSummary();
    districtOffcanvasInstance.hide();
}

function initDistrictPanel() {
    loadDistrictData();
    renderDistrictSummary();
    renderDistrictTable(filteredDistricts);
    initDistrictOffcanvas();
    bindDistrictSearchAndFilter();

    if (addDistrictBtn) addDistrictBtn.addEventListener('click', openAddDistrict);
    if (addEntryPointBtn) addEntryPointBtn.addEventListener('click', () => addEntryPointRow());
    if (saveDistrictBtn) saveDistrictBtn.addEventListener('click', saveDistrict);
}

// ============================================================
// PART B — SIMPLE LISTS (Purpose / Unit / Condition)
// ============================================================
// One config-driven engine for any flat code+name list backed by
// public_code. Adding a new simple list later = one config entry
// + matching table/summary IDs in the blade. No new JS functions.

const SIMPLE_LIST_CONFIG = {
    purpose: {
        label: 'Purpose',
        tableBodyId: 'purposeTableBody',
        summaryId: 'summaryPurposeTotal',
        codeIsAuto: true,           // purpose has no meaningful short code — auto-generate one
        codePrefix: 'PUR',
        codeLabel: 'Code',
        nameLabel: 'Purpose Name',
        namePlaceholder: 'e.g. Commercial (Animal Feed)',
        seed: [
            'Commercial (Animal Feed)',
            'Commercial (Decoration)',
            'Commercial (Human consumption)',
            'Commercial (Landscaping)',
            'Commercial (Planting material)',
            'Individual (Animal Feed)',
            'Individual (Personal consumption)',
            'Individual (Landscaping)',
            'Individual (Planting material)',
            'Individual (Decoration)',
            'Material for product manufacturing',
            'Research (Downstream product)',
            'Research (Lab analysis)',
        ],
    },
    unit: {
        label: 'Unit',
        tableBodyId: 'unitTableBody',
        summaryId: 'summaryUnitTotal',
        codeIsAuto: false,          // units DO have meaningful codes (KG, LTR, etc.)
        codeLabel: 'Code',
        nameLabel: 'Unit Name',
        namePlaceholder: 'e.g. Kilogram',
        seedWithCode: [
            { code: 'KG',  name: 'Kilogram' },
            { code: 'G',   name: 'Gram' },
            { code: 'TON', name: 'Metric Ton' },
            { code: 'PCS', name: 'Pieces' },
            { code: 'BGS', name: 'Bags' },
            { code: 'LTR', name: 'Litre' },
            { code: 'ML',  name: 'Millilitre' },
        ],
    },
    condition: {
        label: 'Condition',
        tableBodyId: 'conditionTableBody',
        summaryId: 'summaryConditionTotal',
        codeIsAuto: true,
        codePrefix: 'COND',
        codeLabel: 'Code',
        nameLabel: 'Condition Name',
        namePlaceholder: 'e.g. Fresh fruit (Whole fruit)',
        seed: [
            'Cuttings',
            'Dried bean',
            'Dried fruit',
            'Fresh fruit (Seed removed)',
            'Fresh fruit (Whole fruit)',
            'Fresh vegetable',
            'Frozen fruit (Seed removed)',
            'Frozen fruit (Whole fruit)',
            'Frozen vegetable',
            'Ramet',
            'Sapling',
            'Seedlings',
            'Seeds',
            'Tissue culture',
        ],
    },
};

// In-memory store per list type: { purpose: [...], unit: [...], condition: [...] }
const simpleLists = {};
const simpleSeqCounters = {};

let editingSimple = null; // { type, id } | null = add mode

const simpleOffcanvasEl = document.getElementById('simpleListOffcanvas');
const simpleOffcanvasLabel = document.getElementById('simpleListOffcanvasLabel');
const simpleOffcanvasSub = document.getElementById('simpleListOffcanvasSub');
const simpleCodeGroup = document.getElementById('simpleCodeGroup');
const simpleCodeLabel = document.getElementById('simpleCodeLabel');
const simpleCodeInput = document.getElementById('simpleCodeInput');
const simpleCodeHint = document.getElementById('simpleCodeHint');
const simpleNameLabel = document.getElementById('simpleNameLabel');
const simpleNameInput = document.getElementById('simpleNameInput');
const saveSimpleBtn = document.getElementById('saveSimpleBtn');
const saveSimpleBtnLabel = document.getElementById('saveSimpleBtnLabel');

let simpleOffcanvasInstance = null;

function loadSimpleListData() {
    Object.entries(SIMPLE_LIST_CONFIG).forEach(([type, cfg]) => {
        simpleSeqCounters[type] = 1;

        if (cfg.codeIsAuto) {
            simpleLists[type] = cfg.seed.map((name) => ({
                id: type + '-' + (simpleSeqCounters[type]),
                code: `${cfg.codePrefix}-${String(simpleSeqCounters[type]++).padStart(2, '0')}`,
                name,
            }));
        } else {
            simpleLists[type] = cfg.seedWithCode.map((item) => ({
                id: type + '-' + (simpleSeqCounters[type]++),
                code: item.code,
                name: item.name,
            }));
        }
    });
}

function renderSimpleSummary(type) {
    const cfg = SIMPLE_LIST_CONFIG[type];
    const el = document.getElementById(cfg.summaryId);
    if (el) el.innerText = simpleLists[type].length;
}

function renderSimpleTable(type, data) {
    const cfg = SIMPLE_LIST_CONFIG[type];
    const body = document.getElementById(cfg.tableBodyId);
    if (!body) return;

    body.innerHTML = '';

    if (!data.length) {
        body.innerHTML = `
            <tr><td colspan="3" class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-4 d-block mb-2"></i>No ${cfg.label.toLowerCase()} found
            </td></tr>`;
        return;
    }

    data.forEach(item => {
        const row = document.createElement('tr');
        row.className = 'ipv-row';
        row.innerHTML = `
            <td><span class="cp-simple-code">${escapeHtml(item.code)}</span></td>
            <td class="fw-semibold">${escapeHtml(item.name)}</td>
            <td>
                <div class="ipv-action-group">
                    <button class="ipv-action-btn text-primary edit-btn" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="ipv-action-btn text-danger delete-btn" title="Delete">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </td>
        `;

        row.querySelector('.edit-btn').addEventListener('click', () => openEditSimple(type, item.id));
        row.querySelector('.delete-btn').addEventListener('click', () => deleteSimple(type, item.id));

        body.appendChild(row);
    });
}

function applySimpleFilter(type) {
    const cfg = SIMPLE_LIST_CONFIG[type];
    const searchEl = document.querySelector(`.cp-simple-search[data-cp-type="${type}"]`);
    const keyword = searchEl ? searchEl.value.toLowerCase().trim() : '';

    const data = keyword
        ? simpleLists[type].filter(item =>
            item.name.toLowerCase().includes(keyword) || item.code.toLowerCase().includes(keyword))
        : simpleLists[type];

    renderSimpleTable(type, data);
}

function deleteSimple(type, id) {
    const cfg = SIMPLE_LIST_CONFIG[type];
    const item = simpleLists[type].find(i => i.id === id);
    if (!item) return;

    if (!confirm(`Delete "${item.name}"? This action cannot be undone.`)) return;

    simpleLists[type] = simpleLists[type].filter(i => i.id !== id);
    applySimpleFilter(type);
    renderSimpleSummary(type);
}

function initSimpleOffcanvas() {
    if (!simpleOffcanvasInstance && simpleOffcanvasEl) {
        simpleOffcanvasInstance = new bootstrap.Offcanvas(simpleOffcanvasEl);
    }
}

function configureSimpleOffcanvas(type) {
    const cfg = SIMPLE_LIST_CONFIG[type];

    simpleCodeLabel.textContent = cfg.codeLabel;
    simpleNameLabel.textContent = cfg.nameLabel;
    simpleNameInput.placeholder = cfg.namePlaceholder;

    if (cfg.codeIsAuto) {
        simpleCodeGroup.classList.add('d-none'); // auto-generated, no input needed
    } else {
        simpleCodeGroup.classList.remove('d-none');
        simpleCodeInput.placeholder = `e.g. ${cfg.seedWithCode[0]?.code || 'CODE'}`;
        simpleCodeHint.textContent = 'Short unique code stored in public_code.cate_code.';
    }
}

function openAddSimple(type) {
    const cfg = SIMPLE_LIST_CONFIG[type];
    editingSimple = null;

    configureSimpleOffcanvas(type);
    simpleOffcanvasLabel.textContent = `Add ${cfg.label}`;
    simpleOffcanvasSub.textContent = `Add a new ${cfg.label.toLowerCase()} option`;
    simpleCodeInput.value = '';
    simpleNameInput.value = '';
    saveSimpleBtnLabel.textContent = `Save ${cfg.label}`;

    simpleOffcanvasEl.dataset.cpType = type;

    initSimpleOffcanvas();
    simpleOffcanvasInstance.show();
}

function openEditSimple(type, id) {
    const cfg = SIMPLE_LIST_CONFIG[type];
    const item = simpleLists[type].find(i => i.id === id);
    if (!item) return;

    editingSimple = { type, id };

    configureSimpleOffcanvas(type);
    simpleOffcanvasLabel.textContent = `Edit ${cfg.label}`;
    simpleOffcanvasSub.textContent = `${item.name} · Code ${item.code}`;
    simpleCodeInput.value = item.code;
    simpleNameInput.value = item.name;
    saveSimpleBtnLabel.textContent = 'Save Changes';

    simpleOffcanvasEl.dataset.cpType = type;

    initSimpleOffcanvas();
    simpleOffcanvasInstance.show();
}

function saveSimple() {
    const type = simpleOffcanvasEl.dataset.cpType;
    const cfg = SIMPLE_LIST_CONFIG[type];
    const name = simpleNameInput.value.trim();

    if (!name) {
        alert(`Please enter a ${cfg.nameLabel.toLowerCase()}.`);
        simpleNameInput.focus();
        return;
    }

    let code = simpleCodeInput.value.trim();

    if (cfg.codeIsAuto && !editingSimple) {
        code = `${cfg.codePrefix}-${String(simpleSeqCounters[type]++).padStart(2, '0')}`;
    } else if (!cfg.codeIsAuto && !code) {
        alert(`Please enter a ${cfg.codeLabel.toLowerCase()}.`);
        simpleCodeInput.focus();
        return;
    }

    if (editingSimple && editingSimple.type === type) {
        const item = simpleLists[type].find(i => i.id === editingSimple.id);
        if (item) {
            item.name = name;
            if (!cfg.codeIsAuto) item.code = code;
        }
    } else {
        simpleLists[type].push({
            id: type + '-' + (simpleSeqCounters[type]++),
            code,
            name,
        });
    }

    applySimpleFilter(type);
    renderSimpleSummary(type);
    simpleOffcanvasInstance.hide();
}

function initSimpleListPanels() {
    loadSimpleListData();

    Object.keys(SIMPLE_LIST_CONFIG).forEach(type => {
        renderSimpleSummary(type);
        renderSimpleTable(type, simpleLists[type]);
    });

    initSimpleOffcanvas();

    // Add buttons (one per panel, identified by data-cp-type)
    document.querySelectorAll('.cp-simple-add-btn').forEach(btn => {
        btn.addEventListener('click', () => openAddSimple(btn.dataset.cpType));
    });

    // Search inputs (one per panel)
    document.querySelectorAll('.cp-simple-search').forEach(input => {
        let t = null;
        input.addEventListener('input', () => {
            clearTimeout(t);
            t = setTimeout(() => applySimpleFilter(input.dataset.cpType), 250);
        });
    });

    // Shared save button
    if (saveSimpleBtn) saveSimpleBtn.addEventListener('click', saveSimple);
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    initPanelNav();
    initDistrictPanel();
    initSimpleListPanels();

    console.log('✅ Control Panel ready (District / Purpose / Unit / Condition).');
});