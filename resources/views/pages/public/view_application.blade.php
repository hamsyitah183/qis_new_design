@extends('pages.app')

@section('pageName', 'View Application')

@push('scripts')
    @vite(['resources/js/pages/importPermit/application_detail.js'])
@endpush


@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/'],
        ['label' => 'Application List', 'url' => '/internal/view_all_application'],
        ['label' => 'Application: '.$application->application_id, 'url' => '#']
        
        ]"
         title="View Application"
        >

    </x-breadcrumb>
@endsection

@section('content')


    <!-- terssttt  -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
               
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
                        </aside>
                        <aside class="wizard-content container">
                            <!-- step0 -->
                            @include('pages.public.view_permit.step0')
                            <!-- step1 -->
                            @include('pages.public.view_permit.step1')

                            <!-- step2 -->


                            @include('pages.public.view_permit.step2')
                            <!-- step3 -->
                            @include('pages.public.view_permit.step3')
                            <!-- step4 -->
                            @include('pages.public.view_permit.step4')
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
@endpush
