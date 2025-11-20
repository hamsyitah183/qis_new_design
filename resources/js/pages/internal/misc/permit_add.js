import $ from "jquery";
import Swal from "sweetalert2";

export function one() {
    
} one();

export function two() {
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
            placeholder: 'Write permit conditions here...',
            theme: 'snow'
        });
    });
}two();

export function three() {
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
} three();

    