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
        const response = await fetch('/get_states');
        const states = await response.json();
        const stateSelect = $('#state');
        stateSelect.empty();
        stateSelect.append('<option value="">Select State</option>');
        states.forEach(state => {
            stateSelect.append(`<option value="${state.name}" data-id="${state.id}">${state.name}</option>`);
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

        const response = await fetch(`/get_districts/${stateId}`);
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
                `<option value="${district.name}" data-id="${district.id}">${district.name}</option>`
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

// Function to load postcodes for a district
async function loadPostcodes(districtId) {
    Swal.fire({
        title: "Load Postcode...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });
    try {
        const response = await fetch(`/get_postcodes/${districtId}`);
        const postcodes = await response.json();

        const postcodeSelect = $('#postcode');

        // Destroy Select2 if already initialized
        if (postcodeSelect.hasClass('select2-hidden-accessible')) {
            postcodeSelect.select2('destroy');
        }

        postcodeSelect.empty();
        postcodeSelect.append('<option value="">Select Postcode</option>');

        postcodes.forEach(postcode => {
            postcodeSelect.append(
                `<option value="${postcode.value}">${postcode.value}</option>`
            );
        });

        // Re-init Select2
        postcodeSelect.select2({
            placeholder: 'Select postcode',
            width: '100%',
            dropdownAutoWidth: true
        });
        Swal.close();

    } catch (error) {
        console.error('Error loading postcodes:', error);
        Swal.fire("Error", "Unable to load postcode", "error");
    }
}



function fileUpload() {
    const fileDropArea = document.getElementById("fileDropArea");
    const fileInput = document.getElementById("fileInput");
    const listContainer = document.getElementById("fileListContainer");
    const emptyState = document.getElementById("fileListEmpty");
    const fileLabelModalEl = document.getElementById("fileLabelModal");
    const fileLabelModalTitle = document.getElementById("fileLabelModalLabel");
    const fileLabelInput = document.getElementById("fileLabelInput");
    const fileLabelName = document.getElementById("fileLabelName");
    const fileLabelPreview = document.getElementById("fileLabelPreview");
    const filePreviewIcon = document.getElementById("filePreviewIcon");
    const saveFileLabelBtn = document.getElementById("saveFileLabelBtn");

    const fileLabelModal = new bootstrap.Modal(fileLabelModalEl);
    let selectedFiles = [];
    let pendingFiles = [];
    let currentEditIndex = null;
    let currentQueueItem = null;

    const MAX_FILES = 10;
    const MAX_SIZE_MB = 10;

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function iconFor(name) {
        var ext = name.split('.').pop().toLowerCase();
        if (ext === 'pdf') return 'ti ti-file-type-pdf';
        return 'ti ti-photo';
    }

    function isImage(file) {
        return file.type.startsWith('image/');
    }

    function syncInput() {
        var dt = new DataTransfer();
        selectedFiles.forEach(function (item) {
            dt.items.add(item.file);
        });
        fileInput.files = dt.files;
    }

    function render() {
        listContainer.innerHTML = '';
        emptyState.style.display = selectedFiles.length ? 'none' : 'block';

        selectedFiles.forEach(function (item, index) {
            var li = document.createElement('li');
            li.className = 'file-list-item d-flex align-items-center justify-content-between gap-2';
            li.innerHTML =
                '<div class="d-flex align-items-center gap-3 me-2">' +
                '<i class="' + iconFor(item.file.name) + '"></i>' +
                '<div class="file-meta">' +
                '<div class="file-name fw-semibold file-label-clickable" style="cursor:pointer;">' + item.label + '</div>' +
                '<div class="file-name text-truncate" style="max-width: 220px;">' + item.file.name + '</div>' +
                '<div class="file-size text-muted">' + formatSize(item.file.size) + '</div>' +
                '</div>' +
                '</div>' +
                '<div class="d-flex gap-2 align-items-center">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary file-preview" title="View document">' +
                '<i class="ti ti-eye"></i>' +
                '</button>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary file-edit" title="Edit label">' +
                '<i class="ti ti-pencil"></i>' +
                '</button>' +
                '<button type="button" class="file-remove btn btn-sm btn-outline-danger" title="Remove">&times;</button>' +
                '</div>';

            li.querySelector('.file-remove').addEventListener('click', function () {
                if (item.previewUrl) {
                    URL.revokeObjectURL(item.previewUrl);
                }
                selectedFiles.splice(index, 1);
                syncInput();
                render();
            });

            li.querySelector('.file-preview').addEventListener('click', function () {
                currentEditIndex = index;
                openModalForExistingFile(selectedFiles[index]);
            });

            li.querySelector('.file-edit').addEventListener('click', function () {
                currentEditIndex = index;
                openModalForExistingFile(selectedFiles[index]);
            });

            li.querySelector('.file-label-clickable').addEventListener('click', function () {
                currentEditIndex = index;
                openModalForExistingFile(selectedFiles[index]);
            });

            listContainer.appendChild(li);
        });
    }

    function showFileModal(file, label, previewUrl) {
        var isEdit = !!label;
        fileLabelModalTitle.textContent = isEdit ? 'Edit uploaded file label' : 'Label your file';
        fileLabelName.textContent = file.name;
        fileLabelInput.value = label || '';

        if (isImage(file)) {
            fileLabelPreview.src = previewUrl || URL.createObjectURL(file);
            fileLabelPreview.style.display = 'block';
            filePreviewIcon.style.display = 'none';
        } else {
            fileLabelPreview.src = '';
            fileLabelPreview.style.display = 'none';
            filePreviewIcon.style.display = 'block';
        }

        fileLabelModal.show();
        setTimeout(function () {
            fileLabelInput.focus();
        }, 200);
    }

    function openModalForExistingFile(item) {
        currentQueueItem = { file: item.file, label: item.label, previewUrl: item.previewUrl };
        showFileModal(item.file, item.label, item.previewUrl);
    }

    function processNextPending() {
        if (!pendingFiles.length) {
            return;
        }
        currentQueueItem = pendingFiles.shift();
        showFileModal(currentQueueItem.file, currentQueueItem.label, currentQueueItem.previewUrl);
    }

    function addQueuedFiles(fileList) {
        var incoming = Array.prototype.slice.call(fileList);

        incoming.forEach(function (file) {
            if (selectedFiles.length + pendingFiles.length >= MAX_FILES) return;

            if (file.size > MAX_SIZE_MB * 1024 * 1024) {
                alert(file.name + ' exceeds the ' + MAX_SIZE_MB + 'MB limit.');
                return;
            }

            var isDuplicate = selectedFiles.some(function (item) {
                return item.file.name === file.name && item.file.size === file.size;
            }) || pendingFiles.some(function (item) {
                return item.file.name === file.name && item.file.size === file.size;
            });
            if (isDuplicate) return;

            pendingFiles.push({ file: file, label: '', previewUrl: isImage(file) ? URL.createObjectURL(file) : null });
        });

        if (!fileLabelModalEl.classList.contains('show')) {
            processNextPending();
        }
    }

    fileDropArea.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function (e) {
        if (e.target.files.length) {
            addQueuedFiles(e.target.files);
            fileInput.value = '';
        }
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        fileDropArea.addEventListener(evt, function (e) {
            e.preventDefault();
            e.stopPropagation();
            fileDropArea.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
        fileDropArea.addEventListener(evt, function (e) {
            e.preventDefault();
            e.stopPropagation();
            fileDropArea.classList.remove('is-dragover');
        });
    });

    fileDropArea.addEventListener('drop', function (e) {
        if (e.dataTransfer && e.dataTransfer.files) {
            addQueuedFiles(e.dataTransfer.files);
        }
    });

    saveFileLabelBtn.addEventListener('click', function () {
        if (!currentQueueItem || !fileLabelModalEl) return;

        var label = fileLabelInput.value.trim();
        if (!label) {
            label = currentQueueItem.file.name;
        }

        if (currentEditIndex !== null) {
            selectedFiles[currentEditIndex].label = label;
            if (currentQueueItem.previewUrl) {
                selectedFiles[currentEditIndex].previewUrl = currentQueueItem.previewUrl;
            }
            currentEditIndex = null;
        } else {
            selectedFiles.push({
                file: currentQueueItem.file,
                label: label,
                previewUrl: currentQueueItem.previewUrl,
            });
        }

        syncInput();
        render();
        fileLabelModal.hide();
        currentQueueItem = null;

        if (pendingFiles.length) {
            processNextPending();
        }
    });

    fileLabelModalEl.addEventListener('hidden.bs.modal', function () {
        if (currentQueueItem && currentEditIndex === null && !selectedFiles.some(function (item) {
            return item.file.name === currentQueueItem.file.name && item.file.size === currentQueueItem.file.size;
        })) {
            if (currentQueueItem.previewUrl) {
                URL.revokeObjectURL(currentQueueItem.previewUrl);
            }
        }
        currentEditIndex = null;
    });

    render();
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
        $(".fullnameLabel").html(
            type === "individual" ? `Name  <span class="text-primary2">*</span>` : `Company name  <span class="text-primary2">*</span> `
        );
        $("#phoneLabel").html(
            type === "individual" ? `Phone Number  <span class="text-primary2">*</span>` : `Company Phone Number  <span class="text-primary2">*</span>`
        );
        $(".icLabel").html(
            type === "individual"
                ? `Identification Number   <span class="text-primary2">*</span>`
                : `Company Registration Number  <span class="text-primary2">*</span>`
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

    function toggleAdditionalFields() {
        var companyFields = $('.company-details');
        var individualFields = $('.individual-details');

        companyFields.addClass('d-none');
        individualFields.addClass('d-none');

        if (type === 'company') {
            companyFields.removeClass('d-none');
        } else if (type === 'individual') {
            individualFields.removeClass('d-none');
        }
    }

    $(document).on("change", 'input[name="type"]', function () {
        type_styling();
        inputEdit();
        toggleAdditionalFields();
    });

    // initialize additional field visibility on page load
    toggleAdditionalFields();

    // State and District dropdown logic
    $(document).on("change", '#state', function () {
        const selectedOption = $(this).find(':selected');
        const selectedStateId = selectedOption.data('id');
        const districtSelect = $('#district');
        const postcodeSelect = $('#postcode');

        console.log('State selected with ID:', selectedStateId);

        if (selectedStateId) {
            loadDistricts(selectedStateId);
            districtSelect.prop('disabled', false);
            postcodeSelect.empty().append('<option value="">Select Postcode</option>');
        } else {
            districtSelect.empty().append('<option value="">Select District</option>');
            districtSelect.prop('disabled', true);
            postcodeSelect.empty().append('<option value="">Select Postcode</option>');
        }
    });

    $(document).on("change", '#district', function () {
        const selectedOption = $(this).find(':selected');
        const selectedDistrictId = selectedOption.data('id');
        console.log('District selected with ID:', selectedDistrictId);

        if (selectedDistrictId) {
            loadPostcodes(selectedDistrictId);
        } else {
            $('#postcode').empty().append('<option value="">Select Postcode</option>');
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
    $('#district').select2({
        placeholder: 'Select district',
        width: '100%',
        dropdownAutoWidth: true
    });
    $('#postcode').select2({
        placeholder: 'Select postcode',
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

        // Append chosen files manually
        const fileInput = document.getElementById("fileInput");
        if (fileInput && fileInput.files.length > 0) {
            for (let i = 0; i < fileInput.files.length; i++) {
                formData.append("attachment[]", fileInput.files[i]);
            }
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
