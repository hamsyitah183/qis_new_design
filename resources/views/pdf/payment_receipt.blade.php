@extends('pdf.layout')

@section('title', 'Payment Receipt — ' . ($order->order_number ?? 'QIS'))
@section('doc-title', 'Official Payment Receipt')
@section('doc-subtitle', 'Order No: ' . ($order->order_number ?? '—'))

@php
    $docRef = $order->order_number ?? '—';
    $appType = $order->application_type ?? 'Application';
    $appId   = $order->order_details['application']['application_id'] ?? ($order->order_details['application']['id'] ?? '—');
    $paidAt  = optional($order->updated_at)->format('d M Y, h:i A') ?? '—';
@endphp

@section('extra-style')
<style>
    .receipt-hero {
        text-align: center;
        background: #eaf6ee;
        border: 2px solid #b7e0c2;
        border-radius: 10px;
        padding: 22px 20px;
        margin-bottom: 18px;
    }
    .receipt-hero .hero-label {
        font-size: 9pt;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #6b7280;
        margin-bottom: 6px;
    }
    .receipt-hero .hero-amount {
        font-size: 28pt;
        font-weight: bold;
        color: #226b3c;
        margin-bottom: 4px;
    }
    .receipt-hero .hero-status {
        display: inline-block;
        background: #2d8f4f;
        color: #fff;
        padding: 3px 16px;
        border-radius: 20px;
        font-size: 9pt;
        font-weight: bold;
        letter-spacing: 0.3px;
    }
    .info-grid {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
    }
    .info-grid td {
        width: 25%;
        vertical-align: top;
        padding: 10px 12px;
        border: 1px solid #eceff1;
        background: #fafbfc;
    }
    .info-grid .info-label {
        font-size: 7.5pt;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #9ca3af;
        margin-bottom: 3px;
    }
    .info-grid .info-value {
        font-size: 9.5pt;
        font-weight: bold;
        color: #1a1a1a;
        word-break: break-word;
    }
    .item-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
    }
    .item-table th,
    .item-table td {
        border: 1px solid #eceff1;
        padding: 7px 8px;
        font-size: 9pt;
    }
    .item-table th {
        background: #f4f7f5;
        color: #4b5563;
        text-align: left;
        font-size: 8pt;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: bold;
    }
    .item-table td.num { text-align: right; }
    .item-table tr:nth-child(even) td { background: #fafbfc; }
    .item-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 8px;
        font-size: 7.5pt;
        background: #f3f4f6;
        color: #4b5563;
        text-transform: capitalize;
    }
    .receipt-note {
        margin-top: 24px;
        padding: 12px 16px;
        background: #f9fafb;
        border-left: 4px solid #2d8f4f;
        font-size: 8pt;
        color: #6b7280;
        line-height: 1.6;
    }
    .empty-state {
        text-align: center;
        color: #9ca3af;
        padding: 24px 0;
        font-size: 9pt;
    }
</style>
@endsection

@section('content')

    {{-- ================= PAYMENT AMOUNT HERO ================= --}}
    <div class="receipt-hero">
        <div class="hero-label">Total Payment Amount</div>
        <div class="hero-amount">RM {{ number_format($order->payment_amount ?? 0, 2) }}</div>
        <div>
            <span class="hero-status">{{ ucfirst($order->transaction_status ?? 'Successful') }}</span>
        </div>
    </div>

    {{-- ================= PAYER & ORDER DETAILS ================= --}}
    <div class="section-block mb-3">
        <div class="section-title"><span class="section-icon"></span>Order &amp; Payer Details</div>
        <table class="info-grid">
            <tr>
                <td>
                    <div class="info-label">Order Number</div>
                    <div class="info-value">{{ $order->order_number ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-label">Payment Date</div>
                    <div class="info-value">{{ $paidAt }}</div>
                </td>
                <td>
                    <div class="info-label">Payer Name</div>
                    <div class="info-value">{{ $order->name ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $order->email ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $order->phone ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-label">Application Type</div>
                    <div class="info-value">{{ $appType }}</div>
                </td>
                <td>
                    <div class="info-label">Application ID</div>
                    <div class="info-value">{{ $appId }}</div>
                </td>
                <td>
                    <div class="info-label">Payment Method</div>
                    <div class="info-value">{{ $order->payment_type ?? 'FPX' }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ================= TRANSACTION REFERENCES ================= --}}
    <div class="section-block mb-3">
        <div class="section-title"><span class="section-icon"></span>Transaction References</div>
        <table class="info-grid">
            <tr>
                <td>
                    <div class="info-label">Seller Reference</div>
                    <div class="info-value">{{ $order->seller_ref ?? '—' }}</div>
                </td>
                <td colspan="2">
                    <div class="info-label">FPX Seller Reference</div>
                    <div class="info-value">{{ $order->fpx_seller_reference ?? '—' }}</div>
                </td>
                <td>
                    <div class="info-label">Transaction Status</div>
                    <div class="info-value">{{ ucfirst($order->transaction_status ?? '—') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ================= PERMIT SUMMARY TABLE ================= --}}
    <div class="section-block mb-3">
        <div class="section-title"><span class="section-icon"></span>Permit Summary</div>
        @if ($permits->isEmpty())
            <div class="empty-state">No permits found for this order.</div>
        @else
            <table class="item-table">
                <thead>
                    <tr>
                        <th>Permit Number</th>
                        <th>Item Name</th>
                        <th class="num">Quantity</th>
                        <th>Unit</th>
                        <th class="num">Value (RM)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permits as $permit)
                        @php
                            $detail = is_array($permit->consignment_detail)
                                ? $permit->consignment_detail
                                : (json_decode($permit->consignment_detail ?? '{}', true) ?? []);
                        @endphp
                        <tr>
                            <td>{{ $permit->permit_number ?? '—' }}</td>
                            <td>{{ $detail['item_name'] ?? ($permit->item_name ?? '—') }}</td>
                            <td class="num">{{ number_format($permit->quantity ?? 0, 2) }}</td>
                            <td>{{ $permit->unit_measurement ?? '—' }}</td>
                            <td class="num">{{ number_format($permit->value ?? 0, 2) }}</td>
                            <td><span class="item-status">{{ $permit->status ?? '—' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ================= OFFICIAL NOTE ================= --}}
    <div class="receipt-note">
        This is an official payment receipt issued by the <strong>Department of Agriculture Sabah</strong>.
        Please retain this document for your records. For enquiries, contact the department with the Order Number above.
    </div>

@endsection
