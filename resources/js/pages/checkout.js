import $ from "jquery";
import Swal from "sweetalert2";

// ---------- Payment Method Selection ----------
function initPaymentMethods() {
    // Highlight default selected method
    const $default = $('.apy-pm-option.is-selected');
    if ($default.length) {
        const method = $default.find('.apy-pm-radio').val();
        $('#apySummaryMethod').text(method);
        $('#selectedPaymentMethod').val(method);
    }

    // Click on the whole option area to select it
    $(document).on('click', '.apy-pm-option-label', function (e) {
        const $option = $(this).closest('.apy-pm-option');
        const method = $option.find('.apy-pm-radio').val();

        $option.find('.apy-pm-radio').prop('checked', true);
        $('.apy-pm-option').removeClass('is-selected');
        $option.addClass('is-selected');
        $('#apySummaryMethod').text(method);
        $('#selectedPaymentMethod').val(method);
    });
}

// ---------- How to Pay Modal ----------
function initInfoButtons() {
    $(document).on('click', '.apy-pm-info-btn', function (e) {
        e.stopPropagation();
        const method = $(this).data('method-info');
        const logo = $(this).siblings('.apy-pm-option-label').find('.apy-pm-logo').attr('src');
        showHowToPayModal(method, logo);
    });
}

function showHowToPayModal(method, logo) {
    $('#apyHowToLabel').text(method);
    $('#apyHowToLogo').attr('src', logo || '');

    const totalAmount = window.TOTAL_AMOUNT || 0;
    const formattedAmount = 'RM ' + parseFloat(totalAmount).toFixed(2);

    const steps = getPaymentSteps(method, formattedAmount);
    $('#apyHowToSteps').html(steps);

    const modal = new bootstrap.Modal(document.getElementById('apyHowToPayModal'));
    modal.show();
}

function getPaymentSteps(method, amount) {
    const stepsConfig = {
        bayuPay: [
            { title: 'Step 1', desc: 'Log in to your BayuPay account', img: '/images/payment/bayupay-step1.png' },
            { title: 'Step 2', desc: `Enter the payment amount: ${amount}`, img: '/images/payment/bayupay-step2.png' },
            { title: 'Step 3', desc: 'Confirm the transaction', img: '/images/payment/bayupay-step3.png' },
            { title: 'Step 4', desc: 'Save the receipt for your records', img: '/images/payment/bayupay-step4.png' },
        ],
        yonoPay: [
            { title: 'Step 1', desc: 'Open your YonoPay app', img: '/images/payment/yonopay-step1.png' },
            { title: 'Step 2', desc: 'Scan the QR code or enter merchant ID', img: '/images/payment/yonopay-step2.png' },
            { title: 'Step 3', desc: `Enter amount: ${amount}`, img: '/images/payment/yonopay-step3.png' },
            { title: 'Step 4', desc: 'Complete the payment and save receipt', img: '/images/payment/yonopay-step4.png' },
        ],
    };

    const steps = stepsConfig[method] || stepsConfig['bayuPay'];

    return steps.map((step) => `
        <div class="apy-step">
            <div class="apy-step-number">${step.title}</div>
            <div class="apy-step-desc">${step.desc}</div>
            <img src="${step.img}" alt="${step.title}" class="apy-step-img"
                 onerror="this.src='/images/payment/placeholder-step.png'">
        </div>
    `).join('');
}

// ---------- Proceed to Payment (opens confirmation modal) ----------
function initPayButton() {
    $(document).on('click', '#apyPayBtn', function () {
        const selectedMethod = $('#selectedPaymentMethod').val() || $('.apy-pm-radio:checked').val();
        if (!selectedMethod) {
            Swal.fire('Error', 'Please select a payment method', 'error');
            return;
        }
        $('#apyConfirmMethod').text(selectedMethod);
        const confirmModal = new bootstrap.Modal(document.getElementById('apyConfirmModal'));
        confirmModal.show();
    });
}

// ---------- Confirm & Pay ----------
// FIX: the previous version submitted #paymentForm via $.ajax and then
// faked a success overlay client-side. /payment needs to actually
// navigate the browser — either to redirect to the real payment
// gateway, or to come back with server-side validation errors — so
// this now does a real form submit, same as the old checkout.js.
function initConfirmButton() {
    $(document).on('click', '#apyPayConfirmBtn', function () {
        const paymentMethod = $('#selectedPaymentMethod').val() || $('input[name="apyPaymentMethod"]:checked').val();

        if (!paymentMethod) {
            Swal.fire('Error', 'Please select a payment method', 'error');
            return;
        }

        const confirmModal = bootstrap.Modal.getInstance(document.getElementById('apyConfirmModal'));
        if (confirmModal) confirmModal.hide();

        // Brief loading feedback — the page is about to navigate away
        // (either to the gateway or back with a server response), so
        // this doesn't need a success/error branch of its own.
        Swal.fire({
            title: 'Processing Payment',
            text: 'Please wait, you will be redirected shortly...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        document.getElementById('paymentForm').submit(); // real submit
    });
}

// ---------- Success overlay ----------
// Only reachable if your backend redirects back to THIS page with a
// success flag after payment completes (e.g. /public/payment?paid=1).
// // TODO verify: confirm what your /payment route actually does on
// success — redirect to an external gateway, or back here with a flag —
// and adjust the check below (or remove this block) to match.
function checkForPaymentSuccessFlag() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('paid') === '1' || params.get('payment') === 'success') {
        showSuccessState();
    }
}

function showSuccessState() {
    const appId = window.APPLICATION?.application_id || '—';
    $('#apySuccessSub').text('Your payment has been submitted successfully.');
    $('#apySuccessRef').text('Application: ' + appId);
    $('#apySuccessOverlay').removeClass('d-none');

    setTimeout(() => {
        const baseUrl = getApplicationUrl(window.APPLICATION?.application_type);
        window.location.href = baseUrl + appId;
    }, 3000);
}

// ---------- Return to Application ----------
function initReturnButton() {
    $(document).on('click', '#returnToApplication', function (e) {
        e.preventDefault();
        const applicationId = $(this).data('app-id');
        const applicationType = $(this).data('app-type');
        const baseUrl = getApplicationUrl(applicationType);

        Swal.fire({
            title: 'Are you sure?',
            text: 'The payment will be cancelled!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, go back!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = baseUrl + applicationId + '#pending';
            }
        });
    });
}

function getApplicationUrl(applicationType) {
    let baseUrl = '/view_application/';
    if (applicationType === 'Inspection Certificate' || applicationType === 'Inspection') {
        baseUrl = '/view_inspection/';
    } else if (applicationType === 'Consignment Certificate' || applicationType === 'Consignment') {
        baseUrl = '/view_consignment/';
    }
    return baseUrl;
}

// ---------- Initialize Everything ----------
$(document).ready(function () {
    initPaymentMethods();
    initInfoButtons();
    initPayButton();
    initConfirmButton();
    initReturnButton();
    checkForPaymentSuccessFlag();
});