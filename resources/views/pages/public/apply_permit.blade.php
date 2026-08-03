@extends('pages.app')

@section('pageName', 'Apply Import Permit')

{{--
    New stylesheet, kept separate from this blade (same convention as
    the landing page). Adjust the path if your project loads CSS
    differently — this mirrors the @vite pattern already used for
    resources/js/pages/importPermit/registerexp.js below.
--}}
@push('style')
    {{-- @vite(['resources/css/pages/importPermit/ipa-wizard.css']) --}}
@endpush

@section('breadcrumb')
    <x-breadcrumb title="Import Permit" title_en="Import Permit" title_bm="Permit Import" :items="[
        ['label' => 'Home', 'url' => '/', 'data-en' => 'Home', 'data-bm' => 'Utama'],
        [
            'label' => 'New Application',
            'url' => '/public/new_application',
            'data-en' => 'New Application',
            'data-bm' => 'Permohonan Baru',
        ],
        [
            'label' => 'Self Apply Import Permit Application',
            'url' => '#',
            'data-en' => 'Self Apply Import Permit Application',
            'data-bm' => 'Permohonan Permit Import Sendiri',
        ],
    ]">
    </x-breadcrumb>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card ipa-wizard-card">

                <div class="card-body p-0">
                    <form id="wizardForm" class="wizard wizard-tab horizontal" action="javascript:void(0)"
                        accept="multipart/form-data">
                        {{-- same wizard-nav / dots / wizard-step / dot structure the Wizard1
                             plugin expects — "ipa-wizard-nav" only adds the visual reskin --}}
                        <aside class="wizard-nav dots ipa-wizard-nav">
                            <div class="wizard-step active" data-step="0">
                                <span class="dot"></span>
                                <span data-en="IMPORTER & EXPORTER" data-bm="PENGIMPORT & PENGEKSPORT">IMPORTER & EXPORTER</span>
                            </div>
                            <div class="wizard-step" data-step="1">
                                <span class="dot"></span>
                                <span data-en="PERMIT DETAILS" data-bm="BUTIRAN PERMIT">PERMIT DETAILS</span>
                            </div>
                            <div class="wizard-step" data-step="2">
                                <span class="dot"></span>
                                <span data-en="PERMIT ITEMS" data-bm="BARANGAN PERMIT">PERMIT ITEMS</span>
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
                            @include('pages.public.permit.step0')
                            @include('pages.public.permit.step1')
                            @include('pages.public.permit.step2')
                            @include('pages.public.permit.step3')

                        </aside>

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



    @vite(['resources/js/pages/importPermit/registerexp.js'])
@endpush
