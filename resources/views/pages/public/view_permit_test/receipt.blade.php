@extends('pages.app')

@section('pageName', 'Payment Receipt')

@push('scripts')
    @vite(['resources/js/pages/importPermit/paymentReceipt.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard',         'url' => '/'],
        ['label' => 'Application List',  'url' => '/public/view_import_permit'],
        ['label' => 'IP-2025-00456',     'url' => '/public/view_import_permit/1'],
        ['label' => 'Receipt',           'url' => '#'],
    ]" title="Payment Receipt">
    </x-breadcrumb>
@endsection

@section('content')

{{--
    Payment Receipt
    -----------------
    Read-only confirmation shown after a successful payment. Mirrors
    applicantPayment's apy-* visual language so it doesn't feel like
    a different product.

    Maps to App\Models\Order + a related Payment row:
        order_number, application_id, payment_reference, payment_date,
        payment_method, payment_amount, order_details (same shape as
        the payment page), name/email/phone snapshot.

    On the real implementation, RECEIPT in paymentReceipt.js gets
    replaced by @json($order->toReceiptArray()) or fetched via the
    order/payment reference in the URL.
--}}

<div class="apy-wrapper">

    {{-- ================================================================ --}}
    {{-- Success banner                                                     --}}
    {{-- ================================================================ --}}
    <div class="apr-success-banner">
        <div class="apr-success-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div>
            <div class="apr-success-title">Payment successful</div>
            <p class="apr-success-sub">
                Your payment has been received and is being processed. A copy of this
                receipt has been sent to your registered email.
            </p>
        </div>
        <div class="apr-success-actions">
            <button type="button" class="apr-btn-icon" id="aprPrintBtn" title="Print receipt">
                <i class="bi bi-printer"></i>
            </button>
            <button type="button" class="apr-btn-icon" id="aprDownloadBtn" title="Download PDF">
                <i class="bi bi-download"></i>
            </button>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Receipt card                                                       --}}
    {{-- ================================================================ --}}
    <div class="apr-receipt-card" id="aprReceiptCard">

        {{-- Header --}}
        <div class="apr-receipt-header">
            <div class="apr-receipt-org">
                <div class="apr-receipt-org-name">Jabatan Pertanian Sabah</div>
                <div class="apr-receipt-org-address">
                    Wisma Pertanian, Jalan Tasik, Beg Berkunci No. 2050,<br>
                    88632 Kota Kinabalu, Sabah
                </div>
            </div>
            <div class="apr-receipt-meta">
                <div class="apr-receipt-meta-label">Official Receipt</div>
                <div class="apr-receipt-meta-no" id="aprReceiptNo">—</div>
            </div>
        </div>

        <div class="apr-receipt-divider"></div>

        {{-- Reference grid --}}
        <div class="apr-ref-grid">
            <div class="apr-ref-cell">
                <div class="apr-ref-label">Payment Reference</div>
                <div class="apr-ref-value" id="aprPaymentRef">—</div>
            </div>
            <div class="apr-ref-cell">
                <div class="apr-ref-label">Order Number</div>
                <div class="apr-ref-value" id="aprOrderNo">—</div>
            </div>
            <div class="apr-ref-cell">
                <div class="apr-ref-label">Application ID</div>
                <div class="apr-ref-value" id="aprAppId">—</div>
            </div>
            <div class="apr-ref-cell">
                <div class="apr-ref-label">Payment Date</div>
                <div class="apr-ref-value" id="aprPaymentDate">—</div>
            </div>
            <div class="apr-ref-cell">
                <div class="apr-ref-label">Payment Method</div>
                <div class="apr-ref-value" id="aprPaymentMethod">—</div>
            </div>
            <div class="apr-ref-cell">
                <div class="apr-ref-label">Status</div>
                <div class="apr-ref-value">
                    <span class="apr-status-badge" id="aprStatusBadge">—</span>
                </div>
            </div>
        </div>

        <div class="apr-receipt-divider"></div>

        {{-- Payer details --}}
        <div class="apr-section-label">Paid By</div>
        <div class="apr-payer-row">
            <div class="apr-payer-cell">
                <div class="apr-payer-label">Name</div>
                <div class="apr-payer-value" id="aprPayerName">—</div>
            </div>
            <div class="apr-payer-cell">
                <div class="apr-payer-label">Email</div>
                <div class="apr-payer-value" id="aprPayerEmail">—</div>
            </div>
            <div class="apr-payer-cell">
                <div class="apr-payer-label">Phone</div>
                <div class="apr-payer-value" id="aprPayerPhone">—</div>
            </div>
        </div>

        <div class="apr-receipt-divider"></div>

        {{-- Itemised list --}}
        <div class="apr-section-label">Items Paid</div>
        <div class="apr-item-table" id="aprItemTable"></div>

        {{-- Totals --}}
        <div class="apr-totals-block">
            <div class="apr-totals-row">
                <span>Subtotal</span>
                <span id="aprSubtotal">RM 0.00</span>
            </div>
            <div class="apr-totals-row">
                <span>Processing fee</span>
                <span id="aprProcessingFee">RM 0.00</span>
            </div>
            <div class="apr-totals-row is-grand">
                <span>Total paid</span>
                <span id="aprGrandTotal">RM 0.00</span>
            </div>
        </div>

        <div class="apr-receipt-divider"></div>

        {{-- Footer note --}}
        <div class="apr-footer-note">
            <i class="bi bi-info-circle"></i>
            This is a computer-generated receipt and does not require a signature.
            For enquiries, contact Jabatan Pertanian Sabah at (088) 211 736.
        </div>

    </div>

    {{-- ================================================================ --}}
    {{-- What happens next                                                  --}}
    {{-- ================================================================ --}}
    <div class="apr-next-card">
        <div class="apr-next-title">
            <i class="bi bi-signpost-2"></i> What happens next
        </div>
        <div class="apr-next-steps" id="aprNextSteps"></div>
    </div>

    {{-- ================================================================ --}}
    {{-- Footer actions                                                     --}}
    {{-- ================================================================ --}}
    <div class="apr-footer-actions">
        <a href="/public/view_import_permit" class="apr-btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Application List
        </a>
        <a href="#" class="apr-btn-primary" id="aprViewAppBtn">
            <i class="bi bi-file-earmark-text"></i> View Application Status
        </a>
    </div>

</div>

@endsection