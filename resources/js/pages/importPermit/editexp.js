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

    if (!itemId) return;

    Swal.fire({
        title: "Loading...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    const url = itemId === 'others' ? '/public/consignment_uses' : `/public/consignment_uses/${itemId}`;
    fetch(url)
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
    itemDropzone = new Dropzone("#itemDropzone", {
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
            files: permit.attachments || [],
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