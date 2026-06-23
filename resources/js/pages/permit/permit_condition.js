// resources/js/pages/permit/permit_condition.js
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

        // Make sure quill syncs to hidden input
        const conditionHtml = quill.root.innerHTML;
        document.getElementById("permit-condition-input").value = conditionHtml;

        const formData = new FormData();
        formData.append("itemName", document.getElementById("itemName").value);
        formData.append("itemCategory", document.getElementById("itemCategory").value);
        formData.append("quanLimit", document.getElementById("quanLimit").value);
        formData.append("quanmunit", document.getElementById("quanmunit").value);
        formData.append('measurement', document.getElementById("quanmunit"))
        formData.append('id', document.getElementById('id').value);
        formData.append('start_date', document.getElementById('start_date').value);
        formData.append('end_date', document.getElementById('end_date').value);
        formData.append('type', 'Import Permit');

        // Tagify values → JSON strings
        formData.append("countryTag", JSON.stringify(countryTagify ? countryTagify.value : []));
        formData.append("usageTags", JSON.stringify(usageTagify ? usageTagify.value : []));

        // Quill HTML
        formData.append("permit_condition", conditionHtml);

        // Ask user which action to take
        Swal.fire({
            title: 'Submit Permit Condition',
            text: "Choose an action:",
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Save & Share with Public',
            denyButtonText: 'Save Only',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isDismissed) {
                // User clicked Cancel
                return;
            }

            // Show loading while saving
            Swal.fire({
                title: 'Saving...',
                text: 'Please wait while the condition is being saved.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            // Save via AJAX
            $.ajax({
                url: `/internal/save_condition`,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function (res) {
                    Swal.close(); // close loading

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: res.message || 'Permit condition saved successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // If user chose "Save & Share"
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/internal/news/',
                                method: "POST",
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr("content"),
                                    condition_id: document.getElementById('id').value,
                                    type: 'Import Permit',
                                    action: 'updated'
                                },
                                success: function (response) {
                                    Swal.fire('Sent!', 'The permit condition has been shared to all users.', 'success');
                                },
                                error: function (xhr) {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: xhr.responseJSON?.message || "Something went wrong.",
                                    });
                                },
                            });
                        }
                    });
                },
                error: function (xhr) {
                    Swal.close(); // close loading
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Failed to save permit condition.',
                    });
                }
            });

        });

    });

    const deleteBtn = document.getElementById('deleteConditionBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            const conditionId = document.getElementById('id').value;
            Swal.fire({
                title: 'Delete this permit condition?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/internal/permit_condition/${conditionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'The permit condition has been deleted.',
                                    timer: 1500,
                                    showConfirmButton: false,
                                }).then(() => {
                                    window.location.href = '/internal/permit_condition';
                                });
                            } else {
                                Swal.fire('Error', data.message ?? 'Failed to delete.', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                        });
                }
            });
        });
    }

});

