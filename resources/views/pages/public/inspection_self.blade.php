@extends('pages.app')

@section('pageName', 'Apply Import Inspection Certificate')


@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => '/', 'data-en' => 'Home', 'data-bm' => 'Laman Utama'],
        ['label' => 'New Application', 'url' => '/public/new_application', 'data-en' => 'New Application', 'data-bm' => 'Permohonan Baru'],
        ['label' => 'Self Apply Inspection Application', 'url' => '#', 'data-en' => 'Self Apply Inspection Application', 'data-bm' => 'Permohonan Pemeriksaan Sendiri'],
    ]" title="Inspection Certificate" title_en="Inspection Certificate" title_bm="Sijil Pemeriksaan">

    </x-breadcrumb>
@endsection

@section('content')


    <!-- terssttt  -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
               
                <div class="card-body p-0"> <!-- method="POST"  data-wizard="active" style="display: block;"-->
                    <form id="wizardForm" class="wizard wizard-tab horizontal" accept="multipart/form-data">
                        <input type="hidden" id="applicationId" value="{{ $id }}">
                        <aside class="wizard-nav dots ipa-wizard-nav">
                            <div class="wizard-step active" data-step="0">
                                <span class="dot"></span>
                                <span data-en="IMPORTER & EXPORTER" data-bm="PENGIMPORT & PENGEKSPORT">IMPORTER & EXPORTER</span>
                            </div>
                            <div class="wizard-step" data-step="1">
                                <span class="dot"></span>
                                <span data-en="INSPECTION DETAILS" data-bm="BUTIRAN PEMERIKSAAN">INSPECTION DETAILS</span>
                            </div>
                            <div class="wizard-step" data-step="2">
                                <span class="dot"></span>
                                <span data-en="CONSIGNMENT ITEMS" data-bm="BARANGAN KONSAINAN">CONSIGNMENT ITEMS</span>
                            </div>
                            <div class="wizard-step" data-step="3">
                                <span class="dot"></span>
                                <span data-en="Payment" data-bm="Pembayaran">Payment</span>
                            </div>
                            <div class="wizard-step" data-step="4">
                                <span class="dot"></span>
                                <span data-en="Confirmation" data-bm="Pengesahan">Confirmation</span>
                            </div>
                        </aside>
                        <aside class="wizard-content container">
                            @include('pages.public.inspection.step0')
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



    @vite(['resources/js/pages/inspection/inspection.js'])
@endpush
