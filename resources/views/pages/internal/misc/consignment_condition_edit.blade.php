@extends('pages.app')

@section('pageName', 'Consignment Item Edit')


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
            'label' => 'Edit Consignment Item',
            'url' => '#',
            'data-en' => 'Edit Consignment Item',
            'data-bm' => 'Sunting Item Konsainan',
        ],
    ]" title="Edit Consignment Item" title_en="Edit Consignment Item"
        title_bm="Sunting Item Konsainan">

    </x-breadcrumb>
@endsection

@push('scripts')
    @vite(['resources/js/pages/internal/misc/consignment_condition_edit.js'])
@endpush

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
               
                <div class="card-body">
                    <input type="hidden" name="id" value={{ $condition->id }} id="id">
                    <div class="row gy-3">
                        <div class="col-xl-12">
                            <label for="blog-title" class="form-label" data-en="Item Name" data-bm="Nama Item">Item
                                Name</label>
                            <input type="text" class="form-control" id="itemName" name="itemName"
                                value="{{ $condition->item_name }}"
                                placeholder="Citrus - Lemon, Chinese Mandarine, Limau Kasturi">
                        </div>
                        <div class="col-xl-12">
                            <label for="blog-title" class="form-label" data-en="Scientific Name"
                                data-bm="Nama Saintifik">Scientific Name</label>
                            <input type="text" class="form-control" id="scientificName" name="scientificName"
                                value="{{ $condition->scientific_name }}"
                                placeholder="Citrus - Lemon, Chinese Mandarine, Limau Kasturi">
                        </div>
                        <div class="col-xl-6">
                            <label for="blog-category" class="form-label" data-en="Category"
                                data-bm="Kategori">Category</label>
                            <select class="form-select" name="itemCategory" id="itemCategory">
                                @foreach ($pbdata as $cate)
                                    <option value="{{ $cate->id }}"
                                        {{ $condition->category == $cate->id ? 'selected' : '' }}>
                                        {{ $cate->description }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                    <div class="row gy-3 mt-1">
                        <div class="col-xl-3">
                            <label for="quanLimit" class="form-label" data-en="Quantity Limit (Special case)"
                                data-bm="Had Kuantiti (Kes Khas)">Quantity Limit (Special case)</label>
                            <input type="number" class="form-control" id="quanLimit"
                                value="{{ $condition->quantity_limit ?? null }}" name="quanLimit" min = '0'>

                        </div>
                        <div class="col-xl-3">
                            <label for="quanmunit" class="form-label" data-en="Measurement Unit (Special case)"
                                data-bm="Unit Ukuran (Kes Khas)">Measurement Unit (Special case)</label>
                            {{-- <input type="text" class="form-control" id="quanmunit" name="quanmunit"> --}}
                            <select class="form-select" name="quanmunit" id="quanmunit">
                                @foreach ($measurements as $measurement)
                                    <option value="{{ $measurement->cate_code }}"
                                        {{ old('quanmunit', $condition->measurement_unit ?? '') == $measurement->cate_code ? 'selected' : '' }}>
                                        {{ $measurement->description }}
                                    </option>
                                @endforeach
                            </select>


                        </div>
                        {{-- @dd($condition) --}}
                        <div class="col-xl-3">
                            <label class="form-label" data-en="Start Date" data-bm="Tarikh Mula">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id = "start_date"
                                value="{{ old('start_date', $condition->start_date) }}">
                        </div>

                        <div class="col-xl-3">
                            <label class="form-label" data-en="End Date" data-bm="Tarikh Tamat">End Date</label>
                            <input type="date" class="form-control" name="end_date" id = "end_date"
                                value="{{ old('end_date', $condition->end_date) }}">
                        </div>



                        <div class="col-xl-12">
                            <label class="form-label" data-en="Country" data-bm="Negara">Country</label>
                            <input id="countryTag" name="countryTag" class="form-control"
                                placeholder="Select or type countries...">
                        </div>
                        <div class="col-xl-12 d-none">
                            <label class="form-label d-block" data-en="Consignment Application (Usage)"
                                data-bm="Permohonan Konsainan (Kegunaan)">Consignment Application (Usage)</label>

                            <!-- Your Tagify input -->
                            <input id="usageTags" name="usageTags" class="form-control"
                                placeholder="Select or type usage...">
                        </div>
                        <div class="col-xl-12"> <!-- style="display:none" -->
                            <label class="form-label d-block" data-en="Permit Condition" data-bm="Syarat Permit">Permit
                                Condition</label>
                            <!-- Quill editor -->
                            <!-- <div id="permit-condition-editor" style="min-height:150px; border:1px solid var(--bs-border-color); border-radius:.5rem; background:var(--bs-body-bg);"></div> -->
                            <div class="quill-wrapper">
                                <div id="permit-condition-editor"></div>
                            </div>

                            <!-- hidden input to submit HTML -->
                            <input type="hidden" name="permit_condition" id="permit-condition-input">
                            <small class="form-text text-muted mt-2"
                                data-en="You may use simple formatting — bold, lists, links."
                                data-bm="Anda boleh menggunakan pemformatan ringkas — tebal, senarai, pautan.">You may use
                                simple formatting — bold, lists,
                                links.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button id="submitConditionBtn" type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> <span data-en="Update Condition"
                            data-bm="Kemaskini Syarat">Update Condition</span>
                    </button>
                    <a href="/internal/consignment_condition" class="btn btn-secondary" data-en="Cancel"
                        data-bm="Batal">Cancel</a>
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {


            // 1. Raw data from Laravel
            const theCountryRaw = {!! json_encode($condition->country) !!};
            // console.log("Raw Country this:", theCountryRaw);

            // 2. Fix: convert string "['CN','ID']" → real array
            const theCountry = Array.isArray(theCountryRaw) ?
                theCountryRaw :
                JSON.parse(theCountryRaw ?? "[]");

            // 3. Build the {value,name} pair list
            const conditionCountry = theCountry.map(i => ({
                value: i,
                name: i
            }));




            const theusageraw = {!! json_encode($condition->usage) !!};
            const theusage = Array.isArray(theusageraw) ?
                theusageraw :
                JSON.parse(theusageraw ?? "[]");

            const conditionUsage = theusage.map(i => ({
                value: i,
                name: i
            }));


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

                    // ─── Map stored country codes to the filtered list ───
                    const conditionCountry = theCountry
                        .map(code => countryList.find(c => c.value === code))
                        .filter(Boolean);

                    countryTagify = new Tagify(document.getElementById("countryTag"), {
                        whitelist: countryList,
                        tagTextProp: 'name',
                        enforceWhitelist: true,
                        editTags: false,
                        dropdown: {
                            enabled: 1,
                            maxItems: 20,
                            highlightFirst: true,
                            mapValueTo: 'name',
                        },
                        dropdownFilter: (item, value) => {
                            const search = value.toLowerCase();
                            return item.name.toLowerCase().includes(search) ||
                                item.value.toLowerCase().includes(search);
                        }
                    });

                    // ✅ Add the saved tags
                    countryTagify.addTags(conditionCountry);
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
                        tagTextProp: 'name',
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
                    usageTagify.addTags(conditionUsage);

                    // console.log("Usage loaded:", usageList);
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

            // Your long text from backend
            const longText = `{!! $condition->addional_condition !!}`;

            // Insert into Quill with formatting
            quill.clipboard.dangerouslyPasteHTML(longText);
        });
    </script>
@endpush
