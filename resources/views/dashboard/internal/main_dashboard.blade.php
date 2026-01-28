@extends('pages.app')

@section('pageName', 'Dashboard')

@push('scripts')
    @vite(['resources/js/pages/dashboard.js'])

@endpush




@section('breadcrumb')
    <x-breadcrumb :items="[
            ['label' => ' ', 'url' => '/'],
<<<<<<< HEAD

        ]" title="Welcome {{ authUser()['user']->fullname }}">
=======
          
        ]" title="Welcome ">
>>>>>>> cbeb9327aa5425b06eff6e8a132b8b8c7a8fb9b5

    </x-breadcrumb>
@endsection

@section('content')

<<<<<<< HEAD
    @php
        $role = authUser()['roles'][0];

    @endphp

    @if ($role == 'admin')
        @include('dashboard.internal.components.admin_dashboard')

    @elseif ($role == 'officer')
        @include('dashboard.internal.components.officer_dashboard')
=======

   @php
       $role = authUser()['roles'][0];

   @endphp

   @if ($role == 'admin')
         @include ('dashboard.internal.admin_dashboard')
    
    @elseif ($role == 'clerk')
         @include ('dashboard.internal.clerk_dashboard')
    

    @elseif($role == 'finance')
        @include('dashboard.internal.finance_dashboard')
>>>>>>> cbeb9327aa5425b06eff6e8a132b8b8c7a8fb9b5

    @endif

@endsection

@push('scripts')

@endpush