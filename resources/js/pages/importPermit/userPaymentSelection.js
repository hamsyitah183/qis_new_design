/**
 * userPaymentSelection.js
 * ------------------------------------------------------------------
 * Awaiting Payment — user selects which approved permit items to
 * include in this payment batch (RM 15 per item). Rejected permits
 * are shown as read-only context rows. On "Proceed to Payment" the
 * selected permit numbers are stored in sessionStorage and the user
 * is redirected to /public/payment.
 */

const ITEM_PRICE = 15; // RM per permit item

// ---------------------------------------------------------------
// Dummy data — application at Awaiting Payment
// ---------------------------------------------------------------

const APPLICATION = {
    application_id: "IP-2025-00456",
    type: "Import Permit",
    status: "awaiting_payment",
    status_duration: "2 hours",
    tags: [
        { label: "Category 1", color: "primary" },
        { label: "Repeat Importer", color: "secondary" },
    ],
    submitted_by: "Tan Wei Ling",
    submitted_at: "12 May 2025, 9:02 AM",
    assigned_officer: "Ahmad Zulkifli",
    sla_due: "Pay within 3 days",
    eta: "20 May 2025",
    transport_type: "Sea Freight",
    entry_point: "Kota Kinabalu Port",
    entry_point_description:
        "Main seaport entry point for Sabah-bound consignments.",
    importer: {
        name: "Borneo Fresh Trading Sdn Bhd",
        phone: "(088) 244 511",
        email: "admin@borneofresh.my",
        address: "Lot 12, Kolombong Industrial Park, 88450 Kota Kinabalu",
        country: "Malaysia",
    },
    exporter: {
        name: "Golden Harvest Pte Ltd",
        phone: "+65 6221 4480",
        email: "export@goldenharvest.sg",
        address: "21 Tanjong Penjuru Crescent",
        country: "Singapore",
    },
    attachments: [
        {
            name: "Invoice_IP-2025-00456.pdf",
            size: "420 KB",
            path: "/consignment/attachment/10",
            mime: "application/pdf",
        },
        { name: "Packing_List.xlsx", size: "88 KB" },
        {
            name: "Bill_of_Lading.pdf",
            size: "310 KB",
            path: "/consignment/attachment/8",
            mime: "application/pdf",
        },
    ],
};

// decision: 'approved' | 'rejected' — set by officer in technical review
// selected: user's tick on this page (only meaningful for approved permits)
const PERMITS = [
    {
        permit_number: "PMT-1201",
        consignment_detail: {
            category: "Fresh Produce",
            item_name: "Fresh Fruit — Corn",
            usage: "Commercial Sale",
        },
        quantity: 1200,
        unit_measurement: "KG",
        purpose: "Commercial Sale",
        value: 2480,
        decision: "approved",
        selected: true,
        rejectionReason: null,
        remark: "Approved by officer Ahmad Zulkifli.",
        attachments: [
            {
                name: "Phytosanitary_Cert.pdf",
                size: "210 KB",
                path: "/consignment/attachment/5",
                mime: "application/pdf",
            },
            { name: "Fumigation_Cert.pdf", size: "180 KB" },
        ],
        conditions: [
            "Must be accompanied by a valid phytosanitary certificate.",
            "Subject to inspection at point of entry.",
        ],
        permitActivity: [
            {
                stage: "doc_verification",
                title: "Documents Verified",
                description: "Verified by clerk Nurul Aisyah.",
                time: "13 May 2025, 8:50 AM",
            },
            {
                stage: "permit_approved",
                title: "Permit Approved",
                description: "Approved by officer Ahmad Zulkifli.",
                time: "14 May 2025, 11:05 AM",
            },
        ],
    },
    {
        permit_number: "PMT-1202",
        consignment_detail: {
            category: "Live Animals",
            item_name: "Live Ornamental Fish",
            usage: "Re-export",
        },
        quantity: 300,
        unit_measurement: "Unit",
        purpose: "Re-export",
        value: 650,
        decision: "rejected",
        selected: false,
        rejectionReason:
            "Annual import quota for this species has been exhausted.",
        remark: "Rejected by officer Ahmad Zulkifli.",
        attachments: [{ name: "Health_Cert.pdf", size: "195 KB" }],
        conditions: [
            "Requires a valid health certificate from country of origin.",
            "Subject to quarantine inspection on arrival.",
        ],
        permitActivity: [
            {
                stage: "doc_verification",
                title: "Documents Verified",
                description: "Verified by clerk Nurul Aisyah.",
                time: "13 May 2025, 8:52 AM",
            },
            {
                stage: "permit_rejected",
                title: "Permit Rejected",
                description: "Rejected — annual import quota exhausted.",
                time: "14 May 2025, 11:20 AM",
            },
        ],
    },
    {
        permit_number: "PMT-1203",
        consignment_detail: {
            category: "Frozen Seafood",
            item_name: "Frozen Seafood — Tilapia",
            usage: "Commercial Sale",
        },
        quantity: 4500,
        unit_measurement: "KG",
        purpose: "Commercial Sale",
        value: 9150,
        decision: "approved",
        selected: true,
        rejectionReason: null,
        remark: "Approved by officer Ahmad Zulkifli.",
        attachments: [
            {
                name: "Health_Cert_Seafood.pdf",
                size: "230 KB",
                path: "/consignment/attachment/3",
                mime: "application/pdf",
            },
            { name: "Cold_Chain_Report.pdf", size: "142 KB" },
            { name: "Catch_Certificate.pdf", size: "98 KB" },
        ],
        conditions: [
            "Must maintain cold-chain temperature below -18°C.",
            "Requires a valid catch certificate.",
        ],
        permitActivity: [
            {
                stage: "doc_verification",
                title: "Documents Verified",
                description: "Verified by clerk Nurul Aisyah.",
                time: "13 May 2025, 8:54 AM",
            },
            {
                stage: "permit_approved",
                title: "Permit Approved",
                description: "Approved by officer Ahmad Zulkifli.",
                time: "14 May 2025, 3:10 PM",
            },
        ],
    },
    {
        permit_number: "PMT-1204",
        consignment_detail: {
            category: "Agricultural Seedlings",
            item_name: "Rubber Seedlings",
            usage: "Research",
        },
        quantity: 80,
        unit_measurement: "Unit",
        purpose: "Research",
        value: 320,
        decision: "approved",
        selected: false,
        rejectionReason: null,
        remark: "Approved by officer Ahmad Zulkifli.",
        attachments: [{ name: "Research_Permit_Letter.pdf", size: "120 KB" }],
        conditions: ["Restricted to approved research institutions only."],
        permitActivity: [
            {
                stage: "doc_verification",
                title: "Documents Verified",
                description: "Verified by clerk Nurul Aisyah.",
                time: "13 May 2025, 8:55 AM",
            },
            {
                stage: "permit_approved",
                title: "Permit Approved",
                description: "Approved by officer Ahmad Zulkifli.",
                time: "14 May 2025, 3:15 PM",
            },
        ],
    },
];

