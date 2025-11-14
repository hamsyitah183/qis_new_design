@extends('pages.front')

@push('style')
    <style>
        input[type="radio"].form-check-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/pages/auth/registerAction.js'])
@endpush

@section('content')
    <div class="row authentication authentication-cover-main mx-0 overflow-hidden">
        <div class="col-xxl-5 col-xl-5 col-lg-12 d-xl-block d-none px-0">
            <div class="authentication-cover overflow-hidden">

                <div class="aunthentication-cover-content d-flex align-items-center justify-content-center">
                    <div class="d-flex justify-content-center align-items-center flex-column">

                        <img src="https://qis-app.sabah.gov.my/assets/logo-small-2e441c05.png" class="mb-2"
                            style="object-fit: contain; height: 350px;">

                        {{-- <img src="https://qis-app.sabah.gov.my/assets/preparedby-f6029c92.png" alt="" srcset="" style="height: 70px;" class="bg-white p-2 rounded-3"> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-7 col-xl-7">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-10 col-xl-9 col-lg-9 col-12 p-4">
                    <div class="card custom-card my-auto border">
                        <div class="card-body p-5">


                            <form enctype="multipart/form-data" id="registerForm" action="/register">
                                <ul class="nav nav-tabs tab-style-8 d-sm-flex d-block justify-content-around border-bottom-0 bg-light rounded-top"
                                    id="myTab1" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link p-3 active" id="order-tab" data-bs-toggle="tab"
                                            data-bs-target="#order-tab-pane" type="button" role="tab"
                                            aria-controls="order-tab" aria-selected="true"><i
                                                class="ti ti-user  me-2 align-middle"></i>Type</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link p-3" id="confirmed-tab" data-bs-toggle="tab"
                                            data-bs-target="#confirm-tab-pane" type="button" role="tab"
                                            aria-controls="confirmed-tab" aria-selected="false" tabindex="-1">
                                            <i class="ti ti-edit me-2 align-middle"></i> Personal Info</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link p-3" id="shipped-tab" data-bs-toggle="tab"
                                            data-bs-target="#shipped-tab-pane" type="button" role="tab"
                                            aria-controls="shipped-tab" aria-selected="false" tabindex="-1">
                                            <i class="ti ti-download me-2 align-middle"></i> Upload Attachment</button>
                                    </li>

                                </ul>
                                <div class="tab-content " id="myTabContent">
                                    @include('pages.authentication.includes.register.1')

                                    <!-- second tab -->
                                    @include('pages.authentication.includes.register.2')

                                    @include('pages.authentication.includes.register.3')

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
