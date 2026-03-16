@extends('pages.app')

@section('pageName', 'Permit Condition List')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Home', 'url' => '#']]" title="Add New Permit Condition">

    </x-breadcrumb>
@endsection

@section('content')
    <style>
        /* Full wrapper inside card */
        .quill-wrapper {
            border: 1px solid var(--bs-border-color);
            border-radius: .5rem;
            min-height: 150px;
            overflow: hidden;
            /* Prevent overflow outside the card */
            background: var(--bs-body-bg);
        }

        /* Make Quill content fit */
        .quill-wrapper .ql-container {
            height: auto !important;
            min-height: 150px;
            border: none !important;
        }

        .quill-wrapper .ql-editor {
            min-height: 150px;
            padding: 12px 15px;
        }
    </style>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        Add New
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-3">
                        <div class="col-xl-12">
                            <label for="blog-title" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="itemName" name="itemName"
                                placeholder="Citrus - Lemon, Chinese Mandarine, Limau Kasturi">
                        </div>
                        <div class="col-xl-6">
                            <label for="blog-category" class="form-label">Category</label>
                            <select class="form-select" name="itemCategory" id="itemCategory">
                                <option value="">Select Category</option>
                                @foreach ($pbdata as $cate)
                                    <option value="{{ $cate->cate_code }}">{{ $cate->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row gy-3 mt-1">
                        <div class="col-xl-3">
                            <label for="quanLimit" class="form-label">Quantity Limit (Special case)</label>
                            <input type="text" class="form-control" id="quanLimit" name="quanLimit">
                        </div>
                        <div class="col-xl-3">
                            <label for="quanmunit" class="form-label">Measurement Unit (Special case)</label>
                            <input type="text" class="form-control" id="quanmunit" name="quanmunit">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label">Country</label>
                            <select id="countrySelect" name="countrySelect[]" class="form-control xintra-select2" multiple
                                data-route="/get_country" style="width: 100%;">
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>

                        <div class="col-xl-12">
                            <label class="form-label d-block">Consignment Application (Usage)</label>
                            <select id="usageSelect" name="usageSelect[]" class="form-control xintra-select2" multiple
                                data-route="/internal/get_pbdata/consignment_application" style="width: 100%;">
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>

                        <div class="col-xl-12"> <!-- style="display:none" -->
                            <label class="form-label d-block">Permit Condition</label>
                            <!-- Quill editor -->
                            <!-- <div id="permit-condition-editor" style="min-height:150px; border:1px solid var(--bs-border-color); border-radius:.5rem; background:var(--bs-body-bg);"></div> -->
                            <div class="quill-wrapper">
                                <div id="permit-condition-editor"></div>
                            </div>

                            <!-- hidden input to submit HTML -->
                            <input type="hidden" name="permit_condition" id="permit-condition-input">
                            <small class="form-text text-muted mt-2">You may use simple formatting — bold, lists,
                                links.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button id="submitConditionBtn" type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Add New Permit Condition
                    </button>
                    <a href="{{ url('/internal/permit_condition') }}" class="btn btn-secondary">Cancel</a>
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
        window.countryTagify = null;
        window.usageTagify = null;
    </script>

    @vite(['resources/js/pages/internal/misc/permit_add.js'])
@endpush
