@extends('pages.app')

@section('pageName', 'New Application')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="Application">

    </x-breadcrumb>
@endsection

@section('content')
    <style>
        /* Inline layout: Xintra Radio Boxes */
        .xintra-radio-box {
            position: relative;
            display: inline-block;
            /* Make them inline */
            width: 200px;  
            /* Adjust width as needed */
            border: 1px solid var(--bs-border-color);
            border-radius: 0.75rem;
            background-color: var(--bs-body-bg);
            transition: all 0.25s ease;
            box-shadow: var(--bs-box-shadow-sm);
            cursor: pointer;
        }

        .xintra-radio-box:hover {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15);
        }

        /* Hide real input, clickable full box */
        .xintra-radio-input {
            position: absolute;
            inset: 0;
            opacity: 0;
            z-index: 2;
            cursor: pointer;
        }

        /* Label styling */
        .xintra-radio-label {
            display: block;
            padding: 1rem;
            border-radius: 0.75rem;
            height: 100%;
        }

        /* Checked state */
        .xintra-radio-box input:checked+.xintra-radio-label {
            background-color: rgba(var(--bs-primary-rgb), 0.08);
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.25);
        }

        .xintra-radio-box input:checked+.xintra-radio-label i {
            color: var(--bs-primary);
        }
    </style>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title" data-bm="Permohonan Baru" data-en="New Application">
                        New Application
                    </div>
                </div>
                <div class="card-body">
                    <ol class="list-group list-group-numbered">
                        <li class="list-group-item d-sm-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto text-muted">
                                <div class="fw-medium fs-14 text-default mb-3" data-bm="Permit Import untuk:" data-en="Import Permit for:">Import Permit for:</div>
                                <div class="d-flex justify-content-center flex-nowrap gap-3" id="customeCheckbox">
                                    <!-- Option 1 -->
                                    <a href = "{{ route('public.permitApplication') }}" class="xintra-radio-box text-center">
                                        {{-- <input type="radio" name="regType" value="Individu" id="planBasic"
                                            class="xintra-radio-input"> --}}
                                        <label for="planBasic" class="xintra-radio-label">
                                            <div class="xintra-radio-content">
                                                <i class="bx bx-user fs-2 mb-2 text-primary"></i>
                                                <!-- <h6 class="mb-1">Yourself</h6> -->
                                                <h6 class="mb-1" data-bm="Import Sendiri" data-en="Self Import">Self Import</h6>
                                                <p class="text-muted small mb-0" data-bm="Anda memohon permit import ini untuk diri sendiri." data-en="You are applying this import permit for yourself.">You are applying this import permit for
                                                    yourself.</p>
                                            </div>
                                        </label>
                                    </a>

                                    <!-- Option 2 -->
                                    <a href = "{{ route('public.permitAssignApplication') }}" class="xintra-radio-box text-center">
                                        <input type="radio" name="regType" value="Company" id="planStandard"
                                            class="xintra-radio-input">
                                        <label for="planStandard" class="xintra-radio-label">
                                            <div class="xintra-radio-content">
                                                <i class="bx bx-buildings fs-2 mb-2 text-primary"></i>
                                                <h6 class="mb-1" data-bm="Untuk Pihak Lain" data-en="For Someone Else">For Someone Else</h6>
                                                <p class="text-muted small mb-0" data-bm="Untuk syarikat atau individu yang anda wakili." data-en="For companies or individuals you represent.">For companies or individuals you represent.
                                                </p>
                                            </div>
                                        </label>
                                    </a>
                                </div>
                            </div>
                            <!-- <span class="badge bg-primary-transparent">32 Views</span> -->
                        </li>
                        <li class="list-group-item d-sm-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto text-muted">
                                <div class="fw-medium fs-14 text-default mb-3" data-bm="Sijil Pemeriksaan" data-en="Inspection Certificate">Inspection Certificate</div>
                                <div class="d-flex justify-content-center flex-nowrap gap-3">
                                    <!-- Option 1 -->
                                    <a href = "{{ route('public.inspectionApplicationSelf') }}" class="xintra-radio-box text-center">
                                        <label for="inspectionSelf" class="xintra-radio-label">
                                            <div class="xintra-radio-content">
                                                <i class="bx bx-user fs-2 mb-2 text-primary"></i>
                                                <!-- <h6 class="mb-1">Yourself</h6> -->
                                                <h6 class="mb-1" data-bm="Import Sendiri" data-en="Self Import">Self Import</h6>
                                                <p class="text-muted small mb-0" data-bm="Anda memohon sijil pemeriksaan ini untuk diri sendiri." data-en="You are applying this inspection certificate for yourself.">You are applying this inspection certificate for
                                                    yourself.</p>
                                            </div>
                                        </label>
                                    </a>

                                    <!-- Option 2 -->
                                    <a href = "{{ route('public.inspectionApplicationOthers') }}" class="xintra-radio-box text-center">
                                        <label for="inspectionOthers" class="xintra-radio-label">
                                            <div class="xintra-radio-content">
                                                <i class="bx bx-buildings fs-2 mb-2 text-primary"></i>
                                                <!-- <h6 class="mb-1" data-bm="Untuk Pihak Lain" data-en="For Someone Else">For Someone Else</h6> -->
                                                <h6 class="mb-1" data-bm="Untuk Pihak Lain" data-en="For Someone Else">For Someone Else</h6>
                                                <p class="text-muted small mb-0" data-bm="Untuk syarikat atau individu yang anda wakili." data-en="For companies or individuals you represent.">For companies or individuals you represent.
                                                </p>
                                            </div>
                                        </label>
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item d-sm-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto text-muted">
                            <div class="fw-medium fs-14 text-default mb-3" data-bm="Sijil Konsainan" data-en="Consignment Certificate">Consignment Certificate</div>
                                <div class="d-flex justify-content-center flex-nowrap gap-3" id="customeCheckbox">
                                    <!-- Option 1 -->
                                    <a href = "{{ route('public.consignment.app') }}" class="xintra-radio-box text-center">
                                        <label for="consignmentSelf" class="xintra-radio-label">
                                            <div class="xintra-radio-content">
                                                <i class="bx bx-user fs-2 mb-2 text-primary"></i>
                                                <!-- <h6 class="mb-1">Yourself</h6> -->
                                                <h6 class="mb-1" data-bm="Import Sendiri" data-en="Self Import">Self Import</h6>
                                                <p class="text-muted small mb-0" data-bm="Anda memohon permit import ini untuk diri sendiri." data-en="You are applying this import permit for yourself.">You are applying this import permit for
                                                    yourself.</p>
                                            </div>
                                        </label>
                                    </a>

                                    <!-- Option 2 -->
                                    <a href = "{{ route('public.consignmentOther.app') }}?type=company" class="xintra-radio-box text-center">
                                        <label for="consignmentOthers" class="xintra-radio-label">
                                            <div class="xintra-radio-content">
                                                <i class="bx bx-buildings fs-2 mb-2 text-primary"></i>
                                                <h6 class="mb-1" data-bm="Untuk Pihak Lain" data-en="For Someone Else">For Someone Else</h6>
                                                <p class="text-muted small mb-0" data-bm="Untuk syarikat atau individu yang anda wakili." data-en="For companies or individuals you represent.">For companies or individuals you represent.
                                                </p>
                                            </div>
                                        </label>
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ol>
                </div>
                <div class="card-footer border-top-0 d-none">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all radio buttons with name regType
            const regTypeRadios = document.querySelectorAll('input[name="regType"]');

            regTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // when clicked, get selected value
                    const selectedValue = this.value;

                    if (selectedValue === 'Individu') {
                        // ✅ redirect to new route
                        window.location.href = "{{ route('public.permitApplication') }}";
                    }

                    // Optional: handle other types if needed
                    else if (selectedValue === 'Company') {
                        window.location.href = "{{ route('public.permitAssignApplication') }}";
                    }
                });
            });
        });
    </script>
@endpush
