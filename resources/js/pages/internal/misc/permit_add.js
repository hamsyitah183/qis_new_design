import $ from "jquery";
import Swal from "sweetalert2";
// Import Select2 module
import select2 from "select2";
import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';


// Make Tagify available globally so your blade script can use it
window.Tagify = Tagify;
window.Quill = Quill;

// Force Select2 to attach to THIS jQuery:
select2($);

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
    // --- 2. Get usage list ---
    fetchUsageList();
    fetchCountryList();
}
one();

export function three() {
    let quill = new Quill("#permit-condition-editor", {
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

    const submitBtn = document.getElementById("submitConditionBtn");
    if (submitBtn) {
        submitBtn.addEventListener("click", function (e) {
            e.preventDefault();

            // Make sure quill syncs to hidden input
            const conditionHtml = quill.root.innerHTML;
            document.getElementById("permit-condition-input").value = conditionHtml;

            const formData = new FormData();
            formData.append("itemName", document.getElementById("itemName").value);
            formData.append("scientificName", document.getElementById("scientificName").value);
            formData.append("itemCategory", document.getElementById("itemCategory").value);
            formData.append("quanLimit", document.getElementById("quanLimit").value);
            formData.append("quanmunit", document.getElementById("quanmunit").value);
            formData.append("start_date", document.getElementById("start_date").value);
            formData.append("end_date", document.getElementById("end_date").value);

            // Select2 values -> JSON strings containing array of objects with "value" key
            const selectedCountries = $("#countrySelect").val() || [];
            const countryTagData = selectedCountries.map((val) => ({ value: val }));
            formData.append("countryTag", JSON.stringify(countryTagData));

            const selectedUsages = $("#usageSelect").val() || [];
            const usageTagData = selectedUsages.map((val) => ({ value: val }));
            formData.append("usageTags", JSON.stringify(usageTagData));

            // Quill HTML
            formData.append("permit_condition", conditionHtml);

            // Ask user which action to take (same UI as edit screen)
            Swal.fire({
                title: "Submit Permit Condition",
                text: "Choose an action:",
                icon: "question",
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: "Save & Share with Public",
                denyButtonText: "Save Only",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isDismissed) {
                    return; // User clicked Cancel or closed the dialog
                }

                // Show loading while saving
                Swal.fire({
                    title: "Saving...",
                    text: "Please wait while the condition is being saved.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: `/internal/save_condition`,
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (res) {
                        Swal.close(); // close loading

                        Swal.fire({
                            icon: "success",
                            title: "Saved!",
                            text:
                                res.message ||
                                "Permit condition saved successfully.",
                            timer: 2000,
                            showConfirmButton: false,
                        }).then(() => {
                            // If user chose "Save & Share with Public"
                            const newConditionId =
                                res.condition_id || res.data?.id;

                            if (result.isConfirmed && newConditionId) {
                                $.ajax({
                                    url: "/internal/news/",
                                    method: "POST",
                                    dataType: "json",
                                    data: {
                                        _token: $(
                                            'meta[name="csrf-token"]'
                                        ).attr("content"),
                                        condition_id: newConditionId,
                                        type: "Import Permit",
                                        action: "released",
                                    },
                                    success: function () {
                                        Swal.fire(
                                            "Sent!",
                                            "The permit condition has been shared to all users.",
                                            "success"
                                        ).then(() => {
                                            window.location.href = `${window.baseUrl}/internal/permit_condition`;
                                        });
                                    },
                                    error: function (xhr) {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Error",
                                            text:
                                                xhr.responseJSON?.message ||
                                                "Something went wrong while sharing.",
                                        }).then(() => {
                                            window.location.href = `${window.baseUrl}/internal/permit_condition`;
                                        });
                                    },
                                });
                            } else if (result.isConfirmed && !newConditionId) {
                                Swal.fire({
                                    icon: "warning",
                                    title: "Saved, but not shared",
                                    text: "The condition was saved but its ID couldn't be read, so the email/share step did not run.",
                                }).then(() => {
                                    window.location.href = `${window.baseUrl}/internal/permit_condition`;
                                });
                            } else {
                                // Just redirect back to list
                                window.location.href = `${window.baseUrl}/internal/permit_condition`;
                            }
                        });
                    },
                    error: function (xhr) {
                        Swal.close(); // close loading
                        console.error("Save Error:", xhr.responseText);
                        let errMsg = "Failed to save permit condition.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        Swal.fire("Error", errMsg, "error");
                    },
                });
            });
        });
    }
}

// Check if document is already loaded, otherwise wait for it
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', three);
} else {
    three();
}
