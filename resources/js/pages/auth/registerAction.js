import Dropzone from "dropzone";
import $ from "jquery";
import Swal from "sweetalert2";
import "dropzone/dist/dropzone.css";
import select2 from "select2";
select2(window.jQuery);

import "select2/dist/css/select2.min.css";


Dropzone.autoDiscover = false;

let type = null;



// Function to load states
async function loadStates() {
    try {
        const response = await fetch('/api/states');
        const states = await response.json();
        const stateSelect = $('#state');
        stateSelect.empty();
        stateSelect.append('<option value="">Select State</option>');
        states.forEach(state => {
            stateSelect.append(`<option value="${state.id}">${state.name}</option>`);
        });
    } catch (error) {
        console.error('Error loading states:', error);
    }
}

// Function to load districts for a state
async function loadDistricts(stateId) {
    try {
        // Show loading Swal
        Swal.fire({
            title: 'Loading districts...',
            text: 'Please wait',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await fetch(`/api/districts/${stateId}`);
        const districts = await response.json();

        const districtSelect = $('#district');

        // Destroy Select2 if already initialized
        if (districtSelect.hasClass('select2-hidden-accessible')) {
            districtSelect.select2('destroy');
        }

        // Reset dropdown
        districtSelect.empty();
        districtSelect.append('<option value="">Select District</option>');

        districts.forEach(district => {
            districtSelect.append(
                `<option value="${district.name}">${district.name}</option>`
            );
        });

        // Re-init Select2
        districtSelect.select2({
            placeholder: 'Select district',
            width: '100%',
            dropdownAutoWidth: true
        });

        // Close Swal after everything is ready
        Swal.close();

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Failed to load districts',
            text: 'Please try again'
        });

        console.error('Error loading districts:', error);
    }
}



function fileUpload() {
    const fileDropArea = document.getElementById("fileDropArea");
    const fileInput = document.getElementById("fileInput");
    const fileNameDisplay = document.getElementById("fileName");

    // Create an <img> element for preview
    let imgPreview = document.createElement("img");
    imgPreview.style.maxWidth = "100%";
    imgPreview.style.maxHeight = "150px";
    imgPreview.style.marginTop = "10px";
    imgPreview.style.marginInline = "auto";
    fileDropArea.appendChild(imgPreview);

    // Click to open file dialog
    fileDropArea.addEventListener("click", () => {
        fileInput.click();
    });

    // Handle file selection (dialog)
    fileInput.addEventListener("change", () => {
        if (fileInput.files.length) {
            const file = fileInput.files[0];
            fileNameDisplay.textContent = file.name;
            showPreview(file);
        }
    });

    // Drag over effect
    fileDropArea.addEventListener("dragover", (e) => {
        e.preventDefault();
        fileDropArea.classList.add("border-primary", "bg-light");
    });

    // Remove dragover effect
    fileDropArea.addEventListener("dragleave", (e) => {
        e.preventDefault();
        fileDropArea.classList.remove("border-primary", "bg-light");
    });

    // Handle drop
    fileDropArea.addEventListener("drop", (e) => {
        e.preventDefault();
        fileDropArea.classList.remove("border-primary", "bg-light");

        const files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files; // set files to input
            fileNameDisplay.textContent = files[0].name;
            showPreview(files[0]);
        }
    });

    // Function to show image preview
    function showPreview(file) {
        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imgPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            imgPreview.src = ""; // remove preview for non-images
        }
    }
}

