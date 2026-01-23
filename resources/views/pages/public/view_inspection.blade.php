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
    ]" title="View Application">

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
                                    Edit
                                </a>
                            @else
                                <a class="btn btn-primary2 btn-wave btn-sm me-2" id="editButton"
                                    href="{{ route('public.inspectionApplicationOthers', ['id' => $application->application_id]) }}">
                                    Edit
                                </a>
                            @endif
                        @endif
                        <button class="btn btn-primary btn-wave btn-sm " id="applicationModal"><i
                                class="ti ti-file-time fs-18"></i> Application Log</button>
                    </div>

                </div>
                <div class="card-body p-0"> <!-- method="POST"  data-wizard="active" style="display: block;"-->
                    <form id="wizardForm" class="wizard wizard-tab horizontal">
                        <aside class="wizard-nav dots">
                            <div class="wizard-step active" data-step="0">
                                <span class="dot"></span>
                                <span>IMPORTER & EXPORTER</span>
                            </div>
                            <div class="wizard-step" data-step="1">
                                <span class="dot"></span>
                                <span>PERMIT DETAILS</span>
                            </div>
                            <div class="wizard-step" data-step="2">
                                <span class="dot"></span>
                                <span>PERMIT ITEMS</span>
                            </div>
                            <div class="wizard-step" data-step="3">
                                <span class="dot"></span>
                                <span>Payment</span>
                            </div>
                            <div class="wizard-step" data-step="4">
                                <span class="dot"></span>
                                <span>Confirmation</span>
                            </div>
                            <div class="wizard-step" data-step="5">
                                <span class="dot"></span>
                                <span>Confirmation</span>
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
                                        ->hasAnyRole(['admin', 'clerk']);

                                $isPublic = auth()->guard('public')->check();
                                $isOwner =
                                    $isPublic && $application->importer->uuid === auth()->guard('public')->user()->uuid;

                            @endphp
                            {{-- @dd($application->status) --}}
                            @if (
                                (str_contains(strtolower($application->status), 'clerk review in-progress') && $isAdminOrClerk) ||
                                    ($application->category_application == 1 && ($isOwner || $isAdminOrClerk)) ||
                                    $isAdminOrClerk)
                                {{-- Step 3 --}}
                                @include('pages.public.view_inspection.step4')
                            @endif

                            @php
                                if ($application->inspectionItems) {
                                    $allPending = $application->inspectionItems->every(
                                        fn($permit) => $permit->status === 'pending for payment',
                                    );
                                } else {
                                    $allPending = false;
                                }

                                $value = $allPending ? 1 : 0;

                                // dd($application->inspectionItems, $value);

                            @endphp


                            @if (authUser()['type'] == 'public' && $value)
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
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        @endslot

    </x-modal>

    <x-modal id="activityLogModal" title="Activity Log">

        <!-- Your table goes here -->
        <div class="table-responsive">
            <table class="table text-nowrap table-hover" id="applicationLogTable">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Action</th>
                        <th scope="col">User</th>
                        <th scope="col">Remark</th>
                        <th scope="col">Status</th>
                        <th scope="col">Time and Date</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">

                </tbody>
            </table>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        @endslot

    </x-modal>
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
