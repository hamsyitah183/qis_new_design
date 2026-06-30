/**
 * ============================================================
 * Import Permit Application Page – Full Functionality
 * ============================================================
 */
// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

// ---- DATA ----
let applications = [];
let filteredApps = [];

// ---- DOM refs (safe) ----
const tableBody = document.getElementById('applicationTableBody');
const searchInput = document.getElementById('searchApplication');
const sortSelect = document.getElementById('sortSelect');

const summaryTotal = document.getElementById('summaryTotal');
const summarySubmitted = document.getElementById('summarySubmitted');
const summaryDocVerify = document.getElementById('summaryDocVerify');
const summaryTechnical = document.getElementById('summaryTechnical');
const summaryPayment = document.getElementById('summaryPayment');
const summaryPayProc = document.getElementById('summaryPayProc');
const summaryCompleted = document.getElementById('summaryCompleted');
const summaryReturned = document.getElementById('summaryReturned');

const filterPanel = document.getElementById('filterPanel');
const filterOverlay = document.getElementById('filterOverlay');
const openFilterBtn = document.getElementById('openFilter');
const closeFilterBtn = document.getElementById('closeFilter');

// These must have IDs in the Blade – add them if missing
const transportSelect = document.getElementById('filterTransport');
const entryPointSelect = document.getElementById('filterEntryPoint');
const dateFromInput = document.getElementById('filterDateFrom');
const dateToInput = document.getElementById('filterDateTo');
const applyFilterBtn = document.getElementById('applyFilterBtn');
const clearFilterBtn = document.getElementById('clearFilterBtn');

// ---- Offcanvas ----
const previewOffcanvas = document.getElementById('applicationPreview');
let offcanvasInstance = null;

// ---- FILTER STATE ----
// transport / entryPoint are now arrays — empty array means "no filter applied"
let filterState = {
    status: [],
    transport: [],
    entryPoint: [],
    dateFrom: '',
    dateTo: ''
};

// ---- SORT STATE ----
let sortState = 'created_desc';

let searchTimeout = null;

// ============================================================
// 1. DUMMY DATA (minimal for demo)
// ============================================================
function loadDummyData() {
    applications = [
        {
            id: 'IPV-2026-0001',
            applicant: 'Ahmad Rahman',
            importer: 'Borneo Trade Sdn Bhd',
            eta: '2026-07-02',
            createdAt: '2026-06-20',
            transport: 'Sea',
            entryPoint: 'Kota Kinabalu',
            permits: 3,
            value: 120000,
            status: 'Submitted'
        },
        {
            id: 'IPV-2026-0002',
            applicant: 'Siti Nurhaliza',
            importer: 'Sabah Agro Importers',
            eta: '2026-07-10',
            createdAt: '2026-06-22',
            transport: 'Air',
            entryPoint: 'Tawau',
            permits: 1,
            value: 45000,
            status: 'Document Verification'
        },
        {
            id: 'IPV-2026-0003',
            applicant: 'John Tan',
            importer: 'East Malaysia Logistics',
            eta: '2026-06-30',
            createdAt: '2026-06-18',
            transport: 'Land',
            entryPoint: 'Sandakan',
            permits: 5,
            value: 300000,
            status: 'Technical Review'
        },
        {
            id: 'IPV-2026-0004',
            applicant: 'Nur Aisyah',
            importer: 'Kota Import Export',
            eta: '2026-07-15',
            createdAt: '2026-06-25',
            transport: 'Sea',
            entryPoint: 'Kota Kinabalu',
            permits: 2,
            value: 78000,
            status: 'Awaiting Payment'
        },
        {
            id: 'IPV-2026-0005',
            applicant: 'Michael Lee',
            importer: 'Sabah Marine Supply',
            eta: '2026-07-05',
            createdAt: '2026-06-21',
            transport: 'Air',
            entryPoint: 'Tawau',
            permits: 4,
            value: 190000,
            status: 'Payment Processing'
        },
        {
            id: 'IPV-2026-0006',
            applicant: 'Roslina Hassan',
            importer: 'Borneo Agri Sdn Bhd',
            eta: '2026-07-18',
            createdAt: '2026-06-15',
            transport: 'Sea',
            entryPoint: 'Sandakan',
            permits: 6,
            value: 450000,
            status: 'Completed'
        },
        {
            id: 'IPV-2026-0007',
            applicant: 'Kevin Ng',
            importer: 'Sarawak Trading Co',
            eta: '2026-06-28',
            createdAt: '2026-06-27',
            transport: 'Land',
            entryPoint: 'Kota Kinabalu',
            permits: 2,
            value: 56000,
            status: 'Returned'
        }
    ];
    filteredApps = [...applications];
}

