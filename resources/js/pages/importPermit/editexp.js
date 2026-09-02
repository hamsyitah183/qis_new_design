import Dropzone from "dropzone";
import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import { generateUUID, getAuthUser } from "../../app";
import "dropzone/dist/dropzone.css";

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
let isInitializing = true; // Prevent change‑exporter dialog on page load
let editingItemId = null;  // ID of the item being edited

let tempItems = [];
let tempAttachments = [];
let itemPurpose = null;
let temporaryItemsAttachment = [];

// ─── Application Attachments State ──
let applicationAttachments = [];
let deletedAttachmentIds = [];
let appDocDropzones = {};
let dropzoneInstances = {};
let attachmentOffcanvas = null;
let itemAttachmentOffcanvas = null;
let currentItemAttachments = [];
let currentItemAttachIndex = 0;
let currentAttachmentIndex = 0;

let existingIds = [];
existingIds = application.consignment_permits
    ? application.consignment_permits.map((p) => p.id)
    : [];

let measurementUnits = null;
let news = null;
let limit = null;
let limitMeasurement = null;

function measurementUnit() {
    return $.ajax({
        url: '/measurement',
        type: "GET",
        dataType: "json",
        cache: false,
        success: (data) => {
            measurementUnits = data;
            console.log('measurement', measurementUnits);
        },
        error: (xhr) => {
            console.error("Failed to load exporters:", xhr.responseText);
        },
    });
}
measurementUnit();

function importerDetail() {
    importer = application.importer_detail;
    $("#impname").val(importer.fullname);
    $("#impfonno").val(importer.phone_number);
    $("#impaddress1").val(importer.address_1);
    $("#impaddress2").val(importer.address_2 ?? "");
}

function exporterDetail() {
    exporter = application.exporter;
    if (!exporter) return;
    console.log("exporter data:", exporter);
    $("#expname").val(exporter.name || "");
    $("#expfonno").val(exporter.phone_no || "");
    $("#expaddress1").val(exporter.address1 || exporter.address || "");
    $("#expcountryCode").val(exporter.ccode || "");
    $("#expcountry").val(exporter.country || "");
}

// ─── Helper to determine MIME type from extension ──
function getMimeType(filename) {
    if (!filename) return "application/octet-stream";
    const ext = filename.split('.').pop().toLowerCase();
    const mimeTypes = {
        'pdf': 'application/pdf',
        'jpg': 'image/jpeg',
        'jpeg': 'image/jpeg',
        'png': 'image/png',
        'doc': 'application/msword',
        'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls': 'application/vnd.ms-excel',
        'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt': 'text/plain',
        'csv': 'text/csv'
    };
    return mimeTypes[ext] || "application/octet-stream";
}

// ─── Helper to create a dummy file from existing attachment ──
function createExistingFile(attachment) {
    const mimeType = attachment.file_type || getMimeType(attachment.file_name);
    const blob = new Blob([' '], { type: mimeType });
    const file = new File([blob], attachment.file_name, { type: mimeType });
    file._isExisting = true;
    file._url = attachment.file_path;
    file._id = attachment.id;
    file.displayName = attachment.file_name;
    return file;
}

function createDummyFileFromAttachment(att) {
    const mimeType = getMimeType(att.name) || att.type || 'application/octet-stream';
    const blob = new Blob([' '], { type: mimeType });
    const file = new File([blob], att.name, { type: mimeType });
    file._isExisting = true;
    file._url = att.url || null;
    file._id = att.id;
    file.displayName = att.displayName || att.name;
    file._attachmentId = att.id;
    return file;
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

            // Auto‑select the exporter if already set
            if (exporter && exporter.id) {
                $select.val(exporter.id).trigger("change");
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
        const $selectRef = $(this);

        const applyExporter = (id) => {
            if (!id) return clearExporterFields();
            exporter = null;
            exporter = exporterListArray.find((e) => e.id == id);
            if (!exporter) return;

            $("#expid").val(exporter.id || "");
            $("#expname").val(exporter.name || "");
            $("#expfonno").val(exporter.phone_no || "");
            $("#expaddress1").val(exporter.address1 || exporter.address || "");
            $("#expcountryCode").val(exporter.ccode || "");
            $("#expcountry").val(exporter.country || "");
            change = 1;
        };

        if (tempItems.length > 0 && !isInitializing) {
            Swal.fire({
                icon: 'warning',
                title: 'Change Exporter?',
                text: 'Want to change the exporter? All the items will be removed!',
                showCancelButton: true,
                confirmButtonText: 'Yes, change it',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
            }).then((result) => {
                if (result.isConfirmed) {
                    tempItems.length = 0;
                    renderAllItems();
                    summarySubmit();
                    applyExporter(selectedId);
                } else {
                    $selectRef.val(exporter?.id ?? "").trigger("change.select2");
                }
            });
            return;
        }

        applyExporter(selectedId);
    });
}

function clearExporterFields() {
    $("#expid, #expname, #expfonno, #expaddress1, #expaddress2").val("");
    $("#expcountryCode, #expcountry").val("");
}

// ------------------------- Consignment / Uses -------------------------
function loadConsignmentSelection() {
    limitMeasurement = null;
    limit = null;
    $('#addItemModal .modal-body .news').find('.alert').remove();

    const countryCode = $("#expcountryCode").val();
    const $select = $("#itemSelect");

    if (!countryCode) {
        // Return a resolved promise to avoid breaking the chain
        return Promise.resolve();
    }

    // Reset select options
    $select.empty().append('<option value="">-- Select Item --</option>');

    if ($select.hasClass("select2-hidden-accessible")) {
        $select.select2("destroy");
    }

    $select.prop("disabled", true);

    Swal.fire({
        title: "Loading...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    // ✅ Return the fetch promise
    return fetch(`/public/get_consignment/${countryCode}`)
        .then((res) => res.json())
        .then((data) => {
            $select.prop("disabled", false);

            // Add "Others" option for custom items
            $select.append(`<option value="others">Others</option>`);

            data.forEach((row) => {
                $select.append(
                    `<option value="${row.id}">${row.entry_display}</option>`
                );
            });

            $select.select2({
                width: "100%",
                placeholder: "-- Select Item --",
                allowClear: true,
                dropdownParent: $("#addItemModal"),
            });

            Swal.close();
            return data; // optional
        })
        .catch((e) => {
            console.error("Error loading items:", e);
            $select.prop("disabled", false);
            Swal.close();
            Swal.fire("Error", "Failed to load consignment items.", "error");
            // Re-throw so the caller can handle the error
            throw e;
        });
}

function loadUses(itemId) {
    const $select = $("#itemUses");
    $select.empty().append('<option value="">-- Select Uses --</option>');

    if (!itemId) return Promise.resolve();

    Swal.fire({
        title: "Loading...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    const url = itemId === 'others' ? '/public/consignment_uses' : `/public/consignment_uses/${itemId}`;
    return fetch(url)
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
                dropdownParent: $("#addItemModal"),
            });

            Swal.close();
        })
        .catch((err) => {
            console.error("Failed to load uses:", err);
            Swal.close();
        });
}