$(document).ready(function () {
    // Load states on page load
    loadStates();

    // ----------------------------
    // 1️⃣ Type selection styling
    // ----------------------------
    function type_styling() {
        $(".type-div .icon-box").removeClass("bg-primary");
        $(".type-div .icon-box .icon").removeClass("text-white");
        $(".type-element").removeClass("border-primary");

        const checkedRadio = $('input[name="type"]:checked');
        if (checkedRadio.length > 0) {
            const parentCard = checkedRadio.closest(".type-element");
            const targetDiv = checkedRadio.closest("label").find(".type-div");
            const targetDivIconBox = targetDiv.find(".icon-box");
            const targetDivIcon = targetDivIconBox.find(".icon");

            type = checkedRadio.data("type");

            targetDivIconBox.addClass("bg-primary");
            targetDivIcon.addClass("text-white");
            parentCard.addClass("border-primary");
        }
    }

    function inputEdit() {
        $(".fullnameLabel").text(
            type === "individual" ? "Name" : "Company name"
        );
        $("#phoneLabel").text(
            type === "individual" ? "Phone Number" : "Company Phone Number"
        );
        $(".icLabel").text(
            type === "individual"
                ? "Identification Number"
                : "Company Registration Number"
        );

        $("#fullname").attr(
            "placeholder",
            type === "individual" ? "John Doe" : " ABC Sdn. Bhd."
        );
        $("#phone_number").attr("placeholder", "123430072");
        $("#no_ic").attr(
            "placeholder",
            type === "individual" ? " 000000120000" : " 000000-X"
        );
    }

    // Initialize on page load
    type_styling();
    inputEdit();

    $(document).on("change", 'input[name="type"]', function () {
        type_styling();
        inputEdit();
    });

    // State and District dropdown logic
    $(document).on("change", '#state', function () {
        const selectedStateId = $(this).val();
        const districtSelect = $('#district');
        
        console.log('State selected with ID:', selectedStateId);
        
        if (selectedStateId) {
            loadDistricts(selectedStateId);
            districtSelect.prop('disabled', false);
        } else {
            districtSelect.empty().append('<option value="">Select District</option>');
            districtSelect.prop('disabled', true);
        }
    });

    // ----------------------------
    // 2️⃣ Tab navigation buttons
    // ----------------------------
    function switchTo(tabButtonId) {
        const tabTrigger = document.querySelector(tabButtonId);
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }

    $("#nextToPersonalTab").on("click", function (e) {
        e.preventDefault();
        if (!type) {
            Swal.fire({
                icon: "error",
                title: "No type selected",
                text: "Please choose an account type first!",
            });
            return;
        }
        switchTo("#confirmed-tab");
    });

    $("#backToAccountTab").on("click", (e) => {
        e.preventDefault();
        switchTo("#order-tab");
    });
    $("#nextToSummaryTab").on("click", (e) => {
        e.preventDefault();
        switchTo("#shipped-tab");
    });
    $("#backToDetailsTab").on("click", (e) => {
        e.preventDefault();
        switchTo("#confirmed-tab");
    });

    $('button[data-bs-toggle="tab"]').on("show.bs.tab", function (e) {
        // target pane
        const target = $(e.target).data("bs-target");

        // If trying to go to tab 2 or 3 before type is selected
        if (
            (target === "#confirm-tab-pane" ||
                target === "#shipped-tab-pane") &&
            !type
        ) {
            e.preventDefault(); // block tab switching

            Swal.fire({
                icon: "error",
                title: "Choose a type first",
                text: "You must select Individual or Company",
            });
        }
    });

    $('#phone_country').select2({
        placeholder: 'Select country',
        width: '100%',
        dropdownAutoWidth: true
    });
    $('#state').select2({
        placeholder: 'Select state',
        width: '100%',
        dropdownAutoWidth: true
    });
    

    // ----------------------------
    // 3️⃣ Dropzone initialization
    // ----------------------------
    fileUpload();

    // ----------------------------
    // 4️⃣ Finish registration: upload Dropzone
    // ----------------------------
    $("#finishRegistrationBtn").on("click", function (e) {
        e.preventDefault();

        const form = $("#registerForm")[0];
        const formData = new FormData(form);

        let account_type = type === "individual" ? "individu" : type;
        formData.append("account_type", account_type);

        // Append file manually
        const fileInput = document.getElementById("fileInput");
        if (fileInput && fileInput.files.length > 0) {
            formData.append("attachment", fileInput.files[0]);
        }

        $.ajax({
            url: $(form).attr("action"),
            type: $(form).attr("method") || "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#finishRegistrationBtn")
                    .prop("disabled", true)
                    .text("Submitting...");

                Swal.fire({
                    title: "Registering...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });
            },
            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Registration Successful",
                    text: response.message || "Your form has been submitted!",
                }).then(() => {
                    window.location.href = response.redirect;
                });
            },
            error: function (xhr, status, error) {
                Swal.close();

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;

                    for (const key in errors) {
                        const $input = $(form).find(`[name="${key}"]`);

                        if ($input.length) {
                            $input.addClass("is-invalid");

                            // Remove old error first
                            $input.next(".invalid-feedback").remove();

                            $input.after(
                                `<div class="invalid-feedback">${errors[key][0]}</div>`
                            );
                        }
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Validation Failed",
                        text: "Please check your input fields.",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            xhr.responseJSON?.message ||
                            "Something went wrong. Please try again.",
                    });
                }

                $("#finishRegistrationBtn")
                    .prop("disabled", false)
                    .text("Submit");
            },
        });
    });
});
