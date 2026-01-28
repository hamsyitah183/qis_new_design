@extends('pages.app')

@section('pageName', 'Apply Import Inspection Certificate')


@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'New Application', 'url' => '/public/new_application'],
        ['label' => 'Apply for Others Inspection Application', 'url' => '#'],
    ]" title="Inspection Certificate">

    </x-breadcrumb>
@endsection

@section('content')


    <!-- terssttt  -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
               
                <div class="card-body p-0"> <!-- method="POST"  data-wizard="active" style="display: block;"-->
                    <form id="wizardFormOthers" class="wizard wizard-tab horizontal" accept="multipart/form-data">
                        <input type="hidden" id="applicationId" value="{{ $id }}">
                        <aside class="wizard-nav dots">
                            <div class="wizard-step active" data-step="0">
                                <span class="dot"></span>
                                <span>IMPORTER & EXPORTER</span>
                            </div>
                            <div class="wizard-step" data-step="1">
                                <span class="dot"></span>
                                <span>INSPECTION DETAILS</span>
                            </div>
                            <div class="wizard-step" data-step="2">
                                <span class="dot"></span>
                                <span>CONSIGNMENT ITEMS</span>
                            </div>
                            <div class="wizard-step" data-step="3">
                                <span class="dot"></span>
                                <span>Summary</span>
                            </div>
                        </aside>
                        <aside class="wizard-content container">
                            @include('pages.public.inspection.stepCompany')                                            
                            @include('pages.public.inspection.step1')
                            @include('pages.public.inspection.step2')
                            @include('pages.public.inspection.step3')
                        </aside>
                    </form>
                    @include('pages.public.inspection.step2modal')                
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

        // Wizard validation error notification
        document.querySelectorAll('.wizard-tab').forEach(wz => {
            wz.addEventListener('wz.error', function (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required details before proceeding to the next step.',
                    confirmButtonColor: '#5e72e4',
                });
            });
        });
    </script>



    @vite(['resources/js/pages/inspection/inspection.js'])
    
@endpush