function formatDate(dateString) {
    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-GB', options);
}

function loadDetails(itemId) {
    fetch(`/public/get_item_details/${itemId}`)
        .then((res) => res.json())
        .then((data) => {
            console.log('data in details', data);
            let item = data.data;
            let startDate = '';

            limit = item.quantity_limit;
            limitMeasurement = item.measurement_unit;

            if (item.start_date) {
                startDate = `from <span class="fw-bold">${formatDate(item.start_date)}</span> until <span class="fw-bold">${formatDate(item.end_date)}</span>`;
            }

            if (item.quantity_limit) {
                const alertHtml = `
                    <div class="col-12 alert alert-primary">
                        <p>
                            The quantity allowed for ${item.item_name} is 
                            <span class="fw-bold">${item.quantity_limit} ${item.measurement_unit}</span> 
                            ${startDate}.
                        </p>
                    </div>
                `;
                $('#addItemModal .modal-body .news').find('.alert').remove();
                $('#addItemModal .modal-body .news').prepend(alertHtml);
            } else {
                $('#addItemModal .modal-body .news').find('.alert').remove();
            }
        })
        .catch((err) => {
            console.error("Failed to load details:", err);
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
        $("#impname, #impid, #impfonno, #impaddress1, #impaddress2, #imp_id, #impemail").val("");
    };

    importer = null;
    importer = data.data;
    console.log("importer data", importer);

    if (data.status !== "success") return hideAll();

    $("#searchresult, #doanotver, #emailnotver").hide();
    $("#impname").val(data.data.fullname);
    $("#impid").val(data.data.id);
    $("#impfonno").val(data.data.phone_number);
    $("#impaddress1").val(data.data.address_1);
    $("#impaddress2").val(data.data.address_2);
    $("#imp_id").val(data.data.id);
    $("#impemail").val(data.data.email);
}

// ------------------------- Permit details -------------------------
function permitDetails() {
    const trnptType = document.getElementById("trnptType");
    const detailsSelect = document.getElementById("entryPoint");

    if (!trnptType) return;

    function loadEntryPoints(value) {
        return new Promise((resolve, reject) => {
            const route = trnptType.dataset.route;
            if (!value || route === "#") {
                $(detailsSelect).html('<option value="">-- Select Option --</option>');
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
                    let options = '<option value="">-- Select Entry Point --</option>';
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

    trnptType.addEventListener("change", function () {
        const value = this.value;
        loadEntryPoints(value).then(() => {
            if (application.entry_point && application.entry_point.id) {
                $(detailsSelect).val(application.entry_point.id).trigger("change");
            }
        });
    });

    $("#entryPoint").on("change", function (e) {
        e.preventDefault();
        entryName = $(this).find("option:selected").data("entry_name");
        console.log("I picked entry:", entryName);
        summarySubmit();
    });

    if (application.transport_type) {
        trnptType.value = application.transport_type;
        const event = new Event("change");
        trnptType.dispatchEvent(event);
    }

    if (application && application.eta) {
        const etaDate = application.eta.split("T")[0];
        document.getElementById("eta").value = etaDate;
    }
}

// ============= attachment =====================
function itemConsigment() {
    const el = document.getElementById("itemDropzone");
    if (!el) return;
    itemDropzone = new Dropzone(el, {
        url: "/",
        autoProcessQueue: false,
        paramName: "file",
        maxFilesize: 10,
        acceptedFiles: ".jpg,.jpeg,.png,.pdf",
        addRemoveLinks: true,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        processing: function (file) {
            Swal.fire({
                title: "Uploading...",
                html: "Please wait while your file is being uploaded.",
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); },
            });
            groupPreview();
        },
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
            groupPreview();

            Swal.fire({
                icon: "success",
                title: "Upload Successful!",
                text: `${response.original_name} has been uploaded.`,
                timer: 3000,
                showConfirmButton: false,
            });
        },
        error: (file, message, xhr) => {
            Swal.close();
            itemDropzone.removeFile(file);
            Swal.fire({
                icon: "error",
                title: "Upload Failed",
                text: message.error || "An unknown error occurred during upload.",
                footer: "Please try again.",
            });
            console.error("Dropzone Error:", message);
        },
        removedfile: function (file) {
            if (file.temp_id) {
                const indexToRemove = tempAttachments.findIndex((a) => a.id === file.temp_id);
                if (indexToRemove > -1) tempAttachments.splice(indexToRemove, 1);
            }
            const _ref = file.previewElement;
            if (_ref) _ref.parentNode.removeChild(_ref);
            groupPreview();
        },
    });

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

            let $group = $dropzone.find(".dz-preview-group");
            if ($group.length === 0) {
                $group = $('<div class="dz-preview-group"></div>');
                $dropzone.find(".dz-message").after($group);
            }

            $previews.appendTo($group);

            for (const file of itemDropzone.getAcceptedFiles()) {
                if (file.type === "application/pdf") {
                    const $preview = $(file.previewElement);
                    const $img = $preview.find(".dz-image img[data-dz-thumbnail]");
                    $img.attr("src", "/images/pdf-logo.png");
                    $img.css({ "object-fit": "contain", width: "100%", height: "100%" });
                }
            }

            $deleteBtns.html('<i class="ti ti-trash"></i>');

            Swal.close();
        }, 100);
    });
}

