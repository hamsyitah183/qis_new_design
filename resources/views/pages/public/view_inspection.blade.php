@extends('pages.app')

@section('pageName', 'Apply Import Inspection Certificate')


@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        [
            'label' => 'Application List',
            'url' => auth('internal')->check()
                ? '/internal/inspection_certificates_list'
                : '/public/inspection_certificates_list',
        ],
        ['label' => 'Application: ' . ($application->application_id ?? ''), 'url' => '#'],
    ]" title="View Application" data-bm="Lihat Permohonan" data-en="View Application">

    </x-breadcrumb>
@endsection

@section('content')


    @php
        $authUuid = authUser()['user']->uuid ?? null;
        // dd($application);
        $status = strtolower($application->status ?? '');

        $importerVerify = strtolower($application->importer_verify ?? '');

        // dd($application->inspectionItems);

    @endphp

    <!-- terssttt  -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">

                    {{-- @dd($application) --}}

                    {{-- @dd($application->user_id, authUser()['user']->uuid) --}}

                    <div class="ms-auto">
                        @if ($application->status == 'Draft' && $application->user_id == authUser()['user']->uuid)
                            @if ($application->category_application == '0')
                                <a class="btn btn-primary2 btn-wave btn-sm me-2" id="editButton"
                                    href="{{ route('public.inspectionApplicationSelf', ['id' => $application->application_id]) }}">
                                    <span data-bm="Sunting" data-en="Edit">Edit</span>
                                </a>
                            @else
                                <a class="btn btn-primary2 btn-wave btn-sm me-2" id="editButton"
                                    href="{{ route('public.inspectionApplicationOthers', ['id' => $application->application_id]) }}">
                                    <span data-bm="Sunting" data-en="Edit">Edit</span>
                                </a>
                            @endif
                        @endif
                        <button class="btn btn-primary btn-wave btn-sm " id="applicationModal"><i class="ti ti-file-time fs-18"></i> <span data-bm="Log Permohonan" data-en="Application Log">Application Log</span></button>
                    </div>

                </div>
                <div class="card-body p-0"> <!-- method="POST"  data-wizard="active" style="display: block;"-->
                    <form id="wizardForm" class="wizard wizard-tab horizontal">
                        <aside class="wizard-nav dots">
                            <div class="wizard-step active" data-step="0">
                                <span class="dot"></span>
                                <span data-bm="PENGIMPORT & PENGEKSPORT" data-en="IMPORTER & EXPORTER">IMPORTER & EXPORTER</span>
                            </div>
                            <div class="wizard-step" data-step="1">
                                <span class="dot"></span>
                                <span data-bm="BUTIRAN PERMIT" data-en="PERMIT DETAILS">PERMIT DETAILS</span>
                            </div>
                            <div class="wizard-step" data-step="2">
                                <span class="dot"></span>
                                <span data-bm="ITEM PERMIT" data-en="PERMIT ITEMS">PERMIT ITEMS</span>
                            </div>
                            <div class="wizard-step" data-step="3">
                                <span class="dot"></span>
                                <span data-bm="Pembayaran" data-en="Payment">Payment</span>
                            </div>
                            <div class="wizard-step" data-step="4">
                                <span class="dot"></span>
                                <span data-bm="Pengesahan" data-en="Confirmation">Confirmation</span>
                            </div>
                            <div class="wizard-step" data-step="5">
                                <span class="dot"></span>
                                <span data-bm="Pengesahan" data-en="Confirmation">Confirmation</span>
                            </div>
                        </aside>
                        <aside class="wizard-content container">
                            <!-- step0 -->
                            @include('pages.public.view_inspection.step0')
                            <!-- step1 -->
                            @include('pages.public.view_inspection.step1')

                            <!-- step2 -->
                            @include('pages.public.view_inspection.step2')
                            <!-- step3 -->
                            @include('pages.public.view_inspection.step3')

                            @php
                                $isInternal = auth()->guard('internal')->check();
                                $isAdminOrClerk =
                                    $isInternal &&
                                    auth()
                                        ->guard('internal')
                                        ->user()
                                        ->hasAnyRole(['admin', 'clerk', 'superadmin']);

                                $isPublic = auth()->guard('public')->check();
                                $isOwner =
                                    $isPublic && $application->importer_id === auth()->guard('public')->user()->uuid;

                                $allPending = $application->inspectionItems->every(
                                    fn($permit) => $permit->status === 'pending for payment',
                                );

                                $value = $allPending ? 1 : 0;

                                // dd($application->status, $isOwner);

                            @endphp
                            {{-- @dd($isOwner || $isAdminOrClerk) --}}
                            {{-- @if (
                                    ($application->status === 'Clerk Review In-Progress' && $isAdminOrClerk) ||
                                    ($application->category_application == 1 && ($isOwner || $isAdminOrClerk))
                                ) --}}
                                @if ( (  str_contains(strtolower($application->status), 'clerk review in-progress') && $isAdminOrClerk) || 
                                (  str_contains(strtolower($application->status), 'wait for company approval') && ( $isOwner || $isAdminOrClerk) )  )
                                {{-- Step 4 --}}
                                @include('pages.public.view_inspection.step4')
                            @endif


                            @if (authUser()['type'] == 'public' && $application->user_id == authUser()['user']->uuid && $value)
                                @include('pages.public.view_inspection.step5')
                            @endif



                        </aside>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <x-modal id="consignmentModal" title="">


        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-bm="Tutup" data-en="Close">Close</button>
        @endslot

    </x-modal>

    <x-modal id="activityLogModal" title="Activity Log" data-bm="Log Aktiviti" data-en="Activity Log">

        <!-- Your table goes here -->
        <div class="table-responsive">
            <table class="table text-nowrap table-hover" id="applicationLogTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" data-bm="Tindakan" data-en="Action">Action</th>
                        <th scope="col" data-bm="Pengguna" data-en="User">User</th>
                        <th scope="col" data-bm="Nota" data-en="Remark">Remark</th>
                        <th scope="col" data-bm="Status" data-en="Status">Status</th>
                        <th scope="col" data-bm="Masa dan Tarikh" data-en="Time and Date">Time and Date</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">

                </tbody>
            </table>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-bm="Tutup" data-en="Close">Close</button>
        @endslot

    </x-modal>

    @include('pages.public.view_inspection.step2modal')
@endsection

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    <script>
        // for form wizard next and prev button
        (function() {
            // 🟢 First wizard
            let firstWizardConfig = {
                wz_class: ".wizard-tab",
                highlight: true,
                highlight_time: 1000,
                progress: true,
                validate: true
            };
            new Wizard1(firstWizardConfig).init();

            // 🟢 Second wizard (with progress bar)
            let secondWizardConfig = {
                wz_class: ".wizard-second-tab", // ✅ fixed selector
                highlight: true,
                highlight_time: 1000,
                progress: true,
                validate: true
            };
            new Wizard1(secondWizardConfig).init();
        })();
    </script>

  



    @vite(['resources/js/pages/inspection/inspection_detail.js'])
@endpush
