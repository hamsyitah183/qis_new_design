@extends('pages.app')

@section('pageName', 'Apply Import Permit')


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
            'label' => 'Apply For others Import Permit Application',
            'url' => '#',
            'data-en' => 'Apply For others Import Permit Application',
            'data-bm' => 'Permohonan Permit Import Bagi Pihak Lain',
        ],
    ]">
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
                            @include('pages.public.permit.stepCompany')
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

            // Event Listener for the wizard error
            document.querySelector(".wizard-tab").addEventListener("wz.error", function(e) {

                let targets = e.detail.target;

                targets.forEach(t => {
                    // Logic for Importer Selection
                    if (t.id == 'impid' || t.id == 'findImporter') {
                        notifyUser('Please select an importer first!');
                        let findImp = document.getElementById('findImporter');
                        if (findImp) {
                            findImp.classList.add('is-invalid');
                            findImp.style.setProperty('border', '1px solid red', 'important');

                            // Remove error on input
                            findImp.addEventListener('input', function() {
                                this.classList.remove('is-invalid');
                                this.style.border = '';
                            }, {
                                once: true
                            });
                        }
                    }


                    // Logic for Exporter Selection
                    if (t.id == 'selectexp') {
                        // Check for Select2
                        let nextElem = t.nextElementSibling;
                        if (nextElem && nextElem.classList.contains('select2')) {
                            let selection = nextElem.querySelector('.select2-selection');
                            if (selection) {
                                selection.style.setProperty('border', '1px solid red', 'important');
                                // Remove error on change (Select2 triggers change on original select)
                                $('#selectexp').one('select2:open change', function() {
                                    selection.style.border = '';
                                });
                            }
                        } else {
                            t.classList.add('is-invalid');
                            t.style.setProperty('border', '1px solid red', 'important');
                            t.addEventListener('change', function() {
                                this.classList.remove('is-invalid');
                                this.style.border = '';
                            }, {
                                once: true
                            });
                        }
                    }

                    // Logic for Item List Validation
                    if (t.id == 'itemCountCheck') {
                        notifyUser('Please add at least one item!');
                        let addBtn = document.getElementById('mdlAddItemBtn');
                        if (addBtn) {
                            addBtn.style.setProperty('border', '1px solid red', 'important');
                            addBtn.style.color = 'red';
                        }
                    }
                });
            });
        })();
    </script>

    <script>
        // page variables intialization
        let tempItems = [];
        let tempAttachments = [];
        let temporaryItemsAttachment = [];

        Dropzone.autoDiscover = false;
    </script>

    <!-- // add new exporter and rebuild exporter selection -->
    @vite(['resources/js/pages/importPermit/registerexp.js'])
@endpush