// ─── Save Consignment Attachment (FIXED) ──────────────────────────
function saveConsignmentAttachment() {
    document.getElementById("saveBtn").addEventListener("click", function (e) {
        e.preventDefault();

        const itemSelectValue = $("#itemSelect").val();
        const itemSelectText = $("#itemSelect option:selected").text();
        const itemValue = $("#itemValue").val().trim();
        const itemQuantity = $("#itemQuantity").val().trim();
        const itemMeasure = $("#itemMeasure").val();
        const itemPurposeDescription = $("#itemPurpose option:selected").data("description") || $("#itemPurpose").val();
        const itemUsesValue = $("#itemUses").val();

        if (!itemSelectValue || !itemValue || !itemQuantity || !itemMeasure || !itemPurposeDescription || !itemUsesValue) {
            Swal.fire({
                icon: "error",
                title: "Incomplete Data",
                text: "Please fill in all required fields before saving.",
            });
            return;
        }

        // Handle custom item (Others)
        let isCustom = itemSelectValue === "others";
        let itemName = isCustom ? $("#customItemName").val().trim() : itemSelectText;
        let itemId = isCustom ? null : itemSelectValue;

        if (isCustom && !itemName) {
            Swal.fire({ icon: "error", title: "Custom Item Name Required", text: "Please enter a custom item name." });
            return;
        }

        // Limit validation (existing logic)
        if (limitMeasurement) {
            let limitInKg = null;
            const selectedUnit = measurementUnits.unit.find(unit =>
                unit.cate_code.toLowerCase() === limitMeasurement.toLowerCase() && unit.is_del === false
            );
            if (selectedUnit) {
                limitInKg = limit * selectedUnit.conversion.conversion;
            }

            let selectedItemInKg = null;
            const selectedItemUnit = measurementUnits.unit.find(unit =>
                unit.cate_code.toLowerCase() === itemMeasure.toLowerCase() && unit.is_del === false
            );
            if (selectedItemUnit) {
                selectedItemInKg = itemQuantity * selectedItemUnit.conversion.conversion;
            }

            if (selectedItemInKg > limitInKg) {
                Swal.fire({
                    icon: "error",
                    title: "The item is over limit",
                    text: "Please fill in again.",
                });
                return;
            }
        }

        const files = itemDropzone.getAcceptedFiles();

        // ─── BUILD ITEM OBJECT ──────────────────────────────────────
        const newItem = {
            id: generateUUID(),
            item_id: itemId,
            item_name: itemName,
            value: itemValue,
            quantity: itemQuantity,
            measure: itemMeasure,
            purpose: itemPurposeDescription,
            uses: itemUsesValue,
            files: [...files],
            newFiles: [...files],
            isCustom: isCustom,
        };

        // ─── UPDATE OR ADD ─────────────────────────────────────────
        if (editingItemId !== null) {
            // Update existing item
            const index = tempItems.findIndex(obj => obj.id === editingItemId);
            if (index !== -1) {
                // Preserve the permit_id and existing attachments
                const oldItem = tempItems[index];
                newItem.permit_id = oldItem.permit_id;
                newItem.existingAttachments = oldItem.existingAttachments || [];
                newItem.files = [...oldItem.existingAttachments, ...files]; // combine old + new
                // Replace the item
                tempItems[index] = newItem;
            }
            editingItemId = null; // reset
        } else {
            // Add new item
            tempItems.push(newItem);
        }

        renderAllItems();
        resetAddItemModal();

        const modalEl = document.getElementById("addItemModal");
        bootstrap.Modal.getInstance(modalEl).hide();

        change = 1;
        summarySubmit();
    });
}

function resetAddItemModal() {
    $("#itemValue").val("");
    $("#itemQuantity").val("");
    $("#itemSelect").val(null).trigger("change");
    $("#itemMeasure").val("").trigger("change");
    $("#itemPurpose").val("").trigger("change");
    $("#itemUses").val(null).trigger("change");
    // Custom fields
    $("#customItemName").val("");
    $("#customItemWrapper").hide();
    // Reset editing flag
    editingItemId = null;
    if (itemDropzone) itemDropzone.removeAllFiles(true);
}

