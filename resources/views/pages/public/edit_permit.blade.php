@extends('pages.app')

@section('pageName', 'Apply Import Permit')


@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'New Application', 'url' => '/public/new_application'],
        ['label' => 'Self Apply Import Permit Application (Draft)', 'url' => '#'],
    ]" title="Import Permit">

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
                    <form id="wizardForm" class="wizard wizard-tab horizontal" accept="multipart/form-data">
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
                                <span>CONSIGNMENT</span>
                            </div>

                            <div class="wizard-step" data-step="3">
                                <span class="dot"></span>
                                <span>SUMMARY</span>
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
        const application = @json($application);
    </script>
    <script>
        // for form wizard next and prev button
        (function() {
            let wizardConfig = {
                wz_class: ".wizard-tab",
                highlight: true,
                highlight_time: 1000,
                progress: true,
                validate: false   // 👈 CHANGE THIS
            };

            new Wizard1(wizardConfig).init();
        })();
    </script>

    @vite(['resources/js/pages/importPermit/editexp.js'])
@endpush
