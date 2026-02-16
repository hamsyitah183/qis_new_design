import Dropzone from "dropzone";
import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import { generateUUID, getAuthUser } from "../../app";
import "dropzone/dist/dropzone.css";
import { render } from "react-dom/cjs/react-dom.production.min";

// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

Dropzone.autoDiscover = false;

console.log("application in edit js", application);

// Global state
let exporterListArray = [];
let entryName = null;
let exporter = null;
let importer = null;
let impAddrs = null;
let itemDropzone = null;

let change = null;

let tempItems = [];
let tempAttachments = [];
let itemPurpose = null;
let temporaryItemsAttachment = [];

let existingIds = [];
existingIds = application.consignment_permits
    ? application.consignment_permits.map((p) => p.id)
    : [];

function importerDetail() {
    // importer = JSON.parse(application.importer_detail);
    importer = application.importer_detail;
    // console.log('importer', importer)
    $("#impname").val(importer.fullname);
    $("#impfonno").val(importer.phone_number);
    $("#impaddress1").val(importer.address_1);
    $("#impaddress2").val(importer.address_2 ?? "");
}

// ------------------------- Exporter List -------------------------
function fetchExporterList() {
    exporter = application.exporter;
    console.log("exporter", exporter);

    const $select = $("#selectexp");
    const url = $select.data("route");

    return $.ajax({
        url,
        type: "GET",
        dataType: "json",
        cache: false,
        success: (data) => {
            exporterListArray = data || [];

            $select
                .empty()
                .append('<option value="">-- Select Exporter --</option>');
            data.forEach((exp) =>
                $select.append(`<option value="${exp.id}">${exp.name}</option>`)
            );

            // Initialize Select2
            $select.select2({
                width: "100%",
                placeholder: "-- Select Exporter --",
                allowClear: true,
            });

            // ✅ Auto-select the exporter if already set
            if (exporter && exporter.id) {
                $select.val(exporter.id).trigger("change"); // trigger change to update fields
                // $select.trigger('change');
            }
        },

        error: (xhr) => {
            console.error("Failed to load exporters:", xhr.responseText);
            Swal.fire({
                icon: "error",
                title: "Failed to Load Exporters",
                text: "Please try again or check your connection.",
            });
        },
    });
}

function handleExporterChange() {
    const $select = $("#selectexp");

    $select.on("change", function () {
        const selectedId = $(this).val();
        console.log("select", selectedId);

        // Clear fields if no selection
        if (!selectedId) return clearExporterFields();
        exporter = null;
        exporter = exporterListArray.find((e) => e.id == selectedId);
        if (!exporter) return;

        console.log("exporter details", exporter);

        $("#expid").val(exporter.id || "");
        $("#expname").val(exporter.name || "");
        $("#expfonno").val(exporter.phone_no || "");
        $("#expaddress1").val(exporter.address1 || exporter.address || "");
        $("#expcountryCode").val(exporter.ccode || "");
        $("#expcountry").val(exporter.country || "");

        change = 1;
    });
}

function clearExporterFields() {
    $("#expid, #expname, #expfonno, #expaddress1, #expaddress2").val("");
    $("#expcountryCode, #expcountry").val("");
}

// ------------------------- Consignment / Uses -------------------------