function renderAllItems() {
    const tableBody = document.querySelector("#itemListTbl tbody");
    tableBody.innerHTML = "";

    const countInput = document.getElementById("itemCountCheck");
    if (countInput) {
        countInput.value = tempItems.length > 0 ? "1" : "";
        if (tempItems.length > 0) {
            countInput.classList.remove("is-invalid");
            countInput.style.border = "";
            const addBtn = document.getElementById("mdlAddItemBtn");
            if (addBtn) {
                addBtn.classList.remove("is-invalid");
                addBtn.style.setProperty("border", "", "important");
                addBtn.style.color = "";
            }
        }
    }

    console.log("temp items", tempItems);

    tempItems.forEach((item, index) => {
        tableBody.insertAdjacentHTML(
            "beforeend",
            `<tr id="item-row-${item.id}">
                <td class="text-wrap">${item.item_name}</td>
                <td class="text-wrap">${item.purpose}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <button class="btn btn-icon btn-success-light view-more-item" data-id="${item.id}">
                            <i class="ti ti-eye"></i>
                        </button>
                        <button class="btn btn-icon btn-danger-light delete-item" data-id="${item.id}">
                            <i class="ti ti-trash"></i>
                        </button>
                        <button class="btn btn-icon btn-info-light edit-item" data-id="${item.id}">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-icon btn-secondary-light copy-item" data-id="${item.id}">
                            <i class="ti ti-copy"></i>
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

        const detailsHTML = `
            <div class="text-start">
                <div class="mb-3"><strong>Item Name:</strong> ${item.item_name}</div>
                <div class="mb-3"><strong>Quantity:</strong> ${item.quantity} ${item.measure}</div>
                <div class="mb-3"><strong>Value:</strong> RM ${item.value}</div>
                <div class="mb-3"><strong>Purpose:</strong> ${item.purpose}</div>
                <div class="mb-3"><strong>Uses:</strong> ${item.uses}</div>
                ${Array.isArray(item.files) && item.files.length > 0 ? 
                    `<div class="mb-3"><strong>Files:</strong> ${item.files.length} file(s) attached</div>` :
                    `<div class="mb-3 text-muted"><strong>Files:</strong> No files attached</div>`
                }
            </div>
        `;

        Swal.fire({
            title: "Item Details",
            html: detailsHTML,
            icon: "info",
            confirmButtonText: "Close",
            width: 500,
        });
    });

    $(document).on("click", ".view-file-btn", function () {
        const base64DataUrl = $(this).data("url");
        if (!base64DataUrl) {
            console.error("No file URL found");
            return;
        }
        const arr = base64DataUrl.split(",");
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        let u8arr = new Uint8Array(n);
        while (n--) { u8arr[n] = bstr.charCodeAt(n); }
        const fileBlob = new Blob([u8arr], { type: mime });
        const fileURL = URL.createObjectURL(fileBlob);
        window.open(fileURL, "_blank");
    });
}

let deleteIds = [];

function deleteItem() {
    $(document).on("click", ".delete-item", function (e) {
        e.preventDefault();

        let id = $(this).data("id");
        if (!tempItems) {
            console.error("tempItems array not found");
            return;
        }

        let index = tempItems.findIndex((obj) => obj.id === id);
        if (index === -1) {
            console.warn("Item not found:", id);
            return;
        }

        let itemId = tempItems[index].permit_id;
        if (!deleteIds.includes(itemId)) {
            deleteIds.push(itemId);
        }

        tempItems.splice(index, 1);
        $("#item-card-" + id).remove();
        renderAllItems();

        console.log("Deleted UUID:", id);
        console.log("Deleted item_id:", itemId);
        console.log("Remaining items:", tempItems);
        console.log("Delete item_ids:", deleteIds);

        summarySubmit();
    });
}

function editItem() {
    $(document).on("click", ".edit-item", function (e) {
        e.preventDefault();

        const id = $(this).data("id");
        const item = tempItems.find((obj) => obj.id === id);
        if (!item) return console.warn("Item not found for id:", id);

        editingItemId = id;
        resetAddItemModal();

        const modalEl = document.getElementById("addItemModal");
        bootstrap.Modal.getOrCreateInstance(modalEl).show();

        Swal.fire({
            title: "Loading item...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        // ✅ Now loadConsignmentSelection() returns a Promise, so we can use .then()
        loadConsignmentSelection()
            .then(() => {
                // Handle custom vs normal
                if (item.isCustom) {
                    $("#itemSelect").val("others").trigger("change");
                    $("#customItemWrapper").show();
                    $("#customItemName").val(item.item_name);
                } else {
                    $("#itemSelect").val(item.item_id).trigger("change");
                    $("#customItemWrapper").hide();
                }

                // Load uses and set value
                return loadUses(item.isCustom ? 'others' : item.item_id);
            })
            .then(() => {
                if (item.uses) {
                    $("#itemUses").val(item.uses).trigger("change");
                }
                // Fill other fields
                $("#itemValue").val(item.value);
                $("#itemQuantity").val(item.quantity);
                $("#itemMeasure").val(item.measure).trigger("change");
                $("#itemPurpose").val(item.purpose).trigger("change");

                // Clear Dropzone and add existing files (only if they are File objects)
                if (itemDropzone) {
                    itemDropzone.removeAllFiles(true);
                    if (item.files && item.files.length > 0) {
                        item.files.forEach((file) => {
                            if (file instanceof File) {
                                itemDropzone.addFile(file);
                            }
                        });
                    }
                }

                Swal.close();
            })
            .catch(() => {
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to load item data for editing.",
                });
            });
    });
}

// ─── Copy Item ─────────────────────────────────────────
function copyItem() {
    $(document).on("click", ".copy-item", function (e) {
        e.preventDefault();

        const id = $(this).data("id");
        if (!tempItems) {
            console.error("tempItems array not found");
            return;
        }

        const index = tempItems.findIndex((obj) => obj.id === id);
        if (index === -1) {
            console.warn("Item not found:", id);
            return;
        }

        const original = tempItems[index];
        const duplicated = {
            ...original,
            id: generateUUID(),
            files: [...(original.files || [])],
            permit_id: null, // new item, no permit_id yet
        };

        tempItems.splice(index + 1, 0, duplicated);
        renderAllItems();
        summarySubmit();

        Swal.fire({
            icon: "success",
            title: "Item Copied",
            text: "A duplicate of the item has been added.",
            timer: 1800,
            showConfirmButton: false,
        });
    });
}

// ─── Load Existing Consignments (FIXED) ────────────────────────────
function loadExistingConsignments() {
    if (!application.consignment_permits) return;

    tempItems = [];

    application.consignment_permits.forEach((permit) => {
        let detail = permit.consignment_detail;
        if (typeof detail === "string") {
            try { detail = JSON.parse(detail); } catch (e) { detail = {}; }
        }

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
            isCustom: detail.isCustom || false,   // ✅ added
            existingAttachments: permit.attachments || [],
            files: [],
            newFiles: [],
            deletedAttachmentIds: [],
        });
    });

    renderAllItems();
}

// ============= attachment =====================

// ─── Save Application ──────────────────────────────────────────────
function saveapplication(isDraft = false) {
    const form = document.querySelector("#wizardForm");
    if (!form) return console.error("Form not found");

    const formData = new FormData(form);

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
                isCustom: item.isCustom || false,
            })
        );

        newFiles.forEach((file) => {
            formData.append("files[]", file);
            formData.append("file_item_index[]", index);
        });
    });

    // Send new application-level attachments
    applicationAttachments.forEach((attachment) => {
        if (attachment.file && !attachment.file._isExisting) {
            formData.append("application_files[]", attachment.file);
            formData.append("application_files_document_type[]", attachment.document_type || "");
            formData.append("application_files_description[]", attachment.description || "");
        }
    });

    // Notify backend of deleted attachments
    if (deletedAttachmentIds && deletedAttachmentIds.length > 0) {
        formData.append("deleted_attachment_ids", JSON.stringify(deletedAttachmentIds));
    }

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
$(document).ready(async function () {
    Swal.fire({
        title: "Loading...",
        html: "Please wait while the page initializes.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        fetchExporterList().then(() => {
            if (exporter && exporter.id) {
                $("#selectexp").val(exporter.id).trigger("change");
            }
        });
        handleExporterChange();

        initAddExporterModal();
        initImporterSearch();
        permitDetails();
        itemConsigment();
        saveConsignmentAttachment();
        viewMore();
        deleteItem();
        editItem();
        copyItem();

        importerDetail();
        exporterDetail();
        loadExistingConsignments();
        if (typeof loadExistingApplicationAttachments === "function") {
            loadExistingApplicationAttachments();
            // ─── NOW init Dropzones (after applicationAttachments is populated) ──
            initApplicationAttachments();
            initAttachmentOffcanvas();
            initAttachmentNavigation();
            initItemAttachmentOffcanvas();
            initItemAttachmentNavigation();
        }
        summarySubmit();  // ensure summary is updated after loading

        measurementUnit();

        $("#itemMeasure").select2({
            width: "100%",
            placeholder: "-- Select Measurement Unit --",
            dropdownParent: $("#addItemModal"),
        });

        $("#addexpcountry").select2({
            width: "100%",
            placeholder: "-- Select Country --",
            dropdownParent: $("#addExporterModal"),
        });

        $("#itemPurpose").select2({
            width: "100%",
            placeholder: "-- Select Purpose --",
            dropdownParent: $("#addItemModal"),
        });

        $("#itemPurpose").on("change", function () {
            const selectedOption = $(this).find("option:selected");
            itemPurpose = selectedOption.data("description") || "";
            console.log("Item purpose selected:", itemPurpose);
        });

        $("#itemSelect").on("change", function () {
            const itemId = $(this).val();
            const $itemUses = $("#itemUses");
            $itemUses.empty().append('<option value="">-- Select Uses --</option>');

            if (!itemId) return;

            if (itemId === "others") {
                // Custom item: show custom name input
                $("#customItemWrapper").show();
                // Load global uses (no itemId)
                loadUses('others');
            } else {
                $("#customItemWrapper").hide();
                loadUses(itemId);
                loadDetails(itemId);
            }
        });

        $("#mdlAddItemBtn").on("click", loadConsignmentSelection);

        // Submit button
        $(document).on("click", "#submitApps", function (e) {
            e.preventDefault();
            console.log("Submit clicked!");
            saveapplication(false);
        });

        // Unsaved changes warning
        $(document).on(
            "click",
            `#logoutButton, .app-sidebar.sticky button, .app-sidebar.sticky a, .breadcrumb .breadcrumb-item a`,
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
                        change = false;
                        if (target.tagName === "A") {
                            window.location.href = target.href;
                        } else {
                            target.click();
                        }
                    }
                    if (result.isDenied) {
                        saveapplication(true);
                        window.location.href = "/public/view_import_permit";
                    }
                });
            }
        );

        isInitializing = false;
    } catch (error) {
        console.error("Error during initialization:", error);
        Swal.fire("Error", "Failed to initialize page. Check console for details.", "error");
    } finally {
        Swal.close();
    }
});

