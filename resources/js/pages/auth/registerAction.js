import Dropzone from "dropzone";
import $ from "jquery";
import Swal from "sweetalert2";
import "dropzone/dist/dropzone.css";
import select2 from "select2";
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

Dropzone.autoDiscover = false;

let type = null;

// ---- Shared file-preview modal state (initialized ONCE) ----
let sharedModal = null;
let sharedModalEl = null;
let sharedPreviewImg = null;
let sharedPreviewIcon = null;
let sharedFileNameDisplay = null;
let sharedPdfViewer = null;
let sharedOpenInNewTabBtn = null;
let sharedModalInitialized = false;

function initSharedPreviewModal() {
    if (sharedModalInitialized) return;
    sharedModalInitialized = true;

    sharedModalEl = document.getElementById("fileLabelModal");
    if (!sharedModalEl || typeof bootstrap === "undefined") {
        console.warn(
            "fileUpload: #fileLabelModal (or bootstrap) not found — file previews are disabled, but upload/remove still work.",
        );
        return;
    }

    sharedModal = bootstrap.Modal.getOrCreateInstance(sharedModalEl);

    sharedPreviewImg = document.getElementById("fileLabelPreview");
    sharedPreviewIcon = document.getElementById("filePreviewIcon");
    sharedFileNameDisplay = document.getElementById("fileLabelName");
    const modalTitle = document.getElementById("fileLabelModalLabel");
    const labelInput = document.getElementById("fileLabelInput");
    const saveBtn = document.getElementById("saveFileLabelBtn");
    const cancelBtn = sharedModalEl.querySelector(
        '.modal-footer [data-bs-dismiss="modal"]',
    );

    // Hide the label input and save button for preview-only mode
    if (labelInput) {
        labelInput.closest(".col-12")?.querySelector(".form-label")?.remove();
        labelInput.style.display = "none";
    }
    if (saveBtn) saveBtn.style.display = "none";
    if (modalTitle) modalTitle.textContent = "File Preview";

    // PDF viewer + "open in new tab" button
    if (sharedPreviewImg) {
        sharedPdfViewer = document.createElement("embed");
        sharedPdfViewer.id = "fileLabelPdfViewer";
        sharedPdfViewer.type = "application/pdf";
        sharedPdfViewer.style.width = "100%";
        sharedPdfViewer.style.height = "500px";
        sharedPdfViewer.style.display = "none";
        sharedPdfViewer.style.border =
            "1px solid var(--bs-border-color, #dee2e6)";
        sharedPdfViewer.style.borderRadius = "6px";
        sharedPreviewImg.insertAdjacentElement("afterend", sharedPdfViewer);
    }

    if (saveBtn) {
        sharedOpenInNewTabBtn = document.createElement("a");
        sharedOpenInNewTabBtn.id = "fileLabelOpenBtn";
        sharedOpenInNewTabBtn.className = "btn btn-outline-primary";
        sharedOpenInNewTabBtn.target = "_blank";
        sharedOpenInNewTabBtn.rel = "noopener noreferrer";
        sharedOpenInNewTabBtn.innerHTML =
            '<i class="ti ti-external-link me-1"></i> Open in New Tab';
        saveBtn.insertAdjacentElement("beforebegin", sharedOpenInNewTabBtn);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", function () {
            bootstrap.Modal.getOrCreateInstance(sharedModalEl).hide();
        });
    }

    sharedModalEl.addEventListener("hidden.bs.modal", function () {
        if (sharedPdfViewer) {
            sharedPdfViewer.src = "";
            sharedPdfViewer.style.display = "none";
        }
        if (sharedPreviewImg) {
            sharedPreviewImg.style.display = "none";
            sharedPreviewImg.removeAttribute("src");
        }
        if (sharedPreviewIcon) {
            sharedPreviewIcon.style.display = "none";
        }
    });
}

