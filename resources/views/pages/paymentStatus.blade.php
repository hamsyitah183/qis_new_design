@extends('pages.app')

@section('pageName', 'Payment Receipt')

@push('scripts')
    @vite(['resources/js/pages/importPermit/paymentReceipt.js'])
@endpush

@php
    $user = $order->order_details['user'];
    $permits = $order->order_details['permits'];
    $status = $paymentData['transaction_status'];

    $isSuccess = $status === 'SUCCESSFUL';
    $isFailed = $status === 'UNSUCCESSFUL';
    $isPending = !$isSuccess && !$isFailed;

    // // TODO verify: confirm these column/relation names against your
    // real Order model — they weren't present in the old blade so this
// is a best guess at what they're likely called.
    $paymentMethod = $order->payment_method ?? '—';
    $paymentDate = optional($order->updated_at)->format('d M Y, h:i A') ?? '—';
    $applicationId = $order->application_id ?? null;

    $receiptNo = 'RCT-' . strtoupper(substr(preg_replace('/[^0-9A-Za-z]/', '', $order->order_number ?? 'XXXX'), -8));

    // Per-item fee isn't broken out in order_details — only the order
// total is. Split evenly for display purposes only; if you add a
// real per-permit fee to order_details later, read that instead.
$itemCount = max(count($permits), 1);
$feePerItem = $order->payment_amount / $itemCount;

$viewAppBaseUrl = match ($order->application_type ?? '') {
    'Inspection Certificate', 'Inspection' => '/view_inspection/',
    'Consignment Certificate', 'Consignment' => '/view_consignment/',
    default => '/view_application/',
    };
@endphp

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        ['label' => 'Order List', 'url' => '/order/list'],
        ['label' => $order->order_number, 'url' => '#'],
    ]" title="Payment Receipt">
    </x-breadcrumb>
@endsection

