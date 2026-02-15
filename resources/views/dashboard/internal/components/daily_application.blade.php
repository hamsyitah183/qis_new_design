<div class="row">
    {{-- Daily Application Volume (Line Chart) --}}
    <div class="col-xl-12">
        <div class="card custom-card">
          
            <div class="card-body">
                {{-- {!! $clerkVolumeChart->container() !!} --}}
                <div id="dailyVolumeChart">

                  

                </div>
            </div>
        </div>
    </div>
</div>



@push('scripts')
    {{-- ApexCharts CDN --}}

    <script src="{{ $clerkVolumeChart->cdn() }}"></script>

    {{-- Chart render script --}}
    {{ $clerkVolumeChart->script() }}

    <script>
            window.clerkVolumeChartId = "{{ $clerkVolumeChart->id }}";
    </script>
@endpush