// ------------------------------ Summary details ---------------------
export function summarySubmit() {
    const targetTable = document.querySelector("#summaryTable3 tbody");

    impAddrs = importer
        ? [importer.address_1, importer.address_2].filter(x => x && x.trim() !== "").join(", ")
        : "";

    permitDetails = {
        applCate: document.getElementById("app_cate").value,
        eta: document.getElementById("eta").value,
        tranType: document.getElementById("trnptType").value,
        entrypoint: document.getElementById("entryPoint").value,
    };

    document.getElementById("importerName").textContent = importer.fullname;
    document.getElementById("importerPhoneno").textContent = importer.phone_number;
    document.getElementById("simpAdd").textContent = impAddrs;

    document.getElementById("sexpName").textContent = exporter.name;
    if (document.getElementById("expName")) document.getElementById("expName").value = exporter.name;
    document.getElementById("sexpfonno").textContent = exporter.phone_no;
    if (document.getElementById("expfonno")) document.getElementById("expfonno").value = exporter.phone_no;
    document.getElementById("sexpAddress").textContent = exporter.address;
    if (document.getElementById("expAddress")) document.getElementById("expAddress").value = exporter.address;
    document.getElementById("sexpCountry").textContent = exporter.country;
    if (document.getElementById("expCountry")) document.getElementById("expCountry").value = exporter.country;

    document.getElementById("seta").textContent = permitDetails.eta;
    document.getElementById("strty").textContent = permitDetails.tranType;
    document.getElementById("sentryp").textContent = entryName;

    targetTable.innerHTML = "";

    tempItems.forEach((item, index) => {
        let attachmentHTML = `
            <button class="btn btn-icon btn-success-light view-more-item" data-id="${item.id}">
                <i class="ti ti-eye"></i>
            </button>
        `;

        targetTable.insertAdjacentHTML(
            "beforeend",
            `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.item_name || ""}</td>
                    <td>${item.quantity || ""} ${item.measure || ""}</td>
                    <td class="text-wrap">${item.purpose || ""}</td>
                    <td class="text-wrap">${item.uses || ""}</td>
                    <td>RM ${item.value || ""}</td>
                    <td>${attachmentHTML}</td>
                </tr>
            `
        );
    });
}

function loadExistingApplicationAttachments() {
    console.log('[APP-ATTACH] application.attachment:', application.attachment);
    
    // Build a map of docName -> docId from the DOM
    const docMap = {};
    document.querySelectorAll('.application-attachment-dropzone').forEach(el => {
        const docId = el.dataset.docId;
        const docName = el.dataset.docName ? el.dataset.docName.trim() : '';
        if (docId && docName) {
            docMap[docName] = docId;
        }
    });
    console.log('[APP-ATTACH] docMap:', docMap);

    // Clear the global array
    applicationAttachments = [];

    if (application.attachment && application.attachment.length > 0) {
        application.attachment.forEach((a) => {
            const docName = a.description ? a.description.trim() : '';
            const docId = docMap[docName] || null;
            console.log('[APP-ATTACH] Processing attachment:', a.file_name, '| description:', docName, '| matched docId:', docId);
            applicationAttachments.push({
                id: a.id || crypto.randomUUID(),
                file: null,
                name: a.file_name,
                displayName: a.file_name,
                size: a.file_size || 0,
                type: a.file_type || "",
                url: a.file_path,
                document_id: docId,
                document_type: docName || "",
                description: docName || "",
            });
        });

        console.log('[APP-ATTACH] applicationAttachments after load:', applicationAttachments);
    } else {
        console.log('[APP-ATTACH] No attachments found in application.attachment');
    }
}

function initApplicationAttachments() {
    const dropzoneElements = document.querySelectorAll('.application-attachment-dropzone');
    if (!dropzoneElements.length) return;

    // Clear previous instances
    dropzoneInstances = {};

    dropzoneElements.forEach((el) => {
        const docId = el.dataset.docId;
        const docName = el.dataset.docName || '';

        if (!docId) return;

        // Guard: destroy any pre-existing Dropzone instance on this element
        if (el.dropzone) {
            el.dropzone.destroy();
        }

        const dz = new Dropzone(el, {
            url: '/',                          // not used (autoProcessQueue = false)
            autoProcessQueue: false,
            addRemoveLinks: false,
            previewsContainer: false,          // we render our own table
            clickable: true,
            acceptedFiles: '.jpg,.jpeg,.png,.pdf,.doc,.docx',
            maxFilesize: 15,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            init: function() {
                // When a file is added, store it in the global array with doc info
                this.on('addedfile', function(file) {
                    // Check if this is an existing file from the server
                    if (file._isExisting && file._id) {
                        // Find the existing attachment in the array
                        const existing = applicationAttachments.find(a => a.id === file._id);
                        if (existing) {
                            // Reuse existing attachment – just link the file object
                            existing.file = file;
                            file._attachmentId = existing.id;
                            // Render table now that the file is linked
                            renderApplicationAttachmentTable(docId);
                            updateDocFileCountBadge(docId);
                            return;
                        }
                    }

                    // New file (uploaded by user)
                    const attachment = {
                        id: crypto.randomUUID(),
                        file: file,
                        name: file.name,
                        displayName: file.displayName || file.name,
                        size: file.size,
                        type: file.type,
                        document_id: docId,
                        document_type: docName,
                        description: docName,   // default description = document requirement name
                    };
                    file._attachmentId = attachment.id;
                    applicationAttachments.push(attachment);
                    renderApplicationAttachmentTable(docId);
                    updateDocFileCountBadge(docId);
                    updateAttachmentTable();   // summary table
                });

                // Handle file removal
                this.on('removedfile', function(file) {
                    const index = applicationAttachments.findIndex(
                        (a) => a.id === file._attachmentId
                    );
                    if (index !== -1) {
                        applicationAttachments.splice(index, 1);
                        renderApplicationAttachmentTable(docId);
                        updateDocFileCountBadge(docId);
                        updateAttachmentTable();
                    }
                });
            },
            error: function(file, message) {
                console.error('Dropzone error:', message);
                if (file.previewElement) {
                    file.previewElement.remove();
                }
            }
        });

        dropzoneInstances[docId] = dz;

        // ─── Load existing attachments for this document ID ──
        const existingForDoc = applicationAttachments.filter(
            (a) => String(a.document_id) === String(docId)
        );
        existingForDoc.forEach((att) => {
            const dummyFile = createDummyFileFromAttachment(att);
            dz.addFile(dummyFile);
        });

        // Render the table for this document (will show existing files)
        renderApplicationAttachmentTable(docId);
        updateDocFileCountBadge(docId);
    });

    // Update the summary table
    updateAttachmentTable();
}

