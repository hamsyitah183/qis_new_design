@extends('pages.app')

@push('scripts')
    @vite(['resources/js/pages/auth/profile.js', 'resources/js/pages/auth/verification.js'])
@endpush

@php
    $user = authUser();
    $name = $user['user'] ? $user['user']->fullname : 'Guest';
    $initials = collect(explode(' ', $name))
        ->map(fn($word) => strtoupper(Str::substr($word, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

@section('pageName', 'My Profile')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'My Profile', 'url' => '#']]" title="My Profile">

    </x-breadcrumb>
@endsection

@section('content')
    <div class="container-lg">
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card profile-card">
                    {{-- <div class="profile-banner-img">
                        <img src="https://laravelui.spruko.com/xintra/build/assets/images/media/media-3.jpg"
                            class="card-img-top" alt="...">
                    </div> --}}
                    <div class="profile-banner-img bg-primary" style="height: 150px;"></div>

                    <div class="card-body pb-0 position-relative">
                        <div class="row profile-content">
                            <div class="col-xl-3">
                                <div class="card custom-card overflow-hidden border">
                                    <div class="card-body border-bottom border-block-end-dashed">
                                        <div class="text-center">
                                            <span
                                                class="avatar avatar-xxl d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold fs-3"
                                                style="width: 64px; height: 64px;">
                                                {{ $initials }}
                                            </span>
                                            <h5 class="fw-semibold mb-1  "> <span class="fullname"></span> </h5>

                                            <p class = "fs-13 mainFullName"></p>

                                            @if ($user['type'] == 'public')
                                                <p class="fs-12 mb-0 text-muted">
                                                    <span>
                                                        <i class="ri-map-pin-line me-1 align-middle "></i>
                                                        <span class="address"></span>
                                                    </span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="p-3 pb-1 d-flex flex-wrap justify-content-between">
                                        <div class="fw-medium fs-15 text-primary1" data-en="Basic Info :" data-bm="Maklumat Asas :">
                                            Basic Info :
                                        </div>
                                    </div>
                                    <div class="card-body border-bottom border-block-end-dashed p-0">
                                        <ul class="list-group list-group-flush" id="basicInfo">
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Name :" data-bm="Nama :">Name :</span><span
                                                        class="text-muted fullname"></span></div>
                                            </li>

                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Email :" data-bm="E-mel :">Email :</span><span
                                                        class="text-muted email"></span></div>
                                            </li>
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Phone :" data-bm="Telefon :">Phone :</span>
                                                    <span class="text-muted phone_number"></span>
                                                </div>
                                            </li>
                                            @if ($user['type'] == 'internal')
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="IC :" data-bm="KP :">IC :</span>
                                                    <span class="text-muted ic"></span>
                                                </div>
                                            </li>
                                            @else
                                                @if($user['user']['account_type'] == 'individu')
                                                     <li class="list-group-item pt-2 border-0">
                                                        <div><span class="fw-medium me-2" data-en="IC :" data-bm="KP :">IC :</span>
                                                            <span class="text-muted ic"></span>
                                                        </div>
                                                    </li>
                                                @else
                                                    <li class="list-group-item pt-2 border-0">
                                                        <div><span class="fw-medium me-2" data-en="Company Number :" data-bm="Nombor Syarikat :">Company Number :</span>
                                                            <span class="text-muted ic"></span>
                                                        </div>
                                                    </li>
                                                @endif
                                            @endif
                                            @if ($user['type'] == 'internal')
                                                <li class="list-group-item pt-2 border-0">
                                                    <div><span class="fw-medium me-2" data-en="Role :" data-bm="Peranan :">Role :</span>
                                                        <span class="text-muted role">{{ $user['roles'][0] }}</span>
                                                    </div>
                                                </li>
                                            @endif

                                        </ul>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-9">
                                @include('pages.authentication.includes.main-profile')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