const ACTIVITY_LOG = [
    {
        stage: "submitted",
        title: "Application Submitted",
        description: "Application IP-2025-00456 was lodged by Tan Wei Ling.",
        time: "12 May 2025, 9:02 AM",
    },
    {
        stage: "doc_verification",
        title: "Stage: Submitted → Document Verification",
        description: "Assigned to clerk Nurul Aisyah.",
        time: "12 May 2025, 10:15 AM",
    },
    {
        stage: "doc_verification",
        title: "Documents Verified by Clerk",
        description: "Forwarded to Technical Review.",
        time: "13 May 2025, 9:00 AM",
    },
    {
        stage: "technical_review",
        title: "Stage: Document Verification → Technical Review",
        description: "Assigned to officer Ahmad Zulkifli.",
        time: "13 May 2025, 9:05 AM",
    },
    {
        stage: "permit_approved",
        title: "Permit PMT-1201 Approved",
        description: "Approved by Ahmad Zulkifli.",
        time: "14 May 2025, 11:05 AM",
    },
    {
        stage: "permit_rejected",
        title: "Permit PMT-1202 Rejected",
        description: "Import quota exhausted.",
        time: "14 May 2025, 11:20 AM",
    },
    {
        stage: "permit_approved",
        title: "Permit PMT-1203 Approved",
        description: "Approved by Ahmad Zulkifli.",
        time: "14 May 2025, 3:10 PM",
    },
    {
        stage: "permit_approved",
        title: "Permit PMT-1204 Approved",
        description: "Approved by Ahmad Zulkifli.",
        time: "14 May 2025, 3:15 PM",
    },
    {
        stage: "awaiting_payment",
        title: "Stage: Technical Review → Awaiting Payment",
        description: "Invoice generated. Awaiting applicant payment.",
        time: "14 May 2025, 3:20 PM",
    },
];

const STAGE_ORDER = [
    "submitted",
    "doc_verification",
    "technical_review",
    "awaiting_payment",
    "payment_processing",
    "completed",
];

const STAGE_CONFIG = {
    submitted: { en: "Submitted", icon: "bi-send-check", color: "info" },
    doc_verification: {
        en: "Document Verification",
        icon: "bi-file-earmark-check",
        color: "secondary",
    },
    returned: {
        en: "Returned / Rejected",
        icon: "bi-arrow-return-left",
        color: "danger",
    },
    technical_review: {
        en: "Technical Review",
        icon: "bi-clipboard-check",
        color: "primary",
    },
    awaiting_payment: {
        en: "Awaiting Payment",
        icon: "bi-hourglass-split",
        color: "warning",
    },
    payment_processing: {
        en: "Payment Processing",
        icon: "bi-credit-card",
        color: "orange",
    },
    completed: { en: "Completed", icon: "bi-check-circle", color: "success" },
    email: {
        en: "Notification Sent",
        icon: "bi-envelope-check",
        color: "gray",
    },
    permit_approved: {
        en: "Permit Approved",
        icon: "bi-check-circle",
        color: "success",
    },
    permit_rejected: {
        en: "Permit Rejected",
        icon: "bi-x-circle",
        color: "danger",
    },
};

const PERMIT_DECISION_CONFIG = {
    approved: { en: "Approved", color: "success" },
    rejected: { en: "Rejected", color: "danger" },
};

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function escapeHtml(v) {
    return String(v ?? "").replace(
        /[&<>"']/g,
        (c) =>
            ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;",
            })[c],
    );
}

