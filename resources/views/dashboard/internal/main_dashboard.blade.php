@extends('pages.app')

@section('pageName', 'Dashboard')

@push('scripts')
@vite(['resources/js/pages/dashboard.js'])

@endpush




@section('breadcrumb')
    <x-breadcrumb :items="[
            ['label' => ' ', 'url' => '/'],
          
        ]" title="Welcome Admin">

    </x-breadcrumb>
@endsection

@section('content')

   
    {{-- finance and application data --}}
    @include('dashboard.internal.components.finance_application_data')



    {{-- public user chart and latest log activity --}}
    <div class="row align-items-stretch mb-2">
        @include('dashboard.internal.components.user_chart')
        @include('dashboard.internal.components.user_last_activity')
    </div>

    {{-- order --}}
    <div class="row align-items-stretch mt-4 mb-4">
        @include('dashboard.internal.components.order_chart')
        @include('dashboard.internal.components.payment_type_chart')
    </div>


@endsection

@push('scripts')
   
@endpush