// ─── Render attachment table for a specific document ────
function renderApplicationAttachmentTable(docId) {
    const $tbody = $(`.application-attachment-table[data-doc-id="${docId}"] tbody`);
    if (!$tbody.length) return;

    $tbody.empty();

    const docAttachments = applicationAttachments.filter(
        (a) => String(a.document_id) === String(docId)
    );

    if (!docAttachments.length) {
        $tbody.append(`
            <tr class="empty-row">
                <td colspan="2" class="text-center text-muted py-2" data-en="No attachments uploaded yet." data-bm="Tiada lampiran dimuat naik lagi.">
                    No attachments uploaded yet.
                </td>
            </tr>
        `);
        return;
    }

    docAttachments.forEach((attachment) => {
        $tbody.append(`
            <tr data-id="${attachment.id}">
                <td class="text-wrap">
                    <a href="#" class="text-decoration-none attachment-name-link" data-id="${attachment.id}">
                        <strong>${attachment.displayName}</strong>
                    </a>
                    <div class="text-muted small">${attachment.name}</div>
                </td>
                <td class="text-end">
                    <button type="button" class="btn btn-icon btn-success-light view-attachment-btn" data-id="${attachment.id}">
                        <i class="ti ti-eye"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-info-light edit-attachment-btn ms-2" data-id="${attachment.id}">
                        <i class="ti ti-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-danger-light ms-2 delete-attachment-btn" data-id="${attachment.id}">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

// ─── Update document file count badge ────────────────────
function updateDocFileCountBadge(docId) {
    const badge = document.querySelector(
        `.doc-file-count[data-doc-id="${docId}"]`
    );
    if (!badge) return;
    const count = applicationAttachments.filter(
        (a) => String(a.document_id) === String(docId)
    ).length;
    badge.textContent = count > 0 ? `${count} file(s)` : "No files";
}

// ─── Remove attachment from its dropzone ────────────────
function removeAttachmentFromDropzone(attachmentId) {
    // Find which dropzone owns this attachment
    const attachment = applicationAttachments.find(a => a.id === attachmentId);
    if (!attachment) return false;

    const docId = attachment.document_id;
    const dz = dropzoneInstances[docId];
    if (!dz) return false;

    const fileIndex = dz.files.findIndex(
        (fileItem) => fileItem._attachmentId === attachmentId
    );
    if (fileIndex === -1) return false;

    const file = dz.files[fileIndex];
    try {
        dz.removeFile(file);
        return true;
    } catch (e) {
        dz.files.splice(fileIndex, 1);
        return true;
    }
}

// ─── Init offcanvas for attachment viewing ──────────────
function initAttachmentOffcanvas() {
    const el = document.getElementById("attachmentOffcanvas");
    if (!el) return;

    attachmentOffcanvas = new bootstrap.Offcanvas(el, {
        backdrop: true,
        keyboard: true,
        scroll: false,
    });

    el.addEventListener("hidden.bs.offcanvas", () => {
        const viewerBody = document.getElementById("attachmentViewer");
        if (viewerBody) {
            const url = viewerBody.dataset.objectUrl;
            if (url) {
                URL.revokeObjectURL(url);
                delete viewerBody.dataset.objectUrl;
            }
            viewerBody.innerHTML = `<div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br>Select an attachment</div>`;
        }
        const detailsBody = document.getElementById("attachmentDetails");
        if (detailsBody) {
            detailsBody.innerHTML = "";
        }
    });
}

// ─── Open attachment viewer ──────────────────────────────
function openAttachmentViewer(attachmentId) {
    const index = applicationAttachments.findIndex(
        (item) => item.id === attachmentId,
    );
    if (index === -1) return;

    const attachment = applicationAttachments[index];
    if (!attachment) return;

    const viewerTitle = document.getElementById("attachmentTitle");
    const viewerCounter = document.getElementById("attachmentCounter");
    const viewerBody = document.getElementById("attachmentViewer");
    const detailsBody = document.getElementById("attachmentDetails");

    if (!viewerTitle || !viewerCounter || !viewerBody || !detailsBody) return;

    currentAttachmentIndex = index;
    viewerTitle.textContent = attachment.displayName;
    viewerCounter.textContent = `${currentAttachmentIndex + 1} / ${applicationAttachments.length}`;
    renderAttachmentPreview(attachment, viewerBody);
    renderAttachmentDetails(attachment, detailsBody);

    document.getElementById("attachmentPrevBtn").disabled =
        currentAttachmentIndex === 0;
    document.getElementById("attachmentNextBtn").disabled =
        currentAttachmentIndex === applicationAttachments.length - 1;

    const editNameInput = document.getElementById("attachmentEditName");
    if (editNameInput) {
        editNameInput.value = attachment.displayName;
    }

    if (attachmentOffcanvas) {
        attachmentOffcanvas.show();
    }
}

function renderAttachmentPreview(attachment, container) {
    const file = attachment.file;
    if (!container) return;

    if (container.dataset.objectUrl) {
        URL.revokeObjectURL(container.dataset.objectUrl);
        delete container.dataset.objectUrl;
    }

    container.innerHTML = "";

    // For server-stored files (no local File object), use the stored URL
    if (!file && attachment.url) {
        const serverUrl = attachment.url;
        const name = (attachment.displayName || attachment.name || '').toLowerCase();
        if (name.endsWith('.pdf') || (attachment.type || '').includes('pdf')) {
            container.innerHTML = `<iframe src="${serverUrl}" class="w-100" style="height:calc(100vh - 220px); border:none;"></iframe>`;
        } else if (name.match(/\.(jpg|jpeg|png|gif|webp)$/) || (attachment.type || '').startsWith('image/')) {
            container.innerHTML = `<img src="${serverUrl}" class="img-fluid rounded" alt="${attachment.displayName}">`;
        } else {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-file-earmark-fill fs-1 text-muted"></i><br>
                    <a href="${serverUrl}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Download / View File</a>
                </div>`;
        }
        return;
    }

    if (!file) {
        container.innerHTML = `<div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br>No preview available</div>`;
        return;
    }

    if (file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
            container.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" alt="${attachment.displayName}">`;
        };
        reader.readAsDataURL(file);
    } else if (
        file.type === "application/pdf" ||
        attachment.name.toLowerCase().endsWith(".pdf")
    ) {
        const url = URL.createObjectURL(file);
        container.innerHTML = `<iframe src="${url}" class="w-100" style="height:calc(100vh - 220px); border:none;"></iframe>`;
        container.dataset.objectUrl = url;
    } else {
        const url = URL.createObjectURL(file);
        container.innerHTML = `
            <div class="text-center">
                <i class="bi bi-file-earmark-fill fs-1 mb-3"></i>
                <p class="mb-2">${attachment.name}</p>
                <a href="${url}" target="_blank" download="${attachment.name}" class="btn btn-sm btn-primary">
                    Download File
                </a>
            </div>
        `;
        container.dataset.objectUrl = url;
    }
}

