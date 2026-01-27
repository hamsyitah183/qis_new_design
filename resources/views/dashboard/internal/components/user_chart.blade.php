<div class="col-12 col-lg-8 col-xl-7 d-flex">
    <div class="card custom-card w-100 h-100 ">
        <div class="card-body p-4 ">
            {!! $userLineChart->container() !!}
        </div>
    </div>
</div>


@push('scripts')
    {{-- ApexCharts CDN --}}
    <script src="{{ $userLineChart->cdn() }}"></script>

    {{-- Chart render script --}}
    {{ $userLineChart->script() }}
@endpush