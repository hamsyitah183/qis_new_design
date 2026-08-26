@extends('pages.app')

@section('pageName', 'Payment Receipt')

@push('scripts')
    @vite(['resources/js/pages/importPermit/paymentReceipt.js'])
@endpush

@php
    $user = $order->order_details['user'];

    if ($order->application->application_type == 'Consignment Certificate') {
        $permits = $order->application->consignmentPermits;
    } elseif ($order->application->application_type == 'Inspection Certificate') {
        $permits = $order->application->inspectionItems;
    } else {
        $permits = $order->application->consignmentPermits;
    }

    $status = $paymentData['transaction_status'];

    $isSuccess = $status === 'SUCCESSFUL';
    $isFailed = $status === 'UNSUCCESSFUL';
    $isPending = !$isSuccess && !$isFailed;

    $paymentMethod = $order->payment_method ?? '—';
    $paymentDate = optional($order->updated_at)->format('d M Y, h:i A') ?? '—';
    $applicationId = $order->application_id ?? null;

    $receiptNo = 'RCT-' . strtoupper(substr(preg_replace('/[^0-9A-Za-z]/', '', $order->order_number ?? 'XXXX'), -8));

    $itemCount = max(count($permits), 1);
    $feePerItem = $order->payment_amount / $itemCount;

    $viewAppBaseUrl = match ($order->application_type ?? '') {
        'Inspection Certificate', 'Inspection' => '/view_inspection_certificates/',
        'Consignment Certificate', 'Consignment' => '/view_consignment/',
        default => '/view_application/',
    };

    // ─── Consignment category data ──────────────────────────────────────
    $isConsignment = $order->application_type === 'Consignment Certificate';
    $prices = [];
    $totalFromPrices = 0;
    if ($isConsignment && $order->application) {
        $prices = json_decode($order->application->prices_total, true) ?? [];
        $totalFromPrices = array_sum(array_column($prices, 'price'));
    }
@endphp

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('Dashboard'), 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Papan Pemuka'],
        [
            'label' => __('Order List'),
            'url' => '/order/list',
            'data-en' => 'Order List',
            'data-bm' => 'Senarai Pesanan',
        ],
        ['label' => $order->order_number, 'url' => '#'],
    ]" title="Payment Receipt" title_en="Payment Receipt" title_bm="Resit Pembayaran">
    </x-breadcrumb>
@endsection

