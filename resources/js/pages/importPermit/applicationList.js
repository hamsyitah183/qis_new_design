/**
 * ============================================================
 * Import Permit Application Page – Full Functionality
 * ============================================================
 */

// ---- DATA ----
let applications = [];
let filteredApps = [];

// ---- DOM refs (safe) ----
const tableBody = document.getElementById('applicationTableBody');
const searchInput = document.getElementById('searchApplication');

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
const applyFilterBtn = document.getElementById('applyFilterBtn');
const clearFilterBtn = document.getElementById('clearFilterBtn');

// ---- Offcanvas ----
const previewOffcanvas = document.getElementById('applicationPreview');
let offcanvasInstance = null;

// ---- FILTER STATE ----
let filterState = {
    status: [],
    transport: 'All',
    entryPoint: 'All'
};

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
// 4. TABLE RENDER
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
// 5. DELETE
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
// 6. SEARCH (debounced)
// ============================================================
function bindSearch() {
    if (!searchInput) return;
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const keyword = e.target.value.toLowerCase().trim();
            if (!keyword) {
                filteredApps = [...applications];
            } else {
                filteredApps = applications.filter(app =>
                    app.id.toLowerCase().includes(keyword) ||
                    app.applicant.toLowerCase().includes(keyword) ||
                    app.importer.toLowerCase().includes(keyword)
                );
            }
            applyFiltersSilent();
        }, 250);
    });
}

// ============================================================
// 7. FILTERS
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
        if (filterState.transport !== 'All' && app.transport !== filterState.transport) return false;
        if (filterState.entryPoint !== 'All' && app.entryPoint !== filterState.entryPoint) return false;
        return true;
    });

    filteredApps = result;
    renderTable(filteredApps);
}

function applyFilters() {
    filterState.status = getSelectedStatuses();
    if (transportSelect) filterState.transport = transportSelect.value;
    if (entryPointSelect) filterState.entryPoint = entryPointSelect.value;
    applyFiltersSilent();
    if (window.innerWidth <= 992) closeFilterPanel();
}

function resetFilters() {
    document.querySelectorAll('.status-filter').forEach(cb => cb.checked = false);
    if (transportSelect) transportSelect.value = 'All';
    if (entryPointSelect) entryPointSelect.value = 'All';
    filterState = { status: [], transport: 'All', entryPoint: 'All' };
    filteredApps = [...applications];
    renderTable(filteredApps);
    if (window.innerWidth <= 992) closeFilterPanel();
}

// ============================================================
// 8. FILTER PANEL TOGGLE
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
}

function closeFilterPanel() {
    filterPanel.classList.remove('show');
    if (filterOverlay) filterOverlay.classList.remove('show');
}

// ============================================================
// 9. OFFCANVAS PREVIEW (simple)
// ============================================================
function openPreview(id) {
    const app = applications.find(a => a.id === id);
    if (!app) return;

    // Update offcanvas header
    const headerTitle = document.querySelector('#applicationPreview .offcanvas-header h5');
    const headerSmall = document.querySelector('#applicationPreview .offcanvas-header small.text-muted');
    if (headerTitle) headerTitle.innerHTML = `Application <small class="text-muted">${app.id}</small>`;
    if (headerSmall) headerSmall.innerText = app.id;

  

    // Show offcanvas
    if (!offcanvasInstance) {
        offcanvasInstance = new bootstrap.Offcanvas(previewOffcanvas);
    }
    offcanvasInstance.show();
}

// ============================================================
// 10. INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    loadDummyData();
    renderSummary();
    filteredApps = [...applications];
    renderTable(filteredApps);

    // Filter panel events (safely)
    if (openFilterBtn) openFilterBtn.addEventListener('click', toggleFilterPanel);
    if (closeFilterBtn) closeFilterBtn.addEventListener('click', closeFilterPanel);
    if (filterOverlay) filterOverlay.addEventListener('click', closeFilterPanel);
    if (applyFilterBtn) applyFilterBtn.addEventListener('click', applyFilters);
    if (clearFilterBtn) clearFilterBtn.addEventListener('click', resetFilters);

    // Search
    bindSearch();

    console.log('✅ Import Permit Application UI ready.');
});