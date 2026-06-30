@extends('pages.app')

@section('pageName', 'Payment')

@push('scripts')
    <script>
        // Used by applicantPayment.js to build payment logo URLs without
        // hardcoding the public path inside the JS module.
        window.PAYMENT_ASSET_BASE = "{{ asset('images/payment') }}";
    </script>
    @vite(['resources/js/pages/importPermit/applicantPayment.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard',         'url' => '/'],
        ['label' => 'Application List',  'url' => '/public/view_import_permit'],
        ['label' => 'IP-2025-00456',     'url' => '/public/view_import_permit/1'],
        ['label' => 'Payment',           'url' => '#'],
    ]" title="Payment">
    </x-breadcrumb>
@endsection

@section('content')

{{--
    Applicant Payment
    ------------------
    This page pays for an EXISTING Order row, not a basket the user
    builds here — by the time someone lands here, `order_details` is
    already fixed (set when the order was created) and `payment_amount`
    is already the authoritative total. So there's no select-all /
    per-row checkbox logic anymore: the left column just displays what
    the order already contains, read-only.

    Maps to App\Models\Order:
        order_number, status, order_details (array), payment_amount,
        name/email/phone (the order's own contact snapshot), payment_type.

    Two payment methods — BayuPay (default) and YonoPay — each with a
    logo and an info icon that opens a step-by-step "how to pay" modal.
    The step images are placeholders for now; drop the real screenshots
    in once available (see PAYMENT_METHODS in applicantPayment.js).
--}}

<div class="apy-wrapper">

    {{-- ================================================================ --}}
    {{-- Page hero                                                          --}}
    {{-- ================================================================ --}}
    <div class="apy-hero">
        <div class="apy-hero-icon"><i class="bi bi-credit-card"></i></div>
        <div>
            <div class="apy-hero-eyebrow">Awaiting Payment</div>
            <h3 class="apy-hero-title">Complete Your Payment</h3>
            <p class="apy-hero-sub">
                This order covers the permit item(s) below. Review the details, choose a
                payment method, then proceed.
            </p>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Reference strip                                                    --}}
    {{-- ================================================================ --}}
    <div class="apy-ref-strip">
        <div class="apy-ref-cell">
            <div class="apy-ref-label">Order Number</div>
            <div class="apy-ref-value" id="apyRefOrderNo">—</div>
        </div>
        <div class="apy-ref-cell">
            <div class="apy-ref-label">Application ID</div>
            <div class="apy-ref-value" id="apyRefAppId">—</div>
        </div>
        <div class="apy-ref-cell">
            <div class="apy-ref-label">Order Status</div>
            <div class="apy-ref-value" id="apyRefStatus">—</div>
        </div>
        <div class="apy-ref-cell">
            <div class="apy-ref-label">Amount Due</div>
            <div class="apy-ref-value" id="apyRefAmount">—</div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Importer & Exporter (sits above the order/payment layout)         --}}
    {{-- ================================================================ --}}
    <div class="apy-card">
        <div class="apy-card-title"><i class="bi bi-people"></i> Importer &amp; Exporter Details</div>
        <div class="apy-parties" id="apyParties"></div>
    </div>

    {{-- ================================================================ --}}
    {{-- Main two-column layout                                             --}}
    {{-- ================================================================ --}}
    <div class="apy-layout">

        {{-- ============================================================ --}}
        {{-- LEFT — order items (read-only) + payment method                --}}
        {{-- ============================================================ --}}
        <div class="apy-left-col">

            <div class="apy-card">
                <div class="apy-card-title"><i class="bi bi-box-seam"></i> Items in this Order</div>
                <div class="apy-order-list" id="apyOrderList"></div>
            </div>

            <div class="apy-card">
                <div class="apy-card-title"><i class="bi bi-credit-card-2-front"></i> Payment Method</div>
                <p class="apy-card-hint">BayuPay is selected by default — tap the <i class="bi bi-info-circle"></i> icon on either option for step-by-step instructions.</p>
                <div class="apy-payment-methods" id="apyPaymentMethods"></div>
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- RIGHT — sticky payment summary                                 --}}
        {{-- ============================================================ --}}
        <div class="apy-summary-col">
            <div class="apy-summary-card">

                <div class="apy-summary-title">
                    <i class="bi bi-receipt"></i> Payment Summary
                </div>

                <div class="apy-summary-lines" id="apySummaryLines"></div>

                <div class="apy-summary-divider"></div>

                <div class="apy-totals">
                    <div class="apy-total-row">
                        <span>Items in order</span>
                        <span id="apyTotalCount">0</span>
                    </div>
                    <div class="apy-total-row">
                        <span>Payment method</span>
                        <span id="apySummaryMethod">—</span>
                    </div>
                    <div class="apy-total-row is-grand">
                        <span>Total payable</span>
                        <span id="apyGrandTotal">RM 0.00</span>
                    </div>
                </div>

                <button type="button" class="apy-btn-pay" id="apyPayBtn">
                    <i class="bi bi-send"></i>
                    Proceed to Payment
                </button>

                <div class="apy-secure-note">
                    <i class="bi bi-shield-check"></i>
                    Secured payment gateway
                </div>

            </div>
        </div>

    </div>

</div>

{{-- ================================================================== --}}
{{-- HOW TO PAY modal (shared — repopulated per method)                   --}}
{{-- ================================================================== --}}
<div class="modal fade" id="apyHowToPayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content apy-howto-modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <img id="apyHowToLogo" src="" alt="" class="apy-howto-logo">
                    How to pay with <span id="apyHowToLabel">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="apy-howto-steps" id="apyHowToSteps"></div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="apy-btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================== --}}
{{-- PAYMENT CONFIRMATION MODAL                                           --}}
{{-- ================================================================== --}}
<div class="modal fade" id="apyConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content" style="border-radius:1rem;border:1px solid var(--default-border);">
            <div class="modal-header border-bottom" style="padding:1.25rem 1.5rem;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-shield-check me-2 text-success"></i>
                    Confirm Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div class="apy-confirm-summary" id="apyConfirmSummary"></div>
                <div class="apy-confirm-note">
                    <i class="bi bi-info-circle"></i>
                    You will be redirected to the payment gateway. Do not close the
                    browser tab until payment is complete.
                </div>
            </div>
            <div class="modal-footer border-top" style="padding:1rem 1.5rem;">
                <button type="button" class="apy-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="apy-btn-pay-confirm" id="apyPayConfirmBtn">
                    <i class="bi bi-send me-1"></i> Confirm &amp; Pay
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================== --}}
{{-- SUCCESS STATE (shown after simulated payment)                        --}}
{{-- ================================================================== --}}
<div class="apy-success-overlay d-none" id="apySuccessOverlay">
    <div class="apy-success-card">
        <div class="apy-success-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="apy-success-title">Payment Submitted</div>
        <div class="apy-success-sub" id="apySuccessSub">—</div>
        <div class="apy-success-ref" id="apySuccessRef">—</div>
        <a href="/public/view_import_permit" class="apy-btn-done">
            <i class="bi bi-arrow-left me-1"></i> Back to Application List
        </a>
    </div>
</div>

@endsection