function loadConsignmentSelection() {
    const countryCode = $("#expcountryCode").val();
    const $select = $("#itemSelect");

    if (!countryCode) return;

    // Reset select options
    $select.empty().append('<option value="">-- Select Item --</option>');

    // Destroy existing Select2 (if already initiated)
    if ($select.hasClass("select2-hidden-accessible")) {
        $select.select2("destroy");
    }

    // Disable select while loading
    $select.prop("disabled", true);

    // Show loading Swal
    Swal.fire({
        title: "Loading...",
        // html: "Please wait while items are loaded.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    fetch(`/public/get_consignment/${countryCode}`)
        .then((res) => res.json())
        .then((data) => {
            $select.prop("disabled", false);

            data.forEach((row) => {
                $select.append(
                    `<option value="${row.id}">${row.entry_display}</option>`
                );
            });

            // Initialize Select2
            $select.select2({
                width: "100%",
                placeholder: "-- Select Item --",
                allowClear: true,
                dropdownParent: $("#addItemModal"), // Important: for modal
            });

            Swal.close(); // Close loading
        })
        .catch((e) => {
            console.error("Error loading items:", e);
            $select.prop("disabled", false);
            Swal.fire("Error", "Failed to load consignment items.", "error");
        });
}

function loadUses(itemId) {
    const $select = $("#itemUses");
    $select.empty().append('<option value="">-- Select Uses --</option>');

    if (!itemId) return;

    Swal.fire({
        title: "Loading...",
        // html: "Please wait while items are loaded.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    fetch(`/public/consignment_uses/${itemId}`)
        .then((res) => res.json())
        .then((data) => {
            if (!data.data) return;
            data.data.forEach((row) => {
                $select.append(`<option value="${row}">${row}</option>`);
            });

            $select.select2({
                width: "100%",
                placeholder: "-- Select Uses --",
                allowClear: true,
                dropdownParent: $("#addItemModal"), // Important: for modal
            });

            Swal.close();
        })
        .catch((err) => {
            console.error("Failed to load uses:", err);
        });
}

// ------------------------- Add Exporter Modal -------------------------
function initAddExporterModal() {
    const modalEl = document.getElementById("addExporterModal");
    const modal = new bootstrap.Modal(modalEl);

    $("#openExporterModalBtn").on("click", (e) => {
        e.preventDefault();
        modal.show();
    });

    $("#addExporterbtn").on("click", (e) => {
        e.preventDefault();

        const routeUrl = $(e.currentTarget).data("route");
        const name = $("#addexpName").val().trim();
        const phone_no = $("#addexpfonno").val().trim();
        const address1 = $("#addexpaddress1").val().trim();
        const address2 = $("#addexpaddress2").val().trim();
        const full_address = `${address1} ${address2}`;
        const country = $("#addexpcountry").val();

        if (!name || !phone_no || !country) {
            return Swal.fire("⚠️ Please fill in all required fields.");
        }

        $.ajax({
            url: routeUrl,
            type: "POST",
            data: { name, phone_no, address: full_address, country },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: () => {
                fetchExporterList();
                Swal.fire({
                    icon: "success",
                    title: "Exporter Saved!",
                    text: "The exporter has been successfully added to the list.",
                    timer: 1800,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: "center",
                });
                $(modalEl).modal("hide");
                $("#addExporterForm")[0].reset();
            },
            error: (xhr) => {
                console.error(xhr.responseText);
                Swal.fire("❌ Failed to save exporter. Please try again.");
            },
        });
    });
}

// ------------------------- Importer Lookup -------------------------
function initImporterSearch() {
    const btn = $("#btnFindImp");
    const input = $("#findImporter");

    btn.on("click", function (e) {
        e.preventDefault();
        const identityNumber = input.val().trim();

        if (!identityNumber) {
            Swal.fire({
                icon: "warning",
                title: "Identity number is empty!",
            });
            return;
        }

        Swal.fire({
            title: "Searching...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        fetch(`/public/get_importers/${identityNumber}`)
            .then((res) => {
                Swal.close();
                if (!res.ok)
                    throw new Error(`HTTP error! status: ${res.status}`);
                return res.json();
            })
            .then(handleImporterResponse)
            .catch((err) => {
                console.error("Importer search error:", err);
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: "Search Failed",
                    text: "Unable to fetch importer data. Please try again.",
                });
            });
    });
}

function handleImporterResponse(data) {
    console.log("handleImporterResponse", data);
    const hideAll = () => {
        $("#searchresult, #doanotver, #emailnotver").hide();
        $(
            "#impname, #impid, #impfonno, #impaddress1, #impaddress2, #imp_id, #impemail"
        ).val("");
    };

    importer = null;
    importer = data.data;
    console.log("importer data", importer);

    if (data.status !== "success") return hideAll();

    // SUCCESS
    $("#searchresult, #doanotver, #emailnotver").hide();
    $("#impname").val(data.data.fullname);
    $("#impid").val(data.data.id);
    $("#impfonno").val(data.data.phone_number);
    $("#impaddress1").val(data.data.address_1);
    $("#impaddress2").val(data.data.address_2);
    $("#imp_id").val(data.data.id);
    $("#impemail").val(data.data.email);
}

// -------------------------Permit details ------------------------
function permitDetails() {
    const trnptType = document.getElementById("trnptType");
    const detailsSelect = document.getElementById("entryPoint");

    if (!trnptType) return;

    // Function to load entry points via AJAX
    function loadEntryPoints(value) {
        return new Promise((resolve, reject) => {
            const route = trnptType.dataset.route;
            if (!value || route === "#") {
                $(detailsSelect).html(
                    '<option value="">-- Select Option --</option>'
                );
                resolve();
                return;
            }

            Swal.fire({
                title: "Loading...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                url: `${route}?type=${encodeURIComponent(value)}`,
                type: "GET",
                dataType: "json",
                success: function (data) {
                    let options =
                        '<option value="">-- Select Entry Point --</option>';
                    data.forEach(function (item) {
                        options += `<option value="${item.id}" data-entry_name="${item.entry_display}">${item.entry_display}</option>`;
                    });
                    $(detailsSelect).html(options);
                    Swal.close();
                    resolve(data);
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                    Swal.close();
                    Swal.fire("Error", "Failed to load entry points", "error");
                    reject(error);
                },
            });
        });
    }

    // Change handler
    trnptType.addEventListener("change", function () {
        const value = this.value;
        loadEntryPoints(value).then(() => {
            // Auto-select entry point if exists in application
            if (application.entry_point && application.entry_point.id) {
                $(detailsSelect)
                    .val(application.entry_point.id)
                    .trigger("change");
            }
        });
    });

    $("#entryPoint").on("change", function (e) {
        e.preventDefault();
        entryName = $(this).find("option:selected").data("entry_name");
        console.log("I picked entry:", entryName);
        summarySubmit();
    });

    // ✅ Auto-select transport type on page load
    if (application.transport_type) {
        trnptType.value = application.transport_type;
        // Trigger change and handle the AJAX + then
        const event = new Event("change");
        trnptType.dispatchEvent(event);
    }

    if (application && application.eta) {
        // Extract YYYY-MM-DD from the datetime string
        const etaDate = application.eta.split("T")[0];
        document.getElementById("eta").value = etaDate;
    }
}

// ============= attachment =====================
function itemConsigment() {
    itemDropzone = new Dropzone("#itemDropzone", {
        url: "/",
        autoProcessQueue: false,
        paramName: "file",
        maxFilesize: 10, // MB
        acceptedFiles: ".jpg,.jpeg,.png,.pdf",
        addRemoveLinks: true,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        // --- 1. SWAL LOADING BEFORE LOAD (Processing) ---
        processing: function (file) {
            Swal.fire({
                title: "Uploading...",
                html: "Please wait while your file is being uploaded.",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
            groupPreview();
        },
        // --- 2. SWAL SUCCESS AFTER LOAD ---
        success: (file, response) => {
            Swal.close();

            tempAttachments.push({
                id: response.id,
                original_name: response.original_name,
                temp_name: response.temp_name,
                temp_path: response.temp_path,
                mime_type: response.mime_type,
                size: response.size,
                type: response.type,
            });

            file.temp_id = response.id;

            // ✅ Call groupPreview here too
            groupPreview();

            console.log("temp", tempAttachments);
            console.log(
                "Latest uploaded file:",
                tempAttachments[tempAttachments.length - 1]
            );
            console.log("All attachments:", tempAttachments);

            Swal.fire({
                icon: "success",
                title: "Upload Successful!",
                text: `${response.original_name} has been uploaded.`,
                timer: 3000,
                showConfirmButton: false,
            });
        },
        // --- 3. SWAL ERROR AFTER LOAD ---
        error: (file, message, xhr) => {
            Swal.close();
            itemDropzone.removeFile(file);
            Swal.fire({
                icon: "error",
                title: "Upload Failed",
                text:
                    message.error || "An unknown error occurred during upload.",
                footer: "Please try again.",
            });
            console.error("Dropzone Error:", message);
        },
        // --- 4. HANDLE FILE REMOVAL ---
        removedfile: function (file) {
            if (file.temp_id) {
                const indexToRemove = tempAttachments.findIndex(
                    (a) => a.id === file.temp_id
                );
                if (indexToRemove > -1)
                    tempAttachments.splice(indexToRemove, 1);
            }
            const _ref = file.previewElement;
            if (_ref) _ref.parentNode.removeChild(_ref);

            groupPreview();
        },
    });

    // ✅ Run groupPreview every time a file is added (before upload)
    itemDropzone.on("addedfile", function (file) {
        groupPreview();
    });
}

function groupPreview() {
    $(document).ready(function () {
        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        setTimeout(function () {
            const $dropzone = $("#itemDropzone");
            const $previews = $dropzone.find(".dz-preview");
            const $deleteBtns = $previews.find(".dz-remove");

            // Create group if it doesn't exist
            let $group = $dropzone.find(".dz-preview-group");
            if ($group.length === 0) {
                $group = $('<div class="dz-preview-group"></div>');
                $dropzone.find(".dz-message").after($group);
            }

            // Move all previews into the group
            $previews.appendTo($group);

            // Replace PDF previews with PDF logo
            for (const file of itemDropzone.getAcceptedFiles()) {
                if (file.type === "application/pdf") {
                    const $preview = $(file.previewElement);
                    const $img = $preview.find(
                        ".dz-image img[data-dz-thumbnail]"
                    );

                    // Set your PDF logo path
                    $img.attr(
                        "src",
                        "/images/pdf-logo.png" // <-- replace with your actual PDF logo path
                    );
                    $img.css({
                        "object-fit": "contain",
                        width: "100%",
                        height: "100%",
                    });
                }
            }

            // Update delete buttons
            $deleteBtns.html('<i class="ti ti-trash"></i>');

            Swal.close();
        }, 100);
    });
}

function saveConsignmentAttachment() {
    document.getElementById("saveBtn").addEventListener("click", function (e) {
        e.preventDefault();

        // ✅ Get field values (handle Select2 if used)
        const itemSelectValue = $("#itemSelect").val();
        const itemSelectText = $("#itemSelect option:selected").text();
        const itemValue = $("#itemValue").val().trim();
        const itemQuantity = $("#itemQuantity").val().trim();
        const itemMeasure = $("#itemMeasure").val();
        const itemPurposeDescription = $("#itemPurpose option:selected").data("description") || $("#itemPurpose").val();
        const itemUsesValue = $("#itemUses").val();

        // ✅ Validation
        if (!itemSelectValue || !itemValue || !itemQuantity || !itemMeasure || !itemPurposeDescription || !itemUsesValue) {
            Swal.fire({
                icon: "error",
                title: "Incomplete Data",
                text: "Please fill in all required fields before saving.",
            });
            return;
        }

        // ✅ Get Dropzone files
        const files = itemDropzone.getAcceptedFiles();

        // ✅ Build the item object
        const newItem = {
            // id: crypto.randomUUID(),
            id: generateUUID(),
            item_id: itemSelectValue,
            item_name: itemSelectText,
            value: itemValue,
            quantity: itemQuantity,
            measure: itemMeasure,
            purpose: itemPurposeDescription,
            uses: itemUsesValue,
            files: [...files],       // UI display
            newFiles: [...files],    // actual upload
        };

        // ✅ Add to temp array
        tempItems.push(newItem);

        // ✅ Render table
        renderAllItems();

        // ✅ Reset modal fields
        resetAddItemModal();

        // ✅ Hide modal
        const modalEl = document.getElementById("addItemModal");
        bootstrap.Modal.getInstance(modalEl).hide();

        // ✅ Trigger summary / update
        change = 1;
        summarySubmit();
    });
}

function resetAddItemModal() {
    // Clear plain inputs
    $("#itemValue").val("");
    $("#itemQuantity").val("");

    // Reset Select2 or plain selects
    $("#itemSelect").val(null).trigger("change");
    $("#itemMeasure").val("").trigger("change");
    $("#itemPurpose").val("").trigger("change");
    $("#itemUses").val(null).trigger("change");

    // Clear Dropzone files
    if (itemDropzone) itemDropzone.removeAllFiles(true);
}


function renderAllItems() {
    const tableBody = document.querySelector("#itemListTbl tbody");
    tableBody.innerHTML = ""; // Clear existing rows

    console.log("temp items", tempItems);

    tempItems.forEach((item, index) => {
        tableBody.insertAdjacentHTML(
            "beforeend",
            `<tr id="item-row-${item.id}">
                <td>${index + 1}</td>
                <td>${item.item_name}</td>
                <td>${item.quantity} ${item.measure}</td>
                <td class = "text-wrap">${item.purpose}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <button class="btn btn-icon btn-success-light view-more-item"
                            data-id="${item.id}">
                            <i class="ti ti-eye"></i>
                        </button>
                        <button class="btn btn-icon btn-danger-light delete-item"
                            data-id="${item.id}">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`
        );
    });
}

function viewMore() {
    $(document).on("click", ".view-more-item", function (e) {
        e.preventDefault();

        let id = $(this).data("id");
        if (!tempItems) return console.error("tempItems array not found");

        let item = tempItems.find((obj) => obj.id === id);
        if (!item) return console.warn("Item not found for id:", id);

        // Render item details
        const detailsDiv = document.getElementById("itemDetailsInfo");
        detailsDiv.innerHTML = `
            <div class="p-1 row">
                <div class = "col-12 col-md-6">
                    <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-tag"></i></span> Item Name:</strong> ${item.item_name}</p>
                </div>
                <div class = "col-12 col-md-6">
                    <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-scale-balanced"></i></span> Quantity:</strong> ${item.quantity} ${item.measure}</p>
                </div>
                <div class = "col-12 col-md-6">
                    <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-money-bill"></i></span> Value:</strong> ${item.value}</p>
                </div>
                <div class = "col-12 col-md-6">
                    <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-pen-fancy"></i></span> Purpose:</strong> ${item.purpose}</p>
                </div>
                <div class = "col-12">
                    <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-gear"></i></span> Uses:</strong> ${item.uses}</p>
                </div>
            </div>
        `;

        // Render files in a table
        const filesTableBody = document.querySelector("#itemFilesTable tbody");
        filesTableBody.innerHTML = "";

        if (Array.isArray(item.files) && item.files.length > 0) {
            item.files.forEach((file) => {
                // ✅ CASE 1: REAL File object (new upload)
                if (file instanceof File) {
                    const reader = new FileReader();

                    reader.onload = function (e) {
                        filesTableBody.insertAdjacentHTML(
                            "beforeend",
                            `
                    <tr>
                        <td>${file.name}</td>
                        <td>${file.type}</td>
                        <td>
                            <button class="btn btn-sm btn-primary view-file-btn"
                                data-url="${e.target.result}"
                                type="button">
                                View
                            </button>
                        </td>
                    </tr>
                    `
                        );
                    };

                    reader.readAsDataURL(file);
                }

                // ✅ CASE 2: EXISTING attachment from DB
                else if (file.file_path) {
                    filesTableBody.insertAdjacentHTML(
                        "beforeend",
                        `
                        <tr data-attachment-id="${file.id}">
                            <td>${file.file_name}</td>
                            <td>${file.file_type}</td>
                            <td class="d-flex gap-2">
                                <a href="${file.file_path}"
                                target="_blank"
                                class="btn btn-sm btn-primary">
                                    View
                                </a>

                               
                            </td>
                        </tr>
                        `
                    );
                }
            });
        } else {
            filesTableBody.innerHTML = `
        <tr>
            <td colspan="3" class="text-center text-muted">
                No files uploaded
            </td>
        </tr>
    `;
        }

        // Show modal
        const modalEl = document.getElementById("ItemDetailsModal");
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    // Handle view file button
    $(document).on("click", ".view-file-btn", function () {
        const base64DataUrl = $(this).data("url");

        if (!base64DataUrl) {
            console.error("No file URL found");
            return;
        }

        // Extract Base64 & MIME type
        const arr = base64DataUrl.split(",");
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        let u8arr = new Uint8Array(n);

        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }

        // Convert to Blob
        const fileBlob = new Blob([u8arr], { type: mime });

        // Create temporary URL
        const fileURL = URL.createObjectURL(fileBlob);

        // Open in a new tab
        window.open(fileURL, "_blank");
    });
}
let deleteIds = [];

function deleteItem() {
    $(document).on("click", ".delete-item", function (e) {
        e.preventDefault();

        let id = $(this).data("id"); // this is tempItems.id (UUID)

        if (!tempItems) {
            console.error("tempItems array not found");
            return;
        }

        // Find item index in array by UUID
        let index = tempItems.findIndex((obj) => obj.id === id);

        if (index === -1) {
            console.warn("Item not found:", id);
            return;
        }

        // ✅ Get item_id instead of id
        let itemId = tempItems[index].permit_id;

        // ✅ Store item_id (avoid duplicates)
        if (!deleteIds.includes(itemId)) {
            deleteIds.push(itemId);
        }

        // Remove from array
        tempItems.splice(index, 1);

        // Remove from UI
        $("#item-card-" + id).remove();

        renderAllItems();

        console.log("Deleted UUID:", id);
        console.log("Deleted item_id:", itemId);
        console.log("Remaining items:", tempItems);
        console.log("Delete item_ids:", deleteIds);

        summarySubmit();
    });
}

function loadExistingConsignments() {
    if (!application.consignment_permits) return;

    tempItems = [];

    application.consignment_permits.forEach((permit) => {
        // let detail = JSON.parse(permit.consignment_detail);
        let detail = permit.consignment_detail;

        console.log("permit detail", detail);

        tempItems.push({
            id: detail.id || crypto.randomUUID(),
            permit_id: permit.id,
            item_id: detail.item_id,
            item_name: detail.item_name,
            value: detail.value,
            quantity: detail.quantity,
            measure: detail.measure,
            purpose: detail.purpose,
            uses: detail.uses,

            // ✅ EXISTING attachments from DB
            existingAttachments: permit.attachments || [],
            files: permit.attachments || [],

            // 🆕 new uploads only
            newFiles: [],

            // ❌ deleted attachment IDs
            deletedAttachmentIds: [],
        });
    });

    renderAllItems();
}

// ============= attachment =====================

function saveapplication(isDraft = false) {
    const form = document.querySelector("#wizardForm");
    if (!form) return console.error("Form not found");

    const formData = new FormData(form);

    // 🔑 tell backend this is draft or submit
    formData.append("is_draft", isDraft ? 1 : 0);

    formData.append("exporterData", JSON.stringify(exporter));
    formData.append("importerData", JSON.stringify(importer));
    formData.append("permitDetails", JSON.stringify(permitDetails));

    formData.append("applicationId", application.application_id);
    formData.append("deleted_item_ids", deleteIds);

    tempItems.forEach((item, index) => {
        const newFiles = Array.isArray(item.newFiles) ? item.newFiles : [];

        formData.append(
            `items[${index}][data]`,
            JSON.stringify({
                permit_id: item.permit_id ?? null,
                item_id: item.item_id,
                item_name: item.item_name,
                value: item.value,
                quantity: item.quantity,
                measure: item.measure,
                purpose: item.purpose,
                uses: item.uses,
            })
        );

        // 🆕 only new files
        newFiles.forEach((file) => {
            formData.append("files[]", file);
            formData.append("file_item_index[]", index);
        });
    });

    Swal.fire({
        title: isDraft ? "Saving Draft..." : "Submitting...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: "/public/save-application",
        type: "POST",
        data: formData,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        processData: false,
        contentType: false,
        success: function (response) {
            Swal.fire({
                icon: "success",
                title: isDraft ? "Draft Saved" : "Application Submitted!",
                timer: 1500,
                showConfirmButton: false,
            });

            if (!isDraft) {
                setTimeout(() => {
                    window.location.href = "/public/view_import_permit";
                }, 1500);
            }
        },
        error: function (xhr) {
            Swal.fire("Error", "Failed to save application", "error");
        },
    });
}

// ------------------------- Initialize -------------------------
// ------------------------- Initialize -------------------------
$(document).ready(async function () {
    // Show loading swal
    Swal.fire({
        title: "Loading...",
        html: "Please wait while the page initializes.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        // Fetch exporter list and set change handler
        // await fetchExporterList();
        fetchExporterList().then(() => {
            if (exporter && exporter.id) {
                const $select = $("#selectexp");
                $select.val(exporter.id).trigger("change"); // This will populate fields
            }
        });
        handleExporterChange();

        // Initialize modals and search
        initAddExporterModal();
        initImporterSearch();
        permitDetails();
        itemConsigment();
        saveConsignmentAttachment();
        viewMore();
        deleteItem();

        importerDetail();
        loadExistingConsignments();

        $("#itemMeasure").select2({
            width: "100%",
            placeholder: "-- Select Measurement Unit --",
            // allowClear: true,
            dropdownParent: $("#addItemModal"), // Important: for modal
        });

        $("#addexpcountry").select2({
            width: "100%",
            placeholder: "-- Select Country --",
            // allowClear: true,
            dropdownParent: $("#addExporterModal"), // Important: for modal
        });

        $("#itemPurpose").select2({
            width: "100%",
            placeholder: "-- Select Purpose --",
            // allowClear: true,
            dropdownParent: $("#addItemModal"), // Important: for modal
        });

        // ------------------- Item Purpose -------------------
        $("#itemPurpose").on("change", function () {
            const selectedOption = $(this).find("option:selected");
            itemPurpose = selectedOption.data("description") || "";
            console.log("Item purpose selected:", itemPurpose);
        });

        // ------------------- Item Select (Consignment) -------------------
        $("#itemSelect").on("change", function () {
            const itemId = $(this).val();
            const $itemUses = $("#itemUses");

            // Reset uses dropdown
            $itemUses
                .empty()
                .append('<option value="">-- Select Uses --</option>');

            if (!itemId) return;

            // Load uses for the selected item
            loadUses(itemId);
        });

        // Expose loadConsignmentSelection globally if needed
        // loadConsignmentSelection();
        $("#mdlAddItemBtn").on("click", loadConsignmentSelection);

        // Submit button handler
        $(document).on("click", "#submitApps", function (e) {
            e.preventDefault();
            console.log("Submit clicked!");
            saveapplication(false);
        });

        $(document).on(
            "click",
            `#logoutButton, 
            .app-sidebar.sticky button, .app-sidebar.sticky a`,
            function (e) {
                if (!change) return;

                e.preventDefault();
                const target = this;

                Swal.fire({
                    title: "Unsaved Changes",
                    text: "You have unsaved changes. What would you like to do?",
                    icon: "warning",
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: "Yes, leave",
                    denyButtonText: "Save as Draft",
                    cancelButtonText: "Stay",
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Leave page
                        change = false;

                        if (target.tagName === "A") {
                            window.location.href = target.href;
                        } else {
                            target.click();
                        }
                    }

                    if (result.isDenied) {
                        console.log("draft is saved !");
                        saveapplication(true); // ✅ draft
                        window.location.href = "/public/view_import_permit";
                        
                    }

                    // result.isDismissed → user clicked "Stay"
                });
            }
        );
    } catch (error) {
        console.error("Error during initialization:", error);
        Swal.fire(
            "Error",
            "Failed to initialize page. Check console for details.",
            "error"
        );
    } finally {
        Swal.close();
    }
});

// ============= attachment =====================

// ------------------------------summary details ---------------------
export function summarySubmit() {
    const targetTable = document.querySelector("#summaryTable3 tbody");

    // --- IMPORTER & EXPORTER SUMMARY ---
    impAddrs = importer
        ? [importer.address_1, importer.address_2]
              .filter((x) => x && x.trim() !== "")
              .join(", ")
        : "";

    permitDetails = {
        applCate: document.getElementById("app_cate").value,
        eta: document.getElementById("eta").value,
        tranType: document.getElementById("trnptType").value,
        entrypoint: document.getElementById("entryPoint").value,
    };

    // Insert importer details
    document.getElementById("importerName").textContent = importer.fullname;
    document.getElementById("importerPhoneno").textContent =
        importer.phone_number;
    document.getElementById("simpAdd").textContent = impAddrs;

    // Exporter details
    document.getElementById("sexpName").textContent = exporter.name;
    document.getElementById("sexpfonno").textContent = exporter.phone_no;
    document.getElementById("sexpAddress").textContent = exporter.address;
    document.getElementById("sexpCountry").textContent = exporter.country;

    // Permit details
    document.getElementById("seta").textContent = permitDetails.eta;
    document.getElementById("strty").textContent = permitDetails.tranType;
    document.getElementById("sentryp").textContent = entryName;

    // --- CONSIGNMENT DETAILS ---
    targetTable.innerHTML = ""; // clear existing rows

    tempItems.forEach((item, index) => {
        // --- Build attachment list ---
        let attachmentHTML = "";

        attachmentHTML = `
            <button class = "btn btn-icon btn-success-light view-more-item" data-id = "${item.id}">
                 <i class="ti ti-eye"></i>
            </button>
            `;

        // --- Insert summary row ---
        targetTable.insertAdjacentHTML(
            "beforeend",
            `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.item_name || ""}</td>
                    <td>${item.quantity || ""}  ${item.measure || ""}</td>
                    <td class = "text-wrap">${item.purpose || ""}</td>
                    <td class = "text-wrap">${item.uses || ""}</td>
                    <td>RM ${item.value || ""}</td>
                    <td>${attachmentHTML}</td>
                </tr>
            `
        );
    });
}

// ------------------------------summary details ---------------------
