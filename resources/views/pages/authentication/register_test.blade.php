@extends('pages.front')

@push('style')
    <style>
        input[type="radio"].form-check-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        @media (max-width: 576px) {
            .text-type {
                font-size: 11px !important;
                width: 80px;
            }

            .tab-content button {
                font-size: 11px;
            }

            .type-div .fs-15  {
                font-size: 13px
            }

            .button-group .text-start p {
                font-size: 11px;
            }
           
        }

        @media (max-width: 375px) {}
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
                        <h4 class="text-center text-white">Register</h4>
                        <img src="https://qis-app.sabah.gov.my/assets/QIS-a3dc1042.gif" class="mb-2"
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
                        <div class="card-body p-2 p-md-3">

                            <div class="d-flex d-xl-none justify-content-center align-items-center p-1 flex-column ">
                                <h4 class="text-center">Register</h4>
                                <img src="https://qis-app.sabah.gov.my/assets/QIS-a3dc1042.gif" class=""
                                    style="object-fit: contain; height: 150px;">
                            </div>

                            <form enctype="multipart/form-data" id="registerForm" action="/register">
                                <ul class="nav nav-tabs tab-style-8 d-flex justify-content-around border-bottom-0 rounded-top"
                                    id="myTab1" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link p-1 p-md-3 active" id="order-tab" data-bs-toggle="tab"
                                            data-bs-target="#order-tab-pane" type="button" role="tab"
                                            aria-controls="order-tab" aria-selected="true">
                                            <span class="d-flex flex-column gap-1 align-items-center d-md-inline">
                                                <i class="ti ti-user me-2 align-middle"></i>
                                                <span class="text-type text-wrap">Type</span>
                                            </span>

                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link p-1 p-md-3" id="confirmed-tab" data-bs-toggle="tab"
                                            data-bs-target="#confirm-tab-pane" type="button" role="tab"
                                            aria-controls="confirmed-tab" aria-selected="false" tabindex="-1">
                                            <span class="d-flex flex-column gap-1 align-items-center d-md-inline">
                                                <i class="ti ti-edit me-2 align-middle"></i>
                                                <span class="text-type text-wrap">Personal Info </span>
                                            </span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link p-1 p-md-3" id="shipped-tab" data-bs-toggle="tab"
                                            data-bs-target="#shipped-tab-pane" type="button" role="tab"
                                            aria-controls="shipped-tab" aria-selected="false" tabindex="-1">
                                            <span class="d-flex flex-column gap-1 align-items-center d-md-inline">
                                                <i class="ti ti-download me-2 align-middle"></i>
                                                <span class="text-type text-wrap"> Upload Attachment </span>
                                            </span>
                                        </button>
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
