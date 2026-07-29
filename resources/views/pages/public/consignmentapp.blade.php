@extends('pages.app')

@section('pageName', 'Apply Import Permit')


@section('breadcrumb')
    <x-breadcrumb :items="[
            ['label' => 'Home', 'url' => '/', 'data-en' => 'Home', 'data-bm' => 'Laman Utama'],
            ['label' => 'New Application', 'url' => '/public/new_application', 'data-en' => 'New Application', 'data-bm' => 'Permohonan Baru'],
            ['label' => 'Self Apply Consignment Certificate Application', 'url' => '#', 'data-en' => 'Self Apply Consignment Certificate Application', 'data-bm' => 'Permohonan Sijil Konsainan Sendiri'],
        ]" title="Consignment Certificate" title_en="Consignment Certificate" title_bm="Sijil Konsainan">

    </x-breadcrumb>
@endsection

@section('content')


    <!-- terssttt  -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">

                <div class="card-body p-0"> <!-- method="POST"  data-wizard="active" style="display: block;"-->
                    <form id="wizardForm" class="wizard wizard-tab horizontal" accept="multipart/form-data">
                        <aside class="wizard-nav dots ipa-wizard-nav">
                            <div class="wizard-step active" data-step="0">
                                <span class="dot"></span>
                                <span data-en="IMPORTER & EXPORTER" data-bm="PENGIMPORT & PENGEKSPORT">IMPORTER & EXPORTER</span>
                            </div>
                            <div class="wizard-step" data-step="1">
                                <span class="dot"></span>
                                <span data-en="CERTIFICATE DETAILS" data-bm="BUTIRAN SIJIL">CERTIFICATE DETAILS</span>
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
                            @include('pages.public.consignment.step0')
                            @include('pages.public.consignment.step1')
                            @include('pages.public.consignment.step2')
                            @include('pages.public.consignment.step3')
                        </aside>
                        <!-- <aside class="wizard-buttons">
                                                                        <button class="wizard-btn btn prev" disabled="true">Prev</button>
                                                                        <button class="wizard-btn btn next">Next</button>
                                                                        <button class="wizard-btn btn finish" style="display: none;">Submit</button>
                                                                    </aside> -->
                    </form>
                    @include('pages.public.consignment.step2modal')
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



    @vite(['resources/js/pages/consignment/consignment.js'])
@endpush