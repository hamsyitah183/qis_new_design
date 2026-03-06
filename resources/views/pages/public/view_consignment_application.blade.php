@extends('pages.app')

@section('pageName', 'View Application')

@push('scripts')
    @vite(['resources/js/pages/consignment/consignment_detail.js'])

@endpush




@section('breadcrumb')
    @php
        $consignmentListUrl = auth()->guard('internal')->check()
            ? '/internal/consignment_certificates_list'
            : '/public/view_all_consignment';
    @endphp
    <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => '/'],
            ['label' => 'Consignment Certificate List', 'url' => $consignmentListUrl],
            ['label' => 'Application: ' . $application->application_id, 'url' => '#'],
        ]" title="View Application">

    </x-breadcrumb>
@endsection

@section('content')

    @php
        $authUuid = authUser()['user']->uuid ?? null;
        $status = strtolower($application->status ?? '');
        $importerVerify = strtolower($application->importer_verify ?? '');
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
                            <a class="btn btn-primary2 btn-wave btn-sm me-2" id="editButton"
                                href="/edit_application/{{ $application->application_id }}">
                                Edit
                            </a>
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
                            @include('pages.public.view_consignment.step0')
                            <!-- step1 -->
                            @include('pages.public.view_consignment.step1')

                            <!-- step2 -->


                            @include('pages.public.view_consignment.step2')
                            <!-- step3 -->
                            @include('pages.public.view_consignment.step3')

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
                                    $isPublic && $application->exporter_id === auth()->guard('public')->user()->uuid;

                                $allPending = $application->consignmentPermits->every(
                                    fn($permit) => $permit->status === 'pending for payment',
                                );

                                $value = $allPending ? 1 : 0;

                                // dd($value);

                            @endphp
                            {{-- @dd($isOwner || $isAdminOrClerk) --}}
                            {{-- @if (
                                    ($application->status === 'Clerk Review In-Progress' && $isAdminOrClerk) ||
                                    ($application->category_application == 1 && ($isOwner || $isAdminOrClerk))
                                ) --}}
                                @if ( (  str_contains(strtolower($application->status), 'clerk review in-progress') && $isAdminOrClerk) || 
                                (  str_contains(strtolower($application->status), 'wait for company approval') && ( $isOwner || $isAdminOrClerk) )  )
                                {{-- Step 4 --}}
                                @include('pages.public.view_consignment.step4')
                            @endif


                            @if (authUser()['type'] == 'public' && $application->user_id == authUser()['user']->uuid && $value)
                                @include('pages.public.view_consignment.step5')
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
            <table class="table text-wrap table-hover" id="applicationLogTable">
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

    @include('pages.public.view_permit.step2modal')

@endsection

@push('scripts')
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    <script>
        // for form wizard next and prev button
        (function () {
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.location.hash === '#pending') {


                document.querySelectorAll('.wizard-step').forEach(el => {
                    el.classList.remove('active');
                });


                const pendingTab = document.getElementById('pendingTab');
                if (pendingTab) {
                    pendingTab.classList.add('active');
                }

            }
        });
    </script>
@endpush