function renderAttachmentDetails(attachment, container) {
    const fields = [
        { label: "File Name", value: attachment.displayName },
        { label: "Original Name", value: attachment.name },
        { label: "File Size", value: `${attachment.size} bytes` },
        { label: "File Type", value: attachment.type || "Unknown" },
    ];
    container.innerHTML = fields
        .map(
            (field) => `
                <div class="mb-3">
                    <strong>${field.label}:</strong>
                    <div class="text-muted">${field.value}</div>
                </div>
            `,
        )
        .join("");
}

function initAttachmentNavigation() {
    $(document).on("click", "#attachmentPrevBtn", function () {
        if (currentAttachmentIndex > 0) {
            const nextId =
                applicationAttachments[currentAttachmentIndex - 1]?.id;
            if (nextId) openAttachmentViewer(nextId);
        }
    });

    $(document).on("click", "#attachmentNextBtn", function () {
        if (currentAttachmentIndex < applicationAttachments.length - 1) {
            const nextId =
                applicationAttachments[currentAttachmentIndex + 1]?.id;
            if (nextId) openAttachmentViewer(nextId);
        }
    });
}

$(document).on("click", ".view-attachment-btn", function () {
    const attachmentId = $(this).data("id");
    openAttachmentViewer(attachmentId);
});

$(document).on("click", ".attachment-name-link", function (e) {
    e.preventDefault();
    const attachmentId = $(this).data("id");
    openAttachmentViewer(attachmentId);
});

$(document).on("click", ".edit-attachment-btn", function () {
    const attachmentId = $(this).data("id");
    const attachment = applicationAttachments.find(
        (item) => item.id === attachmentId,
    );
    if (!attachment) return;
    const newName = prompt("Edit file name:", attachment.displayName);
    if (!newName) return;
    attachment.displayName = newName.trim();
    // Re-render all tables
    const docId = attachment.document_id;
    renderApplicationAttachmentTable(docId);
    updateDocFileCountBadge(docId);
    updateAttachmentTable();
});

$(document).on("click", ".delete-attachment-btn", function () {
    const attachmentId = $(this).data("id");
    const index = applicationAttachments.findIndex(
        (item) => item.id === attachmentId,
    );
    if (index === -1) return;

    const docId = applicationAttachments[index].document_id;

    // Track for backend deletion
    if (applicationAttachments[index].url && !deletedAttachmentIds.includes(attachmentId)) {
        deletedAttachmentIds.push(attachmentId);
    }

    removeAttachmentFromDropzone(attachmentId);
    applicationAttachments.splice(index, 1);
    renderApplicationAttachmentTable(docId);
    updateDocFileCountBadge(docId);
    updateAttachmentTable();

    Swal.fire({
        icon: "success",
        title: "Attachment removed",
        timer: 1000,
        showConfirmButton: false,
    });
});

$(document).on("click", "#attachmentSaveNameBtn", function () {
    const newName = document.getElementById("attachmentEditName").value.trim();

    if (!newName) {
        Swal.fire({
            icon: "warning",
            title: "Empty Name",
            text: "Please enter a file name",
        });
        return;
    }

    if (
        currentAttachmentIndex >= 0 &&
        currentAttachmentIndex < applicationAttachments.length
    ) {
        const attachment = applicationAttachments[currentAttachmentIndex];
        attachment.displayName = newName;

        const docId = attachment.document_id;
        renderApplicationAttachmentTable(docId);
        updateDocFileCountBadge(docId);
        renderAttachmentDetails(
            attachment,
            document.getElementById("attachmentDetails"),
        );

        Swal.fire({
            icon: "success",
            title: "Saved",
            text: "File name updated successfully",
            timer: 1500,
            showConfirmButton: false,
        });

        document.getElementById("attachmentEditName").value = "";
        updateAttachmentTable();
    }
});

// ─── Item Attachment Offcanvas ────────────────────────────
function initItemAttachmentOffcanvas() {
    const el = document.getElementById("itemAttachmentOffcanvas");
    if (!el) return;

    itemAttachmentOffcanvas = new bootstrap.Offcanvas(el, {
        backdrop: true,
        keyboard: true,
        scroll: false,
    });

    el.addEventListener("hidden.bs.offcanvas", () => {
        const viewerBody = document.getElementById("itemAttachViewer");
        if (viewerBody) {
            const url = viewerBody.dataset.objectUrl;
            if (url) {
                URL.revokeObjectURL(url);
                delete viewerBody.dataset.objectUrl;
            }
            viewerBody.innerHTML = `<div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br>Select an attachment</div>`;
        }
        const detailsBody = document.getElementById("itemAttachDetails");
        if (detailsBody) {
            detailsBody.innerHTML = "";
        }
        currentItemAttachIndex = 0;
    });
}

function openItemAttachmentViewer(files, startIndex = 0) {
    if (!files || files.length === 0) return;

    currentItemAttachments = files;
    currentItemAttachIndex = startIndex;

    showItemAttachment(files[startIndex], startIndex);

    if (itemAttachmentOffcanvas) {
        itemAttachmentOffcanvas.show();
    }
}

