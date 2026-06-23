import $ from "jquery";
import Swal from "sweetalert2";

import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';


// Make Tagify available globally so your blade script can use it
window.Tagify = Tagify;
window.Quill = Quill;

document.addEventListener("DOMContentLoaded", function () {

    document.getElementById("submitConditionBtn").addEventListener("click", function () {

        // Sync Quill content
        const conditionHtml = quill.root.innerHTML;
        document.getElementById("permit-condition-input").value = conditionHtml;

        const formData = new FormData();
        formData.append("item_name", document.getElementById("itemName").value);
        formData.append("scientific_name", document.getElementById("scientificName").value);
        formData.append("category", document.getElementById("itemCategory").value);
        formData.append("quantity_limit", document.getElementById("quanLimit").value);
        formData.append("quanmunit", document.getElementById("quanmunit").value);
        formData.append("id", document.getElementById("id").value);
        formData.append("start_date", document.getElementById("start_date").value);
        formData.append("end_date", document.getElementById("end_date").value);

        formData.append("country", JSON.stringify(countryTagify ? countryTagify.value : []));
        formData.append("usage", JSON.stringify(usageTagify ? usageTagify.value : []));
        formData.append("addional_condition", conditionHtml);

        // ✅ Ask user first (3 buttons)
        Swal.fire({
            title: "Submit Consignment Condition",
            text: "Choose an action:",
            icon: "question",
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: "Save & Share with Public",
            denyButtonText: "Save Only",
            cancelButtonText: "Cancel"
        }).then((result) => {

            if (result.isDismissed) {
                return; // Cancel clicked
            }

            // Show loading
            Swal.fire({
                title: "Saving...",
                text: "Please wait while the condition is being saved.",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            // ✅ Save AJAX
            $.ajax({
                url: "/internal/consignment_condition/save",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function (res) {

                    // If Save & Share
                    if (result.isConfirmed) {

                            $.ajax({
                                url: "/internal/news/",
                                method: "POST",
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr("content"),
                                    condition_id: res.id || document.getElementById("id").value,
                                    type: "Consignment",
                                    action: document.getElementById("id").value ? "edit" : "add"
                                },
                            success: function () {
                                Swal.fire({
                                    icon: "success",
                                    title: "Saved & Shared!",
                                    text: "The consignment condition has been shared to all users."
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: "error",
                                    title: "Saved but Share Failed",
                                    text: xhr.responseJSON?.message || "Condition saved but sharing failed."
                                });
                            }
                        });

                    } else {
                        // Save Only
                        Swal.fire({
                            icon: "success",
                            title: "Saved!",
                            text: res.message || "Consignment condition saved successfully."
                        }).then(() => {
                            window.location.href = "/internal/consignment_condition";
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: xhr.responseJSON?.message || "Failed to save consignment condition."
                    });
                }
            });

        });

    });

});

