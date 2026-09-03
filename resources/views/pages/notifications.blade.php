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
@php
    $pageNotifications = collect();
    try {
        $authUser = authUser();
        if (isset($authUser['user']) && isset($authUser['type'])) {
            $userType = $authUser['type'];
            $userUuid = $authUser['user']->uuid ?? null;
            if ($userUuid) {
                $pageNotifications = \Illuminate\Notifications\DatabaseNotification::where('notifiable_type', $userType)
                    ->where('notifiable_id', $userUuid)
                    ->latest()
                    ->get();
            }
        }
    } catch (\Throwable $e) {
        // Fallback gracefully
    }
@endphp

            <div class="card-body p-0 pb-3">
                <ul class="list-group list-group-flush list-unstyled" id="notificationList">
                    @if($pageNotifications->isEmpty())
                        <li class="list-group-item border-bottom-0 text-center">
                            <span class="fw-medium" data-en="No notification" data-bm="Tiada notifikasi">No notification</span>
                        </li>
                    @else
                        @foreach($pageNotifications as $notif)
                            @php
                                $notifData = $notif->data ?? [];
                                $rawUrl = $notifData['url'] ?? '#';
                                $rawUser = $notifData['user'] ?? 'System';
                                if (is_string($rawUser) && (str_starts_with(trim($rawUser), 'http') || str_starts_with(trim($rawUser), '/') || str_contains($rawUser, '://'))) {
                                    if ($rawUrl === '#' || !$rawUrl) {
                                        $rawUrl = trim($rawUser);
                                    }
                                    $rawUser = 'QIS System';
                                }
                                $user = $rawUser;

                                $msgData = $notifData['message'] ?? 'Notification';
                                if (is_array($msgData)) {
                                    $msgEn = $msgData['en'] ?? ($msgData['bm'] ?? 'Notification');
                                    $msgBm = $msgData['bm'] ?? ($msgData['en'] ?? 'Notifikasi');
                                } else {
                                    $msgEn = (string)$msgData;
                                    $msgBm = (string)$msgData;
                                }

                                if (is_string($msgEn) && (str_starts_with(trim($msgEn), 'http') || str_contains($msgEn, '://'))) {
                                    if ($rawUrl === '#' || !$rawUrl) {
                                        $rawUrl = trim($msgEn);
                                    }
                                    $msgEn = 'Application Update';
                                    $msgBm = 'Kemaskini Permohonan';
                                }

                                $diffTime = $notif->created_at ? $notif->created_at->diffForHumans() : 'Just now';
                            @endphp
                            <a href="{{ $rawUrl }}" class="list-group-item border-bottom-0 d-flex gap-2 align-items-start pb-2 border-bottom">
                                <div class="pe-2">
                                    <span class="avatar avatar-md bg-primary avatar-rounded">
                                        <i class="ri-notification-3-line"></i>
                                    </span>
                                </div>

                                <div class="text-wrap">
                                    <span class="fw-medium">{{ $user }}</span>
                                    <p class="text-muted mb-0 fs-12 w-100 text-wrap" data-en="{{ $msgEn }}" data-bm="{{ $msgBm }}">
                                        {{ $msgEn }}
                                    </p>
                                </div>

                                <span class="text-muted ms-auto fs-12" data-en="{{ $diffTime }}" data-bm="{{ $diffTime }}">
                                    {{ $diffTime }}
                                </span>
                            </a>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
    </div>
@endsection