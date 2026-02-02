{{-- finance and application data --}}
@include('dashboard.internal.components.finance_application_data')
{{-- @dd($data) --}}

@include('dashboard.internal.components.daily_application')
{{-- <div id="dailyVolumeChart"></div> --}}


{{-- public user chart and latest log activity --}}
<div class="row align-items-stretch mb-2">
    @include('dashboard.internal.components.user_chart')
    <div class="col-12 col-lg-4 col-xl-5 d-flex">
    
            @include('dashboard.internal.components.recent_activity')
   
    </div>
</div>

{{-- order --}}
<div class="row align-items-stretch mt-4 mb-4">
    @include('dashboard.internal.components.order_chart')
    @include('dashboard.internal.components.payment_type_chart')
</div>