function showItemAttachment(file, index) {
    const viewerTitle = document.getElementById("itemAttachmentTitle");
    const viewerCounter = document.getElementById("itemAttachCounter");
    const viewerBody = document.getElementById("itemAttachViewer");
    const detailsBody = document.getElementById("itemAttachDetails");

    if (!viewerTitle || !viewerCounter || !viewerBody || !detailsBody) return;

    const displayName = file.displayName || file.name;

    currentItemAttachIndex = index;
    viewerTitle.textContent = displayName;
    viewerCounter.textContent = `${index + 1} / ${currentItemAttachments.length}`;

    if (viewerBody.dataset.objectUrl) {
        URL.revokeObjectURL(viewerBody.dataset.objectUrl);
        delete viewerBody.dataset.objectUrl;
    }
    viewerBody.innerHTML = "";

    if (file.type && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
            viewerBody.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" alt="${displayName}">`;
        };
        reader.readAsDataURL(file);
    } else if (
        file.type === "application/pdf" ||
        (file.name && file.name.toLowerCase().endsWith(".pdf"))
    ) {
        const url = URL.createObjectURL(file);
        viewerBody.innerHTML = `<iframe src="${url}" class="w-100" style="height: calc(100vh - 220px); border: none;"></iframe>`;
        viewerBody.dataset.objectUrl = url;
    } else {
        const url = URL.createObjectURL(file);
        viewerBody.innerHTML = `
            <div class="text-center">
                <i class="bi bi-file-earmark-fill fs-1 mb-3"></i>
                <p class="mb-2">${displayName}</p>
                <a href="${url}" target="_blank" download="${file.name}" class="btn btn-sm btn-primary">
                    Download File
                </a>
            </div>
        `;
        viewerBody.dataset.objectUrl = url;
    }

    const fields = file.displayName
        ? [
              { label: "File Name", value: file.displayName },
              { label: "Original Name", value: file.name },
              {
                  label: "File Size",
                  value: (file.size / 1024).toFixed(2) + " KB",
              },
              { label: "File Type", value: file.type || "Unknown" },
          ]
        : [
              { label: "File Name", value: file.name },
              {
                  label: "File Size",
                  value: (file.size / 1024).toFixed(2) + " KB",
              },
              { label: "File Type", value: file.type || "Unknown" },
          ];
    detailsBody.innerHTML = fields
        .map(
            (field) => `
                <div class="mb-3">
                    <strong>${field.label}:</strong>
                    <div class="text-muted">${field.value}</div>
                </div>
            `,
        )
        .join("");

    document.getElementById("itemAttachPrevBtn").disabled = index === 0;
    document.getElementById("itemAttachNextBtn").disabled =
        index === currentItemAttachments.length - 1;

    const editNameInput = document.getElementById("itemAttachEditName");
    if (editNameInput) {
        editNameInput.value = displayName;
    }
}

function initItemAttachmentNavigation() {
    $(document).on("click", "#itemAttachPrevBtn", function () {
        if (currentItemAttachIndex > 0 && currentItemAttachments.length > 0) {
            showItemAttachment(
                currentItemAttachments[currentItemAttachIndex - 1],
                currentItemAttachIndex - 1,
            );
        }
    });

    $(document).on("click", "#itemAttachNextBtn", function () {
        if (
            currentItemAttachIndex < currentItemAttachments.length - 1 &&
            currentItemAttachments.length > 0
        ) {
            showItemAttachment(
                currentItemAttachments[currentItemAttachIndex + 1],
                currentItemAttachIndex + 1,
            );
        }
    });

    $(document).on("click", ".ipv-attach-chip", function () {
        const index = $(this).data("index");
        if (currentItemAttachments.length > 0 && index !== undefined) {
            openItemAttachmentViewer(currentItemAttachments, index);
        }
    });

    $(document).on("click", "#itemAttachSaveNameBtn", function () {
        const newName = document
            .getElementById("itemAttachEditName")
            .value.trim();

        if (!newName) {
            Swal.fire({
                icon: "warning",
                title: "Empty Name",
                text: "Please enter a file name",
            });
            return;
        }

        if (
            currentItemAttachIndex >= 0 &&
            currentItemAttachIndex < currentItemAttachments.length
        ) {
            const file = currentItemAttachments[currentItemAttachIndex];
            file.displayName = newName;

            document.getElementById("itemAttachmentTitle").textContent =
                newName;

            const detailsBody = document.getElementById("itemAttachDetails");
            const fields = [
                { label: "File Name", value: newName },
                { label: "Original Name", value: file.name },
                {
                    label: "File Size",
                    value: (file.size / 1024).toFixed(2) + " KB",
                },
                { label: "File Type", value: file.type || "Unknown" },
            ];
            detailsBody.innerHTML = fields
                .map(
                    (field) => `
                        <div class="mb-3">
                            <strong>${field.label}:</strong>
                            <div class="text-muted">${field.value}</div>
                        </div>
                    `,
                )
                .join("");

            Swal.fire({
                icon: "success",
                title: "Saved",
                text: "File name updated successfully",
                timer: 1500,
                showConfirmButton: false,
            });
        }
    });
}

function updateAttachmentTable() {
    const attachmentTable = document.querySelector(
        "#summaryAttachmentTable tbody",
    );
    if (attachmentTable) {
        attachmentTable.innerHTML = "";

        if (!applicationAttachments || applicationAttachments.length === 0) {
            attachmentTable.insertAdjacentHTML(
                "beforeend",
                `
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">
                        No attachments uploaded
                    </td>
                </tr>
                `,
            );
        } else {
            applicationAttachments.forEach((attachment, index) => {
                const size = attachment.size || 0;
                let sizeDisplay = "";
                if (size < 1024) {
                    sizeDisplay = size + " B";
                } else if (size < 1024 * 1024) {
                    sizeDisplay = (size / 1024).toFixed(1) + " KB";
                } else {
                    sizeDisplay = (size / (1024 * 1024)).toFixed(1) + " MB";
                }

                const type = attachment.type || "";
                let typeDisplay = "Unknown";
                if (type.includes("pdf")) {
                    typeDisplay = "PDF";
                } else if (
                    type.includes("image") ||
                    type.includes("jpg") ||
                    type.includes("jpeg") ||
                    type.includes("png")
                ) {
                    typeDisplay = "Image";
                } else if (type.includes("word") || type.includes("doc")) {
                    typeDisplay = "Document";
                }

                attachmentTable.insertAdjacentHTML(
                    "beforeend",
                    `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="text-wrap">${attachment.displayName || attachment.name || ""}</td>
                        <td>${sizeDisplay}</td>
                        <td>${typeDisplay}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-primary view-attachment-btn" data-id="${attachment.id}">
                                View More
                            </button>
                        </td>
                    </tr>
                    `,
                );
            });
        }
    }
}
