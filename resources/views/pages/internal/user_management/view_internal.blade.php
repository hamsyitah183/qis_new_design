@extends('pages.app')

@php
    $name = $user ? $user->fullname : 'Guest';
    $initials = collect(explode(' ', $name))
        ->map(fn($word) => strtoupper(Str::substr($word, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

@section('pageName', 'View User Profile')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'Internal User List', 'url' => route('internal.internal.list')], ['label' => 'View User', 'url' => '#']]" title="View User">
    </x-breadcrumb>
@endsection

@section('content')
    <div class="container-lg">
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card profile-card">
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
                                            <h5 class="fw-semibold mb-1">{{ $user->fullname }}</h5>

                                            <p class="fs-12 mb-0 text-muted">
                                                <span>
                                                    <i class="ri-map-pin-line me-1 align-middle "></i>
                                                    <span class="address">{{ $user->address_1 }}</span>
                                                </span>
                                            </p>
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
                                                <div><span class="fw-medium me-2" data-en="Name :" data-bm="Nama :">Name :</span><span class="text-muted">{{ $user->fullname }}</span></div>
                                            </li>

                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Email :" data-bm="E-mel :">Email :</span><span class="text-muted">{{ $user->email }}</span></div>
                                            </li>
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Phone :" data-bm="Telefon :">Phone :</span><span class="text-muted">{{ $user->phone_number }}</span>
                                                </div>
                                            </li>
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="IC :" data-bm="KP :">IC :</span><span class="text-muted">{{ $user->no_ic }}</span>
                                                </div>
                                            </li>
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Position :" data-bm="Jawatan :">Position :</span><span class="text-muted">{{ $user->position }}</span>
                                                </div>
                                            </li>
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Branch :" data-bm="Cawangan :">Branch :</span><span class="text-muted">{{ $user->branch }}</span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-9">
                                <!-- tabs -->
                                <div class="card custom-card overflow-hidden border">
                                    <div class="card-body">
                                        <ul class="nav nav-tabs tab-style-6 mb-3 p-0" id="myTab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link w-100 text-start active" id="profile-about-tab" data-bs-toggle="tab"
                                                    data-bs-target="#profile-about-tab-pane" type="button" role="tab"
                                                    aria-controls="profile-about-tab-pane" aria-selected="true" data-en="Profile" data-bm="Profil">Profile</button>
                                            </li>

                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link w-100 text-start" id="activity-log-tab" data-bs-toggle="tab"
                                                    data-bs-target="#activity-log-tab-pane" type="button" role="tab"
                                                    aria-controls="activity-log-tab-pane" aria-selected="false" tabindex="-1" data-en="Activity Log" data-bm="Log Aktiviti">Activity Log</button>
                                            </li>

                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link w-100 text-start" id="permission-list-tab" data-bs-toggle="tab"
                                                    data-bs-target="#permission-list-tab-pane" type="button" role="tab"
                                                    aria-controls="permission-list-tab-pane" aria-selected="false" tabindex="-1" data-en="Permission List" data-bm="Senarai Kebenaran">Permission List</button>
                                            </li>

                                            <li class="nav-item d-none" role="presentation">
                                                <button class="nav-link w-100 text-start" id="vehicle-list-tab" data-bs-toggle="tab"
                                                    data-bs-target="#vehicle-list-tab-pane" type="button" role="tab"
                                                    aria-controls="vehicle-list-tab-pane" aria-selected="false" tabindex="-1" data-en="Vehicle List" data-bm="Senarai Kenderaan">Vehicle List</button>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="profile-tabs">
                                            <!-- Profile Info Tab -->
                                            <div class="tab-pane show active p-0 border-0" id="profile-about-tab-pane" role="tabpanel" aria-labelledby="profile-about-tab" tabindex="0">
                                                <div class="p-3">
                                                    <h6 class="fw-semibold mb-3" data-en="Job Information" data-bm="Maklumat Pekerjaan">Job Information</h6>
                                                    <p><strong data-en="Position:" data-bm="Jawatan:">Position:</strong> {{ $user->position }}</p>
                                                    <p><strong data-en="Office:" data-bm="Pejabat:">Office:</strong> {{ $user->office }}</p>
                                                    <p><strong data-en="Branch:" data-bm="Cawangan:">Branch:</strong> {{ $user->branch }}</p>
                                                    <h6 class="fw-semibold mb-3 mt-4" data-en="Roles" data-bm="Peranan">Roles</h6>
                                                    @if(isset($user->roles) && $user->roles->count() > 0)
                                                        @foreach($user->roles as $role)
                                                            <span class="badge bg-primary">{{ $role->name }}</span>
                                                        @endforeach
                                                    @else
                                                        <p class="text-muted">No roles assigned.</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Activity Log Tab -->
                                            <div class="tab-pane p-0 border-0" id="activity-log-tab-pane" role="tabpanel" aria-labelledby="activity-log-tab" tabindex="0">
                                                <div class="p-3">
                                                    <h6 class="fw-semibold mb-3">Activity Log</h6>
                                                    @if(isset($activities) && $activities->count() > 0)
                                                        <ul class="list-group">
                                                            @foreach($activities as $log)
                                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                    {{ $log->description }}
                                                                    <span class="text-muted small">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p class="text-muted">No activity logs found.</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Permission List Tab -->
                                            <div class="tab-pane p-0 border-0" id="permission-list-tab-pane" role="tabpanel" aria-labelledby="permission-list-tab" tabindex="0">
                                                <div class="p-3">
                                                    <h6 class="fw-semibold mb-3" data-en="Assigned Permissions" data-bm="Kebenaran yang Diberikan">Assigned Permissions</h6>
                                                    @if(isset($user) && $user->getAllPermissions()->count() > 0)
                                                        @php
                                                            $permTrans = [
                                                                'approve public user' => 'lulus pengguna awam',
                                                                'create internal user' => 'cipta pengguna dalaman',
                                                                'create public user' => 'cipta pengguna awam',
                                                                'delete internal user' => 'padam pengguna dalaman',
                                                                'delete public user' => 'padam pengguna awam',
                                                                'manage settings' => 'urus tetapan',
                                                                'read activity log' => 'papar log aktiviti',
                                                                'read internal user' => 'papar pengguna dalaman',
                                                                'read public user' => 'papar pengguna awam',
                                                                'update internal user' => 'kemas kini pengguna dalaman',
                                                                'update public user' => 'kemas kini pengguna awam',
                                                                'view dashboard' => 'papar papan pemuka',
                                                                'view exporter list' => 'papar senarai pengeksport',
                                                                'view importer list' => 'papar senarai pengimport'
                                                            ];
                                                        @endphp
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach($user->getAllPermissions() as $permission)
                                                                @php
                                                                    $bmText = $permTrans[$permission->name] ?? $permission->name;
                                                                @endphp
                                                                <span class="badge bg-info" data-en="{{ $permission->name }}" data-bm="{{ $bmText }}">{{ $permission->name }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-muted" data-en="No permissions found." data-bm="Tiada kebenaran dijumpai.">No permissions found.</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Vehicle List Tab -->
                                            <div class="tab-pane p-0 border-0" id="vehicle-list-tab-pane" role="tabpanel" aria-labelledby="vehicle-list-tab" tabindex="0">
                                                <div class="p-3">
                                                    <h6 class="fw-semibold mb-3" data-en="Vehicle List" data-bm="Senarai Kenderaan">Vehicle List</h6>
                                                    <p class="text-muted" data-en="No vehicles added yet." data-bm="Tiada kenderaan ditambah lagi.">No vehicles added yet.</p>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
