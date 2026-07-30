<div class="col-12 col-lg-7 d-flex">
    <div class="card custom-card w-100 h-100 ">
        <div class="card-body p-4 ">
            <div class="card-title fw-bold mb-3" data-en="Payment Method Revenue by Month" data-bm="Hasil Kaedah Pembayaran Mengikut Bulan">Payment Method Revenue by Month</div>
            {!! $paymentChart->container() !!}
        </div>
    </div>
</div>


@push('scripts')
    {{-- ApexCharts CDN --}}
    <script src="{{ $paymentChart->cdn() }}"></script>

    {{-- Chart render script --}}
    {{ $paymentChart->script() }}
@endpush