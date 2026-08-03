@extends('pages.app')

@section('pageName', 'Payment')

@push('scripts')
    <script>
        // Used by checkout.js to build payment logo URLs without
        // hardcoding the public path inside the JS module.
        window.PAYMENT_ASSET_BASE = "{{ asset('images/payment') }}";
        window.PERMITS = @json($permits);
        window.APPLICATION = @json($application);
        window.AUTH_USER = @json(authUser()['user']);
        window.PAYMENT_METHODS = @json($paymentMethod);
        window.TOTAL_AMOUNT = {{ $total }};
    </script>
    @vite(['resources/js/pages/checkout.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('Dashboard'),         'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Papan Pemuka'],
        ['label' => __('Application List'),  'url' => '/public/view_import_permit', 'data-en' => 'Application List', 'data-bm' => 'Senarai Permohonan'],
        ['label' => $application->application_id, 'url' => '/public/view_import_permit/' . $application->id],
        ['label' => __('Payment'),           'url' => '#', 'data-en' => 'Payment', 'data-bm' => 'Pembayaran'],
    ]" title="Payment" title_en="Payment" title_bm="Pembayaran">
    </x-breadcrumb>
@endsection

@section('content')

<div class="apy-wrapper">

    {{-- Page hero --}}
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

    {{-- Reference strip --}}
    <div class="apy-ref-strip">
        <div class="apy-ref-cell">
            <div class="apy-ref-label" data-en="Application ID" data-bm="ID Permohonan">Application ID</div>
            <div class="apy-ref-value" id="apyRefAppId">{{ $application->application_id }}</div>
        </div>
        <div class="apy-ref-cell">
            <div class="apy-ref-label" data-en="Application Type" data-bm="Jenis Permohonan">Application Type</div>
            <div class="apy-ref-value" id="apyRefAppType">{{ $application->application_type }}</div>
        </div>
        <div class="apy-ref-cell">
            <div class="apy-ref-label" data-en="Status" data-bm="Status">Status</div>
            <div class="apy-ref-value" id="apyRefStatus" data-en="Pending Payment" data-bm="Menunggu Pembayaran">Pending Payment</div>
        </div>
        <div class="apy-ref-cell">
            <div class="apy-ref-label" data-en="Amount Due" data-bm="Jumlah Perlu Dibayar">Amount Due</div>
            <div class="apy-ref-value" id="apyRefAmount">RM {{ number_format($total, 2) }}</div>
        </div>
    </div>

    {{-- Importer & Exporter --}}
    @if ($application->application_type == 'Import Permit' || $application->application_type == 'Inspection Certificate')
    <div class="apy-card">
        <div class="apy-card-title" data-en="Importer &amp; Exporter Details" data-bm="Butiran Pengimport &amp; Pengeksport">
            <i class="bi bi-people"></i> Importer &amp; Exporter Details
        </div>
        <div class="apy-parties" id="apyParties">
            <div class="apy-party-card">
                <div class="apy-party-header">
                    <div class="apy-party-avatar">{{ strtoupper(substr($application->importer->fullname, 0, 1)) }}</div>
                    <div>
                        <div class="apy-party-name">{{ $application->importer->fullname }}</div>
                        <div class="apy-party-sub" data-en="Importer" data-bm="Pengimport">Importer</div>
                    </div>
                </div>
                <div class="apy-party-row"><i class="bi bi-telephone"></i> {{ $application->importer->phone_number }}</div>
                <div class="apy-party-row"><i class="bi bi-envelope"></i> {{ $application->importer->email ?? authUser()['user']->email }}</div>
                <div class="apy-party-row"><i class="bi bi-geo-alt"></i> 
                    {{ $application->importer->address_1 }},
                    {{ $application->importer->address_2 ? $application->importer->address_2 . ', ' : '' }}
                    {{ $application->importer->postcode }},
                    {{ $application->importer->state }}
                </div>
            </div>

            <div class="apy-party-card">
                <div class="apy-party-header">
                    <div class="apy-party-avatar">{{ strtoupper(substr($application->exporter->name, 0, 1)) }}</div>
                    <div>
                        <div class="apy-party-name">{{ $application->exporter->name }}</div>
                        <div class="apy-party-sub" data-en="Exporter" data-bm="Pengeksport">Exporter</div>
                    </div>
                </div>
                <div class="apy-party-row"><i class="bi bi-telephone"></i> {{ $application->exporter->phone_no }}</div>
                <div class="apy-party-row"><i class="bi bi-envelope"></i> {{ $application->exporter->email ?? '—' }}</div>
                <div class="apy-party-row"><i class="bi bi-geo-alt"></i> {{ $application->exporter->address }}, {{ $application->exporter->countryInfo->name }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Main two‑column layout --}}
    <div class="apy-layout">

        {{-- LEFT — order items + payment method --}}
        <div class="apy-left-col">

            <div class="apy-card">
                <div class="apy-card-title" data-en="Items in this Order" data-bm="Item dalam Pesanan Ini">
                    <i class="bi bi-box-seam"></i> Items in this Order
                </div>
                <div class="apy-order-list" id="apyOrderList">
                    @foreach ($permits as $index => $permit)
                    <div class="apy-order-row">
                        <div class="apy-order-row-icon"><i class="bi bi-box-seam"></i></div>
                        <div class="apy-order-row-info">
                            <div class="apy-order-row-name">{{ $permit->consignment_detail['item_name'] ?? '—' }}</div>
                            <div class="apy-order-row-meta">
                                <span class="apy-permit-no">{{ $permit->permit_number ?? '—' }}</span>
                                <span class="apy-meta-sep">·</span>
                                <span>{{ $permit->consignment_detail['category'] ?? 'Import Permit' }}</span>
                                <span class="apy-meta-sep">·</span>
                                <span>{{ $permit->consignment_detail['quantity'] ?? $permit->consignment_detail['weight'] ?? '—' }} KG</span>
                            </div>
                        </div>
                        
                        <div class="apy-order-row-fee">RM 12</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="apy-card">
                <div class="apy-card-title" data-en="Payment Method" data-bm="Kaedah Pembayaran">
                    <i class="bi bi-credit-card-2-front"></i> Payment Method
                </div>
                <p class="apy-card-hint" data-en="BayuPay is selected by default — tap the <i class=&quot;bi bi-info-circle&quot;></i> icon on either option for step-by-step instructions."
                   data-bm="BayuPay dipilih secara lalai — ketik ikon <i class=&quot;bi bi-info-circle&quot;></i> pada mana-mana pilihan untuk arahan langkah demi langkah.">
                    BayuPay is selected by default — tap the <i class="bi bi-info-circle"></i> icon on either option for step-by-step instructions.
                </p>
                <div class="apy-payment-methods" id="apyPaymentMethods">
                    @foreach ($paymentMethod as $item)
                    <div class="apy-pm-option {{ $loop->first ? 'is-selected' : '' }}" data-method="{{ $item->name }}">
                        <label class="apy-pm-option-label">
                            <input type="radio" name="apyPaymentMethod" value="{{ $item->name }}" class="apy-pm-radio" {{ $loop->first ? 'checked' : '' }}>
                            <img src="{{ $item->pic }}" alt="{{ $item->name }}" class="apy-pm-logo">
                            <span class="apy-pm-name">{{ $item->name }}</span>
                        </label>
                        <button type="button" class="apy-pm-info-btn" data-method-info="{{ $item->name }}" title="How to pay with {{ $item->name }}">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- RIGHT — payment summary --}}
        <div class="apy-summary-col">
            <div class="apy-summary-card">

                <div class="apy-summary-title" data-en="Payment Summary" data-bm="Ringkasan Pembayaran">
                    <i class="bi bi-receipt"></i> Payment Summary
                </div>

                <div class="apy-summary-lines" id="apySummaryLines">
                    @foreach ($permits as $permit)
                    <div class="apy-summary-line">
                        <span>{{ $permit->permit_number ?? 'Item ' . ($loop->index + 1) }}</span>
                        <span>RM 12.00</span>
                    </div>
                    @endforeach
                </div>

                <div class="apy-summary-divider"></div>

                <div class="apy-totals">
                    <div class="apy-total-row">
                        <span data-en="Items in order" data-bm="Item dalam pesanan">Items in order</span>
                        <span id="apyTotalCount">{{ count($permits) }}</span>
                    </div>
                    <div class="apy-total-row">
                        <span data-en="Payment method" data-bm="Kaedah pembayaran">Payment method</span>
                        <span id="apySummaryMethod">{{ $paymentMethod[0]->name ?? '—' }}</span>
                    </div>
                    <div class="apy-total-row is-grand">
                        <span data-en="Total payable" data-bm="Jumlah perlu dibayar">Total payable</span>
                        <span id="apyGrandTotal">RM {{ number_format($total, 2) }}</span>
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

    {{-- Return to Application button --}}
    <div class="text-center mt-3">
        <button type="button" class="btn btn-outline-secondary" id="returnToApplication"
                data-app-id="{{ $application->application_id }}" 
                data-app-type="{{ $application->application_type }}">
            <i class="bi bi-arrow-left"></i> <span data-en="Return to Application" data-bm="Kembali ke Permohonan">Return to Application</span>
        </button>
    </div>

