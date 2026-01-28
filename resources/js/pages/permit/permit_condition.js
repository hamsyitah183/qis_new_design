import $ from "jquery";
import Swal from "sweetalert2";


document.addEventListener("DOMContentLoaded", function() {

    document.getElementById("submitConditionBtn").addEventListener("click", function() {

        // Make sure quill syncs to hidden input
        const conditionHtml = quill.root.innerHTML;
        document.getElementById("permit-condition-input").value = conditionHtml;

        const formData = new FormData();
        formData.append("itemName", document.getElementById("itemName").value);
        formData.append("itemCategory", document.getElementById("itemCategory").value);
        formData.append("quanLimit", document.getElementById("quanLimit").value);
        formData.append("quanmunit", document.getElementById("quanmunit").value);
        formData.append("spedate", document.getElementById("spedate").value);
        formData.append('id', document.getElementById('id').value);

        // Tagify values → JSON strings
        formData.append("countryTag", JSON.stringify(countryTagify ? countryTagify.value : []));
        formData.append("usageTags", JSON.stringify(usageTagify ? usageTagify.value : []));

        // Quill HTML
        formData.append("permit_condition", conditionHtml);

        // ✅ Show Swal loading
        Swal.fire({
            title: 'Saving...',
            text: 'Please wait while the condition is being saved.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `/internal/save_condition`,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            success: function(res) {
                Swal.close(); // close loading

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: res.message || 'Permit condition saved successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });

                // Optionally reset form or close modal
                // document.getElementById("yourFormId").reset();
            },
            error: function(xhr) {
                Swal.close(); // close loading

                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to save permit condition.',
                });
            }
        });
    });

});
