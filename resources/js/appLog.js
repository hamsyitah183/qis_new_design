export function activityLogDesign(activityLogs, qrScanLogs = []) {
    // Merge activity logs and QR scan logs into a single timeline.
    const timelineEntries = buildTimelineEntries(activityLogs, qrScanLogs);

    if (timelineEntries.length === 0) {
        return `<p class="text-muted text-center py-3">No activity logs found.</p>`;
    }

    let html = `<div class="order-track mt-3 position-relative">`;

    timelineEntries.forEach((entry, index) => {
        const headingId = `heading-${index}`;
        const collapseId = `collapse-${index}`;
        const title = entry.status || "-";
        const time = entry.createdAt ? formatTime(entry.createdAt) : "-";
        const iconHtml = getIcon(title, entry.kind);

        // Open first and last accordion by default.
        const isOpen = index === 0 || index === timelineEntries.length - 1;
        const collapseClass = isOpen
            ? "accordion-collapse border-top-0 collapse show"
            : "accordion-collapse border-top-0 collapse";
        const buttonClass = isOpen
            ? "px-0 pt-0 accordion-button-custom active-accordion"
            : "px-0 pt-0 collapsed accordion-button-custom";

        const titleBm = getStatusBm(title);
        html += `
        <div class="accordion position-relative" id="accordion-${index}">
            <div class="accordion-item border-0 bg-transparent mb-2">
                <div class="accordion-header" id="${headingId}">
                    <a class="${buttonClass} text-decoration-none" href="javascript:void(0)" role="button" 
                       data-bs-toggle="collapse" data-bs-target="#${collapseId}" 
                       aria-expanded="${isOpen}" aria-controls="${collapseId}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative">
                                ${iconHtml}
                            </div>
                            <div class="flex-fill d-flex align-items-center justify-content-between">
                                <p class="fw-semibold mb-0 fs-14 text-dark" data-en="${escapeHtml(title)}" data-bm="${escapeHtml(titleBm)}">${escapeHtml(title)}</p>
                                <span class="text-muted fs-12">${time}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div id="${collapseId}" class="${collapseClass}" aria-labelledby="${headingId}" data-bs-parent="#accordion-${index}">
                    <div class="accordion-body pt-1 ps-5 mb-0 pb-2">
                        ${renderTimelineEntryDetails(entry)}
                    </div>
                </div>
            </div>
        </div>
        `;
    });

    html += `</div>`;

    // JS to handle header highlight when opened
    setTimeout(() => {
        document.querySelectorAll('.accordion-button-custom').forEach(button => {
            button.addEventListener('click', function () {
                // Remove highlight from all
                document.querySelectorAll('.accordion-button-custom').forEach(b => b.classList.remove('active-accordion'));
                // Add highlight to currently expanding
                if (!this.classList.contains('collapsed')) {
                    this.classList.add('active-accordion');
                }
            });
        });
    }, 500);

    return html;
}

function buildTimelineEntries(activityLogs, qrScanLogs) {
    const activityEntries = (Array.isArray(activityLogs) ? activityLogs : []).map(
        (log) => ({
            kind: "activity",
            status: log?.status || log?.action || "-",
            createdAt: log?.created_at || null,
            causer: log?.causer?.fullname || "System",
            remark: log?.remark || "-",
        })
    );

    const qrEntries = (Array.isArray(qrScanLogs) ? qrScanLogs : [])
        .filter((log) => {
            const normalizedResult = String(log?.result || "").toLowerCase();
            return normalizedResult === "approved" || normalizedResult === "valid" || (normalizedResult === "" && !!log?.is_valid);
        })
        .map((log) => {
            return {
                kind: "qr",
                status: "QR Permit Validated",
                createdAt: log?.scanned_at || null,
                user: log?.internal_user_name || "-",
                position: log?.internal_user_position || "-",
                scannedValue: log?.scanned_value || "-",
                result: "Valid",
            };
        });

    return [...activityEntries, ...qrEntries].sort((a, b) => {
        const timeA = a.createdAt ? new Date(a.createdAt).getTime() : 0;
        const timeB = b.createdAt ? new Date(b.createdAt).getTime() : 0;
        return timeA - timeB;
    });
}

