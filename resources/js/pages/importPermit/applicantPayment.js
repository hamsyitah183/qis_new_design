/**
 * applicantPayment.js
 * ------------------------------------------------------------------
 * Payment page for a single, already-created Order (App\Models\Order).
 * There's no permit-selection UI here anymore — order_details and
 * payment_amount are fixed the moment the order was created upstream
 * (wherever the user picked which approved permits to pay for). This
 * page only displays that order and collects a payment method.
 *
 * Two payment methods: BayuPay (default) and YonoPay. Each has a logo
 * (loaded from window.PAYMENT_ASSET_BASE, set by the blade via
 * asset('images/payment')) and an info icon opening a step-by-step
 * "how to pay" modal. The step images are placeholders — swap
 * .apy-howto-step-image's contents for a real <img> per step once
 * those screenshots exist.
 */

// ---------------------------------------------------------------
// Dummy data — a single order, already created
// ---------------------------------------------------------------

const ORDER = {
    order_number:     'ORD-20250516-0091',
    application_id:   'IP-2025-00456',
    application_type: 'Import Permit',
    status:           'Pending Payment',
    name:             'Tan Wei Ling',
    email:            'tanweiling@email.com',
    phone:            '012-345 6789',
    payment_amount:   45.00, // authoritative total — set when the order was created
    payment_type:     null,  // not yet chosen
    order_details: [
        { permit_number: 'PMT-1201', item_name: 'Fresh Fruit — Corn',          category: 'Fresh Produce',   quantity: 1200, unit: 'KG',   fee: 15 },
        { permit_number: 'PMT-1203', item_name: 'Frozen Seafood — Tilapia',    category: 'Frozen Seafood',  quantity: 4500, unit: 'KG',   fee: 15 },
        { permit_number: 'PMT-1205', item_name: 'Canned Pineapple',            category: 'Processed Food',  quantity: 2000, unit: 'KG',   fee: 15 },
    ],
};

const IMPORTER = {
    name: 'Borneo Fresh Trading Sdn Bhd',
    phone: '(088) 244 511',
    email: 'admin@borneofresh.my',
    address: 'Lot 12, Kolombong Industrial Park, 88450 Kota Kinabalu',
    country: 'Malaysia',
};

const EXPORTER = {
    name: 'Golden Harvest Pte Ltd',
    phone: '+65 6221 4480',
    email: 'export@goldenharvest.sg',
    address: '21 Tanjong Penjuru Crescent',
    country: 'Singapore',
};

// ---------------------------------------------------------------
// Payment methods — BayuPay selected by default
// ---------------------------------------------------------------

const PAYMENT_METHODS = [
    {
        key: 'bayupay',
        label: 'BayuPay',
        logo: 'bayupay.png',
        steps: [
            'Select BayuPay and click "Proceed to Payment".',
            "You'll be redirected to the BayuPay gateway — choose your bank from the list.",
            'Log in to your online banking and review the payment details.',
            'Approve the transaction with your TAC / OTP.',
            "You'll be redirected back here automatically once payment is confirmed.",
        ],
    },
    {
        key: 'yonopay',
        label: 'YonoPay',
        logo: 'Yonopay.png',
        steps: [
            'Select YonoPay and click "Proceed to Payment".',
            'A QR code will be generated on screen.',
            'Open the YonoPay app and scan the QR code.',
            'Confirm the amount and approve the payment in the app.',
            'This page will update automatically once payment is detected.',
        ],
    },
];

