@extends('pages.app')

@push('style')
    {{-- adjust the path if your project loads CSS differently --}}
    @vite(['resources/css/pages/order/order-details.css'])
@endpush

@push('scripts')
    <script>
        window.AUTH_TYPE = @json(authUser()['type']);
    </script>
    @vite(['resources/js/pages/order/order_list.js'])
@endpush

@php
    $isInternal = authUser()['type'] == 'internal';

    $statusMap = [
        'completed' => 'success',
        'paid' => 'success',
        'successful' => 'success',
        'pending' => 'warning',
        'processing' => 'info',
        'failed' => 'danger',
        'unsuccessful' => 'danger',
        'cancelled' => 'gray',
    ];
    $statusColor = $statusMap[strtolower($order->status ?? '')] ?? 'secondary';
@endphp

@section('pageName', 'Order Details')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Order List', 'url' => '/order/list'],
        ['label' => 'Order Details', 'url' => '#'],
    ]" title="Order Details">
    </x-breadcrumb>
@endsection

@section('content')

    <div class="ipv-wrapper row g-4">

        <!-- ============================================================ -->
        <!-- LEFT: Order summary sidebar                                   -->
        <!-- ============================================================ -->
        <div class="col-xl-4 col-lg-5">
            <div class="ipv-side-card">

                <span class="ipv-tag is-{{ $statusColor }}">{{ $order->status ?? 'Unknown' }}</span>

                <div class="ipv-app-type mt-2" data-bm="Pesanan" data-en="Order">Order</div>
                <div class="ipv-app-id">{{ $order->order_number ?? '—' }}</div>

                <div class="ipv-divider"></div>

                <div class="ipv-section-label" data-bm="Permohonan Berkaitan" data-en="Linked Application">Linked Application</div>
                <div class="ipv-detail-row">
                    <div class="ipv-detail-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <span class="ipv-detail-label" data-bm="ID Permohonan" data-en="Application ID">Application ID</span>
                    <span class="ipv-detail-value">{{ $order->order_details['application']['application_id'] ?? ($application->application_id ?? '—') }}</span>
                </div>
                <div class="ipv-detail-row">
                    <div class="ipv-detail-icon"><i class="bi bi-box-seam"></i></div>
                    <span class="ipv-detail-label" data-bm="Permit" data-en="Permit(s)">Permit(s)</span>
                    <span class="ipv-detail-value">{{ $permits->pluck('permit_number')->implode(', ') ?: '—' }}</span>
                </div>

                <div class="ipv-divider"></div>

                <div class="ipv-value-box">
                    <div>
                        <div class="ipv-value-label" data-bm="Jumlah Bayaran" data-en="Payment Amount">Payment Amount</div>
                        <div class="ipv-value-amount">
                            {{ $order->payment_amount ? 'RM ' . number_format($order->payment_amount, 2) : '—' }}
                        </div>
                    </div>
                </div>

                @if ($order->order_details['application']['application_id'] ?? $application->application_id ?? null)
                    <a href="/view_application/{{ $order->order_details['application']['application_id'] ?? $application->application_id }}"
                        class="ipv-btn-outline w-100 justify-content-center mt-3">
                        <i class="bi bi-arrow-up-right-square"></i> <span data-bm="Lihat Permohonan" data-en="View Application">View Application</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- RIGHT: Details                                                 -->
        <!-- ============================================================ -->
        <div class="col-xl-8 col-lg-7">
            <div class="ipv-main-card">

                <!-- ---------- Payment Details ---------- -->
                <div class="ipv-section-label-row">
                    <span class="ipv-section-label" data-bm="Butiran Bayaran" data-en="Payment Details">Payment Details</span>
                </div>
                <div class="apr-ref-grid mb-2">
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-bm="Ruj Penjual" data-en="Seller Ref">Seller Ref</div>
                        <div class="apr-ref-value">{{ $order->seller_ref ?? '—' }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-bm="Rujukan Penjual FPX" data-en="FPX Seller Reference">FPX Seller Reference</div>
                        <div class="apr-ref-value">{{ $order->fpx_seller_reference ?? '—' }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-bm="Nama" data-en="Name">Name</div>
                        <div class="apr-ref-value">{{ $order->name ?? '—' }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-bm="E-mel" data-en="Email">Email</div>
                        <div class="apr-ref-value">{{ $order->email ?? '—' }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-bm="Telefon" data-en="Phone">Phone</div>
                        <div class="apr-ref-value">{{ $order->phone ?? '—' }}</div>
                    </div>
                    <div class="apr-ref-cell">
                        <div class="apr-ref-label" data-bm="Status Transaksi" data-en="Transaction Status">Transaction Status</div>
                        <div class="apr-ref-value">{{ $order->transaction_status ?? '—' }}</div>
                    </div>
                    @if ($isInternal)
                        <div class="apr-ref-cell">
                            <div class="apr-ref-label">Kod Transaksi</div>
                            <div class="apr-ref-value">{{ $order->kod_transaksi ?? '—' }}</div>
                        </div>
                    @endif
                </div>

                @if (!empty($order->transaction_data))
                    <details class="ipv-hint-note mb-3">
                        <summary style="cursor:pointer;color:var(--text-muted);font-size:.8rem;">
                            <i class="bi bi-code-slash"></i> <span data-bm="Lihat data transaksi asal" data-en="View raw transaction data">View raw transaction data</span>
                        </summary>
                        <pre class="mt-2 p-2" style="background:var(--gray-1);border-radius:8px;font-size:.72rem;white-space:pre-wrap;word-break:break-all;">{{ $order->transaction_data }}</pre>
                    </details>
                @endif

                <div class="ipv-divider"></div>

                <!-- ---------- Application Details (importer / exporter) ---------- -->
                <div class="ipv-section-label-row">
                    <span class="ipv-section-label" data-bm="Butiran Permohonan" data-en="Application Details">Application Details</span>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <div class="ipv-party">
                            <div class="ipv-party-header">
                                <div class="ipv-party-avatar">
                                    {{ strtoupper(substr(optional($application->importer)->fullname ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="ipv-party-name">{{ optional($application->importer)->fullname ?? '—' }}</div>
                                    <div class="ipv-party-sub" data-bm="Pengimport" data-en="Importer">Importer</div>
                                </div>
                            </div>
                            <div class="ipv-contact-row">
                                <div class="ipv-contact-icon"><i class="bi bi-geo-alt"></i></div>
                                <div>
                                    <div class="ipv-contact-label" data-bm="Alamat" data-en="Address">Address</div>
                                    <div class="ipv-contact-value">
                                        {{ optional($application->importer)->address_1 ?? '—' }}
                                        @if (!empty(optional($application->importer)->address_2)), {{ $application->importer->address_2 }} @endif
                                        @if (!empty(optional($application->importer)->postcode)), {{ $application->importer->postcode }} @endif
                                        @if (!empty(optional($application->importer)->districtInfo)), {{ $application->importer->districtInfo->name }} @endif
                                        @if (!empty(optional($application->importer)->stateInfo)), {{ $application->importer->stateInfo->name }} @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="ipv-party is-exporter">
                            <div class="ipv-party-header">
                                <div class="ipv-party-avatar">
                                    {{ strtoupper(substr(optional($application->exporter)->name ?? $fullname ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="ipv-party-name">{{ optional($application->exporter)->name ?? $fullname ?? '—' }}</div>
                                    <div class="ipv-party-sub" data-bm="Pengeksport" data-en="Exporter">Exporter</div>
                                </div>
                            </div>
                            <div class="ipv-contact-row">
                                <div class="ipv-contact-icon"><i class="bi bi-telephone"></i></div>
                                <div>
                                    <div class="ipv-contact-label" data-bm="Telefon" data-en="Phone">Phone</div>
                                    <div class="ipv-contact-value">{{ optional($application->exporter)->phone_no ?? $fullname ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="ipv-contact-row">
                                <div class="ipv-contact-icon"><i class="bi bi-geo-alt"></i></div>
                                <div>
                                    <div class="ipv-contact-label" data-bm="Alamat" data-en="Address">Address</div>
                                    <div class="ipv-contact-value">
                                        {{ optional($application->exporter)->address ?? '—' }},
                                        {{ optional(optional($application->exporter)->countryInfo)->name ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ipv-divider"></div>

                <!-- ---------- Permit Details ---------- -->
                <div class="ipv-section-label-row">
                    <span class="ipv-section-label"><span data-bm="Butiran Permit" data-en="Permit Details">Permit Details</span> ({{ $permits->count() }})</span>
                </div>

                @if ($permits->isEmpty())
                    <div class="ipv-empty-state">
                        <i class="bi bi-inbox"></i>
                        <p data-bm="Tiada permit dijumpai untuk pesanan ini." data-en="No permits found on this order.">No permits found on this order.</p>
                    </div>
                @else
                    <div class="apr-item-table">
                        <div class="apr-item-row apr-item-row-head">
                            <span data-bm="Nombor Permit" data-en="Permit Number">Permit Number</span>
                            <span data-bm="Nama Item" data-en="Item Name">Item Name</span>
                        </div>
                        @foreach ($permits as $permit)
                            <div class="apr-item-row">
                                <span class="apr-item-permit-no">{{ $permit->permit_number ?? '—' }}</span>
                                <span class="apr-item-name-cell">
                                    <span class="apr-item-name">{{ $permit->consignment_detail['item_name'] ?? '—' }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>

    </div>

@endsection