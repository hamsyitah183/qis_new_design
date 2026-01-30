@extends('pages.app')

@section('pageName', 'Dashboard')


@php

    $role = authUser()['roles'][0];

@endphp




@section('breadcrumb')

    <x-breadcrumb :items="[['label' => ' ', 'url' => '/']]" title="Welcome  {{ authUser()['user']->fullname }}">


    </x-breadcrumb>
@endsection

@section('content')




    @if ($role == 'admin')
        @include ('dashboard.internal.admin_dashboard')
        @vite(['resources/js/pages/admin_dashboard.js'])
    @elseif ($role == 'clerk')
        @include ('dashboard.internal.clerk_dashboard')
        @vite(['resources/js/pages/clerk_dashboard.js'])
    @elseif($role == 'finance')
        @include('dashboard.internal.finance_dashboard')
    @elseif ($role == 'officer')
        @include('dashboard.internal.components.officer_dashboard')
        @vite(['resources/js/pages/officer_dashboard.js'])
    @endif

@endsection

@push('scripts')
@endpush