// ---- State / District / Postcode loading (unchanged) ----
async function loadStates() {
    try {
        const response = await fetch("/get_states");
        const states = await response.json();
        const stateSelect = $("#state");
        stateSelect.empty();
        stateSelect.append('<option value="">Select State</option>');
        states.forEach((state) => {
            stateSelect.append(
                `<option value="${state.name}" data-id="${state.id}">${state.name}</option>`,
            );
        });
    } catch (error) {
        console.error("Error loading states:", error);
    }
}

async function loadDistricts(stateId) {
    try {
        Swal.fire({
            title: "Loading districts...",
            text: "Please wait",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        const response = await fetch(`/get_districts/${stateId}`);
        const districts = await response.json();

        const districtSelect = $("#district");

        if (districtSelect.hasClass("select2-hidden-accessible")) {
            districtSelect.select2("destroy");
        }

        districtSelect.empty();
        districtSelect.append('<option value="">Select District</option>');

        districts.forEach((district) => {
            districtSelect.append(
                `<option value="${district.name}" data-id="${district.id}">${district.name}</option>`,
            );
        });

        districtSelect.select2({
            placeholder: "Select district",
            width: "100%",
            dropdownAutoWidth: true,
        });

        Swal.close();
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Failed to load districts",
            text: "Please try again",
        });
        console.error("Error loading districts:", error);
    }
}

