export function activityLogDesign(activityLogs, qrScanLogs = []) {
    // Merge activity logs and QR scan logs into a single timeline.
    const timelineEntries = buildTimelineEntries(activityLogs, qrScanLogs);

    if (timelineEntries.length === 0) {
        return `<p class="text-muted">No activity logs found.</p>`;
    }

    let html = `<div class="order-track mt-1 position-relative">`;

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

        html += `
        <div class="accordion position-relative" id="accordion-${index}">
            <div class="accordion-item border-0 bg-transparent mb-3">
                <div class="accordion-header" id="${headingId}">
                    <a class="${buttonClass}" href="javascript:void(0)" role="button" 
                       data-bs-toggle="collapse" data-bs-target="#${collapseId}" 
                       aria-expanded="${isOpen}" aria-controls="${collapseId}">
                        <div class="d-flex mb-0 lh-1">
                            <div class="me-2 position-relative">
                                ${iconHtml}
                            </div>
                            <div class="flex-fill d-flex align-items-center justify-content-between">
                                <p class="fw-medium mb-0 fs-14 text-wrap">${escapeHtml(title)}</p>
                                <span class="fs-12">${time}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div id="${collapseId}" class="${collapseClass}" aria-labelledby="${headingId}" data-bs-parent="#accordion-${index}">
                    <div class="accordion-body pt-0 ps-5 mb-0 pb-0">
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
                if (this.classList.contains('collapsed') === false) {
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
            status: log?.status || "-",
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
            const resultText = "Valid";
            const statusLabel = "QR Permit Approved";

            return {
                kind: "qr",
                status: statusLabel,
                createdAt: log?.scanned_at || null,
                user: log?.internal_user_name || "-",
                position: log?.internal_user_position || "-",
                scannedValue: log?.scanned_value || "-",
                result: resultText,
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
        const resultBadgeClass = "badge bg-success";

        return `
            <p class="mb-0 fs-12">
                <span class="fw-bold text-muted fs-12">User:</span>
                <span class="text-primary fs-12">${escapeHtml(entry.user)}</span><br>
                <span class="fw-bold text-muted fs-12">Position:</span>
                <span class="fs-12">${escapeHtml(entry.position)}</span><br>
                <span class="fw-bold text-muted fs-12">Scanned Value:</span>
                <span class="fs-12">${escapeHtml(entry.scannedValue)}</span><br>
                <span class="fw-bold text-muted fs-12">Result:</span>
                <span class="${resultBadgeClass}">${escapeHtml(entry.result)}</span><br>
                <span class="fw-bold text-muted fs-12">Date & Time:</span>
                <span class="fs-12">${entry.createdAt ? formatTime(entry.createdAt) : "-"}</span>
            </p>
        `;
    }

    return `
        <p class="mb-0 fs-12">
            <span class="fw-bold text-muted fs-12">User:</span>
            <span class="text-primary fs-12">${escapeHtml(entry.causer)}</span><br>
            <span class="fw-bold text-muted mt-1">Log:</span>
            <span class="fs-12">${escapeHtml(entry.remark)}</span>
        </p>
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
        const qrStatus = status.toLowerCase();
        const isValid = qrStatus.includes("approved") || (qrStatus.includes("valid") && !qrStatus.includes("invalid"));
        const badgeClass = isValid
            ? "border border-success border-opacity-10 bg-success-transparent"
            : "border border-danger border-opacity-10 bg-danger-transparent";
        const iconClass = isValid ? "ti ti-scan fs-14" : "ti ti-alert-triangle fs-14";
        return `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur ${badgeClass}">
                    <i class="${iconClass}"></i>
                </span>`;
    }

    const s = status.toLowerCase(); // make sure it's lowercase
    let iconHtml = '';

    if (s === 'submitted') {
        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-primary border-opacity-10 bg-primary-transparent">
                        <i class="bi bi-send fs-14"></i>
                    </span>`;
    } else if (s === 'clerk verified') {
        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-info border-opacity-10 bg-info-transparent">
                        <i class="bi bi-person-check-fill fs-14"></i>
                    </span>`;
    } else if (s === 'item accepted') {
        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-info border-opacity-10 bg-success-transparent">
                        <i class="bi bi-check2-all fs-14"></i>
                    </span>`;
    } else if (s === 'officer verification completed') {
        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-success border-opacity-10 bg-success-transparent">
                        <i class='bx bx-box fs-14'></i> 
                    </span>`;
    } else if (s === 'clerk review in-progress') {
        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-secondary border-opacity-10 bg-secondary-transparent">
                        <i class="bi bi-person-fill-gear fs-14"></i>
                    </span>`;
    } else if (s === 'officer verified') {

        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-warning border-opacity-10 bg-warning-transparent">
                       <i class="bi bi-file-earmark-check fs-14"></i>
                    </span>`;
    } else if (s === 'officer rejected') {

        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-danger border-opacity-10 bg-danger-transparent">
                       <i class="bi bi-file-earmark-excel fs-14"></i>
                    </span>`;
    }  else if (s === 'user reapply consignment') {

        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-info border-opacity-10 bg-info-transparent">
                       <i class="bi bi-file-earmark-arrow-up fs-14"></i>
                    </span>`;
    } else if (s === 'user payment') {

        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-primary1 border-opacity-10 bg-primary1-transparent">
                       <i class="bi bi-wallet2 fs-14"></i>
                    </span>`;
    }  else if (s === 'payment successful') {

        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-success border-opacity-10 bg-success-transparent">
                       <i class="bi bi-cash fs-14"></i>
                    </span>`;
    } else if (s === 'payment unsuccessful') {

        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-danger border-opacity-10 bg-danger-transparent">
                       <i class="bi bi-cash fs-14"></i>
                    </span>`;
    } else if (s === 'payment is pending for authorization') {

        iconHtml = `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-warning border-opacity-10 bg-warning-transparent">
                       <i class="bi bi-cash fs-14"></i>
                    </span>`;
    } 
    
    else {
        iconHtml = defaultIcon();
    }

    return iconHtml;
}

// fallback icon
function defaultIcon() {
    return `<span class="avatar avatar-sm avatar-rounded track-order-icon backdrop-blur border border-primary border-opacity-10 bg-primary-transparent">
                <img src="https://laravelui.spruko.com/xintra/build/assets/images/ecommerce/png/18.png" alt="">
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


