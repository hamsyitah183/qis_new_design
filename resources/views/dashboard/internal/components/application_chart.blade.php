<div class="col-12 col-lg-5 d-flex">
    <div class="card custom-card w-100 h-100 ">
        <div class="card-body p-4 ">
            {!! $applicationChart->container() !!}
        </div>
    </div>
</div>


@push('scripts')
    {{-- ApexCharts CDN --}}
    <script src="{{ $applicationChart->cdn() }}"></script>

    {{-- Chart render script --}}
    {{ $applicationChart->script() }}
@endpush