// ============================================================
// 2. UTILITIES
// ============================================================
function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB');
}

function getStatusClass(status) {
    const map = {
        'Submitted': 'bg-secondary',
        'Document Verification': 'bg-primary',
        'Technical Review': 'bg-warning',
        'Awaiting Payment': 'bg-info',
        'Payment Processing': 'bg-info',
        'Completed': 'bg-success',
        'Returned': 'bg-danger'
    };
    return map[status] || 'bg-light';
}

function getStatusBadgeHTML(status) {
    return `<span class="badge ${getStatusClass(status)}">${status}</span>`;
}

function countStatus(status) {
    return applications.filter(app => app.status === status).length;
}

// ============================================================
// 3. SUMMARY CARDS (with null checks)
// ============================================================
function renderSummary() {
    if (summaryTotal) summaryTotal.innerText = applications.length;
    if (summarySubmitted) summarySubmitted.innerText = countStatus('Submitted');
    if (summaryDocVerify) summaryDocVerify.innerText = countStatus('Document Verification');
    if (summaryTechnical) summaryTechnical.innerText = countStatus('Technical Review');
    if (summaryPayment) summaryPayment.innerText = countStatus('Awaiting Payment');
    if (summaryPayProc) summaryPayProc.innerText = countStatus('Payment Processing');
    if (summaryCompleted) summaryCompleted.innerText = countStatus('Completed');
    if (summaryReturned) summaryReturned.innerText = countStatus('Returned');
}

// ============================================================
// 4. SORTING
// ============================================================
function sortApplications(data, sortKey) {
    const sorted = [...data];

    switch (sortKey) {
        case 'created_desc':
            sorted.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
            break;
        case 'created_asc':
            sorted.sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
            break;
        case 'eta_asc':
            sorted.sort((a, b) => new Date(a.eta) - new Date(b.eta));
            break;
        case 'eta_desc':
            sorted.sort((a, b) => new Date(b.eta) - new Date(a.eta));
            break;
        case 'value_desc':
            sorted.sort((a, b) => b.value - a.value);
            break;
        case 'value_asc':
            sorted.sort((a, b) => a.value - b.value);
            break;
        case 'permits_desc':
            sorted.sort((a, b) => b.permits - a.permits);
            break;
        case 'permits_asc':
            sorted.sort((a, b) => a.permits - b.permits);
            break;
        default:
            break;
    }

    return sorted;
}

function bindSort() {
    if (!sortSelect) return;
    sortSelect.addEventListener('change', function(e) {
        sortState = e.target.value;
        applyFiltersSilent();
    });
}

// ============================================================
// 5. SELECT2 INIT
// ============================================================
function initSelect2() {
    if (typeof $ === 'undefined' || !$.fn.select2) {
        console.warn('Select2 / jQuery not loaded — falling back to native multi-select.');
        return;
    }

    $('#filterTransport').select2({
        placeholder: 'All transport types',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#filterPanel'),
    });

    $('#filterEntryPoint').select2({
        placeholder: 'All entry points',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#filterPanel'),
    });
}

function getSelect2Values(selectEl) {
    if (typeof $ !== 'undefined' && $.fn.select2 && selectEl) {
        return $(selectEl).val() || [];
    }
    // Fallback: native multi-select
    return selectEl
        ? Array.from(selectEl.selectedOptions).map(opt => opt.value)
        : [];
}

function resetSelect2(selectEl) {
    if (typeof $ !== 'undefined' && $.fn.select2 && selectEl) {
        $(selectEl).val(null).trigger('change');
    } else if (selectEl) {
        Array.from(selectEl.options).forEach(opt => opt.selected = false);
    }
}