@section('content')

    <div class="apy-wrapper">

        {{-- ================================================================ --}}
        {{-- Outcome banner — three real states, not just the success case    --}}
        {{-- ================================================================ --}}
        <div class="apr-success-banner {{ $isFailed ? 'is-danger' : ($isPending ? 'is-pending' : '') }}">
            <div class="apr-success-icon">
                @if ($isSuccess)
                    <i class="bi bi-check-circle-fill"></i>
                @elseif ($isFailed)
                    <i class="bi bi-x-circle-fill"></i>
                @else
                    <i class="bi bi-hourglass-split"></i>
                @endif
            </div>
            <div>
                <div class="apr-success-title">
                    @if ($isSuccess)
                        Payment successful
                    @elseif ($isFailed)
                        Payment failed
                    @else
                        Payment pending
                    @endif
                </div>
                <p class="apr-success-sub">
                    @if ($isSuccess)
                        Your order ({{ $order->order_number }}) payment was successful and is being processed.
                        A copy of this receipt has been sent to your registered email.
                    @elseif ($isFailed)
                        Your order ({{ $order->order_number }}) payment was unsuccessful. Please try again — no
                        amount has been deducted for this attempt.
                    @else
                        Your order ({{ $order->order_number }}) is pending authorization from your bank. This can
                        take a few minutes up to 1 business day.
                    @endif
                </p>
                @if (!empty($paymentData['message']))
                    <p class="apr-success-sub" style="margin-top:.25rem;font-style:italic;">
                        {{ $paymentData['message'] }}
                    </p>
                @endif
            </div>
            @if ($isSuccess)
                <div class="apr-success-actions">
                    <button type="button" class="apr-btn-icon" id="aprPrintBtn" title="Print receipt">
                        <i class="bi bi-printer"></i>
                    </button>
                    <button type="button" class="apr-btn-icon" id="aprDownloadBtn" title="Download PDF">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            @endif
        </div>

        {{-- ================================================================ --}}
        {{-- Receipt card — full itemised receipt only when payment succeeded  --}}
        {{-- ================================================================ --}}
        @if ($isSuccess)
            <div class="apr-receipt-card" id="aprReceiptCard">

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
                        <div class="apr-receipt-meta-no">{{ $receiptNo }}</div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-ref-grid">
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Payment Reference</div>
                        <div class="apr-ref-value">{{ $order->fpx_seller_reference }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Order Number</div>
                        <div class="apr-ref-value">{{ $order->order_number }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Application Type</div>
                        <div class="apr-ref-value">{{ $order->application_type }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Payment Date</div>
                        <div class="apr-ref-value">{{ $paymentDate }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Payment Method</div>
                        <div class="apr-ref-value">{{ $paymentMethod }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Status</div>
                        <div class="apr-ref-value">
                            <span class="apr-status-badge is-paid">Paid</span>
                        </div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-section-label">Paid By</div>
                <div class="apr-payer-row">
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label">Name</div>
                        <div class="apr-payer-value">{{ $user['fullname'] }}</div>
                    </div>
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label">Email</div>
                        <div class="apr-payer-value">{{ $user['email'] }}</div>
                    </div>
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label">Phone</div>
                        <div class="apr-payer-value">{{ $user['phone_number'] }}</div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-section-label">Items Paid</div>
                <div class="apr-item-table">
                    <div class="apr-item-row apr-item-row-head">
                        <span>Permit</span>
                        <span>Item</span>
                        <span class="apr-col-right">Fee</span>
                    </div>
                    @foreach ($permits as $item)
                        @php
                            $itemName = $item['item_name'] ?? ($item['consignment_detail']['item_name'] ?? '—');
                            $category = $item['category'] ?? ($item['consignment_detail']['category'] ?? null);
                            $quantity = $item['quantity'] ?? ($item['consignment_detail']['quantity'] ?? null);
                            $unit = $item['measure'] ?? ($item['consignment_detail']['measure'] ?? '');
                        @endphp
                        <div class="apr-item-row">
                            <span class="apr-item-permit-no">{{ $item['permit_number'] ?? '—' }}</span>
                            <span class="apr-item-name-cell">
                                <span class="apr-item-name">{{ $itemName }}</span>
                                @if ($category || $quantity)
                                    <span class="apr-item-meta">
                                        {{ $category }}
                                        @if ($category && $quantity)
                                            &middot;
                                        @endif
                                        @if ($quantity)
                                            {{ number_format($quantity) }} {{ $unit }}
                                        @endif
                                    </span>
                                @endif
                            </span>
                            <span class="apr-col-right apr-item-fee">RM {{ number_format($feePerItem, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- // TODO verify: order_details doesn't currently carry a
                 real subtotal/processing-fee split, only the grand total.
                 Subtotal is shown equal to the total and fee as RM 0.00
                 until that breakdown exists server-side. --}}
                <div class="apr-totals-block">
                    <div class="apr-totals-row">
                        <span>Subtotal</span>
                        <span>RM {{ number_format($order->payment_amount, 2) }}</span>
                    </div>
                    <div class="apr-totals-row">
                        <span>Processing fee</span>
                        <span>RM 0.00</span>
                    </div>
                    <div class="apr-totals-row is-grand">
                        <span>Total paid</span>
                        <span>RM {{ number_format($order->payment_amount, 2) }}</span>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-footer-note">
                    <i class="bi bi-info-circle"></i>
                    This is a computer-generated receipt and does not require a signature.
                    For enquiries, contact Jabatan Pertanian Sabah at (088) 211 736.
                </div>

            </div>

            <div class="apr-next-card">
                <div class="apr-next-title">
                    <i class="bi bi-signpost-2"></i> What happens next
                </div>
                <div class="apr-next-steps">
                    <div class="apr-next-step">
                        <div class="apr-next-step-icon"><i class="bi bi-hourglass-split"></i></div>
                        <div>
                            <div class="apr-next-step-title">Bank authorization</div>
                            <div class="apr-next-step-desc">
                                Your payment is being verified by the bank. This usually takes a few minutes,
                                but can take up to 1 business day.
                            </div>
                        </div>
                    </div>
                    <div class="apr-next-step">
                        <div class="apr-next-step-icon"><i class="bi bi-patch-check"></i></div>
                        <div>
                            <div class="apr-next-step-title">Permit issuance</div>
                            <div class="apr-next-step-desc">
                                Once payment is confirmed, each paid permit will move to Issued / Active status
                                and become available for download.
                            </div>
                        </div>
                    </div>
                    <div class="apr-next-step">
                        <div class="apr-next-step-icon"><i class="bi bi-bell"></i></div>
                        <div>
                            <div class="apr-next-step-title">Notification</div>
                            <div class="apr-next-step-desc">
                                You will receive an email and an in-app notification as soon as your permits
                                are ready.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- ============================================================ --}}
            {{-- Failed / pending — simpler status card, no itemised receipt   --}}
            {{-- since nothing has actually been charged/confirmed yet         --}}
            {{-- ============================================================ --}}
            <div class="apr-receipt-card">
                <div class="apr-section-label">Order Details</div>
                <div class="apr-ref-grid">
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Order Number</div>
                        <div class="apr-ref-value">{{ $order->order_number }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Application Type</div>
                        <div class="apr-ref-value">{{ $order->application_type }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">FPX Reference</div>
                        <div class="apr-ref-value">{{ $order->fpx_seller_reference }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Amount</div>
                        <div class="apr-ref-value">RM {{ number_format($order->payment_amount, 2) }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label">Status</div>
                        <div class="apr-ref-value">
                            <span class="apr-status-badge {{ $isFailed ? 'is-failed' : 'is-processing' }}">
                                {{ $isFailed ? 'Failed' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-section-label">Paid By</div>
                <div class="apr-payer-row">
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label">Name</div>
                        <div class="apr-payer-value">{{ $user['fullname'] }}</div>
                    </div>
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label">Email</div>
                        <div class="apr-payer-value">{{ $user['email'] }}</div>
                    </div>
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label">Phone</div>
                        <div class="apr-payer-value">{{ $user['phone_number'] }}</div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-section-label">Permit(s) in this Order</div>
                <div class="apr-item-table">
                    <div class="apr-item-row apr-item-row-head">
                        <span>Permit</span>
                        <span colspan="2">Item</span>
                    </div>
                    @foreach ($permits as $item)
                        <div class="apr-item-row">
                            <span class="apr-item-permit-no">{{ $item['permit_number'] ?? '—' }}</span>
                            <span class="apr-item-name-cell">
                                <span
                                    class="apr-item-name">{{ $item['item_name'] ?? ($item['consignment_detail']['item_name'] ?? '—') }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ================================================================ --}}
        {{-- Footer actions                                                     --}}
        {{-- ================================================================ --}}
        <div class="apr-footer-actions">
            <a href="/order/list" class="apr-btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Order List
            </a>
            @if ($applicationId)
                <a href="{{ $viewAppBaseUrl . $applicationId }}{{ $isFailed || $isPending ? '#pending' : '' }}"
                    class="apr-btn-primary">
                    <i class="bi bi-file-earmark-text"></i> View Application Status
                </a>
            @endif
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        /**
         * paymentReceipt.js
         * ------------------------------------------------------------------
         * The receipt content itself is now rendered server-side directly in
         * payment-status.blade.php from the real $order / $paymentData, so
         * this file no longer loads anything from sessionStorage or demo data
         * — it just wires up the print/download buttons that only appear on
         * a successful payment.
         */

        function initActions() {
            document.getElementById('aprPrintBtn')?.addEventListener('click', () => {
                window.print();
            });

            document.getElementById('aprDownloadBtn')?.addEventListener('click', () => {
                // Placeholder until a real PDF endpoint exists — falls back to the
                // print dialog, where the user can choose "Save as PDF".
                window.print();
            });
        }

        let _initialized = false;

        function init() {
            if (_initialized) return;
            _initialized = true;

            initActions();
        }

        document.addEventListener('DOMContentLoaded', init);
    </script>
@endpush

{{-- payment processing --}}
{{-- 1. bayupay klau close page, pemit masih payment processing 7 minutes, 'processing payment' --}}