async function loadPostcodes(districtId) {
    Swal.fire({
        title: "Load Postcode...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });
    try {
        const response = await fetch(`/get_postcodes/${districtId}`);
        const postcodes = await response.json();

        const postcodeSelect = $("#postcode");

        if (postcodeSelect.hasClass("select2-hidden-accessible")) {
            postcodeSelect.select2("destroy");
        }

        postcodeSelect.empty();
        postcodeSelect.append('<option value="">Select Postcode</option>');

        postcodes.forEach((postcode) => {
            postcodeSelect.append(
                `<option value="${postcode.value}">${postcode.value}</option>`,
            );
        });

        postcodeSelect.select2({
            placeholder: "Select postcode",
            width: "100%",
            dropdownAutoWidth: true,
        });
        Swal.close();
    } catch (error) {
        console.error("Error loading postcodes:", error);
        Swal.fire("Error", "Unable to load postcode", "error");
    }
}

// ---- Document file upload (per document ID) with preview ----
export function fileUpload(docId) {
    console.log(`🔧 Initializing fileUpload for doc ${docId}`);

    const dropArea = document.querySelector(
        `.file-drop-area[data-doc-id="${docId}"]`,
    );
    const fileInput = dropArea ? dropArea.querySelector(".file-input") : null;
    const listContainer = document.querySelector(
        `.file-list-container[data-doc-id="${docId}"]`,
    );
    const emptyState = document.querySelector(
        `.file-list-empty[data-doc-id="${docId}"]`,
    );
    const statusBadge = document.querySelector(
        `.doc-status-badge[data-doc-id="${docId}"]`,
    );

    if (!dropArea || !fileInput || !listContainer || !emptyState) {
        console.warn(
            `⚠️ fileUpload: missing required elements for doc ${docId}`,
        );
        return;
    }

    console.log(`✅ Found elements for doc ${docId}`);

    initSharedPreviewModal();

    let selectedFiles = [];
    const fileUrlMap = new WeakMap();

    const MAX_FILES = 10;
    const MAX_SIZE_MB = 10;

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + " B";
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
        return (bytes / (1024 * 1024)).toFixed(1) + " MB";
    }

    function iconFor(name) {
        const ext = name.split(".").pop().toLowerCase();
        return ext === "pdf" ? "ti ti-file-type-pdf" : "ti ti-photo";
    }

    function isImage(file) {
        return file.type.startsWith("image/");
    }

    function isPdf(file) {
        return (
            file.type === "application/pdf" ||
            file.name.toLowerCase().endsWith(".pdf")
        );
    }

    function getFileUrl(file) {
        if (!fileUrlMap.has(file)) {
            fileUrlMap.set(file, URL.createObjectURL(file));
        }
        return fileUrlMap.get(file);
    }

    function syncInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach((file) => dt.items.add(file));
        fileInput.files = dt.files;
        console.log(
            `🔄 Synced doc ${docId}: now has ${fileInput.files.length} file(s)`,
        );
    }

    // Updates the row-header badge (e.g. "2 files" / "No files") to reflect
    // the current number of staged files for this document.
    function updateStatusBadge() {
        if (!statusBadge) return;
        const count = selectedFiles.length;
        const label = statusBadge.querySelector("span[data-en]");

        if (count > 0) {
            statusBadge.classList.add("has-files");
            if (label) {
                label.removeAttribute("data-en");
                label.removeAttribute("data-bm");
                label.textContent = count + (count === 1 ? " file" : " files");
            } else {
                statusBadge.textContent = count + (count === 1 ? " file" : " files");
            }
        } else {
            statusBadge.classList.remove("has-files");
            if (label) {
                label.setAttribute("data-en", "No files");
                label.setAttribute("data-bm", "Tiada fail");
                label.textContent = "No files";
            } else {
                statusBadge.textContent = "No files";
            }
        }
    }

    function viewFile(file) {
        const url = getFileUrl(file);

        if (!sharedModal) {
            window.open(url, "_blank", "noopener,noreferrer");
            return;
        }

        if (sharedPreviewImg) sharedPreviewImg.style.display = "none";
        if (sharedPdfViewer) sharedPdfViewer.style.display = "none";
        if (sharedPreviewIcon) sharedPreviewIcon.style.display = "none";

        if (isImage(file) && sharedPreviewImg) {
            sharedPreviewImg.src = url;
            sharedPreviewImg.style.display = "block";
        } else if (isPdf(file) && sharedPdfViewer) {
            sharedPdfViewer.src = url;
            sharedPdfViewer.style.display = "block";
        } else if (sharedPreviewIcon) {
            sharedPreviewIcon.style.display = "block";
            const iconSpan =
                sharedPreviewIcon.querySelector("i") || sharedPreviewIcon;
            if (iconSpan.tagName === "I") {
                iconSpan.className = "ti ti-file-text ti-5x text-muted";
            }
            const msg = sharedPreviewIcon.querySelector("p");
            if (msg)
                msg.textContent =
                    "Preview not available for this file type. Use 'Open in New Tab' to view it.";
        }

        if (sharedFileNameDisplay)
            sharedFileNameDisplay.textContent = file.name;
        if (sharedOpenInNewTabBtn) {
            sharedOpenInNewTabBtn.href = url;
            sharedOpenInNewTabBtn.download = file.name;
        }

        sharedModal.show();
    }

    function render() {
        listContainer.innerHTML = "";
        emptyState.style.display = selectedFiles.length ? "none" : "block";

        selectedFiles.forEach((file, index) => {
            const li = document.createElement("li");
            li.className = "file-list-item";
            li.innerHTML = `
                <i class="${iconFor(file.name)}"></i>
                <div class="file-meta">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${formatSize(file.size)}</div>
                </div>
                <div class="file-actions">
                    <button type="button" class="btn btn-sm btn-icon btn-success-light btn-wave file-view" title="View file">
                        <i class="ti ti-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-danger-light  btn-wave file-remove" title="Remove">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            `;

            li.querySelector(".file-view").addEventListener("click", () => {
                viewFile(file);
            });

            li.querySelector(".file-remove").addEventListener("click", () => {
                const url = fileUrlMap.get(file);
                if (url) {
                    URL.revokeObjectURL(url);
                    fileUrlMap.delete(file);
                }
                selectedFiles.splice(index, 1);
                syncInput();
                render();
                updateStatusBadge();
            });

            listContainer.appendChild(li);
        });
    }

    function addFiles(fileList) {
        const incoming = Array.from(fileList);
        console.log(`📎 Adding ${incoming.length} file(s) to doc ${docId}`);

        incoming.forEach((file) => {
            if (selectedFiles.length >= MAX_FILES) return;

            if (file.size > MAX_SIZE_MB * 1024 * 1024) {
                alert(`${file.name} exceeds the ${MAX_SIZE_MB}MB limit.`);
                return;
            }

            const isDuplicate = selectedFiles.some(
                (f) => f.name === file.name && f.size === file.size,
            );
            if (isDuplicate) return;

            selectedFiles.push(file);
        });

        syncInput();
        render();
        updateStatusBadge();
    }

    // Event listeners
    dropArea.addEventListener("click", () => fileInput.click());

    // NOTE: we intentionally do NOT reset fileInput.value here.
    // selectedFiles + syncInput() is the single source of truth for
    // fileInput.files (rebuilt via DataTransfer). Clearing .value would
    // wipe fileInput.files right after we just set it, which was why
    // every doc showed "0 file(s)" at submit time despite files being
    // added successfully.
    fileInput.addEventListener("change", (e) => {
        if (e.target.files.length) {
            addFiles(e.target.files);
        }
    });

    ["dragenter", "dragover"].forEach((evt) => {
        dropArea.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.add("is-dragover");
        });
    });

    ["dragleave", "drop"].forEach((evt) => {
        dropArea.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropArea.classList.remove("is-dragover");
        });
    });

    dropArea.addEventListener("drop", (e) => {
        if (e.dataTransfer && e.dataTransfer.files) {
            addFiles(e.dataTransfer.files);
        }
    });

    render();
    updateStatusBadge();
}

