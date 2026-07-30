@extends('pages.app')

@section('pageName', 'Permit Condition List')


@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Dashboard', 'url' => '/', 'data-en' => 'Dashboard', 'data-bm' => 'Dashboard'], ['label' => 'Permit Condition List', 'url' => '/internal/permit_condition', 'data-en' => 'Permit Condition List', 'data-bm' => 'Senarai Syarat Permit'], ['label' => 'Add New Permit Condition', 'url' => '#', 'data-en' => 'Add New Permit Condition', 'data-bm' => 'Tambah Syarat Permit Baru']]" title="Add New Permit Condition" title_en="Add New Permit Condition" title_bm="Tambah Syarat Permit Baru">

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
                    <div class="card-title" data-en="Add New" data-bm="Tambah Baru">
                        Add New
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-3">
                        <div class="col-xl-12">
                            <label for="blog-title" class="form-label" data-en="Item Name" data-bm="Nama Item">Item Name</label>
                            <input type="text" class="form-control" id="itemName" name="itemName"
                                placeholder="Citrus - Lemon, Chinese Mandarine, Limau Kasturi">
                        </div>
                        <div class="col-xl-12">
                            <label for="blog-title" class="form-label" data-en="Scientific Name" data-bm="Nama Saintifik">Scientific Name</label>
                            <input type="text" class="form-control" id="scientificName" name="scientificName"
                                placeholder=" ">
                        </div>
                        <div class="col-xl-6">
                            <label for="blog-category" class="form-label" data-en="Category" data-bm="Kategori">Category</label>
                            <select class="form-select" name="itemCategory" id="itemCategory">
                                <option value="" data-en="Select Category" data-bm="Pilih Kategori">Select Category</option>
                                @foreach ($pbdata as $cate)
                                    <option value="{{ $cate->cate_code }}">{{ $cate->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row gy-3 mt-1">
                        <div class="col-xl-3">
                            <label for="quanLimit" class="form-label" data-en="Quantity Limit (Special case)" data-bm="Had Kuantiti (Kes Khas)">Quantity Limit (Special case)</label>
                            <input type="text" class="form-control" id="quanLimit" name="quanLimit">
                        </div>
                        <div class="col-xl-3">
                            <label for="quanmunit" class="form-label" data-en="Measurement Unit (Special case)" data-bm="Unit Ukuran (Kes Khas)">Measurement Unit (Special case)</label>
                            {{-- <input type="text" class="form-control" id="quanmunit" name="quanmunit"> --}}
                            <select name="quanmunit" id="quanmunit" class="form-select">
                                @foreach ($measurementUnit as $item)
                                    <option value="{{ $item->id }}">{{ $item->publicCode->description }}  ({{  $item->publicCode->cate_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label" data-en="Start Date" data-bm="Tarikh Mula">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="col-xl-3">
                            <label class="form-label" data-en="End Date" data-bm="Tarikh Tamat">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        <div class="col-xl-12">
                            <label class="form-label" data-en="Country" data-bm="Negara">Country</label>
                            <select id="countrySelect" name="countrySelect[]" class="form-control xintra-select2" multiple
                                data-route="/get_country" style="width: 100%;">
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>

                        <div class="col-xl-12">
                            <label class="form-label d-block" data-en="Consignment Application (Usage)" data-bm="Permohonan Konsainan (Kegunaan)">Consignment Application (Usage)</label>
                            <select id="usageSelect" name="usageSelect[]" class="form-control xintra-select2" multiple
                                data-route="/internal/get_pbdata/consignment_application" style="width: 100%;">
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>

                        <div class="col-xl-12"> <!-- style="display:none" -->
                            <label class="form-label d-block" data-en="Permit Condition" data-bm="Syarat Permit">Permit Condition</label>
                            <!-- Quill editor -->
                            <!-- <div id="permit-condition-editor" style="min-height:150px; border:1px solid var(--bs-border-color); border-radius:.5rem; background:var(--bs-body-bg);"></div> -->
                            <div class="quill-wrapper">
                                <div id="permit-condition-editor"></div>
                            </div>

                            <!-- hidden input to submit HTML -->
                            <input type="hidden" name="permit_condition" id="permit-condition-input">
                            <small class="form-text text-muted mt-2" data-en="You may use simple formatting — bold, lists, links." data-bm="Anda boleh menggunakan pemformatan ringkas — tebal, senarai, pautan.">You may use simple formatting — bold, lists,
                                links.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button id="submitConditionBtn" type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> <span data-en="Add New Permit Condition" data-bm="Tambah Syarat Permit Baru">Add New Permit Condition</span>
                    </button>
                    <a href="{{ url('/internal/permit_condition') }}" class="btn btn-secondary" data-en="Cancel" data-bm="Batal">Cancel</a>
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
