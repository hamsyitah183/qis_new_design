@extends('pages.app')

@section('pageName', 'Add Consignment Item')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Consignment List', 'url' => '/internal/consignment_condition'],
        ['label' => 'Add Consignment Item', 'url' => '#'],
    ]" title="Add Consignment Item">
    </x-breadcrumb>
@endsection

@push('scripts')
@vite(['resources/js/pages/internal/misc/consignment_condition_edit.js'])
@endpush


@section('content')

    <style>
        .quill-wrapper {
            border: 1px solid var(--bs-border-color);
            border-radius: .5rem;
            min-height: 150px;
            overflow: hidden;
            background: var(--bs-body-bg);
        }

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
                        Add New Consignment Item
                    </div>
                </div>

                <div class="card-body">

                    <!-- Hidden ID (empty for create) -->
                    <input type="hidden" id="id" name="id">

                    <div class="row gy-3">

                        <!-- Item Name -->
                        <div class="col-xl-12">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="itemName" name="itemName"
                                placeholder="Citrus - Lemon, Chinese Mandarine, Limau Kasturi">
                        </div>

                        <!-- Category -->
                        <div class="col-xl-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="itemCategory" id="itemCategory">
                                <option value="">-- Select Category --</option>
                                @foreach ($pbdata as $cate)
                                    <option value="{{ $cate->cate_code }}">
                                        {{ $cate->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="row gy-3 mt-1">

                        <!-- Quantity -->
                        <div class="col-xl-3">
                            <label class="form-label">
                                Quantity Limit (Special case)
                            </label>
                            <input type="text" class="form-control" id="quanLimit" name="quanLimit">
                        </div>

                        <!-- Unit -->
                        <div class="col-xl-3">
                            <label class="form-label">
                                Measurement Unit (Special case)
                            </label>
                            <input type="text" class="form-control" id="quanmunit" name="quanmunit">
                        </div>

                        <!-- Date -->
                        <div class="col-xl-6">
                            <label class="form-label">
                                Date Limit (Special case)
                            </label>
                            <input type="date" class="form-control" id="spedate" name="spedate">
                        </div>

                        <!-- Country -->
                        <div class="col-xl-12">
                            <label class="form-label">Country</label>
                            <input id="countryTag" name="countryTag" class="form-control"
                                placeholder="Select or type countries...">
                        </div>

                        <!-- Usage -->
                        <div class="col-xl-12">
                            <label class="form-label d-block">
                                Consignment Application (Usage)
                            </label>
                            <input id="usageTags" name="usageTags" class="form-control"
                                placeholder="Select or type usage...">
                        </div>

                        <!-- Permit Condition -->
                        <div class="col-xl-12">
                            <label class="form-label d-block">
                                Permit Condition
                            </label>

                            <div class="quill-wrapper">
                                <div id="permit-condition-editor"></div>
                            </div>

                            <input type="hidden" name="permit_condition" id="permit-condition-input">

                            <small class="form-text text-muted mt-2">
                                You may use simple formatting — bold, lists, links.
                            </small>
                        </div>

                    </div>

                </div>

                <div class="card-footer text-end">

                    <button id="submitConditionBtn" type="button" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i>
                        Create Condition
                    </button>

                    <a href="/internal/consignment_condition" class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script>
        window.conditionData = null; // Explicitly empty for create
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {




           

            $.ajax({
                url: '/get_country',
                method: 'GET',
                success: function(response) {

                    // ✅ normalized whitelist
                    const countryList = response.data.map(c => ({
                        value: c.value,
                        name: c.name
                    }));

                    console.log('Country List:', countryList);

                    

                    countryTagify = new Tagify(document.getElementById("countryTag"), {
                        whitelist: countryList,
                        tagTextProp: 'name', // show country name in tag
                        enforceWhitelist: true,
                        editTags: false,

                        dropdown: {
                            enabled: 1,
                            maxItems: 20,
                            highlightFirst: true,
                            mapValueTo: 'name',
                        },

                        // 🔥 THIS enables searching by BOTH code & name
                        dropdownFilter: (item, value) => {
                            const search = value.toLowerCase();

                            return (
                                item.value.toLowerCase().includes(search) ||
                                // code (MY, CN)
                                item.name.toLowerCase().includes(search) // country name
                            );
                        }
                    });



                }
            });


            // --- 2. Get usage list ---
            $.ajax({
                url: `/internal/get_pbdata/consignment_application`,
                method: 'GET',
                success: function(response) {
                    const usageList = response.data.map(i => ({
                        value: i.description,
                        name: i.description
                    }));

                    console.log('Usage List:', usageList);

                    usageTagify = new Tagify(document.getElementById("usageTags"), {
                        whitelist: usageList,
                        enforceWhitelist: false,
                        editTags: false,
                        dropdown: {
                            enabled: 1,
                            maxItems: 20,
                            highlightFirst: true,
                            mapValueTo: "name",
                        },
                        dropdownFilter: (item, value) =>
                            item.name.toLowerCase().includes(value.toLowerCase())
                    });

                }
            });

        });
    </script>

    <script>
        let quill;

        document.addEventListener("DOMContentLoaded", function() {
            quill = new Quill('#permit-condition-editor', {
                modules: {
                    toolbar: [
                        [{
                            header: [1, 2, 3, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            list: 'ordered'
                        }, {
                            list: 'bullet'
                        }],
                        ['link', 'blockquote', 'code-block'],
                        [{
                            align: []
                        }],
                        ['clean']
                    ]
                },
                placeholder: 'Write permit conditions here...',
                theme: 'snow'
            });

         
        });
    </script>
@endpush
