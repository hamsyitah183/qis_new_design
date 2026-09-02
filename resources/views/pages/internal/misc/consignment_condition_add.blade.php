@extends('pages.app')

@section('pageName', 'Add Consignment Item')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
        [
            'label' => 'Consignment List',
            'url' => '/internal/consignment_condition',
            'data-en' => 'Consignment List',
            'data-bm' => 'Senarai Konsainan',
        ],
        [
            'label' => 'Add Consignment Item',
            'url' => '#',
            'data-en' => 'Add Consignment Item',
            'data-bm' => 'Tambah Item Konsainan',
        ],
    ]" title="Add Consignment Item" title_en="Add Consignment Item"
        title_bm="Tambah Item Konsainan">
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



                <div class="card-body">

                    <!-- Hidden ID (empty for create) -->
                    <input type="hidden" id="id" name="id">

                    <div class="row gy-3">

                        <!-- Item Name -->
                        <div class="col-xl-12">
                            <label class="form-label" data-en="Item Name" data-bm="Nama Item">Item Name</label>
                            <input type="text" class="form-control" id="itemName" name="itemName"
                                placeholder="Citrus - Lemon, Chinese Mandarine, Limau Kasturi">
                        </div>

                        <div class="col-xl-12">
                            <label class="form-label" data-en="Scientific Name" data-bm="Nama Saintifik">Scientific
                                Name</label>
                            <input type="text" class="form-control" id="scientificName" name="scientificName"
                                placeholder="">
                        </div>

                        <!-- Category -->
                        <div class="col-xl-6">
                            <label class="form-label" data-en="Category" data-bm="Kategori">Category</label>
                            <select class="form-select" name="itemCategory" id="itemCategory">
                                <option value="" data-en="-- Select Category --" data-bm="-- Pilih Kategori --">--
                                    Select Category --</option>

                                @foreach ($pbdata as $cate)
                                    <option value="{{ $cate->id }}">
                                        {{ $cate->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="row gy-3 mt-1">

                        <!-- Quantity -->
                        <div class="col-xl-3">
                            <label class="form-label" data-en="Quantity Limit (Special case)"
                                data-bm="Had Kuantiti (Kes Khas)">
                                Quantity Limit (Special case)
                            </label>
                            <input type="text" class="form-control" id="quanLimit" name="quanLimit">
                        </div>

                        <!-- Unit -->
                        <div class="col-xl-3">
                            <label class="form-label" data-en="Measurement Unit (Special case)"
                                data-bm="Unit Ukuran (Kes Khas)">
                                Measurement Unit (Special case)
                            </label>
                            <input type="text" class="form-control" id="quanmunit" name="quanmunit">
                        </div>

                        <!-- Start Date -->
                        <div class="col-xl-3">
                            <label class="form-label" data-en="Start Date (Special case)" data-bm="Tarikh Mula (Kes Khas)">
                                Start Date (Special case)
                            </label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>

                        <!-- End Date -->
                        <div class="col-xl-3">
                            <label class="form-label" data-en="End Date (Special case)" data-bm="Tarikh Tamat (Kes Khas)">
                                End Date (Special case)
                            </label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>

                        <!-- Country -->
                        <div class="col-xl-12">
                            <label class="form-label" data-en="Country" data-bm="Negara">Country</label>
                            <input id="countryTag" name="countryTag" class="form-control"
                                placeholder="Select or type countries...">
                        </div>

                        <!-- Usage -->
                        <div class="col-xl-12 d-none">
                            <label class="form-label d-block" data-en="Consignment Application (Usage)"
                                data-bm="Permohonan Konsainan (Kegunaan)">
                                Consignment Application (Usage)
                            </label>
                            <input id="usageTags" name="usageTags" class="form-control"
                                placeholder="Select or type usage...">
                        </div>

                        <!-- Permit Condition -->
                        <div class="col-xl-12">
                            <label class="form-label d-block" data-en="Permit Condition" data-bm="Syarat Permit">
                                Permit Condition
                            </label>

                            <div class="quill-wrapper">
                                <div id="permit-condition-editor"></div>
                            </div>

                            <input type="hidden" name="permit_condition" id="permit-condition-input">

                            <small class="form-text text-muted mt-2"
                                data-en="You may use simple formatting — bold, lists, links."
                                data-bm="Anda boleh menggunakan pemformatan ringkas — tebal, senarai, pautan.">
                                You may use simple formatting — bold, lists, links.
                            </small>
                        </div>

                    </div>

                </div>

                <div class="card-footer text-end">

                    <button id="submitConditionBtn" type="button" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i>
                        <span data-en="Create Condition" data-bm="Cipta Syarat">Create Condition</span>
                    </button>

                    <a href="/internal/consignment_condition" class="btn btn-secondary" data-en="Cancel"
                        data-bm="Batal">
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
        window.countryTagify = null;
        window.usageTagify = null;
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $.ajax({
                url: '/get_country',
                method: 'GET',
                success: function(response) {

                    // ─── Only Sarawak (SWK) and Brunei (BN) ───
                    const allowedCodes = ['SWK', 'BN'];
                    const countryList = response.data
                        .filter(c => allowedCodes.includes(c.value))
                        .map(c => ({
                            value: c.value,
                            name: c.name
                        }));

                    countryTagify = new Tagify(document.getElementById("countryTag"), {
                        whitelist: countryList,
                        tagTextProp: 'name', // display name in tags
                        enforceWhitelist: true,
                        editTags: false,
                        dropdown: {
                            enabled: 1,
                            maxItems: 20,
                            highlightFirst: true,
                            mapValueTo: 'name', // show name in dropdown list
                        },
                        // ─── Search by BOTH name and code, but prefer name ───
                        dropdownFilter: (item, value) => {
                            const search = value.toLowerCase();
                            // Try name first, then code
                            return item.name.toLowerCase().includes(search) ||
                                item.value.toLowerCase().includes(search);
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
