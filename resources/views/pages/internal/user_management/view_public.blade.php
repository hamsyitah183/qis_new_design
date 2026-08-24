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
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        ['label' => 'Public User List', 'url' => route('internal.public.list')],
        ['label' => 'View User', 'url' => '#'],
    ]" title="View User">
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
                                        <div class="fw-medium fs-15 text-primary1" data-en="Basic Info :"
                                            data-bm="Maklumat Asas :">
                                            Basic Info :
                                        </div>
                                    </div>
                                    <div class="card-body border-bottom border-block-end-dashed p-0">
                                        <ul class="list-group list-group-flush" id="basicInfo">
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Name :" data-bm="Nama :">Name
                                                        :</span><span class="text-muted">{{ $user->fullname }}</span></div>
                                            </li>

                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Email :" data-bm="E-mel :">Email
                                                        :</span><span class="text-muted">{{ $user->email }}</span></div>
                                            </li>
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Phone :"
                                                        data-bm="Telefon :">Phone :</span><span
                                                        class="text-muted">{{ $user->phone_number }}</span>
                                                </div>
                                            </li>
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="IC :" data-bm="KP :">IC
                                                        :</span><span class="text-muted">{{ $user->no_ic }}</span>
                                                </div>
                                            </li>
                                            <li class="list-group-item pt-2 border-0">
                                                <div><span class="fw-medium me-2" data-en="Account Type :"
                                                        data-bm="Jenis Akaun :">Account Type :</span><span
                                                        class="text-muted">{{ ucfirst($user->account_type) }}</span>
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
                                                <button class="nav-link w-100 text-start active" id="profile-about-tab"
                                                    data-bs-toggle="tab" data-bs-target="#profile-about-tab-pane"
                                                    type="button" role="tab" aria-controls="profile-about-tab-pane"
                                                    aria-selected="true" data-en="Profile" data-bm="Profil">Profile</button>
                                            </li>

                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link w-100 text-start" id="activity-log-tab"
                                                    data-bs-toggle="tab" data-bs-target="#activity-log-tab-pane"
                                                    type="button" role="tab" aria-controls="activity-log-tab-pane"
                                                    aria-selected="false" tabindex="-1" data-en="Activity Log"
                                                    data-bm="Log Aktiviti">Activity Log</button>
                                            </li>

                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link w-100 text-start" id="list-document-tab"
                                                    data-bs-toggle="tab" data-bs-target="#list-document-tab-pane"
                                                    type="button" role="tab" aria-controls="list-document-tab-pane"
                                                    aria-selected="false" tabindex="-1" data-en="List Documents"
                                                    data-bm="Senarai Dokumen">List Documents</button>
                                            </li>

                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link w-100 text-start" id="vehicle-list-tab"
                                                    data-bs-toggle="tab" data-bs-target="#vehicle-list-tab-pane"
                                                    type="button" role="tab" aria-controls="vehicle-list-tab-pane"
                                                    aria-selected="false" tabindex="-1" data-en="Vehicle List"
                                                    data-bm="Senarai Kenderaan">Vehicle List</button>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="profile-tabs">
                                            <!-- Profile Info Tab -->
                                            <div class="tab-pane show active p-0 border-0" id="profile-about-tab-pane"
                                                role="tabpanel" aria-labelledby="profile-about-tab" tabindex="0">
                                                <div class="p-3">
                                                    <h6 class="fw-semibold mb-3" data-en="Personal Information"
                                                        data-bm="Maklumat Peribadi">Personal Information</h6>

                                                    <div class="row gy-3">
                                                        <div class="col-xl-6">
                                                            <strong data-en="Full Name" data-bm="Nama Penuh">Full
                                                                Name:</strong>
                                                            <span class="text-muted">{{ $user->fullname }}</span>
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <strong data-en="Email" data-bm="E-mel">Email:</strong>
                                                            <span class="text-muted">{{ $user->email }}</span>
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <strong data-en="Phone Number" data-bm="Nombor Telefon">Phone
                                                                Number:</strong>
                                                            <span class="text-muted">{{ $user->phone_number }}</span>
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <strong data-en="Office Number"
                                                                data-bm="Nombor Pejabat">Office Number:</strong>
                                                            <span
                                                                class="text-muted">{{ $user->office_number ?? '—' }}</span>
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <strong data-en="IC Number" data-bm="Nombor KP">IC
                                                                Number:</strong>
                                                            <span class="text-muted">{{ $user->no_ic }}</span>
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <strong data-en="Account Type" data-bm="Jenis Akaun">Account
                                                                Type:</strong>
                                                            <span
                                                                class="badge bg-info">{{ ucfirst($user->account_type) }}</span>
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <strong data-en="DOA Verified" data-bm="Disahkan DOA">DOA
                                                                Verified:</strong>
                                                            <span
                                                                class="badge bg-{{ $user->doa_verified ? 'success' : 'warning' }}">
                                                                {{ $user->doa_verified ? 'Yes' : 'Pending' }}
                                                            </span>
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <strong data-en="Email Verified"
                                                                data-bm="E-mel Disahkan">Email Verified:</strong>
                                                            <span
                                                                class="badge bg-{{ $user->email_verified_at ? 'success' : 'danger' }}">
                                                                {{ $user->email_verified_at ? 'Yes' : 'No' }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    @if ($user->account_type === 'company' && !empty($user->person_in_charge))
                                                        <hr>
                                                        <h6 class="fw-semibold mb-3" data-en="Persons In Charge"
                                                            data-bm="Orang Bertanggungjawab">
                                                            Persons In Charge
                                                        </h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th data-en="Name" data-bm="Nama">Name</th>
                                                                        <th data-en="Position" data-bm="Jawatan">Position
                                                                        </th>
                                                                        <th data-en="Phone" data-bm="Telefon">Phone</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($user->person_in_charge as $pic)
                                                                        <tr>
                                                                            <td>{{ $pic['name'] }}</td>
                                                                            <td>{{ $pic['position'] }}</td>
                                                                            <td>{{ $pic['phone'] }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    <hr>

                                                    <h6 class="fw-semibold mb-3" data-en="Address Information"
                                                        data-bm="Maklumat Alamat">Address Information</h6>

                                                    <div class="row gy-3">
                                                        <div class="col-xl-12">
                                                            <strong data-en="Address" data-bm="Alamat">Address
                                                            </strong>
                                                            <span class="text-muted">{{ $user->address_1 ?? '—' }}</span>
                                                        </div>

                                                        <div class="col-xl-6">
                                                            <strong data-en="Postcode" data-bm="Poskod">Postcode:</strong>
                                                            <span class="text-muted">{{ $user->postcode ?? '—' }}</span>
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <strong data-en="District" data-bm="Daerah">District:</strong>
                                                            <span class="text-muted">{{ $user->district ?? '—' }}</span>
                                                        </div>
                                                        <div class="col-xl-12">
                                                            <strong data-en="State" data-bm="Negeri">State:</strong>
                                                            <span class="text-muted">{{ $user->state ?? '—' }}</span>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <h6 class="fw-semibold mb-3" data-en="Account Information"
                                                        data-bm="Maklumat Akaun">Account Information</h6>
                                                    <div class="row gy-3">
                                                        <div class="col-xl-6">
                                                            <strong data-en="Registered On"
                                                                data-bm="Didaftar Pada">Registered On:</strong>
                                                            <span
                                                                class="text-muted">{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '—' }}</span>
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <strong data-en="Last Updated"
                                                                data-bm="Kemaskini Terakhir">Last Updated:</strong>
                                                            <span
                                                                class="text-muted">{{ $user->updated_at ? $user->updated_at->format('d M Y, H:i') : '—' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Activity Log Tab -->
                                            <div class="tab-pane p-0 border-0" id="activity-log-tab-pane" role="tabpanel"
                                                aria-labelledby="activity-log-tab" tabindex="0">
                                                <div class="p-3">
                                                    <h6 class="fw-semibold mb-3" data-en="Activity Log"
                                                        data-bm="Log Aktiviti">Activity Log</h6>
                                                    @if (isset($activities) && $activities->count() > 0)
                                                        <ul class="list-group">
                                                            @foreach ($activities as $log)
                                                                <li
                                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                                    {{ $log->description }}
                                                                    <span
                                                                        class="text-muted small">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p class="text-muted" data-en="No activity logs found."
                                                            data-bm="Tiada log aktiviti dijumpai.">No activity logs found.
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- List Document Tab -->
                                            <div class="tab-pane p-0 border-0" id="list-document-tab-pane"
                                                role="tabpanel" aria-labelledby="list-document-tab" tabindex="0">
                                                <div class="p-3">
                                                    <h6 class="fw-semibold mb-3" data-en="Documents" data-bm="Dokumen">
                                                        Documents</h6>
                                                    @if (isset($user->attachments) && $user->attachments->count() > 0)
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th data-en="File Name" data-bm="Nama Fail">File Name
                                                                    </th>
                                                                    <th data-en="Document Type" data-bm="Jenis Dokumen">
                                                                        Document Type</th>
                                                                    <th data-en="Uploaded At" data-bm="Dimuat Naik Pada">
                                                                        Uploaded At</th>
                                                                    <th data-en="Action" data-bm="Tindakan">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($user->attachments as $attachment)
                                                                    <tr>
                                                                        <td>{{ $attachment->original_file_name ?? basename($attachment->file_path) }}
                                                                        </td>
                                                                        <td>{{ $attachment->document_type ?? 'N/A' }}</td>
                                                                        <td>{{ $attachment->created_at->format('d M Y, H:i') }}
                                                                        </td>
                                                                        <td>
                                                                            @if ($attachment->file_path)
                                                                                <a href="{{ asset($attachment->file_path) }}"
                                                                                    target="_blank"
                                                                                    class="btn btn-sm btn-primary"
                                                                                    data-en="View"
                                                                                    data-bm="Lihat">View</a>
                                                                            @else
                                                                                <span data-en="N/A"
                                                                                    data-bm="N/A">N/A</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <p class="text-muted" data-en="No documents uploaded."
                                                            data-bm="Tiada dokumen dimuat naik.">No documents uploaded.</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Vehicle List Tab -->
                                            <div class="tab-pane p-0 border-0" id="vehicle-list-tab-pane" role="tabpanel"
                                                aria-labelledby="vehicle-list-tab" tabindex="0">
                                                <div class="p-3">
                                                    <h6 class="fw-semibold mb-3" data-en="Vehicle List"
                                                        data-bm="Senarai Kenderaan">Vehicle List</h6>
                                                    @if (isset($user->vehicles) && $user->vehicles->count() > 0)
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th data-en="Vehicle Name" data-bm="Nama Kenderaan">
                                                                        Vehicle Name</th>
                                                                    <th data-en="Vehicle Number"
                                                                        data-bm="Nombor Kenderaan">Vehicle Number</th>
                                                                    <th data-en="Created At" data-bm="Dicipta Pada">
                                                                        Created At</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($user->vehicles as $vehicle)
                                                                    <tr>
                                                                        <td>{{ $vehicle->vehicle_name }}
                                                                        </td>
                                                                        <td>{{ $vehicle->vehicle_number ?? 'N/A' }}</td>
                                                                        <td>{{ $vehicle->created_at ? $vehicle->created_at->format('d M Y, H:i') : '—' }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <p class="text-muted" data-en="No vehicles list."
                                                            data-bm="Tiada senarai kenderaan.">No vehicles list.</p>
                                                    @endif
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
