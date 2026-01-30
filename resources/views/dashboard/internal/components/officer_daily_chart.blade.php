<div class="row">
    {{-- Daily Application Volume (Line Chart) --}}
    <div class="col-xl-12">
        <div class="card custom-card">
          
            <div class="card-body">
                {!! $permitChart->container() !!}
            </div>
        </div>
    </div>
</div>



@push('scripts')
    {{-- ApexCharts CDN --}}

    <script src="{{ $permitChart->cdn() }}"></script>

    {{-- Chart render script --}}
    {{ $permitChart->script() }}

  
@endpush