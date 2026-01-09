import $ from "jquery";
import Swal from "sweetalert2";
// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";
function fetchCountryList() {
    const $select = $("#countrySelect");
    const url = $select.data("route");

    return $.ajax({
        url,
        type: "GET",
        dataType: "json",
        cache: false,
        success: (response) => {
            const data = response.data || [];

            // Clear previous options
            $select.empty();

            // Optional: placeholder option
            $select.append('<option value="">-- Select Countries --</option>');

            // Add options dynamically
            data.forEach((country) => {
                // country.value = code, country.name = full name
                $select.append(
                    `<option value="${country.value}">${country.name} (${country.value})</option>`
                );
            });

            // Initialize Select2
            $select.select2({
                width: "100%",
                placeholder: "-- Select Countries --",
                allowClear: true,
                multiple: true,
                matcher: function (params, data) {
                    // If no search term, return all data
                    if ($.trim(params.term) === "") {
                        return data;
                    }

                    // Search by both name or value
                    const term = params.term.toLowerCase();
                    if (
                        data.text.toLowerCase().includes(term) ||
                        data.id.toLowerCase().includes(term)
                    ) {
                        return data;
                    }

                    // Return null if not matched
                    return null;
                },
            });
        },
        error: (xhr) => {
            console.error("Failed to load countries:", xhr.responseText);
            Swal.fire({
                icon: "error",
                title: "Failed to Load Countries",
                text: "Please try again or check your connection.",
            });
        },
    });
}

function fetchUsageList() {
    const $select = $("#usageSelect");
    const url = $select.data("route");

    return $.ajax({
        url,
        type: "GET",
        dataType: "json",
        cache: false,
        success: (response) => {
            const data = response.data || [];

            // Clear existing options
            $select.empty();

            // Optional placeholder
            $select.append('<option value="">-- Select Usage --</option>');

            // Add options dynamically
            data.forEach((item) => {
                // value and text are the same
                $select.append(
                    `<option value="${item.description}">${item.description}</option>`
                );
            });

            // Initialize Select2
            $select.select2({
                width: "100%",
                placeholder: "-- Select Usage --",
                allowClear: true,
                multiple: true,
                matcher: function (params, data) {
                    if ($.trim(params.term) === "") return data;

                    const term = params.term.toLowerCase();
                    if (data.text.toLowerCase().includes(term)) {
                        return data;
                    }

                    return null;
                },
            });
        },
        error: (xhr) => {
            console.error("Failed to load usage list:", xhr.responseText);
            Swal.fire({
                icon: "error",
                title: "Failed to Load Usage List",
                text: "Please try again or check your connection.",
            });
        },
    });
}
export function one() {
    document.addEventListener("DOMContentLoaded", function () {
        // --- 2. Get usage list ---
        fetchUsageList();
        fetchCountryList();
    });
}
one();

export function three() {
    let quill;

    document.addEventListener("DOMContentLoaded", function () {
        quill = new Quill("#permit-condition-editor", {
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ["bold", "italic", "underline", "strike"],
                    [{ list: "ordered" }, { list: "bullet" }],
                    ["link", "blockquote", "code-block"],
                    [{ align: [] }],
                    ["clean"],
                ],
            },
            placeholder: "Write permit conditions here...",
            theme: "snow",
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        document
            .getElementById("submitConditionBtn")
            .addEventListener("click", function () {
                // Make sure quill syncs to hidden input
                const conditionHtml = quill.root.innerHTML;
                document.getElementById("permit-condition-input").value =
                    conditionHtml;

                const formData = new FormData();
                formData.append(
                    "itemName",
                    document.getElementById("itemName").value
                );
                formData.append(
                    "itemCategory",
                    document.getElementById("itemCategory").value
                );
                formData.append(
                    "quanLimit",
                    document.getElementById("quanLimit").value
                );
                formData.append(
                    "quanmunit",
                    document.getElementById("quanmunit").value
                );
                formData.append(
                    "spedate",
                    document.getElementById("spedate").value
                );

                // Tagify values → JSON strings
                formData.append(
                    "countryTag",
                    JSON.stringify(countryTagify ? countryTagify.value : [])
                );
                formData.append(
                    "usageTags",
                    JSON.stringify(usageTagify ? usageTagify.value : [])
                );

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
                    permit_condition: conditionHtml,
                });

                $.ajax({
                    url: `/internal/save_condition`,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (res) {
                        Swal.fire(
                            "Success",
                            "Permit condition saved successfully!",
                            "success"
                        ).then(() => {
                            window.location.href = `${window.baseUrl}/internal/permit_condition`;
                        });
                    },
                    error: function (xhr) {
                        console.error("Save Error:", xhr.responseText);
                    },
                });
            });
    });
}
three();
