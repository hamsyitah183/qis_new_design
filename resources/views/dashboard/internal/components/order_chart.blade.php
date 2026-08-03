<div class="col-12 col-lg-5 d-flex">
    <div class="card custom-card w-100 h-100 ">
        <div class="card-body p-4 ">
            <div class="card-title fw-bold" data-en="Order Payment Status" data-bm="Status Pembayaran Pesanan">Order Payment Status</div>
            <div class="text-muted fs-13 mb-3" data-en="2026" data-bm="2026">2026</div>
            {!! $orderChart->container() !!}
        </div>
    </div>
</div>


@push('scripts')
    {{-- ApexCharts CDN --}}
    <script src="{{ $orderChart->cdn() }}"></script>

    {{-- Chart render script --}}
    {{ $orderChart->script() }}
@endpush