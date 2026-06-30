/**
 * paymentReceipt.js
 * ------------------------------------------------------------------
 * Read-only receipt page shown after a successful payment.
 *
 * Reads the just-completed payment from sessionStorage (key:
 * 'ipa_last_receipt'), which applicantPayment.js should write right
 * before redirecting here. Falls back to demo data if nothing is
 * found (useful for direct preview during development).
 *
 * Expected sessionStorage payload shape:
 * {
 *   order_number, application_id, payment_reference, payment_date,
 *   payment_method, status,
 *   payer: { name, email, phone },
 *   items: [{ permit_number, item_name, category, quantity, unit, fee }],
 *   processing_fee_total, subtotal, grand_total
 * }
 */

// ---------------------------------------------------------------
// Demo fallback data
// ---------------------------------------------------------------

const DEMO_RECEIPT = {
    order_number:       'ORD-20250516-0091',
    application_id:     'IP-2025-00456',
    payment_reference:  'PAY-M9X7K2QF',
    payment_date:       '16 May 2025, 4:32 PM',
    payment_method:     'BayuPay',
    status:             'paid',
    payer: {
        name:  'Tan Wei Ling',
        email: 'tanweiling@email.com',
        phone: '012-345 6789',
    },
    items: [
        { permit_number: 'PMT-1201', item_name: 'Fresh Fruit — Corn',       category: 'Fresh Produce',  quantity: 1200, unit: 'KG', fee: 15 },
        { permit_number: 'PMT-1203', item_name: 'Frozen Seafood — Tilapia', category: 'Frozen Seafood', quantity: 4500, unit: 'KG', fee: 15 },
        { permit_number: 'PMT-1205', item_name: 'Canned Pineapple',         category: 'Processed Food', quantity: 2000, unit: 'KG', fee: 15 },
    ],
};

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function escapeHtml(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',
    }[c]));
}

function money(n) {
    return Number(n || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function genReceiptNo(orderNumber) {
    return `RCT-${(orderNumber || 'XXXX').replace(/[^0-9A-Z]/gi, '').slice(-8)}`;
}

// ---------------------------------------------------------------
// Load receipt data
// ---------------------------------------------------------------

function loadReceipt() {
    try {
        const raw = sessionStorage.getItem('ipa_last_receipt');
        if (raw) return JSON.parse(raw);
    } catch {}
    return DEMO_RECEIPT;
}

// ---------------------------------------------------------------
// Render: header / reference grid
// ---------------------------------------------------------------

function renderHeader(receipt) {
    document.getElementById('aprReceiptNo').textContent     = genReceiptNo(receipt.order_number);
    document.getElementById('aprPaymentRef').textContent    = receipt.payment_reference;
    document.getElementById('aprOrderNo').textContent       = receipt.order_number;
    document.getElementById('aprAppId').textContent         = receipt.application_id;
    document.getElementById('aprPaymentDate').textContent   = receipt.payment_date;
    document.getElementById('aprPaymentMethod').textContent = receipt.payment_method;

    const badge = document.getElementById('aprStatusBadge');
    const isPaid = receipt.status === 'paid';
    badge.textContent = isPaid ? 'Paid' : 'Processing';
    badge.className   = `apr-status-badge ${isPaid ? 'is-paid' : 'is-processing'}`;
}

// ---------------------------------------------------------------
// Render: payer details
// ---------------------------------------------------------------

function renderPayer(receipt) {
    document.getElementById('aprPayerName').textContent  = receipt.payer?.name  || '—';
    document.getElementById('aprPayerEmail').textContent = receipt.payer?.email || '—';
    document.getElementById('aprPayerPhone').textContent = receipt.payer?.phone || '—';
}

// ---------------------------------------------------------------
// Render: itemised list + totals
// ---------------------------------------------------------------

function renderItems(receipt) {
    const items = receipt.items || [];

    document.getElementById('aprItemTable').innerHTML = `
        <div class="apr-item-row apr-item-row-head">
            <span>Permit</span>
            <span>Item</span>
            <span class="apr-col-right">Fee</span>
        </div>
        ${items.map(item => `
            <div class="apr-item-row">
                <span class="apr-item-permit-no">${escapeHtml(item.permit_number)}</span>
                <span class="apr-item-name-cell">
                    <span class="apr-item-name">${escapeHtml(item.item_name)}</span>
                    <span class="apr-item-meta">
                        ${escapeHtml(item.category)} &middot;
                        ${Number(item.quantity).toLocaleString()} ${escapeHtml(item.unit)}
                    </span>
                </span>
                <span class="apr-col-right apr-item-fee">RM ${money(item.fee)}</span>
            </div>
        `).join('')}
    `;

    const subtotal = items.reduce((s, i) => s + Number(i.fee || 0), 0);
    const processingFee = receipt.processing_fee_total ?? 0; // separate platform fee, if any
    const grandTotal = receipt.grand_total ?? (subtotal + processingFee);

    document.getElementById('aprSubtotal').textContent      = `RM ${money(subtotal)}`;
    document.getElementById('aprProcessingFee').textContent = `RM ${money(processingFee)}`;
    document.getElementById('aprGrandTotal').textContent    = `RM ${money(grandTotal)}`;
}

// ---------------------------------------------------------------
// Render: what happens next
// ---------------------------------------------------------------

function renderNextSteps() {
    const steps = [
        {
            icon: 'bi-hourglass-split',
            title: 'Bank authorization',
            desc: 'Your payment is being verified by the bank. This usually takes a few minutes, but can take up to 1 business day.',
        },
        {
            icon: 'bi-patch-check',
            title: 'Permit issuance',
            desc: 'Once payment is confirmed, each paid permit will move to Issued / Active status and become available for download.',
        },
        {
            icon: 'bi-bell',
            title: 'Notification',
            desc: 'You will receive an email and an in-app notification as soon as your permits are ready.',
        },
    ];

    document.getElementById('aprNextSteps').innerHTML = steps.map(s => `
        <div class="apr-next-step">
            <div class="apr-next-step-icon"><i class="bi ${s.icon}"></i></div>
            <div>
                <div class="apr-next-step-title">${escapeHtml(s.title)}</div>
                <div class="apr-next-step-desc">${escapeHtml(s.desc)}</div>
            </div>
        </div>
    `).join('');
}

// ---------------------------------------------------------------
// Actions — print, download, view application
// ---------------------------------------------------------------

function initActions(receipt) {
    document.getElementById('aprPrintBtn')?.addEventListener('click', () => {
        window.print();
    });

    document.getElementById('aprDownloadBtn')?.addEventListener('click', () => {
        // Placeholder until a real PDF endpoint exists — falls back to print dialog,
        // where the user can choose "Save as PDF".
        window.print();
    });

    const viewAppBtn = document.getElementById('aprViewAppBtn');
    if (viewAppBtn && receipt.application_id) {
        viewAppBtn.href = `/public/view_import_permit/${encodeURIComponent(receipt.application_id)}`;
    }
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

let _initialized = false;

function init() {
    if (_initialized) return;
    _initialized = true;
    if (!document.getElementById('aprReceiptCard')) return;

    const receipt = loadReceipt();

    renderHeader(receipt);
    renderPayer(receipt);
    renderItems(receipt);
    renderNextSteps();
    initActions(receipt);

    // Clear the one-time receipt payload so refreshing doesn't replay stale state
    // (keep this commented if you want the receipt to persist on refresh)
    // sessionStorage.removeItem('ipa_last_receipt');
}

document.addEventListener('DOMContentLoaded', init);