@extends('pages.app')

@section('pageName', 'Permit Condition List')


@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'],
        ['label' => 'Permit Condition List', 'url' => '/internal/permit_condition', 'data-en' => 'Permit Condition List', 'data-bm' => 'Senarai Syarat Permit'],
        ['label' => 'Edit Permit Condition', 'url' => '#', 'data-en' => 'Edit Permit Condition', 'data-bm' => 'Sunting Syarat Permit'],
    ]" title="Edit Permit Condition" title_en="Edit Permit Condition" title_bm="Sunting Syarat Permit">
    </x-breadcrumb>
@endsection

@push('scripts')
    @vite(['resources/js/pages/permit/permit_condition.js'])
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

        /* Tagify custom styling to match Bootstrap */
        .tagify {
            --tag-bg: var(--bs-primary);
            --tag-hover: var(--bs-primary-dark);
            --tag-text-color: #fff;
            --tags-border-color: var(--bs-border-color);
            --tag-remove-bg: var(--bs-danger);
            --tag-remove-btn-color: #fff;
            --tag-remove-btn-bg--hover: var(--bs-danger-dark);
            border-radius: .375rem;
            padding: .25rem .5rem;
        }
        .tagify__tag {
            margin: 2px 4px 2px 0;
        }
        .tagify__input {
            min-width: 80px;
        }
    </style>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title" data-en="Edit Permit Condition" data-bm="Sunting Syarat Permit">
                        Edit Permit Condition
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" name="id" value="{{ $condition->id }}" id="id">
                    <div class="row gy-3">
                        <div class="col-xl-12">
                            <label for="blog-title" class="form-label" data-en="Item Name" data-bm="Nama Item">Item Name</label>
                            <input type="text" class="form-control" id="itemName" name="itemName"
                                value="{{ $condition->item_name }}"
                                placeholder="Citrus - Lemon, Chinese Mandarine, Limau Kasturi">
                        </div>
                        <div class="col-xl-12">
                            <label for="blog-title" class="form-label" data-en="Scientific Name" data-bm="Nama Saintifik">Scientific Name</label>
                            <input type="text" class="form-control" id="scientificName" name="scientificName"
                            value="{{ $condition->scientific_name }}"
                                placeholder=" ">
                        </div>

                        {{-- ─── NEW: Another Name (Tagify) ─────────────────────────── --}}
                        <div class="col-xl-12">
                            <label class="form-label" data-en="Another Name(s)" data-bm="Nama Lain">Another Name(s)</label>
                            <input id="anotherNameTags" name="anotherNameTags" class="form-control"
                                placeholder="Type alternate names...">
                            <small class="form-text text-muted" data-en="Add alternate names for this item (e.g., local names, synonyms)."
                                   data-bm="Tambah nama alternatif untuk item ini (contoh: nama tempatan, sinonim).">
                                Add alternate names for this item (e.g., local names, synonyms).
                            </small>
                        </div>

                        <div class="col-xl-6">
                            <label for="blog-category" class="form-label" data-en="Category" data-bm="Kategori">Category</label>
                            <select class="form-select" name="itemCategory" id="itemCategory">
                                <option value="{{ $condition->category }}" selected>{{ $condition->code->description }}
                                </option>
                                @foreach ($pbdata as $cate)
                                    <option value="{{ $cate->cate_code }}">{{ $cate->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row gy-3 mt-1">
                        <div class="col-xl-3">
                            <label for="quanLimit" class="form-label" data-en="Quantity Limit (Special case)" data-bm="Had Kuantiti (Kes Khas)">Quantity Limit (Special case)</label>
                            <input type="number" class="form-control" id="quanLimit"
                                value="{{ $condition->quantity_limit ?? null }}" name="quanLimit" min='0'>

                        </div>
                        <div class="col-xl-3">
                            <label for="quanmunit" class="form-label" data-en="Measurement Unit (Special case)" data-bm="Unit Ukuran (Kes Khas)">Measurement Unit (Special case)</label>
                            <select class="form-select" name="quanmunit" id="quanmunit">
                                @foreach ($measurements as $measurement)
                                    <option value="{{ $measurement->cate_code }}"
                                        {{ old('quanmunit', $condition->measurement_unit ?? '') == $measurement->cate_code ? 'selected' : '' }}>
                                        {{ $measurement->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label" data-en="Start Date" data-bm="Tarikh Mula">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date"
                                value="{{ old('start_date', $condition->start_date) }}">
                        </div>

                        <div class="col-xl-3">
                            <label class="form-label" data-en="End Date" data-bm="Tarikh Tamat">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="end_date"
                                value="{{ old('end_date', $condition->end_date) }}">
                        </div>

                        <div class="col-xl-12">
                            <label class="form-label" data-en="Country" data-bm="Negara">Country</label>
                            <input id="countryTag" name="countryTag" class="form-control"
                                placeholder="Select or type countries...">
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label d-block" data-en="Consignment Application (Usage)" data-bm="Permohonan Konsainan (Kegunaan)">Consignment Application (Usage)</label>
                            <input id="usageTags" name="usageTags" class="form-control"
                                placeholder="Select or type usage...">
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label d-block" data-en="Permit Condition" data-bm="Syarat Permit">Permit Condition</label>
                            <div class="quill-wrapper">
                                <div id="permit-condition-editor"></div>
                            </div>
                            <input type="hidden" name="permit_condition" id="permit-condition-input">
                            <small class="form-text text-muted mt-2" data-en="You may use simple formatting — bold, lists, links." data-bm="Anda boleh menggunakan pemformatan ringkas — tebal, senarai, pautan.">You may use simple formatting — bold, lists,
                                links.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <button id="deleteConditionBtn" type="button" class="btn btn-danger">
                        <i class="ri-delete-bin-line me-1"></i> <span data-en="Delete" data-bm="Padam">Delete</span>
                    </button>
                    <div class="d-flex gap-2">
                        <button id="submitConditionBtn" type="submit" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> <span data-en="Update Condition" data-bm="Kemaskini Syarat">Update Condition</span>
                        </button>
                        <a href="{{ url('/internal/permit_condition') }}" class="btn btn-secondary" data-en="Cancel" data-bm="Batal">Cancel</a>
                    </div>
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
        window.anotherNameTagify = null;
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // ─── 1. Country Tagify ──────────────────────────────────────
            const theCountryRaw = {!! json_encode($condition->country) !!};
            const theCountry = Array.isArray(theCountryRaw) ?
                theCountryRaw :
                JSON.parse(theCountryRaw ?? "[]");

            $.ajax({
                url: '/get_country',
                method: 'GET',
                success: function(response) {
                    const countryList = response.data.map(c => ({
                        value: c.value,
                        name: c.name
                    }));

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
                            return (
                                item.value.toLowerCase().includes(search) ||
                                item.name.toLowerCase().includes(search)
                            );
                        }
                    });

                    countryTagify.addTags(conditionCountry);
                }
            });

            // ─── 2. Usage Tagify ────────────────────────────────────────
            const theusageraw = {!! json_encode($condition->usage) !!};
            const theusage = Array.isArray(theusageraw) ?
                theusageraw :
                JSON.parse(theusageraw ?? "[]");

            const conditionUsage = theusage.map(i => ({
                value: i,
                name: i
            }));

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
                    usageTagify.addTags(conditionUsage);
                }
            });

            // ─── 3. Another Name Tagify ──────────────────────────────────
            const anotherNameRaw = {!! json_encode($condition->another_name) !!};
            const anotherNameArray = Array.isArray(anotherNameRaw) ?
                anotherNameRaw :
                JSON.parse(anotherNameRaw ?? "[]");

            const anotherNameTags = anotherNameArray.map(name => ({
                value: name,
                name: name
            }));

            // No whitelist – free text entry
            anotherNameTagify = new Tagify(document.getElementById("anotherNameTags"), {
                tagTextProp: 'name',
                enforceWhitelist: false,
                editTags: false,
                dropdown: {
                    enabled: 0, // no dropdown, just free typing
                },
                // Allow any text
                patterns: {
                    text: /^.+$/ // any non-empty string
                }
            });

            // Add existing tags
            if (anotherNameTags.length) {
                anotherNameTagify.addTags(anotherNameTags);
            }

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

            const longText = `{!! $condition->addional_condition !!}`;
            quill.clipboard.dangerouslyPasteHTML(longText);
        });
    </script>
@endpush