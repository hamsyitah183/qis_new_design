@extends('pages.app')

@section('pageName', 'Dashboard')

@php
    $role = authUser()['roles'][0] ?? null;
@endphp

@section('breadcrumb')
    <x-breadcrumb
        :items="[['label' => ' ', 'url' => '/']]"
        title="Welcome {{ authUser()['user']->fullname }}"
    />
@endsection

@section('content')

    {{-- DASHBOARD CONTENT --}}
    @if (in_array($role, ['admin', 'superadmin']))
        @include('dashboard.internal.admin_dashboard')

    @elseif ($role === 'clerk')
        @include('dashboard.internal.clerk_dashboard')

    @elseif ($role === 'finance')
        @include('dashboard.internal.finance_dashboard')

    @elseif ($role === 'officer')
        @include('dashboard.internal.components.officer_dashboard')

    @elseif ($role === 'boundary officer')
        @include('dashboard.internal.boundary_dashboard')
    @endif

@endsection

{{-- SCRIPTS --}}
@push('scripts')
    @if (in_array($role, ['admin', 'superadmin']))
        @vite(['resources/js/pages/dashboard/admin_dashboard.js'])

    @elseif ($role === 'clerk')
        @vite(['resources/js/pages/dashboard/clerk_dashboard.js'])

    @elseif ($role === 'officer')
        @vite(['resources/js/pages/dashboard/officer_dashboard.js'])
    @endif
@endpush