@section('content')

    <div class="apy-wrapper">

        {{-- ================================================================ --}}
        {{-- Outcome banner — three real states                              --}}
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
                        <span data-en="Payment successful" data-bm="Pembayaran berjaya">Payment successful</span>
                    @elseif ($isFailed)
                        <span data-en="Payment failed" data-bm="Pembayaran gagal">Payment failed</span>
                    @else
                        <span data-en="Payment pending" data-bm="Pembayaran tertunggak">Payment pending</span>
                    @endif
                </div>
                <p class="apr-success-sub">
                    @if ($isSuccess)
                        <span
                            data-en="Your order ({{ $order->order_number }}) payment was successful and is being processed. A copy of this receipt has been sent to your registered email."
                            data-bm="Bayaran pesanan anda ({{ $order->order_number }}) berjaya dan sedang diproses. Satu salinan resit ini telah dihantar ke e-mel berdaftar anda.">
                            Your order ({{ $order->order_number }}) payment was successful and is being processed.
                            A copy of this receipt has been sent to your registered email.
                        </span>
                    @elseif ($isFailed)
                        <span
                            data-en="Your order ({{ $order->order_number }}) payment was unsuccessful. Please try again — no amount has been deducted for this attempt."
                            data-bm="Bayaran pesanan anda ({{ $order->order_number }}) tidak berjaya. Sila cuba semula — tiada jumlah yang telah dipotong untuk percubaan ini.">
                            Your order ({{ $order->order_number }}) payment was unsuccessful. Please try again — no
                            amount has been deducted for this attempt.
                        </span>
                    @else
                        <span
                            data-en="Your order ({{ $order->order_number }}) is pending authorization from your bank. This can take a few minutes up to 1 business day."
                            data-bm="Pesanan anda ({{ $order->order_number }}) menunggu pengesahan daripada bank anda. Ini boleh mengambil masa beberapa minit sehingga 1 hari perniagaan.">
                            Your order ({{ $order->order_number }}) is pending authorization from your bank. This can
                            take a few minutes up to 1 business day.
                        </span>
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
                        <div class="apr-receipt-meta-label" data-en="Official Receipt" data-bm="Resit Rasmi">Official
                            Receipt</div>
                        <div class="apr-receipt-meta-no">{{ $receiptNo }}</div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-ref-grid">
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Payment Reference" data-bm="Rujukan Pembayaran">Payment
                            Reference</div>
                        <div class="apr-ref-value">{{ $order->fpx_seller_reference }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Order Number" data-bm="Nombor Pesanan">Order Number</div>
                        <div class="apr-ref-value">{{ $order->order_number }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Application Type" data-bm="Jenis Permohonan">Application Type
                        </div>
                        <div class="apr-ref-value">{{ $order->application_type }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Payment Date" data-bm="Tarikh Pembayaran">Payment Date</div>
                        <div class="apr-ref-value">{{ $paymentDate }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Payment Method" data-bm="Kaedah Pembayaran">Payment Method</div>
                        <div class="apr-ref-value">{{ $order->payment_type }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Status" data-bm="Status">Status</div>
                        <div class="apr-ref-value">
                            <span class="apr-status-badge is-paid" data-en="Paid" data-bm="Dibayar">Paid</span>
                        </div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-section-label" data-en="Paid By" data-bm="Dibayar Oleh">Paid By</div>
                <div class="apr-payer-row">
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label" data-en="Name" data-bm="Nama">Name</div>
                        <div class="apr-payer-value">{{ $user['fullname'] }}</div>
                    </div>
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label" data-en="Email" data-bm="E-mel">Email</div>
                        <div class="apr-payer-value">{{ $user['email'] }}</div>
                    </div>
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label" data-en="Phone" data-bm="Telefon">Phone</div>
                        <div class="apr-payer-value">{{ $user['phone_number'] }}</div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                {{-- ─── Items Paid ───────────────────────────────────────────────── --}}
                @php
                    $showFeeColumn = $order->application_type === 'Import Permit';
                @endphp

                <div class="apr-section-label" data-en="Items Paid" data-bm="Item Dibayar">Items Paid</div>
                <div class="apr-item-table">
                    <div class="apr-item-row apr-item-row-head">
                        <span data-en="Permit" data-bm="Permit">Permit</span>
                        <span data-en="Item" data-bm="Item">Item</span>
                        @if ($showFeeColumn)
                            <span class="apr-col-right" data-en="Fee" data-bm="Yuran">Fee</span>
                        @else
                            <span data-en="Quantity" data-bm="Kuantiti">Quantity</span>
                        @endif
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
                            @if ($showFeeColumn)
                                <span class="apr-col-right apr-item-fee">RM {{ number_format($feePerItem, 2) }}</span>
                            @else
                                <span>{{ number_format($quantity ?? 0) }} {{ $unit }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- ─── Category Summary (Consignment only) ───────────────────── --}}
                @if ($isConsignment && !empty($prices))
                    <div class="apr-section-label" style="margin-top:1.5rem;" data-en="Category Summary"
                        data-bm="Ringkasan Kategori">Category Summary</div>
                    <div class="apr-item-table">
                        <div class="apr-item-row apr-item-row-head">

                            <span data-en="Category" data-bm="Kategori">Category</span>
                            <span data-en="Quantity" data-bm="Kuantiti">Quantity</span>
                            <span class="apr-col-right" data-en="Total" data-bm="Jumlah">Total</span>
                        </div>
                        @foreach ($prices as $idx => $cat)
                            <div class="apr-item-row">

                                <span class="apr-item-name-cell">
                                    <span class="apr-item-name">{{ $cat['category_name'] ?? 'Uncategorized' }}</span>
                                </span>
                                <span>{{ number_format($cat['quantity'] ?? 0) }} kg</span>
                                <span class="apr-col-right apr-item-fee">RM
                                    {{ number_format($cat['price'] ?? 0, 2) }}</span>
                            </div>
                        @endforeach
                        <div class="apr-item-row" style="font-weight: 700; background-color: var(--gray-1);">
                            <span colspan="2" class="text-end" data-en="Total" data-bm="Jumlah">Total</span>
                            <span>{{ number_format(array_sum(array_column($prices, 'quantity'))) }} kg</span>
                            <span class="apr-col-right">RM
                                {{ number_format(array_sum(array_column($prices, 'price')), 2) }}</span>
                        </div>
                    </div>
                @endif

                <div class="apr-totals-block">
                    <div class="apr-totals-row">
                        <span data-en="Subtotal" data-bm="Jumlah Kasar">Subtotal</span>
                        <span>RM {{ number_format($order->payment_amount, 2) }}</span>
                    </div>
                    <div class="apr-totals-row">
                        <span data-en="Processing fee" data-bm="Yuran Pemprosesan">Processing fee</span>
                        <span>RM 0.00</span>
                    </div>
                    <div class="apr-totals-row is-grand">
                        <span data-en="Total paid" data-bm="Jumlah Dibayar">Total paid</span>
                        <span>RM {{ number_format($order->payment_amount, 2) }}</span>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-footer-note"
                    data-en="This is a computer-generated receipt and does not require a signature. For enquiries, contact Jabatan Pertanian Sabah at (088) 211 736."
                    data-bm="Ini adalah resit yang dijana komputer dan tidak memerlukan tandatangan. Untuk pertanyaan, hubungi Jabatan Pertanian Sabah di (088) 211 736.">
                    <i class="bi bi-info-circle"></i>
                    This is a computer-generated receipt and does not require a signature.
                    For enquiries, contact Jabatan Pertanian Sabah at (088) 211 736.
                </div>

            </div>

            <div class="apr-next-card">
                <div class="apr-next-title" data-en="What happens next" data-bm="Apa yang berlaku seterusnya">
                    <i class="bi bi-signpost-2"></i> What happens next
                </div>
                <div class="apr-next-steps">
                    <div class="apr-next-step">
                        <div class="apr-next-step-icon"><i class="bi bi-hourglass-split"></i></div>
                        <div>
                            <div class="apr-next-step-title" data-en="Bank authorization" data-bm="Pengesahan bank">Bank
                                authorization</div>
                            <div class="apr-next-step-desc"
                                data-en="Your payment is being verified by the bank. This usually takes a few minutes, but can take up to 1 business day."
                                data-bm="Pembayaran anda sedang disahkan oleh bank. Ini biasanya mengambil masa beberapa minit, tetapi boleh mengambil masa sehingga 1 hari perniagaan.">
                                Your payment is being verified by the bank. This usually takes a few minutes,
                                but can take up to 1 business day.
                            </div>
                        </div>
                    </div>
                    <div class="apr-next-step">
                        <div class="apr-next-step-icon"><i class="bi bi-patch-check"></i></div>
                        <div>
                            <div class="apr-next-step-title" data-en="Permit issuance" data-bm="Pengeluaran permit">
                                Permit issuance</div>
                            <div class="apr-next-step-desc"
                                data-en="Once payment is confirmed, each paid permit will move to Issued / Active status and become available for download."
                                data-bm="Setelah pembayaran disahkan, setiap permit yang dibayar akan bertukar kepada status Dikeluarkan / Aktif dan tersedia untuk dimuat turun.">
                                Once payment is confirmed, each paid permit will move to Issued / Active status
                                and become available for download.
                            </div>
                        </div>
                    </div>
                    <div class="apr-next-step">
                        <div class="apr-next-step-icon"><i class="bi bi-bell"></i></div>
                        <div>
                            <div class="apr-next-step-title" data-en="Notification" data-bm="Notifikasi">Notification
                            </div>
                            <div class="apr-next-step-desc"
                                data-en="You will receive an email and an in-app notification as soon as your permits are ready."
                                data-bm="Anda akan menerima e-mel dan notifikasi dalam aplikasi sebaik sahaja permit anda sedia.">
                                You will receive an email and an in-app notification as soon as your permits
                                are ready.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- ============================================================ --}}
            {{-- Failed / pending — simpler status card                      --}}
            {{-- ============================================================ --}}
            <div class="apr-receipt-card">
                <div class="apr-section-label" data-en="Order Details" data-bm="Butiran Pesanan">Order Details</div>
                <div class="apr-ref-grid">
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Order Number" data-bm="Nombor Pesanan">Order Number</div>
                        <div class="apr-ref-value">{{ $order->order_number }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Application Type" data-bm="Jenis Permohonan">Application Type
                        </div>
                        <div class="apr-ref-value">{{ $order->application_type }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="FPX Reference" data-bm="Rujukan FPX">FPX Reference</div>
                        <div class="apr-ref-value">{{ $order->fpx_seller_reference }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Amount" data-bm="Jumlah">Amount</div>
                        <div class="apr-ref-value">RM {{ number_format($order->payment_amount, 2) }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-en="Status" data-bm="Status">Status</div>
                        <div class="apr-ref-value">
                            <span class="apr-status-badge {{ $isFailed ? 'is-failed' : 'is-processing' }}">
                                @if ($isFailed)
                                    <span data-en="Failed" data-bm="Gagal">Failed</span>
                                @else
                                    <span data-en="Pending" data-bm="Tertunggak">Pending</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-section-label" data-en="Paid By" data-bm="Dibayar Oleh">Paid By</div>
                <div class="apr-payer-row">
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label" data-en="Name" data-bm="Nama">Name</div>
                        <div class="apr-payer-value">{{ $user['fullname'] }}</div>
                    </div>
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label" data-en="Email" data-bm="E-mel">Email</div>
                        <div class="apr-payer-value">{{ $user['email'] }}</div>
                    </div>
                    <div class="apr-payer-cell">
                        <div class="apr-payer-label" data-en="Phone" data-bm="Telefon">Phone</div>
                        <div class="apr-payer-value">{{ $user['phone_number'] }}</div>
                    </div>
                </div>

                <div class="apr-receipt-divider"></div>

                <div class="apr-section-label" data-en="Permit(s) in this Order" data-bm="Permit dalam Pesanan Ini">
                    Permit(s) in this Order</div>
                <div class="apr-item-table">
                    <div class="apr-item-row apr-item-row-head">
                        <span data-en="Permit" data-bm="Permit">Permit</span>
                        <span colspan="2" data-en="Item" data-bm="Item">Item</span>
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
                <i class="bi bi-arrow-left"></i> <span data-en="Back to Order List"
                    data-bm="Kembali ke Senarai Pesanan">Back to Order List</span>
            </a>
            @if ($applicationId)
                <a href="{{ $viewAppBaseUrl . $applicationId }}{{ $isFailed || $isPending ? '#pending' : '' }}"
                    class="apr-btn-primary">
                    <i class="bi bi-file-earmark-text"></i> <span data-en="View Application Status"
                        data-bm="Lihat Status Permohonan">View Application Status</span>
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