function renderTimelineEntryDetails(entry) {
    if (entry.kind === "qr") {
        return `
            <div class="timeline-details">
                <div class="row g-2">
                    <div class="col-12">
                        <span class="fw-semibold text-muted" data-en="User:" data-bm="Pengguna:">User:</span>
                        <span class="text-primary">${escapeHtml(entry.user)}</span>
                    </div>
                    <div class="col-12">
                        <span class="fw-semibold text-muted" data-en="Position:" data-bm="Jawatan:">Position:</span>
                        <span>${escapeHtml(entry.position)}</span>
                    </div>
                    <div class="col-12">
                        <span class="fw-semibold text-muted" data-en="Scanned Value:" data-bm="Nilai Diimbas:">Scanned Value:</span>
                        <span>${escapeHtml(entry.scannedValue)}</span>
                    </div>
                    <div class="col-12">
                        <span class="fw-semibold text-muted" data-en="Result:" data-bm="Keputusan:">Result:</span>
                        <span class="badge bg-success" data-en="${escapeHtml(entry.result)}" data-bm="${escapeHtml(entry.result) === 'Valid' ? 'Sah' : escapeHtml(entry.result)}">${escapeHtml(entry.result)}</span>
                    </div>
                    <div class="col-12">
                        <span class="fw-semibold text-muted" data-en="Date & Time:" data-bm="Tarikh & Masa:">Date & Time:</span>
                        <span>${entry.createdAt ? formatTime(entry.createdAt) : "-"}</span>
                    </div>
                </div>
            </div>
        `;
    }

    return `
        <div class="timeline-details">
            <div class="row g-2">
                <div class="col-12">
                    <span class="fw-semibold text-muted" data-en="User:" data-bm="Pengguna:">User:</span>
                    <span class="text-primary">${escapeHtml(entry.causer)}</span>
                </div>
                <div class="col-12">
                    <span class="fw-semibold text-muted" data-en="Remark:" data-bm="Catatan:">Remark:</span>
                    <span class="text-secondary" data-en="${escapeHtml(entry.remark)}" data-bm="${escapeHtml(getRemarkBm(entry.remark))}">${escapeHtml(entry.remark)}</span>
                </div>
            </div>
        </div>
    `;
}

