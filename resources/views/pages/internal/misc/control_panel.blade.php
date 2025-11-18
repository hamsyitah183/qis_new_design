@extends('pages.app')

@section('pageName', 'Control Panel')


@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
          
        ]" 
        title="System Control Panel"
    >
     
    </x-breadcrumb>
@endsection

@section('content')

                    <div class="row mb-5">
                        <div class="col-xl-3">
                            <div class="card custom-card">
                                <div class="card-body">
                                    <ul class="nav nav-tabs flex-column nav-tabs-header mb-0 mail-settings-tab" role="tablist">
                                        <li class="nav-item me-0" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#email-settings" aria-selected="false" tabindex="-1"><i class="ri-map-pin-line me-2 align-middle fs-14 lh-1 text-primary"></i>District Entry</a>
                                        </li>
                                        <li class="nav-item me-0" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#security" aria-selected="false" tabindex="-1"><i class="ri-flag-2-line me-2 align-middle fs-14 lh-1 text-primary"></i> Consignment Purpose</a>
                                        </li>
                                        <li class="nav-item me-0" role="presentation">
                                            <a class="nav-link" data-bs-toggle="tab" role="tab" aria-current="page" href="#notification-settings" aria-selected="false" tabindex="-1"><i class="ri-ruler-line me-2 align-middle fs-14 lh-1 text-primary"></i>Unit Measurement</a>
                                        </li>
                                        <li class="nav-item me-0" role="presentation">
                                            <a class="nav-link active" data-bs-toggle="tab" role="tab" aria-current="page" href="#account-settings" aria-selected="true"><i class="ri-folders-line me-2 align-middle fs-14 lh-1 text-primary"></i>Condition Category</a>
                                        </li>
                                        <li class="nav-item me-0" role="presentation">
                                            <a class="nav-link " data-bs-toggle="tab" role="tab" aria-current="page" href="#rejection-settings" aria-selected="true"><i class="ri-file-shield-line me-2 align-middle fs-14 lh-1 text-danger"></i>Rejection Notes</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-9">
                            <div class="card custom-card">
                                <div class="card-body p-0">
                                    <div class="tab-content border-0">
                                        <!-- tab1 -->
                                        @include('pages.internal.misc.cp_tab1')
                                        <!-- tab2 -->
                                        @include('pages.internal.misc.cp_tab2')
                                        <!-- tab3 -->                                        
                                        @include('pages.internal.misc.cp_tab3')
                                        <!-- tab4 --> 
                                        @include('pages.internal.misc.cp_tab4')
                                        <!-- tab5 -->
                                        @include('pages.internal.misc.cp_tab5')                                        
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>

@endsection

@push('scripts')
<script>
    window.baseUrl = "{{ url('/') }}";
</script>

    
@endpush