</div>

{{-- Hidden form for payment submission --}}
<form id="paymentForm" method="POST" action="{{ url('/payment') }}" style="display: none;">
    @csrf
    <input type="hidden" name="name" value="{{ authUser()['user']->fullname }}">
    <input type="hidden" name="email" value="{{ authUser()['user']->email }}">
    <input type="hidden" name="no_phone" value="{{ authUser()['user']->phone_number }}">
    <input type="hidden" name="amount" value="{{ $total }}">
    <input type="hidden" name="application_type" value="{{ $application->application_type }}">
    <input type="hidden" name="application_id" value="{{ $application->application_id }}">
    <input type="hidden" name="user_id" value="{{ authUser()['user']->uuid }}">
    <input type="hidden" name="paymentMethod" id="selectedPaymentMethod" value="{{ $paymentMethod[0]->name ?? '' }}">
    @foreach ($permits as $permit)
    <input type="hidden" name="permit_ids[]" value="{{ $permit->id }}">
    @endforeach
</form>

{{-- How to Pay modal --}}
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

{{-- Payment Confirmation modal --}}
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
                <div class="apy-confirm-summary" id="apyConfirmSummary">
                    <div class="apy-confirm-row">
                        <span data-en="Application ID" data-bm="ID Permohonan">Application ID</span>
                        <strong>{{ $application->application_id }}</strong>
                    </div>
                    <div class="apy-confirm-row">
                        <span data-en="Items in order" data-bm="Item dalam pesanan">Items in order</span>
                        <strong>{{ count($permits) }} items</strong>
                    </div>
                    <div class="apy-confirm-permits">
                        @foreach ($permits as $permit)
                        <div class="apy-confirm-permit-row">
                            <span class="apy-confirm-permit-no">{{ $permit->permit_number ?? '—' }}</span>
                            <span>{{ $permit->consignment_detail['item_name'] ?? '—' }}</span>
                            <span class="apy-confirm-fee">RM 12</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="apy-confirm-row is-total">
                        <span data-en="Total payable" data-bm="Jumlah perlu dibayar">Total payable</span>
                        <strong class="apy-confirm-total">RM {{ number_format($total, 2) }}</strong>
                    </div>
                    <div class="apy-confirm-row">
                        <span data-en="Payment method" data-bm="Kaedah pembayaran">Payment method</span>
                        <strong id="apyConfirmMethod">{{ $paymentMethod[0]->name ?? '—' }}</strong>
                    </div>
                </div>
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

{{-- Success overlay --}}
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