function money(n) {
    return Number(n || 0).toLocaleString("en-MY", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function fileMeta(filename) {
    const ext = (filename.split(".").pop() || "").toLowerCase();
    if (ext === "pdf")
        return { icon: "bi-file-earmark-pdf-fill", cls: "is-pdf" };
    if (["xlsx", "xls", "csv"].includes(ext))
        return { icon: "bi-file-earmark-excel-fill", cls: "is-excel" };
    if (["doc", "docx"].includes(ext))
        return { icon: "bi-file-earmark-word-fill", cls: "is-word" };
    if (["jpg", "jpeg", "png"].includes(ext))
        return { icon: "bi-file-earmark-image-fill", cls: "is-image" };
    if (["zip", "rar"].includes(ext))
        return { icon: "bi-file-earmark-zip-fill", cls: "is-zip" };
    return { icon: "bi-file-earmark-fill", cls: "is-default" };
}

// ---------------------------------------------------------------
// Attachment viewer (unchanged pattern from officer/clerk pages)
// ---------------------------------------------------------------

const attachmentRegistry = new Map();
const attachmentDataMap = new Map();
let attachmentSeq = 0;
let currentListId = null;
let currentIndex = 0;
let attachmentOffcanvas = null;

function paintAttachmentList(containerEl, files, visibleCount) {
    const listId = containerEl.dataset.listId;
    if (!listId) return;
    attachmentDataMap.set(listId, files);

    const shown = files.slice(0, visibleCount);
    const remaining = files.length - shown.length;

    let html = shown
        .map((file, idx) => {
            const meta = fileMeta(file.name);
            return `
            <div class="ipv-attach-chip" data-list-id="${listId}" data-index="${idx}" style="cursor:pointer;">
                <div class="ipv-attach-icon ${meta.cls}"><i class="bi ${meta.icon}"></i></div>
                <div class="ipv-attach-info">
                    <div class="ipv-attach-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                    <div class="ipv-attach-size">${escapeHtml(file.size)}</div>
                </div>
            </div>
        `;
        })
        .join("");

    if (remaining > 0)
        html += `<div class="ipv-attach-more" data-list-id="${listId}">+${remaining}</div>`;
    containerEl.innerHTML = html;
}

function renderAttachmentList(containerEl, files, visibleCount) {
    if (!containerEl) return;
    if (!files?.length) {
        containerEl.innerHTML =
            '<span class="ipv-attach-size" style="padding:0.4rem 0;">No attachments.</span>';
        return;
    }
    const listId = "attach-" + attachmentSeq++;
    attachmentRegistry.set(listId, files);
    containerEl.dataset.listId = listId;
    paintAttachmentList(containerEl, files, visibleCount);
}

document.addEventListener("click", (e) => {
    const more = e.target.closest(".ipv-attach-more");
    if (more) {
        const listId = more.dataset.listId;
        const files = attachmentRegistry.get(listId);
        const el = document.querySelector(`[data-list-id="${listId}"]`);
        if (el && files) paintAttachmentList(el, files, files.length);
        return;
    }
    const chip = e.target.closest(".ipv-attach-chip");
    if (chip) {
        openAttachmentViewer(
            chip.dataset.listId,
            parseInt(chip.dataset.index, 10),
        );
        return;
    }

    if (e.target.closest("#attachmentPrevBtn") && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex > 0)
            openAttachmentViewer(currentListId, currentIndex - 1);
    }
    if (e.target.closest("#attachmentNextBtn") && currentListId) {
        const files = attachmentDataMap.get(currentListId);
        if (files && currentIndex < files.length - 1)
            openAttachmentViewer(currentListId, currentIndex + 1);
    }
});

function initOffcanvas() {
    const el = document.getElementById("attachmentOffcanvas");
    if (el)
        attachmentOffcanvas = new bootstrap.Offcanvas(el, {
            backdrop: true,
            keyboard: true,
            scroll: false,
        });
}

function openAttachmentViewer(listId, index) {
    const files = attachmentDataMap.get(listId);
    if (!files?.length) return;
    currentListId = listId;
    currentIndex = index;
    const file = files[index];
    if (!file) return;
    document.getElementById("attachmentTitle").textContent = file.name;
    document.getElementById("attachmentCounter").textContent =
        `${index + 1} / ${files.length}`;
    renderViewer(file);
    renderAttachmentDetails(file);
    document.getElementById("attachmentPrevBtn").disabled = index === 0;
    document.getElementById("attachmentNextBtn").disabled =
        index === files.length - 1;
    attachmentOffcanvas?.show();
}

function renderViewer(file) {
    const container = document.getElementById("attachmentViewer");
    const { mime = "", path = "" } = file;
    if (!path) {
        container.innerHTML =
            '<div class="text-muted"><i class="bi bi-file-earmark-fill fs-1"></i><br>No file available</div>';
        return;
    }
    if (mime.startsWith("image/"))
        container.innerHTML = `<img src="${escapeHtml(path)}" style="max-width:100%;max-height:70vh;">`;
    else if (mime === "application/pdf")
        container.innerHTML = `<iframe src="${escapeHtml(path)}" style="width:100%;height:70vh;border:none;"></iframe>`;
    else
        container.innerHTML = `<div class="text-center"><i class="bi bi-file-earmark-fill fs-1 d-block mb-3" style="color:var(--gray-5);"></i><p class="text-muted">Preview not available.</p></div>`;
}

function renderAttachmentDetails(file) {
    document.getElementById("attachmentDetails").innerHTML = [
        { label: "File Name", value: file.name },
        { label: "File Size", value: file.size },
        { label: "File Type", value: file.mime || "Unknown" },
    ]
        .map(
            (f) => `
        <div class="detail-row">
            <span class="detail-label">${escapeHtml(f.label)}</span>
            <span class="detail-value">${escapeHtml(f.value)}</span>
        </div>
    `,
        )
        .join("");
}

// ---------------------------------------------------------------
// Permit detail offcanvas
// ---------------------------------------------------------------

let permitDetailOffcanvas = null;

function initPermitDetailOffcanvas() {
    const el = document.getElementById("permitDetailOffcanvas");
    if (el)
        permitDetailOffcanvas = new bootstrap.Offcanvas(el, {
            backdrop: true,
            keyboard: true,
            scroll: false,
        });
}

function openPermitDetail(permitNumber) {
    const permit = PERMITS.find((p) => p.permit_number === permitNumber);
    if (!permit) return;

    const decisionCfg = PERMIT_DECISION_CONFIG[permit.decision] || {
        en: "Pending",
        color: "secondary",
    };
    const detail = permit.consignment_detail;

    document.getElementById("permitDetailOffcanvasLabel").textContent =
        permit.permit_number;
    const badge = document.getElementById("pdBadge");
    badge.textContent = decisionCfg.en;
    badge.className = `ipv-badge ms-2 is-${decisionCfg.color}`;

    document.getElementById("pdDetailsContent").innerHTML = `
        <div class="pd-section-label">Consignment Info</div>
        <div class="pd-info-grid">
            ${[
                { label: "Category", value: detail.category },
                { label: "Item", value: detail.item_name },
                { label: "Usage", value: detail.usage },
                { label: "Purpose", value: permit.purpose },
                {
                    label: "Quantity",
                    value: `${permit.quantity.toLocaleString()} ${permit.unit_measurement}`,
                },
                { label: "Declared Value", value: `RM ${money(permit.value)}` },
            ]
                .map(
                    (r) => `
                <div class="pd-info-cell">
                    <div class="pd-cell-label">${escapeHtml(r.label)}</div>
                    <div class="pd-cell-value">${escapeHtml(r.value)}</div>
                </div>
            `,
                )
                .join("")}
        </div>

        <div class="pd-section-label mt-4">Officer Decision</div>
        <div class="ipv-permit-remark is-${decisionCfg.color}">
            <i class="bi bi-${permit.decision === "approved" ? "check-circle" : "x-circle"}"></i>
            <span>${escapeHtml(permit.remark)}</span>
            ${permit.rejectionReason ? `<div style="margin-top:.3rem;font-size:.78rem;opacity:.85;font-style:italic;">"${escapeHtml(permit.rejectionReason)}"</div>` : ""}
        </div>

        <div class="pd-section-label mt-4">Import Conditions</div>
        ${permit.conditions
            .map(
                (c) => `
            <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
        `,
            )
            .join("")}

        <div class="pd-section-label mt-4">Attachments (${permit.attachments.length})</div>
        <div class="ipv-attach-list" id="pd-attach-${permit.permit_number}"></div>

        ${
            permit.decision === "approved"
                ? `
            <div class="pd-section-label mt-4">Payment</div>
            <div class="ps-oc-payment-row">
                <div class="ps-oc-fee-chip">
                    <i class="bi bi-tag"></i> Processing fee: <strong>RM ${money(ITEM_PRICE)}</strong>
                </div>
                <span class="ipv-badge is-${permit.selected ? "success" : "secondary"}">
                    ${permit.selected ? "Selected for payment" : "Not selected"}
                </span>
            </div>
        `
                : ""
        }
    `;

    renderAttachmentList(
        document.getElementById(`pd-attach-${permit.permit_number}`),
        permit.attachments,
        permit.attachments.length,
    );

    // Activity tab
    document.getElementById("pdActivityTimeline").innerHTML =
        (permit.permitActivity || [])
            .map((entry) => {
                const stageCfg =
                    STAGE_CONFIG[entry.stage] || STAGE_CONFIG.email;
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
            })
            .join("") ||
        '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity yet.</p></div>';

    bootstrap.Tab.getOrCreateInstance(
        document.getElementById("pd-details-tab"),
    )?.show();
    permitDetailOffcanvas?.show();
}

// ---------------------------------------------------------------
// Sidebar
// ---------------------------------------------------------------

function renderSidebar() {
    document.getElementById("psAppType").textContent = APPLICATION.type;
    document.getElementById("psAppId").textContent = APPLICATION.application_id;
    document.getElementById("psSubmittedBy").textContent =
        APPLICATION.submitted_by;
    document.getElementById("psCreatedAt").textContent =
        `Application submitted on ${APPLICATION.submitted_at}`;
    document.getElementById("psTags").innerHTML = APPLICATION.tags
        .map(
            (tag) =>
                `<span class="ipv-tag is-${tag.color}">${escapeHtml(tag.label)}</span>`,
        )
        .join("");

    const total = PERMITS.reduce((s, p) => s + p.value, 0);
    document.getElementById("psTotalValue").textContent = `RM ${money(total)}`;

    document.getElementById("psImporterBlock").innerHTML = partyBlockHtml(
        APPLICATION.importer,
        "Importer",
    );
    const exporterEl = document.getElementById("psExporterBlock");
    exporterEl.innerHTML = partyBlockHtml(APPLICATION.exporter, "Exporter");
    exporterEl.classList.add("is-exporter");

    renderAttachmentList(
        document.getElementById("psAppAttachments"),
        APPLICATION.attachments,
        3,
    );
}

function partyBlockHtml(party, label) {
    const initial = (party.name || "?").charAt(0).toUpperCase();
    return `
        <div class="ipv-party-header">
            <div class="ipv-party-avatar">${initial}</div>
            <div>
                <div class="ipv-party-name">${escapeHtml(party.name)}</div>
                <div class="ipv-party-sub">${label}</div>
            </div>
        </div>
        <div class="ipv-contact-row">
            <div class="ipv-contact-icon"><i class="bi bi-telephone"></i></div>
            <div><div class="ipv-contact-label">Phone</div><div class="ipv-contact-value">${escapeHtml(party.phone)}</div></div>
        </div>
        <div class="ipv-contact-row">
            <div class="ipv-contact-icon"><i class="bi bi-envelope"></i></div>
            <div><div class="ipv-contact-label">Email</div><div class="ipv-contact-value">${escapeHtml(party.email)}</div></div>
        </div>
        <div class="ipv-contact-row">
            <div class="ipv-contact-icon"><i class="bi bi-geo-alt"></i></div>
            <div><div class="ipv-contact-label">Address</div>
            <div class="ipv-contact-value">${escapeHtml(party.address)}, ${escapeHtml(party.country)}</div></div>
        </div>
    `;
}

// ---------------------------------------------------------------
// Stage stepper + info row
// ---------------------------------------------------------------

function renderStageStepper() {
    const el = document.getElementById("psStageStepper");
    const currentIdx = STAGE_ORDER.indexOf(APPLICATION.status);

    el.innerHTML = STAGE_ORDER.map((key, i) => {
        const cfg = STAGE_CONFIG[key];
        let cls = "is-pending";
        if (i < currentIdx) cls = "is-complete";
        else if (i === currentIdx) cls = "is-current";
        return `<div class="ipv-stage-step ${cls}">${cfg.en}</div>`;
    }).join("");

    document.getElementById("psStatusLabel").textContent =
        (STAGE_CONFIG[APPLICATION.status] || {}).en || APPLICATION.status;
    document.getElementById("psStatusDuration").textContent =
        `In this status for ${APPLICATION.status_duration}`;
    document.getElementById("psAssignedOfficer").textContent =
        APPLICATION.assigned_officer;
    document.getElementById("psSlaDue").textContent = APPLICATION.sla_due;
}

// ---------------------------------------------------------------
// Transport / Condition / Activity tabs
// ---------------------------------------------------------------

function renderTransportDetails() {
    document.getElementById("psTransportDetails").innerHTML = [
        { icon: "bi-calendar-event", label: "ETA", value: APPLICATION.eta },
        {
            icon: "bi-truck",
            label: "Transport Type",
            value: APPLICATION.transport_type,
        },
        {
            icon: "bi-geo-alt",
            label: "Entry Point",
            value: APPLICATION.entry_point,
        },
        {
            icon: "bi-info-circle",
            label: "Entry Point Notes",
            value: APPLICATION.entry_point_description,
        },
    ]
        .map(
            (r) => `
        <div class="ipv-detail-row">
            <div class="ipv-detail-icon"><i class="bi ${r.icon}"></i></div>
            <span class="ipv-detail-label">${r.label}</span>
            <span class="ipv-detail-value">${escapeHtml(r.value)}</span>
        </div>
    `,
        )
        .join("");
}

function renderConditionTab() {
    document.getElementById("psConditionList").innerHTML = PERMITS.map(
        (permit) => `
        <div class="ipv-condition-card">
            <div class="ipv-condition-card-head">
                <div>
                    <div class="ipv-condition-card-title">${escapeHtml(permit.consignment_detail.item_name)}</div>
                    <div class="ipv-condition-card-sub">${escapeHtml(permit.consignment_detail.category)} &middot; #${escapeHtml(permit.permit_number)}</div>
                </div>
            </div>
            ${permit.conditions
                .map(
                    (c) => `
                <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
            `,
                )
                .join("")}
        </div>
    `,
    ).join("");
}

function renderActivityTimeline() {
    const el = document.getElementById("psActivityTimeline");
    if (!ACTIVITY_LOG.length) {
        el.innerHTML =
            '<div class="ipv-empty-state"><i class="bi bi-clock-history"></i><p>No activity yet.</p></div>';
        return;
    }
    el.innerHTML = ACTIVITY_LOG.map((entry) => {
        const cfg = STAGE_CONFIG[entry.stage] || STAGE_CONFIG.email;
        return `
            <div class="ipv-timeline-item">
                <div class="ipv-timeline-icon is-${cfg.color}"><i class="bi ${cfg.icon}"></i></div>
                <div class="ipv-timeline-body">
                    <div>
                        <div class="ipv-timeline-title">${escapeHtml(entry.title)}</div>
                        <p class="ipv-timeline-desc">${escapeHtml(entry.description)}</p>
                    </div>
                    <span class="ipv-timeline-time">${escapeHtml(entry.time)}</span>
                </div>
            </div>
        `;
    }).join("");
}

// ---------------------------------------------------------------
// Permit accordion with per-item checkboxes
// ---------------------------------------------------------------

function permitStatusBadge(permit) {
    if (permit.decision === "approved") {
        const selClass = permit.selected ? "is-success" : "is-secondary";
        const selLabel = permit.selected ? "Selected" : "Approved";
        return `<span class="ipv-badge ${selClass}">${selLabel}</span>`;
    }
    return `<span class="ipv-badge is-danger">Rejected</span>`;
}

const PERMIT_STATUS_CONFIG = {
    queued: { en: "Queued for review", color: "gray", payable: false },
    approved: {
        en: "Approved · Ready to pay",
        color: "success",
        payable: true,
    },
    rejected: { en: "Rejected", color: "danger", payable: false },
    pending_payment: {
        en: "Pending payment authorization",
        color: "warning",
        payable: false,
    },
    issued: { en: "Issued · Active", color: "teal", payable: false },
    payment_failed: { en: "Payment failed", color: "orange", payable: false },
};

function permitStatusPill(permit) {
    const cfg =
        PERMIT_STATUS_CONFIG[permit.decision] || PERMIT_STATUS_CONFIG.queued;
    if (permit.decision === "approved" && permit.selected) {
        return `<span class="ps-status-pill is-success"><i class="bi bi-check-circle-fill"></i> Selected to pay</span>`;
    }
    return `<span class="ps-status-pill is-${cfg.color}">${escapeHtml(cfg.en)}</span>`;
}

function renderPermitAccordion() {
    document.getElementById("psPermitCount").textContent = PERMITS.length;
    const el = document.getElementById("psPermitAccordion");

    el.innerHTML = PERMITS.map((permit) => {
        const detail = permit.consignment_detail;
        const isApproved = permit.decision === "approved";
        const isRejected = permit.decision === "rejected";
        const checked = permit.selected ? "checked" : "";

        return `
            <div class="ipv-permit-item ps-row ${permit.selected ? "is-open ps-item-selected" : ""} ${isRejected ? "ps-item-rejected" : ""}"
                 data-permit="${escapeHtml(permit.permit_number)}">

                <div class="ipv-permit-header ps-header">

                    <label class="ps-check-label" title="${isRejected ? "Rejected — fix and resubmit to pay" : "Toggle selection"}"
                           onclick="event.stopPropagation()">
                        <input type="checkbox"
                               class="ps-native-check"
                               data-permit="${escapeHtml(permit.permit_number)}"
                               ${checked}
                               ${isRejected ? "disabled" : ""}>
                        <span class="ps-checkmark ${permit.selected ? "is-checked" : ""} ${isRejected ? "is-disabled" : ""}"></span>
                    </label>

                    <div class="ps-header-main">
                        <div class="ps-header-title-row">
                            <span class="ipv-permit-id">#${escapeHtml(permit.permit_number)}</span>
                            <span class="ipv-permit-name">${escapeHtml(detail.item_name)}</span>
                        </div>
                        <div class="ps-header-meta-row">
                            ${permitStatusPill(permit)}
                            <span class="ps-meta-sep">·</span>
                            <span class="ps-meta-text">RM ${money(permit.value)} declared</span>
                            ${isApproved ? `<span class="ps-meta-sep">·</span><span class="ps-meta-text">RM ${ITEM_PRICE} fee</span>` : ""}
                        </div>
                    </div>

                    ${
                        isRejected
                            ? `
                        <button type="button" class="ps-edit-btn" data-permit-number="${escapeHtml(permit.permit_number)}" title="Edit and resubmit">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                    `
                            : `
                        <button type="button" class="ipv-view-detail-btn"
                            data-permit-number="${escapeHtml(permit.permit_number)}" title="Full details">
                            <i class="bi bi-arrow-up-right-square"></i>
                        </button>
                    `
                    }
                    <i class="bi bi-chevron-down ipv-chevron"></i>
                </div>

                <div class="ipv-permit-body">

                    ${
                        isRejected
                            ? `
                        <div class="ps-rejected-banner">
                            <i class="bi bi-x-circle-fill"></i>
                            <div>
                                <div class="ps-rejected-banner-title">This permit was rejected</div>
                                <div class="ps-rejected-banner-reason">"${escapeHtml(permit.rejectionReason)}"</div>
                                <button type="button" class="ps-edit-inline-btn" data-permit-number="${escapeHtml(permit.permit_number)}">
                                    <i class="bi bi-pencil-square"></i> Edit item and resubmit for review
                                </button>
                            </div>
                        </div>
                    `
                            : ""
                    }

                    <div class="ipv-permit-grid">
                        <div class="ipv-permit-meta"><span class="meta-label">Category</span><span class="meta-value">${escapeHtml(detail.category)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Usage</span><span class="meta-value">${escapeHtml(detail.usage)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Purpose</span><span class="meta-value">${escapeHtml(permit.purpose)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Quantity</span><span class="meta-value">${permit.quantity.toLocaleString()} ${escapeHtml(permit.unit_measurement)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Value</span><span class="meta-value">RM ${money(permit.value)}</span></div>
                        <div class="ipv-permit-meta"><span class="meta-label">Permit No.</span><span class="meta-value">${escapeHtml(permit.permit_number)}</span></div>
                    </div>

                    <div class="ipv-permit-subsection-title">Import conditions</div>
                    ${permit.conditions
                        .map(
                            (c) => `
                        <div class="ipv-condition-item"><i class="bi bi-check-circle"></i><span>${escapeHtml(c)}</span></div>
                    `,
                        )
                        .join("")}

                    <div class="ipv-permit-subsection-title">Attachments (${permit.attachments.length})</div>
                    <div class="ipv-attach-list" id="attachList-${escapeHtml(permit.permit_number)}"></div>

                </div>
            </div>
        `;
    }).join("");

    PERMITS.forEach((permit) => {
        renderAttachmentList(
            document.getElementById(`attachList-${permit.permit_number}`),
            permit.attachments,
            2,
        );
    });

    let _accordionInit = false;
    if (!_accordionInit) {
        _accordionInit = true;
        document
            .getElementById("psPermitAccordion")
            .addEventListener("click", (e) => {
                const viewBtn = e.target.closest(".ipv-view-detail-btn");
                if (viewBtn) {
                    e.stopPropagation();
                    openPermitDetail(viewBtn.dataset.permitNumber);
                    return;
                }

                const editBtn = e.target.closest(
                    ".ps-edit-btn, .ps-edit-inline-btn",
                );
                if (editBtn) {
                    e.stopPropagation();
                    openEditRejectedPermit(editBtn.dataset.permitNumber);
                    return;
                }

                if (e.target.closest(".ps-check-label")) return;

                const header = e.target.closest(".ipv-permit-header");
                if (header)
                    header
                        .closest(".ipv-permit-item")
                        ?.classList.toggle("is-open");
            });

        document
            .getElementById("psPermitAccordion")
            .addEventListener("change", (e) => {
                const cb = e.target.closest(".ps-native-check");
                if (!cb || cb.disabled) return;
                togglePermitSelection(cb.dataset.permit, cb.checked);
            });
    }

    updateSummary();
}

// ---------------------------------------------------------------
// Toggle selection + live updates
// ---------------------------------------------------------------

function togglePermitSelection(permitNumber, checked) {
    const permit = PERMITS.find((p) => p.permit_number === permitNumber);
    if (!permit || permit.decision !== "approved") return;
    permit.selected = checked;

    // Update the row visuals without a full re-render
    const item = document.querySelector(
        `.ipv-permit-item[data-permit="${permitNumber}"]`,
    );
    if (item) {
        item.classList.toggle("ps-item-selected", checked);
        const checkmark = item.querySelector(".ps-checkmark");
        if (checkmark) checkmark.classList.toggle("is-checked", checked);
        const badge = item.querySelector(".ipv-badge");
        if (badge) {
            badge.textContent = checked ? "Selected" : "Approved";
            badge.className = `ipv-badge is-${checked ? "success" : "secondary"}`;
        }
        const cb = item.querySelector(".ps-native-check");
        if (cb) cb.checked = checked;
    }

    updateSummary();
}

function updateSummary() {
    const approvedPermits = PERMITS.filter((p) => p.decision === "approved");
    const rejectedPermits = PERMITS.filter((p) => p.decision === "rejected");
    const selectedPermits = PERMITS.filter((p) => p.selected);
    const count = selectedPermits.length;
    const total = count * ITEM_PRICE;
    const fillPct = approvedPermits.length
        ? (count / approvedPermits.length) * 100
        : 0;

    document.getElementById("psCountTotal").textContent = PERMITS.length;
    document.getElementById("psCountSelected").textContent = count;
    document.getElementById("psCountRejected").textContent =
        rejectedPermits.length;
    document.getElementById("psFillSelected").style.width = `${fillPct}%`;
    document.getElementById("psProgressHint").textContent =
        count > 0
            ? `${count} item${count > 1 ? "s" : ""} selected — RM ${money(total)} total.`
            : "Tick the approved items you wish to pay for.";

    document.getElementById("psFeeCount").textContent = count;
    document.getElementById("psFeeTotalLabel").textContent =
        `RM ${money(total)}`;

    document.getElementById("psBarCount").textContent = count;
    document.getElementById("psBarPlural").textContent = count !== 1 ? "s" : "";
    document.getElementById("psBarTotal").textContent = money(total);

    const payBtn = document.getElementById("psPayBtn");
    const payBar = document.getElementById("psPayBar");

    payBtn.disabled = count === 0;
    payBar.classList.toggle("ps-pay-bar-visible", count > 0);
}

// ---------------------------------------------------------------
// Edit rejected permit — re-open for correction & resubmission
// ---------------------------------------------------------------

let editPermitModal = null;
let editingPermitNumber = null;

function ensureEditModal() {
    if (document.getElementById("psEditPermitModal")) return;

    const modalHtml = `
    <div class="modal fade" id="psEditPermitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
            <div class="modal-content ipv-reject-modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>
                        Edit permit — <span id="psEditPermitNo">—</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="ps-edit-reason-box" id="psEditReasonBox"></div>

                    <div class="ipa-form-grid">
                        <div class="ipa-field">
                            <label>Quantity <span class="ipa-required">*</span></label>
                            <input type="number" min="0" class="ipa-input" id="psEditQty" placeholder="0">
                        </div>
                        <div class="ipa-field">
                            <label>Unit <span class="ipa-required">*</span></label>
                            <input type="text" class="ipa-input" id="psEditUnit" placeholder="e.g. KG">
                        </div>
                        <div class="ipa-field">
                            <label>Declared Value (RM) <span class="ipa-required">*</span></label>
                            <input type="number" min="0" step="0.01" class="ipa-input" id="psEditValue" placeholder="0.00">
                        </div>
                    </div>

                    <div class="ipa-field mt-3">
                        <label>Note to officer (optional)</label>
                        <textarea class="ipa-input" id="psEditNote" rows="3"
                            placeholder="Explain what you corrected, e.g. updated catch certificate attached."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ipv-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="ipv-btn-verify" id="psEditSaveBtn">
                        <i class="bi bi-send"></i> Resubmit for review
                    </button>
                </div>
            </div>
        </div>
    </div>
`;
    document.body.insertAdjacentHTML("beforeend", modalHtml);
    editPermitModal = new bootstrap.Modal(
        document.getElementById("psEditPermitModal"),
    );

    document
        .getElementById("psEditSaveBtn")
        .addEventListener("click", saveEditedPermit);
}

function openEditRejectedPermit(permitNumber) {
    const permit = PERMITS.find((p) => p.permit_number === permitNumber);
    if (!permit) return;

    ensureEditModal();
    editingPermitNumber = permitNumber;

    document.getElementById("psEditPermitNo").textContent = permitNumber;
    document.getElementById("psEditReasonBox").innerHTML = `
        <i class="bi bi-info-circle"></i>
        <div>
            <div class="ps-edit-reason-title">Why this was rejected</div>
            <div class="ps-edit-reason-text">${escapeHtml(permit.rejectionReason || "No reason provided.")}</div>
        </div>
    `;
    document.getElementById("psEditQty").value = permit.quantity;
    document.getElementById("psEditUnit").value = permit.unit_measurement;
    document.getElementById("psEditValue").value = permit.value;
    document.getElementById("psEditNote").value = "";

    editPermitModal.show();
}

function saveEditedPermit() {
    const permit = PERMITS.find((p) => p.permit_number === editingPermitNumber);
    if (!permit) return;

    const qty =
        parseFloat(document.getElementById("psEditQty").value) ||
        permit.quantity;
    const unit =
        document.getElementById("psEditUnit").value.trim() ||
        permit.unit_measurement;
    const value =
        parseFloat(document.getElementById("psEditValue").value) ||
        permit.value;
    const note = document.getElementById("psEditNote").value.trim();

    permit.quantity = qty;
    permit.unit_measurement = unit;
    permit.value = value;
    permit.decision = "queued"; // back into review
    permit.selected = false;
    permit.rejectionReason = null;
    permit.remark = note
        ? `Resubmitted by applicant. Note: "${note}"`
        : "Resubmitted by applicant for re-review.";

    permit.permitActivity = permit.permitActivity || [];
    permit.permitActivity.push({
        stage: "technical_review",
        title: "Resubmitted by applicant",
        description: permit.remark,
        time: new Date().toLocaleString("en-MY", {
            day: "numeric",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        }),
    });

    editPermitModal.hide();
    renderPermitAccordion();
}

// ---------------------------------------------------------------
// Proceed to payment — store selection + navigate
// ---------------------------------------------------------------

function handlePayment() {
    const selected = PERMITS.filter((p) => p.selected);
    if (!selected.length) return;

    // Store selection for the payment page to read
    const payload = {
        application_id: APPLICATION.application_id,
        permits: selected.map((p) => ({
            permit_number: p.permit_number,
            item_name: p.consignment_detail.item_name,
            category: p.consignment_detail.category,
            value: p.value,
            fee: ITEM_PRICE,
        })),
        total_fee: selected.length * ITEM_PRICE,
    };
    sessionStorage.setItem("ipa_payment_selection", JSON.stringify(payload));
    window.location.href = "/public/payment";
}

// ---------------------------------------------------------------
// Tabs — using data-ipv-tab to match the officer/clerk pattern
// ---------------------------------------------------------------

function initTabs() {
    const tabs = document.querySelectorAll(".ipv-tabnav-item");
    const panes = document.querySelectorAll(".ipv-tabpane");
    tabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            const target = tab.dataset.ipvTab;
            tabs.forEach((t) => t.classList.toggle("is-active", t === tab));
            panes.forEach((p) =>
                p.classList.toggle("is-active", p.dataset.ipvPane === target),
            );
        });
    });
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

let _initialized = false;

function init() {
    if (_initialized) return;
    _initialized = true;
    if (!document.getElementById("psAppId")) return;

    renderSidebar();
    renderStageStepper();
    renderTransportDetails();
    renderConditionTab();
    renderActivityTimeline();
    renderPermitAccordion();
    initTabs();
    initOffcanvas();
    initPermitDetailOffcanvas();

    document
        .getElementById("psPayBtn")
        .addEventListener("click", handlePayment);
}

document.addEventListener("DOMContentLoaded", init);
