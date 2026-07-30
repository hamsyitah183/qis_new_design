<div class="row">
    {{-- Daily Application Volume (Line Chart) --}}
    <div class="col-xl-12">
        <div class="card custom-card">
          
            <div class="card-body">
                {{-- {!! $clerkVolumeChart->container() !!} --}}
                <div class="card-title fw-bold" data-en="Daily Application Volume" data-bm="Jumlah Permohonan Harian">Daily Application Volume</div>
                <div class="text-muted fs-13 mb-3" data-en="Total submissions across all modules (last 7 days) — demo data" data-bm="Jumlah serahan merentasi semua modul (7 hari lepas) — data demo">Total submissions across all modules (last 7 days) — demo data</div>
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