// ---- Document ready ----
$(document).ready(function () {
    loadStates();

    // Type selection styling
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
            type === "individual"
                ? `Name  <span class="text-primary2">*</span>`
                : `Company name  <span class="text-primary2">*</span> `,
        );
        $("#phoneLabel").html(
            type === "individual"
                ? `Phone Number  <span class="text-primary2">*</span>`
                : `Company Phone Number  <span class="text-primary2">*</span>`,
        );
        $(".icLabel").html(
            type === "individual"
                ? `Identification Number   <span class="text-primary2">*</span>`
                : `Company Registration Number  <span class="text-primary2">*</span>`,
        );

        $("#fullname").attr(
            "placeholder",
            type === "individual" ? "John Doe" : " ABC Sdn. Bhd.",
        );
        $("#phone_number").attr("placeholder", "123430072");
        $("#no_ic").attr(
            "placeholder",
            type === "individual" ? " 000000120000" : " 000000-X",
        );
    }

    type_styling();
    inputEdit();

    function toggleAdditionalFields() {
        var companyFields = $(".company-details");
        var individualFields = $(".individual-details");

        companyFields.addClass("d-none");
        individualFields.addClass("d-none");

        if (type === "company") {
            companyFields.removeClass("d-none");
        } else if (type === "individual") {
            individualFields.removeClass("d-none");
        }
    }

    $(document).on("change", 'input[name="type"]', function () {
        type_styling();
        inputEdit();
        toggleAdditionalFields();
    });

    toggleAdditionalFields();

    // State & District changes
    $(document).on("change", "#state", function () {
        const selectedOption = $(this).find(":selected");
        const selectedStateId = selectedOption.data("id");
        const districtSelect = $("#district");
        const postcodeSelect = $("#postcode");

        if (selectedStateId) {
            loadDistricts(selectedStateId);
            districtSelect.prop("disabled", false);
            postcodeSelect
                .empty()
                .append('<option value="">Select Postcode</option>');
        } else {
            districtSelect
                .empty()
                .append('<option value="">Select District</option>');
            districtSelect.prop("disabled", true);
            postcodeSelect
                .empty()
                .append('<option value="">Select Postcode</option>');
        }
    });

    $(document).on("change", "#district", function () {
        const selectedOption = $(this).find(":selected");
        const selectedDistrictId = selectedOption.data("id");

        if (selectedDistrictId) {
            loadPostcodes(selectedDistrictId);
        } else {
            $("#postcode")
                .empty()
                .append('<option value="">Select Postcode</option>');
        }
    });

    // Tab navigation
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
    $("#backToSummaryTab").on("click", (e) => {
        e.preventDefault();
        switchTo("#shipped-tab");
    });
    $("#backToDetailsTab").on("click", (e) => {
        e.preventDefault();
        switchTo("#confirmed-tab");
    });
    $("#nextToPasswordTab").on("click", (e) => {
        e.preventDefault();
        switchTo("#password-tab");
    });

    $('button[data-bs-toggle="tab"]').on("show.bs.tab", function (e) {
        const target = $(e.target).data("bs-target");
        if (
            (target === "#confirm-tab-pane" ||
                target === "#shipped-tab-pane") &&
            !type
        ) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Choose a type first",
                text: "You must select Individual or Company",
            });
        }
    });

    // Select2
    $("#phone_country").select2({
        placeholder: "Select country",
        width: "100%",
        dropdownAutoWidth: true,
    });
    $("#state").select2({
        placeholder: "Select state",
        width: "100%",
        dropdownAutoWidth: true,
    });
    $("#district").select2({
        placeholder: "Select district",
        width: "100%",
        dropdownAutoWidth: true,
    });
    $("#postcode").select2({
        placeholder: "Select postcode",
        width: "100%",
        dropdownAutoWidth: true,
    });

    // Initialize upload for each document section
    document.querySelectorAll(".document-upload-section").forEach((section) => {
        const docId = section.dataset.docId;
        if (docId) fileUpload(docId);
    });

    // ---- FINISH REGISTRATION ----
    $("#finishRegistrationBtn").on("click", function (e) {
        e.preventDefault();

        const form = $("#registerForm")[0];
        const formData = new FormData(); // start empty

        // 1. Append all non-file inputs from the form.
        // Radios/checkboxes need special handling: jQuery's .val() on a
        // radio returns that radio's OWN value regardless of whether it's
        // checked, so a naive loop over every :input appends every radio
        // in a group (e.g. both "individual" and "company" for `type`).
        // Only append radios/checkboxes that are actually checked.
        $(form)
            .find(":input:not(:file)")
            .each(function () {
                const $el = $(this);
                const name = $el.attr("name");
                if (!name) return;

                if ($el.is(":radio") || $el.is(":checkbox")) {
                    if ($el.is(":checked")) {
                        formData.append(name, $el.val());
                    }
                    return;
                }

                const value = $el.val();
                if (value !== undefined && value !== null) {
                    formData.append(name, value);
                }
            });

        // 2. Append account_type (if not already in the form)
        let account_type = type === "individual" ? "individu" : type;
        formData.append("account_type", account_type);

        // 3. Append files from each document-specific input
        console.log("📤 Submitting form, checking file inputs...");
        document.querySelectorAll(".file-input").forEach((input) => {
            const docId = input.dataset.docId;
            const fileCount = input.files.length;
            console.log(`  📎 Doc ${docId} has ${fileCount} file(s)`);
            if (fileCount > 0) {
                for (let i = 0; i < fileCount; i++) {
                    const file = input.files[i];
                    console.log(
                        `    ➕ Appending: ${file.name} (${file.size} bytes)`,
                    );
                    formData.append(`attachment[${docId}][]`, file);
                }
            }
        });

        // Log final FormData entries for debugging
        console.log("📦 Final FormData entries:");
        for (let pair of formData.entries()) {
            if (pair[1] instanceof File) {
                console.log(
                    `  ${pair[0]}: ${pair[1].name} (${pair[1].size} bytes)`,
                );
            } else {
                console.log(`  ${pair[0]}: ${pair[1]}`);
            }
        }

        // AJAX call (unchanged)
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
            error: function (xhr) {
                Swal.close();

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;

                    for (const key in errors) {
                        const $input = $(form).find(`[name="${key}"]`);
                        if ($input.length) {
                            $input.addClass("is-invalid");
                            $input.next(".invalid-feedback").remove();
                            $input.after(
                                `<div class="invalid-feedback">${errors[key][0]}</div>`,
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