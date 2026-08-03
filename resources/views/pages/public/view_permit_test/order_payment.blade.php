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
        ['label' => __('Dashboard'),         'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Papan Pemuka'],
        ['label' => __('Application List'),  'url' => '/public/view_import_permit', 'data-en' => 'Application List', 'data-bm' => 'Senarai Permohonan'],
        ['label' => 'IP-2025-00456',         'url' => '/public/view_import_permit/1'],
        ['label' => __('Payment'),           'url' => '#', 'data-en' => 'Payment', 'data-bm' => 'Pembayaran'],
    ]" title="Payment" title_en="Payment" title_bm="Pembayaran">
    </x-breadcrumb>
@endsection

@section('content')

<div class="apy-wrapper">

    {{-- ================================================================ --}}
    {{-- Page hero                                                         --}}
    {{-- ================================================================ --}}
    <div class="apy-hero">
        <div class="apy-hero-icon"><i class="bi bi-credit-card"></i></div>
        <div>
            <div class="apy-hero-eyebrow" data-en="Awaiting Payment" data-bm="Menunggu Pembayaran">Awaiting Payment</div>
            <h3 class="apy-hero-title" data-en="Complete Your Payment" data-bm="Selesaikan Pembayaran Anda">Complete Your Payment</h3>
            <p class="apy-hero-sub" data-en="This order covers the permit item(s) below. Review the details, choose a payment method, then proceed."
               data-bm="Pesanan ini merangkumi item permit di bawah. Semak butiran, pilih kaedah pembayaran, kemudian teruskan.">
                This order covers the permit item(s) below. Review the details, choose a
                payment method, then proceed.
            </p>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Reference strip                                                  --}}
    {{-- ================================================================ --}}
    <div class="apy-ref-strip">
        <div class="apy-ref-cell">
            <div class="apy-ref-label" data-en="Order Number" data-bm="Nombor Pesanan">Order Number</div>
            <div class="apy-ref-value" id="apyRefOrderNo">—</div>
        </div>
        <div class="apy-ref-cell">
            <div class="apy-ref-label" data-en="Application ID" data-bm="ID Permohonan">Application ID</div>
            <div class="apy-ref-value" id="apyRefAppId">—</div>
        </div>
        <div class="apy-ref-cell">
            <div class="apy-ref-label" data-en="Order Status" data-bm="Status Pesanan">Order Status</div>
            <div class="apy-ref-value" id="apyRefStatus">—</div>
        </div>
        <div class="apy-ref-cell">
            <div class="apy-ref-label" data-en="Amount Due" data-bm="Jumlah Perlu Dibayar">Amount Due</div>
            <div class="apy-ref-value" id="apyRefAmount">—</div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Importer & Exporter (sits above the order/payment layout)         --}}
    {{-- ================================================================ --}}
    <div class="apy-card">
        <div class="apy-card-title" data-en="Importer &amp; Exporter Details" data-bm="Butiran Pengimport &amp; Pengeksport">
            <i class="bi bi-people"></i> Importer &amp; Exporter Details
        </div>
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
                <div class="apy-card-title" data-en="Items in this Order" data-bm="Item dalam Pesanan Ini">
                    <i class="bi bi-box-seam"></i> Items in this Order
                </div>
                <div class="apy-order-list" id="apyOrderList"></div>
            </div>

            <div class="apy-card">
                <div class="apy-card-title" data-en="Payment Method" data-bm="Kaedah Pembayaran">
                    <i class="bi bi-credit-card-2-front"></i> Payment Method
                </div>
                <p class="apy-card-hint" data-en="BayuPay is selected by default — tap the <i class=&quot;bi bi-info-circle&quot;></i> icon on either option for step-by-step instructions."
                   data-bm="BayuPay dipilih secara lalai — ketik ikon <i class=&quot;bi bi-info-circle&quot;></i> pada mana-mana pilihan untuk arahan langkah demi langkah.">
                    BayuPay is selected by default — tap the <i class="bi bi-info-circle"></i> icon on either option for step-by-step instructions.
                </p>
                <div class="apy-payment-methods" id="apyPaymentMethods"></div>
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- RIGHT — sticky payment summary                                 --}}
        {{-- ============================================================ --}}
        <div class="apy-summary-col">
            <div class="apy-summary-card">

                <div class="apy-summary-title" data-en="Payment Summary" data-bm="Ringkasan Pembayaran">
                    <i class="bi bi-receipt"></i> Payment Summary
                </div>

                <div class="apy-summary-lines" id="apySummaryLines"></div>

                <div class="apy-summary-divider"></div>

                <div class="apy-totals">
                    <div class="apy-total-row">
                        <span data-en="Items in order" data-bm="Item dalam pesanan">Items in order</span>
                        <span id="apyTotalCount">0</span>
                    </div>
                    <div class="apy-total-row">
                        <span data-en="Payment method" data-bm="Kaedah pembayaran">Payment method</span>
                        <span id="apySummaryMethod">—</span>
                    </div>
                    <div class="apy-total-row is-grand">
                        <span data-en="Total payable" data-bm="Jumlah perlu dibayar">Total payable</span>
                        <span id="apyGrandTotal">RM 0.00</span>
                    </div>
                </div>

                <button type="button" class="apy-btn-pay" id="apyPayBtn">
                    <i class="bi bi-send"></i>
                    <span data-en="Proceed to Payment" data-bm="Teruskan ke Pembayaran">Proceed to Payment</span>
                </button>

                <div class="apy-secure-note" data-en="Secured payment gateway" data-bm="Gateway pembayaran selamat">
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
                    <span data-en="How to pay with" data-bm="Cara bayar dengan">How to pay with</span>
                    <span id="apyHowToLabel">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="apy-howto-steps" id="apyHowToSteps"></div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="apy-btn-secondary" data-bs-dismiss="modal" data-en="Close" data-bm="Tutup">Close</button>
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
                    <span data-en="Confirm Payment" data-bm="Sahkan Pembayaran">Confirm Payment</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div class="apy-confirm-summary" id="apyConfirmSummary"></div>
                <div class="apy-confirm-note" data-en="You will be redirected to the payment gateway. Do not close the browser tab until payment is complete."
                     data-bm="Anda akan dialihkan ke gateway pembayaran. Jangan tutup tab pelayar sehingga pembayaran selesai.">
                    <i class="bi bi-info-circle"></i>
                    You will be redirected to the payment gateway. Do not close the
                    browser tab until payment is complete.
                </div>
            </div>
            <div class="modal-footer border-top" style="padding:1rem 1.5rem;">
                <button type="button" class="apy-btn-secondary" data-bs-dismiss="modal" data-en="Cancel" data-bm="Batal">Cancel</button>
                <button type="button" class="apy-btn-pay-confirm" id="apyPayConfirmBtn">
                    <i class="bi bi-send me-1"></i> <span data-en="Confirm &amp; Pay" data-bm="Sahkan &amp; Bayar">Confirm &amp; Pay</span>
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
        <div class="apy-success-title" data-en="Payment Submitted" data-bm="Pembayaran Dihantar">Payment Submitted</div>
        <div class="apy-success-sub" id="apySuccessSub">—</div>
        <div class="apy-success-ref" id="apySuccessRef">—</div>
        <a href="/public/view_import_permit" class="apy-btn-done">
            <i class="bi bi-arrow-left me-1"></i> <span data-en="Back to Application List" data-bm="Kembali ke Senarai Permohonan">Back to Application List</span>
        </a>
    </div>
</div>

@endsection