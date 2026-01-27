@extends('pages.app')

@section('pageName', 'Dashboard')

@php
    $role = authUser()['roles'][0];

@endphp

@push('scripts')
   @if ($role == 'admin')
    @vite(['resources/js/pages/admin_dashboard.js'])
   @endif
@endpush




@section('breadcrumb')
    <x-breadcrumb :items="[['label' => ' ', 'url' => '/']]" title="Welcome  {{ authUser()['user']->fullname }}">

    </x-breadcrumb>
@endsection

@section('content')
   
    

    @if ($role == 'admin')
        @include ('dashboard.internal.admin_dashboard')
    @elseif ($role == 'clerk')
        @include ('dashboard.internal.clerk_dashboard')
    @elseif($role == 'finance')
        @include('dashboard.internal.finance_dashboard')
    @endif

@endsection

@push('scripts')
@endpush
