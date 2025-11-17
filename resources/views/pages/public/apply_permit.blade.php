@extends('pages.app')

@section('pageName', 'Apply Import Permit')


@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
          
        ]" 
        title="Import Permit"
    >
     
    </x-breadcrumb>
@endsection

@section('content')


                    <!-- terssttt  -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        NEW PERMIT APPLICATION
                                    </div>
                                </div>
                                <div class="card-body p-0"> <!-- method="POST"  data-wizard="active" style="display: block;"-->
                                    <form id="wizardForm" class="wizard wizard-tab horizontal" >
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
                                            @include('pages.public.permit.step0')
                                            @include('pages.public.permit.step1')
                                            @include('pages.public.permit.step2')
                                            @include('pages.public.permit.step3')
                                            <!-- <div class="wizard-step" data-title="APPLICATION STATUS" data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu" data-limit="3" data-step="4">
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <div class="tab-pane active show" id="finish" role="tabpanel">
                                                            <div class="row d-flex justify-content-center">
                                                                <div class="col-lg-10">
                                                                    <div class="text-center p-4">
                                                                        <span class="avatar avatar-xl avatar-rounded bg-success-transparent svg-success">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" opacity="0.2"></circle><polyline points="88 136 112 160 168 104" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></circle></svg>
                                                                        </span>
                                                                        <h3 class="mt-2">Successful</h3>
                                                                        <p>Your permit application has successfully submitted.</p>
                                                                        
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> -->
                                        </aside>
                                        <!-- <aside class="wizard-buttons">
                                            <button class="wizard-btn btn prev" disabled="true">Prev</button>
                                            <button class="wizard-btn btn next">Next</button>
                                            <button class="wizard-btn btn finish" style="display: none;">Submit</button>
                                        </aside> -->
                                    </form>
                                    @include('pages.public.permit.step2modal')                
                                </div>
                            </div>
                        </div>
                    </div>

                    
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
            wz_class: ".wizard-second-tab",   // ✅ fixed selector
            highlight: true,
            highlight_time: 1000,
            progress: true,
            validate: true
        };
        new Wizard1(secondWizardConfig).init();
    })();
</script>

<script>
    // page variables intialization
    let tempItems = [];
    let tempAttachments = [];
    let temporaryItemsAttachment = [];

    Dropzone.autoDiscover = false;
</script>


    @vite(['resources/js/pages/importPermit/summarysubmit.js'])
    @vite(['resources/js/pages/importPermit/itemmodal.js'])
    <!-- auto fill in Importer $ Exporter -->
    @vite(['resources/js/pages/importPermit/autofill.js'])
    <!-- auto fill entry point -->
    @vite(['resources/js/pages/importPermit/entrypoint.js'])
    <!-- modal functions -->
     @vite(['resources/js/pages/importPermit/step2modal.js'])
    <!-- // add new exporter and rebuild exporter selection -->
    @vite(['resources/js/pages/importPermit/registerexp.js'])
    
@endpush

