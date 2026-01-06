@extends('pages.app')

@section('pageName', 'Notifications')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
    {{-- @vite(['resources/js/pages/internal/misc/permit_condition_list.js']) --}}
    @vite(['resources/js/notification.js'])
@endpush


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '/'], ['label' => 'Notification', 'url' => '#']]" title="Notifications">

    </x-breadcrumb>
@endsection


@section('content')
    <div class="col-xxl-12 col-md-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    <span class="mb-0 fs-16" id="readCount">Notifications</span>
                </div>
                <div class="dropdown">
                    <div class="btn btn-outline-light border btn-full btn-sm" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        View All <i class="ti ti-chevron-down ms-1"></i>
                    </div>

                    <ul class="dropdown-menu" role="menu">
                        <li><a class="dropdown-item dropdown-item-notification" href="#" data-time="1">Last 1 hr</a></li>
                        <li><a class="dropdown-item dropdown-item-notification" href="#" data-time="24">Last 24 hrs</a></li>
                        <li><a class="dropdown-item dropdown-item-notification" href="#" data-time="168">Last 7 days</a></li>
                        <li><a class="dropdown-item dropdown-item-notification" href="#" data-time="720">Last 30 days</a></li>
                    </ul>
                </div>

            </div>
            <div class="card-body p-0 pb-3">
                <ul class="list-group list-group-flush list-unstyled" id="notificationList">
                    {{-- <li class="list-group-item border-bottom-0 d-flex gap-2 align-items-start pb-2">
                        <div
                            class="avatar avatar-sm bg-primary-transparent flex-shrink-0 avatar-rounded border border-primary border-opacity-10">
                            <i class="ri-user-fill"></i>
                        </div>
                        <div class="text-truncate">
                            <span class="fw-medium">New Job Posted</span>
                            <p class="text-muted mb-0 fs-12 w-80 text-truncate">Frontend Developer</p>
                        </div>
                        <span class="text-muted ms-auto fs-12 flex-shrink-0">2 mins ago</span>
                    </li> --}}

                </ul>

            </div>
        </div>
    </div>

@endsection
