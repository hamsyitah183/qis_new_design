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

   @php
       $role = authUser()['roles'][0];

   @endphp

   @if ($role == 'admin')
         @include ('dashboard.internal.admin_dashboard')
    
    @elseif ($role == 'clerk')
         @include ('dashboard.internal.clerk_dashboard')
    
    @endif

@endsection

@push('scripts')
   
@endpush