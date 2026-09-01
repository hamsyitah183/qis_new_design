@extends('pages.app')

@section('pageName', 'Dashboard')

@php
    $role = authUser()['roles'][0] ?? null;
@endphp

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => ' ', 'url' => '/']]" title="Welcome {{ authUser()['user']->fullname }}"
        title_en="Welcome {{ authUser()['user']->fullname }}" title_bm="Selamat Datang {{ authUser()['user']->fullname }}" />
@endsection

@section('content')
  
    @php
        $auth = authUser();
        $isRestrictedRole = false;
        if ($auth && $auth['type'] === 'internal') {
            $roles = $auth['roles']; // Collection of role names
            $restrictedRoles = ['boundary officer', 'finance'];
            $isRestrictedRole = $roles->intersect($restrictedRoles)->isNotEmpty();
        }
    @endphp

    @if ($overduePendingApps > 0 && !$isRestrictedRole)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger p-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 text-danger">
                                    <span data-en="Attention Required!" data-bm="Perhatian Diperlukan!">Attention
                                        Required!</span>
                                </h5>
                                <p class="mb-0">
                                    <span data-en="There are" data-bm="Terdapat">There are</span>
                                    <strong class="text-danger">{{ $overduePendingApps }}</strong>
                                    <span
                                        data-en="application(s) that have been pending for more than 3 days. Please review them as soon as possible."
                                        data-bm="permohonan yang telah tertangguh lebih daripada 3 hari. Sila semak segera.">
                                        application(s) that have been pending for more than 3 days. Please review them as
                                        soon as possible.
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
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
