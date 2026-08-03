@extends('pages.app')

@section('pageName', __('Apply Application'))

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '/', 'data-en' => 'Home', 'data-bm' => 'Utama'], ['label' => 'New Application', 'url' => '#', 'data-en' => 'New Application', 'data-bm' => 'Permohonan Baru']]" title=" ">

    </x-breadcrumb>
@endsection

@push('scripts')
    @vite(['resources/js/pages/apply_application.js'])
@endpush

@push('style')
    <style>
        input[type="radio"].form-check-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .cursor-pointer {
            cursor: pointer !important;
        }

        @media (max-width: 576px) {
            .text-type {
                font-size: 11px !important;
                width: 80px;
            }

            .tab-content button {
                font-size: 11px;
            }

            .type-div .fs-15 {
                font-size: 13px
            }

            .button-group .text-start p {
                font-size: 11px;
            }

        }

        @media (max-width: 375px) {}
    </style>
@endpush

@section('content')

    <div class="col-12">
        <div class="d-flex justify-content-center align-items-center">
            <div class="card custom-card my-auto border">
                <div class="card-body p-4">
                    <div>
                        <ul class="nav nav-tabs tab-style-8 d-flex justify-content-around border-bottom-0 rounded-top"
                            id="myTab1" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link p-1 p-md-3 active" id="order-tab" data-bs-toggle="tab"
                                    data-bs-target="#order-tab-pane" type="button" role="tab" aria-controls="order-tab"
                                    aria-selected="true">
                                    <span class="d-flex flex-column gap-1 align-items-center d-md-inline">

                                        <span class="text-type text-wrap" data-en="Application Type" data-bm="Jenis Permohonan">Application Type</span>
                                    </span>
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link p-1 p-md-3" id="shipped-tab" data-bs-toggle="tab"
                                    data-bs-target="#shipped-tab-pane" type="button" role="tab"
                                    aria-controls="shipped-tab" aria-selected="false" tabindex="-1">
                                    <span class="d-flex flex-column gap-1 align-items-center d-md-inline">
                                        
                                        <span class="text-type text-wrap" data-en="Category" data-bm="Kategori"> Category </span>
                                    </span>
                                </button>
                            </li>

                        </ul>
                        <div class="tab-content" id="myTabContent">
                            @include('pages.public.application.type')
                            @include('pages.public.application.category')
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection