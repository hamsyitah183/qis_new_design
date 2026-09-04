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
                                                        {{-- @dd($user) --}}
                                                        <div class="col-xl-6">
                                                            <strong data-en="District" data-bm="Daerah">District:</strong>
                                                            <span class="text-muted">{{   $user->districtInfo->name ??  $user->district  ??'—' }}</span>
                                                        </div>
                                                        <div class="col-xl-12">
                                                            <strong data-en="State" data-bm="Negeri">State:</strong>
                                                            <span class="text-muted">{{ $user->stateInfo->name ?? $user->state   ??'—' }}</span>
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
                                                    @if (isset($activities) && $activities->count() > 0)
                                                        @php
                                                            $activityTranslations = [
                                                                'logged in to the system' =>
                                                                    'telah log masuk ke sistem',
                                                                'logged out from the system' =>
                                                                    'telah log keluar dari sistem',
                                                                'created a public user account for' =>
                                                                    'telah mencipta akaun pengguna awam untuk',
                                                                'created a internal user account for' =>
                                                                    'telah mencipta akaun pengguna dalaman untuk',
                                                                'registered a new account.' =>
                                                                    'telah mendaftar akaun baharu.',
                                                                'updated their profile information.' =>
                                                                    'telah mengemas kini maklumat profil mereka.',
                                                                'internal user' => 'pengguna dalaman',
                                                                'public user ' => 'pengguna awam ',
                                                                'public user' => 'pengguna awam',
                                                                'is new user for boundary officer' =>
                                                                    'pengguna baharu untuk pegawai sempadan',
                                                                'has created a new import permit application draft' =>
                                                                    'telah mencipta draf permohonan permit import baharu',
                                                                'has created a new inspection certificate application draft' =>
                                                                    'telah mencipta draf permohonan sijil pemeriksaan baharu',
                                                                'has created a new consignment application draft' =>
                                                                    'telah mencipta draf permohonan konsainan baharu',
                                                                'has submitted an import permit application' =>
                                                                    'telah menghantar permohonan permit import',
                                                                'has submitted an inspection certificate application' =>
                                                                    'telah menghantar permohonan sijil pemeriksaan',
                                                                'has submitted a consignment application' =>
                                                                    'telah menghantar permohonan konsainan',
                                                                'has updated an import permit application draft' =>
                                                                    'telah mengemas kini draf permohonan permit import',
                                                                'has updated an inspection certificate application draft' =>
                                                                    'telah mengemas kini draf permohonan sijil pemeriksaan',
                                                                'has updated a consignment application draft' =>
                                                                    'telah mengemas kini draf permohonan konsainan',
                                                                'verification is in-progress by' =>
                                                                    'pengesahan sedang dijalankan oleh',
                                                                'was verified by' => 'telah disahkan oleh',
                                                                'verification is rejected by' =>
                                                                    'pengesahan ditolak oleh',
                                                                'is not approved by' => 'tidak diluluskan oleh',
                                                                'is uploading an attachment to get verification' =>
                                                                    'sedang memuat naik lampiran untuk mendapatkan pengesahan',
                                                                'has added an exporter' => 'telah menambah pengeksport',
                                                                'has updated an exporter' =>
                                                                    'telah mengemas kini pengeksport',
                                                                'has deleted an exporter' =>
                                                                    'telah memadam pengeksport',
                                                                'has added an importer' => 'telah menambah pengimport',
                                                                'has updated an importer' =>
                                                                    'telah mengemas kini pengimport',
                                                                'has approved consignment application' =>
                                                                    'telah meluluskan permohonan konsainan',
                                                                'has rejected consignment application' =>
                                                                    'telah menolak permohonan konsainan',
                                                                'has verified consignment application' =>
                                                                    'telah mengesahkan permohonan konsainan',
                                                                'has deleted a consignment application' =>
                                                                    'telah memadam permohonan konsainan',
                                                                'has submitted a drafted consignment application' =>
                                                                    'telah menghantar draf permohonan konsainan',
                                                                'has deleted an inspection application' =>
                                                                    'telah memadam permohonan pemeriksaan',
                                                                'deleted inspection application' =>
                                                                    'memadam permohonan pemeriksaan',
                                                                'accepted inspection item' =>
                                                                    'menerima item pemeriksaan',
                                                                'rejected inspection item' =>
                                                                    'menolak item pemeriksaan',
                                                                'has successfully completed payment for order' =>
                                                                    'telah berjaya melengkapkan pembayaran untuk pesanan',
                                                                'payment failed for order' =>
                                                                    'pembayaran gagal untuk pesanan',
                                                                'reapplied for permit' => 'memohon semula permit',
                                                                'updated application' => 'mengemas kini permohonan',
                                                                'created application' => 'mencipta permohonan',
                                                            ];

                                                            $filterCategories = [
                                                                'Authentication' => [
                                                                    'en' => 'Authentication',
                                                                    'bm' => 'Log Masuk / Keluar',
                                                                    'keywords' => 'logged in,logged out',
                                                                ],
                                                                'Applications' => [
                                                                    'en' => 'Applications & Drafts',
                                                                    'bm' => 'Permohonan & Draf',
                                                                    'keywords' =>
                                                                        'draft,submitted,reapplied,application,permit,inspection,consignment',
                                                                ],
                                                                'Approvals' => [
                                                                    'en' => 'Approvals & Verifications',
                                                                    'bm' => 'Kelulusan & Pengesahan',
                                                                    'keywords' => 'approved,rejected,verified,accepted',
                                                                ],
                                                                'Payments' => [
                                                                    'en' => 'Payments',
                                                                    'bm' => 'Pembayaran',
                                                                    'keywords' => 'payment',
                                                                ],
                                                                'ImportersExporters' => [
                                                                    'en' => 'Importers & Exporters',
                                                                    'bm' => 'Pengimport & Pengeksport',
                                                                    'keywords' => 'importer,exporter',
                                                                ],
                                                                'Attachments' => [
                                                                    'en' => 'Attachments',
                                                                    'bm' => 'Lampiran',
                                                                    'keywords' => 'attachment',
                                                                ],
                                                            ];
                                                        @endphp
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="fw-semibold mb-0" data-en="Activity Log"
                                                                data-bm="Log Aktiviti">Activity Log</h6>
                                                            <div class="w-50">
                                                                <select id="activitySearch" class="form-select select2"
                                                                    multiple="multiple">
                                                                    @foreach ($filterCategories as $cat => $data)
                                                                        <option value="{{ $data['keywords'] }}"
                                                                            data-en="{{ $data['en'] }}"
                                                                            data-bm="{{ $data['bm'] }}">
                                                                            {{ $data['en'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <ul class="list-group" id="activityListGroup">
                                                            @foreach ($activities as $log)
                                                                @php
                                                                    $descEn = $log->description;
                                                                    $descBm = $descEn;
                                                                    foreach ($activityTranslations as $en => $bm) {
                                                                        $descBm = str_replace($en, $bm, $descBm);
                                                                    }
                                                                @endphp
                                                                <li
                                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <span class="activity-desc"
                                                                        data-en="{{ $descEn }}"
                                                                        data-bm="{{ $descBm }}">{{ $descEn }}</span>
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
                                                        @php
                                                            $groupedAttachments = $user->attachments->groupBy(
                                                                'document_type',
                                                            );
                                                        @endphp

                                                        <div class="d-flex flex-column gap-2"
                                                            id="user-document-accordion">
                                                            @foreach ($groupedAttachments as $docType => $files)
                                                                @php
                                                                    $latestUpload = $files->max('created_at');
                                                                    $hasUnread = $files->contains(
                                                                        fn($f) => !$f->is_read,
                                                                    );
                                                                @endphp
                                                                <div class="card custom-card border shadow-sm mb-0 p-3">
                                                                    <div class="d-flex align-items-center justify-content-between doc-accordion-toggle"
                                                                        role="button" aria-expanded="false"
                                                                        data-target="doc-type-panel-{{ Str::slug($docType) }}">
                                                                        <div
                                                                            class="d-flex align-items-center gap-2 min-w-0">
                                                                            <i
                                                                                class="ti ti-file-text fs-18 text-muted flex-shrink-0"></i>
                                                                            <div class="min-w-0">
                                                                                <div class="fw-semibold fs-14">
                                                                                    {{ $docType ?? 'N/A' }}</div>
                                                                            </div>
                                                                        </div>
                                                                        <div
                                                                            class="d-flex align-items-center gap-2 flex-shrink-0">
                                                                            @if ($hasUnread)
                                                                                <span class="badge bg-warning-transparent">
                                                                                    <i class="ti ti-clock"></i> Pending
                                                                                    Review
                                                                                </span>
                                                                            @endif
                                                                            <span
                                                                                class="badge rounded-pill bg-light text-muted">
                                                                                {{ $files->count() }}
                                                                                {{ Str::plural('file', $files->count()) }}
                                                                            </span>
                                                                            <i
                                                                                class="ti ti-chevron-down doc-accordion-icon fs-16 text-muted"></i>
                                                                        </div>
                                                                    </div>

                                                                    <div class="doc-accordion-panel d-none"
                                                                        id="doc-type-panel-{{ Str::slug($docType) }}">
                                                                        <div
                                                                            class="pt-3 mt-3 border-top border-block-start-dashed">
                                                                            <ul
                                                                                class="list-unstyled d-flex flex-column gap-2 mb-0">
                                                                                @foreach ($files as $attachment)
                                                                                    @php
                                                                                        $fileUrl = url(
                                                                                            'storage/' .
                                                                                                ltrim(
                                                                                                    str_replace(
                                                                                                        '/storage/',
                                                                                                        '',
                                                                                                        $attachment->file_path,
                                                                                                    ),
                                                                                                    '/',
                                                                                                ),
                                                                                        );
                                                                                        $isRead =
                                                                                            (bool) $attachment->is_read;
                                                                                    @endphp
                                                                                    <li
                                                                                        class="file-list-item existing-file">
                                                                                        <i
                                                                                            class="{{ str_ends_with($attachment->file_path, '.pdf') ? 'ti ti-file-type-pdf' : 'ti ti-photo' }}"></i>
                                                                                        <div class="file-meta">
                                                                                            <div
                                                                                                class="d-flex align-items-center gap-2 flex-wrap">
                                                                                                <div class="file-name">
                                                                                                    {{ $attachment->original_file_name ?? basename($attachment->file_path) }}
                                                                                                </div>
                                                                                                @if ($isRead)
                                                                                                    <span
                                                                                                        class="badge bg-success-transparent fs-11 py-0 px-1">
                                                                                                        <i
                                                                                                            class="ti ti-check fs-11"></i>
                                                                                                        Read
                                                                                                    </span>
                                                                                                @endif
                                                                                            </div>
                                                                                            @if ($attachment->file_size)
                                                                                                <div class="file-size">
                                                                                                    {{ number_format($attachment->file_size / 1024, 2) }}
                                                                                                    KB
                                                                                                </div>
                                                                                            @endif
                                                                                            <div
                                                                                                class="file-uploaded-date text-muted fs-12">
                                                                                                Uploaded on:
                                                                                                {{ $attachment->created_at->format('d M Y, H:i') }}
                                                                                            </div>
                                                                                            @if ($attachment->valid_from || $attachment->valid_until)
                                                                                                <div
                                                                                                    class="text-muted fs-12">
                                                                                                    Valid:
                                                                                                    {{ $attachment->valid_from ? \Carbon\Carbon::parse($attachment->valid_from)->format('d M Y') : '—' }}
                                                                                                    –
                                                                                                    {{ $attachment->valid_until ? \Carbon\Carbon::parse($attachment->valid_until)->format('d M Y') : '—' }}
                                                                                                </div>
                                                                                            @endif
                                                                                            @if ($attachment->rejected_reason)
                                                                                                <div
                                                                                                    class="alert alert-danger d-flex align-items-start gap-2 mt-2 mb-0 py-2 px-2">
                                                                                                    
                                                                                                    <div class="fs-12">
                                                                                                        <span
                                                                                                            class="fw-semibold">Rejected:</span>
                                                                                                        {{ $attachment->rejected_reason }}
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endif
                                                                                        </div>
                                                                                        <div
                                                                                            class="file-actions d-flex align-items-center gap-1">
                                                                                            <button
                                                                                                class="btn btn-sm btn-icon btn-success-light btn-wave file-view-btn"
                                                                                                data-url="{{ $fileUrl }}"
                                                                                                data-name="{{ $attachment->original_file_name ?? 'Document' }}"
                                                                                                onclick="window.viewExistingFile(this.dataset.url, this.dataset.name)">
                                                                                                <i class="ti ti-eye"></i>
                                                                                            </button>
                                                                                            @if (!$isRead)
                                                                                                <button type="button"
                                                                                                    class="btn btn-sm btn-icon btn-success btn-wave approve-attachment-btn"
                                                                                                    data-id="{{ $attachment->id }}"
                                                                                                    title="Approve">
                                                                                                    <i
                                                                                                        class="ti ti-check"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                    class="btn btn-sm btn-icon btn-danger btn-wave reject-attachment-btn"
                                                                                                    data-id="{{ $attachment->id }}"
                                                                                                    title="Reject">
                                                                                                    <i class="ti ti-x"></i>
                                                                                                </button>
                                                                                            @endif
                                                                                        </div>
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-muted" data-en="No documents uploaded."
                                                            data-bm="Tiada dokumen dimuat naik.">
                                                            No documents uploaded.</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Rejection Reason Modal -->
                                            <div class="modal fade" id="rejectAttachmentModal" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" data-en="Reject Document"
                                                                data-bm="Tolak Dokumen">Reject Document</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label for="rejectReasonInput" class="form-label"
                                                                data-en="Reason for Rejection"
                                                                data-bm="Sebab Penolakan">Reason for Rejection <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea id="rejectReasonInput" class="form-control" rows="4"
                                                                placeholder="Explain why this document is being rejected..."></textarea>
                                                            <input type="hidden" id="rejectAttachmentId">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                data-bs-dismiss="modal" data-en="Cancel"
                                                                data-bm="Batal">Cancel</button>
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                id="confirmRejectBtn">
                                                                <i class="ti ti-x"></i> <span data-en="Confirm Rejection"
                                                                    data-bm="Sahkan Penolakan">Confirm
                                                                    Rejection</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Rejection Reason Modal -->
                                            <div class="modal fade" id="rejectAttachmentModal" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" data-en="Reject Document"
                                                                data-bm="Tolak Dokumen">Reject Document</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label for="rejectReasonInput" class="form-label"
                                                                data-en="Reason for Rejection"
                                                                data-bm="Sebab Penolakan">Reason for Rejection <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea id="rejectReasonInput" class="form-control" rows="4"
                                                                placeholder="Explain why this document is being rejected..."></textarea>
                                                            <input type="hidden" id="rejectAttachmentId">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                data-bs-dismiss="modal" data-en="Cancel"
                                                                data-bm="Batal">Cancel</button>
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                id="confirmRejectBtn">
                                                                <i class="ti ti-x"></i> <span data-en="Confirm Rejection"
                                                                    data-bm="Sahkan Penolakan">Confirm
                                                                    Rejection</span>
                                                            </button>
                                                        </div>
                                                    </div>
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

                                                                    <th data-en="Vehicle Number"
                                                                        data-bm="Nombor Kenderaan">Vehicle Number</th>
                                                                    <th data-en="Created At" data-bm="Dicipta Pada">
                                                                        Created At</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($user->vehicles as $vehicle)
                                                                    <tr>

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

    <!-- File Preview Modal -->
    <div class="modal fade" id="fileLabelModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileLabelModalLabel">File Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="fileLabelPreview" src="" class="img-fluid rounded d-none" alt="Preview">
                    <iframe id="fileLabelPdfViewer" src="" class="w-100 d-none"
                        style="height: 70vh; border: none;"></iframe>
                    <div id="filePreviewIcon" class="d-none py-5">
                        <i class="ti ti-file-text ti-5x text-muted"></i>
                        <p class="text-muted mt-3 mb-0">Preview not available. Use 'Open in New Tab' to view it.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <span id="fileLabelName" class="me-auto text-muted fs-13"></span>
                    <a id="fileLabelOpenBtn" href="#" target="_blank" class="btn btn-primary btn-sm"
                        style="display: none;">
                        <i class="ti ti-download"></i> Open / Download
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/pages/internal/user_management/view_public.js'])
    @push('style')
        <style>
            .doc-accordion-toggle {
                cursor: pointer;
                user-select: none;
            }

            .doc-accordion-icon {
                transition: transform 0.2s ease;
            }

            .detail-label {
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: #6c757d;
                margin-bottom: 4px;
            }

            .detail-value {
                font-size: 0.9rem;
                font-weight: 500;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            window.baseUrl = "{{ url('/') }}";
            window.csrfToken = "{{ csrf_token() }}";
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // ─── Accordion toggle ───────────────────────────────
                document.querySelectorAll('.doc-accordion-toggle').forEach((toggle) => {
                    toggle.addEventListener('click', function() {
                        const targetId = this.dataset.target;
                        const panel = document.getElementById(targetId);
                        const icon = this.querySelector('.doc-accordion-icon');
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';

                        panel.classList.toggle('d-none', isExpanded);
                        this.setAttribute('aria-expanded', String(!isExpanded));
                        icon.classList.toggle('ti-chevron-up', !isExpanded);
                        icon.classList.toggle('ti-chevron-down', isExpanded);
                    });
                });

                // ─── Approve ────────────────────────────────────────
                document.querySelectorAll('.approve-attachment-btn').forEach((btn) => {
                    btn.addEventListener('click', async function(e) {
                        e.stopPropagation();
                        const id = this.dataset.id;

                        const result = await Swal.fire({
                            title: 'Approve this document?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, approve',
                            confirmButtonColor: '#198754',
                        });

                        if (!result.isConfirmed) return;

                        try {
                            const response = await fetch(
                                `${window.baseUrl}/internal/verification/attachment/${id}/approve`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': window.csrfToken,
                                        'Accept': 'application/json',
                                    },
                                });
                            const data = await response.json();
                            if (!response.ok) throw new Error(data.message || 'Failed to approve.');

                            await Swal.fire('Approved!', data.message || 'Document approved.',
                                'success');
                            window.location.reload();
                        } catch (err) {
                            Swal.fire('Error', err.message, 'error');
                        }
                    });
                });

                // ─── Reject — open modal ────────────────────────────
                document.querySelectorAll('.reject-attachment-btn').forEach((btn) => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        document.getElementById('rejectAttachmentId').value = this.dataset.id;
                        document.getElementById('rejectReasonInput').value = '';

                        const modal = new bootstrap.Modal(document.getElementById(
                            'rejectAttachmentModal'));
                        modal.show();
                    });
                });

                // ─── Reject — confirm submission ────────────────────
                document.getElementById('confirmRejectBtn').addEventListener('click', async function() {
                    const id = document.getElementById('rejectAttachmentId').value;
                    const reason = document.getElementById('rejectReasonInput').value.trim();

                    if (!reason) {
                        Swal.fire('Error', 'Please provide a reason for rejection.', 'error');
                        return;
                    }

                    try {
                        const response = await fetch(
                            `${window.baseUrl}/internal/verification/attachment/${id}/reject`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': window.csrfToken,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    reason: reason
                                }),
                            });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Failed to reject.');

                        bootstrap.Modal.getInstance(document.getElementById('rejectAttachmentModal'))
                            .hide();
                        await Swal.fire('Rejected', data.message || 'Document rejected.', 'success');
                        window.location.reload();
                    } catch (err) {
                        Swal.fire('Error', err.message, 'error');
                    }
                });
            });

            function viewExistingFile(url, name) {
                const modalEl = document.getElementById("fileLabelModal");
                if (!modalEl) {
                    window.open(url, "_blank", "noopener,noreferrer");
                    return;
                }

                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

                const previewImg = document.getElementById("fileLabelPreview");
                const previewIcon = document.getElementById("filePreviewIcon");
                const pdfViewer = document.getElementById("fileLabelPdfViewer");
                const fileNameDisplay = document.getElementById("fileLabelName");
                const openBtn = document.getElementById("fileLabelOpenBtn");
                const modalTitle = document.getElementById("fileLabelModalLabel");

                // Reset all viewers
                previewImg.classList.add("d-none");
                previewIcon.classList.add("d-none");
                pdfViewer.classList.add("d-none");
                previewImg.src = "";
                pdfViewer.src = "";

                const ext = (url || "").split(".").pop().toLowerCase().split("?")[0];

                if (["jpg", "jpeg", "png", "gif", "bmp", "svg", "webp"].includes(ext)) {
                    previewImg.src = url;
                    previewImg.classList.remove("d-none");
                } else if (ext === "pdf") {
                    pdfViewer.src = url;
                    pdfViewer.classList.remove("d-none");
                } else {
                    previewIcon.classList.remove("d-none");
                }

                fileNameDisplay.textContent = name || "Document";
                openBtn.href = url;
                openBtn.download = name || "document";
                openBtn.style.display = "inline-block";
                modalTitle.textContent = "File Preview";

                modal.show();
            }

            window.viewExistingFile = viewExistingFile;
        </script>
    @endpush
