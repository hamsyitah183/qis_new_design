@extends('pages.app')

@section('pageName', 'Permit Condition List')


@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
          
        ]" 
        title="Add New Permit Condition"
    >
     
    </x-breadcrumb>
@endsection

@section('content')
<style>
/* Full wrapper inside card */
.quill-wrapper {
    border: 1px solid var(--bs-border-color);
    border-radius: .5rem;
    min-height: 150px;
    overflow: hidden;          /* Prevent overflow outside the card */
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
                                            <input type="text" class="form-control" id="itemName" name="itemName" value="{{ $condition->item_name }}" placeholder="Citrus - Lemon, Chinese Mandarine, Limau Kasturi">
                                        </div>
                                        <div class="col-xl-6">
                                            <label for="blog-category" class="form-label">Category</label>
                                            <select class="form-select"  name="itemCategory" id="itemCategory" >
                                                <option value="{{ $condition->category }}" selected>{{ $condition->code->description }}</option>
                                                @foreach ($pbdata as $cate)
                                                    <option value="{{ $cate->cate_code }}" >{{ $cate->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row gy-3 mt-1">
                                        <div class="col-xl-3">
                                            <label for="quanLimit" class="form-label">Quantity Limit (Special case)</label>
                                            <input type="text" class="form-control" id="quanLimit" value="{{ $condition->quantity_limit ?? null }}" name="quanLimit">
                                        </div>
                                        <div class="col-xl-3">
                                            <label for="quanmunit" class="form-label">Measurement Unit (Special case)</label>
                                            <input type="text" class="form-control" id="quanmunit" name="quanmunit" >
                                        </div>
                                        <div class="col-xl-6">
                                            <label for="spedate" class="form-label">Date Limit (Special case)</label>
                                            <input type="date" class="form-control" id="spedate" value="{{ $condition->date_limit }}" name="spedate">
                                        </div>
                                        <div class="col-xl-12">
                                            <label class="form-label">Country</label>
                                            <input 
                                                id="countryTag" 
                                                name="countryTag" 
                                                class="form-control" 
                                                placeholder="Select or type countries..."
                                                
                                            >
                                        </div>
                                        <div class="col-xl-12">
                                            <label class="form-label d-block">Consignment Application (Usage)</label>

                                            <!-- Your Tagify input -->
                                            <input id="usageTags" 
                                                name="usageTags" 
                                                class="form-control" 
                                                placeholder="Select or type usage..."
                                                
                                                >
                                        </div>
                                        <div class="col-xl-12" > <!-- style="display:none" -->
                                            <label class="form-label d-block">Permit Condition</label>
                                            <!-- Quill editor -->
                                            <!-- <div id="permit-condition-editor" style="min-height:150px; border:1px solid var(--bs-border-color); border-radius:.5rem; background:var(--bs-body-bg);"></div> -->
                                            <div class="quill-wrapper">
                                                <div id="permit-condition-editor"></div>
                                            </div>

                                            <!-- hidden input to submit HTML -->
                                            <input type="hidden" name="permit_condition" id="permit-condition-input" value="{{ htmlspecialchars($condition->addional_condition) }}">
                                            <small class="form-text text-muted mt-2">You may use simple formatting — bold, lists, links.</small>
                                        </div>                                        
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button id="submitConditionBtn" type="submit" class="btn btn-primary">
                                        <i class="ri-add-line me-1"></i> Add New Permit Condition
                                    </button>
                                    <a  class="btn btn-secondary">Cancel</a>
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
    window.quill = null;
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const theCountry = {!! json_encode($condition->country) !!};
        const conditionCountry = theCountry.map(i => ({
                    value: i,
                    name: i
                }));
        console.log("Condition Country this:", conditionCountry);

        const theusage = {!! json_encode($condition->usage) !!};
        const conditionUsage = theusage.map(i => ({
                    value: i,
                    name: i
                }));

        $.ajax({
            url: '/get_country',
            method: 'GET',
            success: function(response) {
                const countryList = response.data;

                countryTagify = new Tagify(document.getElementById("countryTag"), {
                    whitelist: countryList,
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
                countryTagify.addTags(conditionCountry);

                console.log("Country loaded:", countryList);
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
                usageTagify.addTags(conditionUsage);

                console.log("Usage loaded:", usageList);
            }
        });

    });
</script>

<script>
    let quill;

    document.addEventListener("DOMContentLoaded", function () {
        quill = new Quill('#permit-condition-editor', {
            modules: {
                toolbar: [
                    [{ header: [1,2,3,false]}],
                    ['bold','italic','underline','strike'],
                    [{list:'ordered'}, {list:'bullet'}],
                    ['link','blockquote','code-block'],
                    [{align:[]}],
                    ['clean']
                ]
            },
            // ⭐ Load Existing HTML into Quill
            const oldHtml = document.getElementById("existingCondition").value;
            if (oldHtml) {
                quill.root.innerHTML = oldHtml;
            }

            // ⭐ Update hidden input on text change
            quill.on("text-change", function () {
                document.getElementById("permit-condition-input").value =
                    quill.root.innerHTML;
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        document.getElementById("submitConditionBtn").addEventListener("click", function () {

            // Make sure quill syncs to hidden input
            const conditionHtml = quill.root.innerHTML;
            document.getElementById("permit-condition-input").value = conditionHtml;

            const formData = new FormData();
            formData.append("itemName", document.getElementById("itemName").value);
            formData.append("itemCategory", document.getElementById("itemCategory").value);
            formData.append("quanLimit", document.getElementById("quanLimit").value);
            formData.append("quanmunit", document.getElementById("quanmunit").value);
            formData.append("spedate", document.getElementById("spedate").value);

            // Tagify values → JSON strings
            formData.append("countryTag", JSON.stringify(countryTagify ? countryTagify.value : []));
            formData.append("usageTags", JSON.stringify(usageTagify ? usageTagify.value : []));

            // Quill HTML
            formData.append("permit_condition", conditionHtml);
            console.log("Submitting form data:", {
                itemName: document.getElementById("itemName").value,
                itemCategory: document.getElementById("itemCategory").value,
                quanLimit: document.getElementById("quanLimit").value,
                quanmunit: document.getElementById("quanmunit").value,
                spedate: document.getElementById("spedate").value,
                countryTag: countryTagify ? countryTagify.value : [],
                usageTags: usageTagify ? usageTagify.value : [],
                permit_condition: conditionHtml
            });

            $.ajax({
                url: `${window.baseUrl}/internal/save_condition`,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function (res) {
                    console.log("Saved:", res);
                },
                error: function (xhr) {
                    console.error("Save Error:", xhr.responseText);
                }
            });
        });

    });
</script>
@endpush

