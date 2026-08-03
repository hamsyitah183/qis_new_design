@extends('pages.app')

@section('pageName', 'Notifications')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    @vite(['resources/js/notification.js'])
@endpush

@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '/', 'data-en' => 'Home', 'data-bm' => 'Utama'], 
            ['label' => 'Notification', 'url' => '#', 'data-en' => 'Notification', 'data-bm' => 'Notifikasi']
        ]" 
        title="Notifications" 
        titleEn="Notifications" 
        titleBm="Notifikasi">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="col-xxl-12 col-md-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    <span class="mb-0 fs-16" id="readCount" data-en="Notifications" data-bm="Notifikasi">Notifications</span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-light border btn-full btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-filter me-1"></i> <span data-en="Filter" data-bm="Tapis">Filter</span> <i class="ti ti-chevron-down ms-1"></i>
                    </button>

                    <ul class="dropdown-menu" role="menu">
                        <li><a class="dropdown-item dropdown-item-notification active" href="#" data-time="" data-en="All" data-bm="Semua">All</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item dropdown-item-notification" href="#" data-time="1" data-en="Last 1 hour" data-bm="1 jam lepas">Last 1 hour</a></li>
                        <li><a class="dropdown-item dropdown-item-notification" href="#" data-time="24" data-en="Last 24 hours" data-bm="24 jam lepas">Last 24 hours</a></li>
                        <li><a class="dropdown-item dropdown-item-notification" href="#" data-time="168" data-en="Last 7 days" data-bm="7 hari lepas">Last 7 days</a></li>
                        <li><a class="dropdown-item dropdown-item-notification" href="#" data-time="720" data-en="Last 30 days" data-bm="30 hari lepas">Last 30 days</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0 pb-3">
                <ul class="list-group list-group-flush list-unstyled" id="notificationList">
                    <!-- Notifications will be loaded here -->
                </ul>
            </div>
        </div>
    </div>
@endsection