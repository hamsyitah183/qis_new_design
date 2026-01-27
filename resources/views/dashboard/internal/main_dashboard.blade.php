@extends('pages.app')

@section('pageName', 'Dashboard')

@push('scripts')
    @vite(['resources/js/pages/dashboard.js'])

@endpush




@section('breadcrumb')
    <x-breadcrumb :items="[
            ['label' => ' ', 'url' => '/'],

        ]" title="Welcome {{ authUser()['user']->fullname }}">

    </x-breadcrumb>
@endsection

@section('content')

    @php
        $role = authUser()['roles'][0];

    @endphp

    @if ($role == 'admin')
        @include('dashboard.internal.components.admin_dashboard')

    @elseif ($role == 'officer')
        @include('dashboard.internal.components.officer_dashboard')

    @endif

@endsection

@push('scripts')

@endpush