// ============================================================
// 6. TABLE RENDER
// ============================================================
function renderTable(data) {
    if (!tableBody) return;
    tableBody.innerHTML = '';

    if (!data.length) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                    No applications found
                </td>
            </tr>
        `;
        return;
    }

    data.forEach(app => {
        const row = document.createElement('tr');
        row.className = 'ipv-row';
        row.setAttribute('data-id', app.id);

        row.innerHTML = `
            <td><div class="fw-semibold">${app.id}</div></td>
            <td>${app.applicant}</td>
            <td>${app.importer}</td>
            <td>${formatDate(app.eta)}</td>
            <td><span class="badge bg-light text-dark">${app.transport}</span></td>
            <td>${app.permits}</td>
            <td>RM ${formatNumber(app.value)}</td>
            <td>${getStatusBadgeHTML(app.status)}</td>
            <td>
                <div class="ipv-action-group">
                    <button class="ipv-action-btn text-primary view-btn" data-id="${app.id}" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <a href="/view_import/test" class="ipv-action-btn text-primary nav-btn" title="Open full page">
                        <i class="bi bi-arrow-up-right-square"></i>
                    </a>
                    <button class="ipv-action-btn text-danger delete-btn" data-id="${app.id}" title="Delete">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </td>
        `;

        // Row click → open preview
        row.addEventListener('click', function(e) {
            if (e.target.closest('button') || e.target.closest('a')) return;
            const id = this.getAttribute('data-id');
            if (id) openPreview(id);
        });

        // View button
        row.querySelector('.view-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.getAttribute('data-id');
            if (id) openPreview(id);
        });

        // Delete button
        row.querySelector('.delete-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.getAttribute('data-id');
            if (id) deleteApplication(id);
        });

        tableBody.appendChild(row);
    });
}

// ============================================================
// 7. DELETE
// ============================================================
function deleteApplication(id) {
    if (!confirm(`Delete application ${id}? This action cannot be undone.`)) return;
    applications = applications.filter(app => app.id !== id);
    filteredApps = filteredApps.filter(app => app.id !== id);
    renderSummary();
    renderTable(filteredApps);
    if (offcanvasInstance) offcanvasInstance.hide();
}

// ============================================================
// 8. SEARCH (debounced)
// ============================================================
function bindSearch() {
    if (!searchInput) return;
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFiltersSilent();
        }, 250);
    });
}

// ============================================================
// 9. FILTERS
// ============================================================
function getSelectedStatuses() {
    return Array.from(document.querySelectorAll('.status-filter:checked'))
                .map(cb => cb.value);
}

function applyFiltersSilent() {
    const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';

    let result = applications.filter(app => {
        if (keyword) {
            const match = app.id.toLowerCase().includes(keyword) ||
                          app.applicant.toLowerCase().includes(keyword) ||
                          app.importer.toLowerCase().includes(keyword);
            if (!match) return false;
        }
        if (filterState.status.length > 0 && !filterState.status.includes(app.status)) return false;

        // Multi-select transport — empty array means no filter
        if (filterState.transport.length > 0 && !filterState.transport.includes(app.transport)) return false;

        // Multi-select entry point — empty array means no filter
        if (filterState.entryPoint.length > 0 && !filterState.entryPoint.includes(app.entryPoint)) return false;

        // Submission date range filter
        if (filterState.dateFrom) {
            const created = new Date(app.createdAt);
            const from = new Date(filterState.dateFrom);
            if (created < from) return false;
        }
        if (filterState.dateTo) {
            const created = new Date(app.createdAt);
            const to = new Date(filterState.dateTo);
            to.setHours(23, 59, 59, 999);
            if (created > to) return false;
        }

        return true;
    });

    result = sortApplications(result, sortState);

    filteredApps = result;
    renderTable(filteredApps);
}

// ============================================================
// 9b. ACTIVE-FILTER BADGE ON THE "Filter" TOOLBAR BUTTON
// ============================================================
// Counts how many distinct filter *criteria* are currently applied
// (a multi-select with 3 values still only counts as "1 active
// filter group", which matches how the panel is organized — status /
// transport / entry point / date range). Recomputed every time
// applyFilters() or resetFilters() runs, so it can never drift out of
// sync with filterState.
function countActiveFilterGroups() {
    let count = 0;
    if (filterState.status.length > 0) count++;
    if (filterState.transport.length > 0) count++;
    if (filterState.entryPoint.length > 0) count++;
    if (filterState.dateFrom || filterState.dateTo) count++;
    return count;
}

function renderFilterBadge() {
    if (!openFilterBtn) return;

    const count = countActiveFilterGroups();
    let badge = openFilterBtn.querySelector('.ipv-filter-badge');

    if (count === 0) {
        badge?.remove();
        openFilterBtn.classList.remove('has-active-filters');
        return;
    }

    openFilterBtn.classList.add('has-active-filters');

    if (!badge) {
        badge = document.createElement('span');
        badge.className = 'ipv-filter-badge';
        openFilterBtn.appendChild(badge);
    }
    badge.textContent = count;
}

function applyFilters() {
    filterState.status = getSelectedStatuses();
    filterState.transport = getSelect2Values(transportSelect);
    filterState.entryPoint = getSelect2Values(entryPointSelect);
    if (dateFromInput) filterState.dateFrom = dateFromInput.value;
    if (dateToInput) filterState.dateTo = dateToInput.value;

    if (filterState.dateFrom && filterState.dateTo && filterState.dateFrom > filterState.dateTo) {
        alert('"From" date cannot be later than "To" date.');
        return;
    }

    applyFiltersSilent();
    renderFilterBadge();
    if (window.innerWidth <= 992) closeFilterPanel();
}

function resetFilters() {
    document.querySelectorAll('.status-filter').forEach(cb => cb.checked = false);
    resetSelect2(transportSelect);
    resetSelect2(entryPointSelect);
    if (dateFromInput) dateFromInput.value = '';
    if (dateToInput) dateToInput.value = '';
    filterState = { status: [], transport: [], entryPoint: [], dateFrom: '', dateTo: '' };
    filteredApps = [...applications];
    filteredApps = sortApplications(filteredApps, sortState);
    renderTable(filteredApps);
    renderFilterBadge();
    if (window.innerWidth <= 992) closeFilterPanel();
}

// ============================================================
// 10. FILTER PANEL TOGGLE
// ============================================================
function toggleFilterPanel() {
    if (filterPanel.classList.contains('show')) {
        closeFilterPanel();
    } else {
        openFilterPanel();
    }
}

function openFilterPanel() {
    filterPanel.classList.add('show');
    if (filterOverlay && window.innerWidth <= 992) {
        filterOverlay.classList.add('show');
    }
    // Select2 mis-measures width when initialized inside a collapsed/hidden
    // panel — re-trigger a resize-safe refresh once it's visible.
    if (typeof $ !== 'undefined' && $.fn.select2) {
        setTimeout(() => {
            $('#filterTransport, #filterEntryPoint').trigger('change.select2');
        }, 50);
    }
}

function closeFilterPanel() {
    filterPanel.classList.remove('show');
    if (filterOverlay) filterOverlay.classList.remove('show');
}

// ============================================================
// 11. OFFCANVAS PREVIEW (simple)
// ============================================================
function openPreview(id) {
    const app = applications.find(a => a.id === id);
    if (!app) return;

    const headerTitle = document.querySelector('#applicationPreview .offcanvas-header h5');
    const headerSmall = document.querySelector('#applicationPreview .offcanvas-header small.text-muted');
    if (headerTitle) headerTitle.innerHTML = `Application <small class="text-muted">${app.id}</small>`;
    if (headerSmall) headerSmall.innerText = app.id;

    if (!offcanvasInstance) {
        offcanvasInstance = new bootstrap.Offcanvas(previewOffcanvas);
    }
    offcanvasInstance.show();
}

// ============================================================
// 12. INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    loadDummyData();
    renderSummary();
    filteredApps = sortApplications([...applications], sortState);
    renderTable(filteredApps);

    initSelect2();
    renderFilterBadge(); // starts at 0 active filters

    if (openFilterBtn) openFilterBtn.addEventListener('click', toggleFilterPanel);
    if (closeFilterBtn) closeFilterBtn.addEventListener('click', closeFilterPanel);
    if (filterOverlay) filterOverlay.addEventListener('click', closeFilterPanel);
    if (applyFilterBtn) applyFilterBtn.addEventListener('click', applyFilters);
    if (clearFilterBtn) clearFilterBtn.addEventListener('click', resetFilters);

    bindSearch();
    bindSort();

    console.log('✅ Import Permit Application UI ready.');
});