let selectedMethod = 'bayupay';

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function escapeHtml(v) {
    return String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

function money(n) {
    return Number(n || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function genPaymentRef() {
    return 'PAY-' + Date.now().toString(36).toUpperCase();
}

function assetBase() {
    return (window.PAYMENT_ASSET_BASE || '/images/payment').replace(/\/$/, '');
}

function logoUrl(filename) {
    return `${assetBase()}/${filename}`;
}

function methodByKey(key) {
    return PAYMENT_METHODS.find((m) => m.key === key);
}

// ---------------------------------------------------------------
// Reference strip
// ---------------------------------------------------------------

function renderRefStrip() {
    document.getElementById('apyRefOrderNo').textContent = ORDER.order_number;
    document.getElementById('apyRefAppId').textContent   = ORDER.application_id;
    document.getElementById('apyRefStatus').textContent  = ORDER.status;
    document.getElementById('apyRefAmount').textContent  = `RM ${money(ORDER.payment_amount)}`;
}

// ---------------------------------------------------------------
// Importer / Exporter
// ---------------------------------------------------------------

function partyCardHtml(party, label) {
    const initial = (party.name || '?').charAt(0).toUpperCase();
    return `
        <div class="apy-party-card">
            <div class="apy-party-header">
                <div class="apy-party-avatar">${initial}</div>
                <div>
                    <div class="apy-party-name">${escapeHtml(party.name)}</div>
                    <div class="apy-party-sub">${label}</div>
                </div>
            </div>
            <div class="apy-party-row"><i class="bi bi-telephone"></i> ${escapeHtml(party.phone)}</div>
            <div class="apy-party-row"><i class="bi bi-envelope"></i> ${escapeHtml(party.email)}</div>
            <div class="apy-party-row"><i class="bi bi-geo-alt"></i> ${escapeHtml(party.address)}, ${escapeHtml(party.country)}</div>
        </div>
    `;
}

function renderParties() {
    document.getElementById('apyParties').innerHTML =
        partyCardHtml(IMPORTER, 'Importer') + partyCardHtml(EXPORTER, 'Exporter');
}

// ---------------------------------------------------------------
// Order items — read only, no selection
// ---------------------------------------------------------------

function renderOrderList() {
    console.log('item', ORDER.order_details)
    document.getElementById('apyOrderList').innerHTML = ORDER.order_details.map((item) => `
        <div class="apy-order-row">
            <div class="apy-order-row-icon"><i class="bi bi-box-seam"></i></div>
            <div class="apy-order-row-info">
                <div class="apy-order-row-name">${escapeHtml(item.item_name)}</div>
                <div class="apy-order-row-meta">
                    <span class="apy-permit-no">${escapeHtml(item.permit_number)}</span>
                    <span class="apy-meta-sep">&middot;</span>
                    <span>${escapeHtml(item.category)}</span>
                    <span class="apy-meta-sep">&middot;</span>
                    <span>${item.quantity.toLocaleString()} ${escapeHtml(item.unit)}</span>
                </div>
            </div>
            <div class="apy-order-row-fee">RM ${money(item.fee)}</div>
        </div>
    `).join('');
}

// ---------------------------------------------------------------
// Payment methods
// ---------------------------------------------------------------

function renderPaymentMethods() {
    document.getElementById('apyPaymentMethods').innerHTML = PAYMENT_METHODS.map((m) => `
        <div class="apy-pm-option ${m.key === selectedMethod ? 'is-selected' : ''}" data-method="${m.key}">
            <label class="apy-pm-option-label">
                <input type="radio" name="apyPaymentMethod" value="${m.key}" class="apy-pm-radio" ${m.key === selectedMethod ? 'checked' : ''}>
                <img src="${logoUrl(m.logo)}" alt="${escapeHtml(m.label)}" class="apy-pm-logo">
                <span class="apy-pm-name">${escapeHtml(m.label)}</span>
            </label>
            <button type="button" class="apy-pm-info-btn" data-method-info="${m.key}" title="How to pay with ${escapeHtml(m.label)}">
                <i class="bi bi-info-circle"></i>
            </button>
        </div>
    `).join('');
}

function selectMethod(key) {
    selectedMethod = key;
    document.querySelectorAll('.apy-pm-option').forEach((opt) => {
        opt.classList.toggle('is-selected', opt.dataset.method === key);
        opt.querySelector('.apy-pm-radio').checked = opt.dataset.method === key;
    });
    document.getElementById('apySummaryMethod').textContent = methodByKey(key)?.label || '—';
}

function initPaymentMethodEvents() {
    document.getElementById('apyPaymentMethods').addEventListener('click', (e) => {
        const infoBtn = e.target.closest('.apy-pm-info-btn');
        if (infoBtn) {
            openHowToPay(infoBtn.dataset.methodInfo);
            return;
        }
        const option = e.target.closest('.apy-pm-option');
        if (option) selectMethod(option.dataset.method);
    });
}

// ---------------------------------------------------------------
// How-to-pay modal
// ---------------------------------------------------------------

let howToModal = null;

function openHowToPay(methodKey) {
    const method = methodByKey(methodKey);
    if (!method) return;

    document.getElementById('apyHowToLogo').src = logoUrl(method.logo);
    document.getElementById('apyHowToLogo').alt = method.label;
    document.getElementById('apyHowToLabel').textContent = method.label;

    document.getElementById('apyHowToSteps').innerHTML = method.steps.map((text, i) => `
        <div class="apy-howto-step">
            <div class="apy-howto-step-num">${i + 1}</div>
            <div class="apy-howto-step-body">
                <div class="apy-howto-step-text">${escapeHtml(text)}</div>
                <div class="apy-howto-step-image">
                    <i class="bi bi-image"></i>
                    <span>Step screenshot placeholder</span>
                </div>
            </div>
        </div>
    `).join('');

    howToModal.show();
}

// ---------------------------------------------------------------
// Summary panel
// ---------------------------------------------------------------

function renderSummary() {
    document.getElementById('apySummaryLines').innerHTML = ORDER.order_details.map((item) => `
        <div class="apy-summary-line">
            <div class="apy-summary-line-left">
                <div class="apy-summary-permit-no">${escapeHtml(item.permit_number)}</div>
                <div class="apy-summary-permit-name">${escapeHtml(item.item_name)}</div>
            </div>
            <div class="apy-summary-line-fee">RM ${money(item.fee)}</div>
        </div>
    `).join('');

    document.getElementById('apyTotalCount').textContent = ORDER.order_details.length;
    document.getElementById('apySummaryMethod').textContent = methodByKey(selectedMethod)?.label || '—';
    document.getElementById('apyGrandTotal').textContent = `RM ${money(ORDER.payment_amount)}`;
}

// ---------------------------------------------------------------
// Payment confirm modal + success state
// ---------------------------------------------------------------

let confirmModal = null;

function initPaymentFlow() {
    confirmModal = new bootstrap.Modal(document.getElementById('apyConfirmModal'), { backdrop: 'static', keyboard: false });
    howToModal   = new bootstrap.Modal(document.getElementById('apyHowToPayModal'));

    document.getElementById('apyPayBtn').addEventListener('click', () => {
        const method = methodByKey(selectedMethod);

        document.getElementById('apyConfirmSummary').innerHTML = `
            <div class="apy-confirm-row">
                <span>Order Number</span>
                <strong>${escapeHtml(ORDER.order_number)}</strong>
            </div>
            <div class="apy-confirm-row">
                <span>Items in order</span>
                <strong>${ORDER.order_details.length} item${ORDER.order_details.length !== 1 ? 's' : ''}</strong>
            </div>
            <div class="apy-confirm-permits">
                ${ORDER.order_details.map((item) => `
                    <div class="apy-confirm-permit-row">
                        <span class="apy-confirm-permit-no">${escapeHtml(item.permit_number)}</span>
                        <span>${escapeHtml(item.item_name)}</span>
                        <span class="apy-confirm-fee">RM ${money(item.fee)}</span>
                    </div>
                `).join('')}
            </div>
            <div class="apy-confirm-row is-total">
                <span>Total payable</span>
                <strong class="apy-confirm-total">RM ${money(ORDER.payment_amount)}</strong>
            </div>
            <div class="apy-confirm-row">
                <span>Payment method</span>
                <strong>${escapeHtml(method?.label || '—')}</strong>
            </div>
        `;

        confirmModal.show();
    });

    document.getElementById('apyPayConfirmBtn').addEventListener('click', () => {
        const confirmBtn = document.getElementById('apyPayConfirmBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="apy-spinner"></span> Processing…';

        ORDER.payment_type = selectedMethod;

        // Simulate gateway redirect + callback
        setTimeout(() => {
            confirmModal.hide();
            showSuccessState();
        }, 2000);
    });
}

function showSuccessState() {
    const method = methodByKey(selectedMethod);
    const ref = genPaymentRef();

    document.getElementById('apySuccessSub').textContent =
        `Payment of RM ${money(ORDER.payment_amount)} via ${method?.label || 'your selected method'} for order ${ORDER.order_number} has been submitted and is pending confirmation.`;
    document.getElementById('apySuccessRef').textContent = `Payment reference: ${ref}`;

    document.getElementById('apySuccessOverlay').classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ---------------------------------------------------------------
// Init
// ---------------------------------------------------------------

let _initialized = false;

function init() {
    if (_initialized) return;
    _initialized = true;
    if (!document.getElementById('apyOrderList')) return;

    renderRefStrip();
    renderParties();
    renderOrderList();
    renderPaymentMethods();
    initPaymentMethodEvents();
    renderSummary();
    initPaymentFlow();
}

document.addEventListener('DOMContentLoaded', init);