function escapeHtml(value) {
    return String(value ?? "-")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

function getIcon(status, kind = "activity") {
    if (!status) return defaultIcon();

    if (kind === "qr") {
        return `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-success border-opacity-10 bg-success-transparent">
                    <i class="ti ti-scan fs-14"></i>
                </span>`;
    }

    const s = status.toLowerCase().trim();
    let iconHtml = '';

    // Map statuses to icons and bm translations
    const statusMap = {
        'submitted': { icon: 'bi bi-send', bg: 'bg-primary-transparent', border: 'border-primary', bm: 'Dihantar' },
        'clerk review in-progress': { icon: 'bi bi-person-fill-gear', bg: 'bg-secondary-transparent', border: 'border-secondary', bm: 'Semakan Kerani Dalam Proses' },
        'clerk verified': { icon: 'bi bi-person-check-fill', bg: 'bg-info-transparent', border: 'border-info', bm: 'Disahkan Kerani' },
        'clerk approved': { icon: 'bi bi-person-check-fill', bg: 'bg-info-transparent', border: 'border-info', bm: 'Diluluskan Kerani' },
        'item accepted': { icon: 'bi bi-check2-all', bg: 'bg-success-transparent', border: 'border-success', bm: 'Item Diterima' },
        'officer verification completed': { icon: 'bx bx-box', bg: 'bg-success-transparent', border: 'border-success', bm: 'Pengesahan Pegawai Selesai' },
        'officer verified': { icon: 'bi bi-file-earmark-check', bg: 'bg-warning-transparent', border: 'border-warning', bm: 'Disahkan Pegawai' },
        'officer rejected': { icon: 'bi bi-file-earmark-excel', bg: 'bg-danger-transparent', border: 'border-danger', bm: 'Ditolak Pegawai' },
        'user reapply consignment': { icon: 'bi bi-file-earmark-arrow-up', bg: 'bg-info-transparent', border: 'border-info', bm: 'Pengguna Mohon Semula Konsainan' },
        'user payment': { icon: 'bi bi-wallet2', bg: 'bg-primary-transparent', border: 'border-primary', bm: 'Pembayaran Pengguna' },
        'payment successful': { icon: 'bi bi-cash', bg: 'bg-success-transparent', border: 'border-success', bm: 'Pembayaran Berjaya' },
        'payment unsuccessful': { icon: 'bi bi-cash', bg: 'bg-danger-transparent', border: 'border-danger', bm: 'Pembayaran Gagal' },
        'payment is pending for authorization': { icon: 'bi bi-cash', bg: 'bg-warning-transparent', border: 'border-warning', bm: 'Pembayaran Menunggu Pengesahan' },
        'completed': { icon: 'bi bi-check2-circle', bg: 'bg-success-transparent', border: 'border-success', bm: 'Selesai' },
        'printed': { icon: 'bi bi-printer', bg: 'bg-primary-transparent', border: 'border-primary', bm: 'Dicetak' },
        'approved': { icon: 'bi bi-check-circle', bg: 'bg-success-transparent', border: 'border-success', bm: 'Diluluskan' },
        'rejected': { icon: 'bi bi-x-circle', bg: 'bg-danger-transparent', border: 'border-danger', bm: 'Ditolak' },
        'draft': { icon: 'bi bi-file-earmark', bg: 'bg-secondary-transparent', border: 'border-secondary', bm: 'Draf' },
    };

    // Find matching status (partial match)
    let matchedKey = null;
    for (const key of Object.keys(statusMap)) {
        if (s.includes(key) || key.includes(s)) {
            matchedKey = key;
            break;
        }
    }

    if (matchedKey) {
        const config = statusMap[matchedKey];
        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border ${config.border} border-opacity-10 ${config.bg}">
                        <i class="${config.icon} fs-14"></i>
                    </span>`;
    } else {
        iconHtml = defaultIcon();
    }

    return iconHtml;
}

export function getStatusBm(status) {
    if (!status) return status;
    const s = status.toLowerCase().trim();
    const statusMap = {
        'submitted': 'Dihantar',
        'clerk review in-progress': 'Semakan Kerani Dalam Proses',
        'clerk verified': 'Disahkan Kerani',
        'clerk approved': 'Diluluskan Kerani',
        'item accepted': 'Item Diterima',
        'officer verification completed': 'Pengesahan Pegawai Selesai',
        'officer verified': 'Disahkan Pegawai',
        'officer rejected': 'Ditolak Pegawai',
        'user reapply consignment': 'Pengguna Mohon Semula Konsainan',
        'user payment': 'Pembayaran Pengguna',
        'payment successful': 'Pembayaran Berjaya',
        'payment unsuccessful': 'Pembayaran Gagal',
        'payment is pending for authorization': 'Pembayaran Menunggu Pengesahan',
        'completed': 'Selesai',
        'printed': 'Dicetak',
        'approved': 'Diluluskan',
        'rejected': 'Ditolak',
        'draft': 'Draf',
        'qr permit validated': 'Permit QR Disahkan'
    };

    for (const key of Object.keys(statusMap)) {
        if (s.includes(key) || key.includes(s)) {
            return statusMap[key];
        }
    }
    return status;
}

export function getRemarkBm(remark) {
    if (!remark) return remark;
    const r = remark.toLowerCase().trim();
    const remarkMap = {
        'application approved by clerk via email': 'Permohonan diluluskan oleh kerani melalui e-mel',
        'application approved by officer via email': 'Permohonan diluluskan oleh pegawai melalui e-mel',
        'application approved by clerk': 'Permohonan diluluskan oleh kerani',
        'application verified by importer': 'Permohonan disahkan oleh pengimport',
        'application rejected by importer': 'Permohonan ditolak oleh pengimport',
        'all permits have completed processing': 'Semua permit telah selesai diproses',
        'user reapply the consignment': 'Pengguna memohon semula konsainan',
        'inspection application saved as draft': 'Permohonan pemeriksaan disimpan sebagai draf',
        'inspection application draft updated': 'Draf permohonan pemeriksaan dikemas kini',
        'inspection application submitted': 'Permohonan pemeriksaan dihantar',
        'inspection application deleted': 'Permohonan pemeriksaan dipadam',
        'Inspection application Clerk Verified': 'Permohonan pemeriksaan diperika oleh kerani',
        'all inspection item are accepted': 'Semua item pemeriksaan diterima',
        'all inspection items processed': 'Semua item pemeriksaan telah diproses',
        'application is ready to be paid': 'Permohonan sedia untuk dibayar',
        'all permits under this application have been completed': 'Semua permit di bawah permohonan ini telah selesai',
        'officer verification completed': 'Pengesahan Pegawai Selesai',
        'fully processed': 'Selesai Diproses'
    };

    for (const key of Object.keys(remarkMap)) {
        if (r.includes(key)) {
            return remarkMap[key]; // Return standard translation
        }
    }
    return remark; // Return original if custom (like a custom rejection reason)
}

function defaultIcon() {
    return `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-primary border-opacity-10 bg-primary-transparent">
                <i class="bi bi-circle fs-14"></i>
            </span>`;
}


function formatTime(datetime) {
    const d = new Date(datetime);

    const options = {
        day: '2-digit',
        month: 'long', // e.g., May
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    };

    return d.toLocaleString('en-GB', options);
}


