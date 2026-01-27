<div class="col-12 col-lg-5 d-flex">
    <div class="card custom-card w-100 h-100 ">
        <div class="card-body p-4 ">
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