import $ from "jquery";
import Swal from "sweetalert2";
import { formatTime, getCountry, getEntryPoint } from "../../app";
let application = null;
let value = null;
import "dropzone/dist/dropzone.css";
// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";
import { initApplicationDetails } from "./application_detail";

Dropzone.autoDiscover = false;

// Global state
let exporterListArray = [];
let entryName = null;
let exporter = null;
let importer = null;
let impAddrs = null;
let itemDropzone = null;
let saveBtn = null;

let change = null;

let tempItems = [];
let tempAttachments = [];
let itemPurpose = null;
let temporaryItemsAttachment = [];


/* -------------------------------
Get application ID from URL
-------------------------------- */

async function loadConsignmentSelection(selectedItemId = null) {
    const countryCode = $("#expcountryCode").val();
    const $select = $("#itemSelect");

    if (!countryCode) return;

    // Reset select
    $select.empty().append('<option value="">-- Select Item --</option>');

    // Destroy Select2 if exists
    if ($select.hasClass("select2-hidden-accessible")) {
        $select.select2("destroy");
    }

    $select.prop("disabled", true);

    Swal.fire({
        title: "Loading...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const res = await fetch(`${window.baseUrl}/public/get_consignment/${countryCode}`);
        const data = await res.json();

        $select.prop("disabled", false);

        data.forEach(row => {
            $select.append(
                `<option value="${row.id}">${row.entry_display}</option>`
            );
        });

        // Init Select2
        $select.select2({
            width: "100%",
            placeholder: "-- Select Item --",
            allowClear: true,
            dropdownParent: $("#addItemModal"),
        });


        if (selectedItemId) {
            $select.val(String(selectedItemId)).trigger("change");
        }

        Swal.close();
    } catch (e) {
        console.error("Error loading items:", e);
        $select.prop("disabled", false);
        Swal.close();
    }
}


function loadUses(itemId) {
    const $select = $("#itemUses");

    $select
        .empty()
        .append('<option value="">-- Select Uses --</option>');

    if (!itemId) return;

    Swal.fire({
        title: "Loading...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    fetch(`${window.baseUrl}/public/consignment_uses/${itemId}`)
        .then(res => res.json())
        .then(data => {
            // SUPPORT BOTH RESPONSE TYPES
            const uses = data.data ?? data;

            if (!Array.isArray(uses)) return;

            uses.forEach(use => {
                $select.append(`<option value="${use}">${use}</option>`);
            });

            Swal.close();
        })
        .catch(err => {
            console.error("Failed to load uses:", err);
            Swal.close();
        });
}



function reapply(application) {
    $(document).on("click", ".reapply", async function (e) {
        e.preventDefault();

        const id = $(this).data("permit");
        const permits = application.consignment_permits;
        const permit = permits.find(p => p.id == id);

        if (!permit) {
            console.warn("Permit not found!");
            return;
        }

        $('#saveBtn')
            .data('id', id)
            .attr('data-id', id);

  

        const selectedItemId = detail.item_id;

        await loadConsignmentSelection(selectedItemId);
       
        // Show modal AFTER select is ready
        const modalEl = document.getElementById("addItemModal");
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
}


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

function reapply_consignment()
{
    $(document).on('click', function(e) {
        e.preventDefault();

        
    })
}

function saveConsignmentAttachment() {
    document.getElementById("saveBtn").addEventListener("click", function (e) {
        e.preventDefault();

        console.log("Saving consignment item...");

        // ✅ Get values (Select2 fields via jQuery)
        const itemSelectValue = $("#itemSelect").val();
        const itemSelectText = $("#itemSelect option:selected").text();
        const itemValue = $("#itemValue").val().trim();
        const itemQuantity = $("#itemQuantity").val().trim();
        const itemMeasure = $("#itemMeasure").val();
        const itemPurpose = $("#itemPurpose").val();
        const itemUsesValue = $("#itemUses").val();

        // ✅ Validation
        if (
            !itemSelectValue ||
            !itemValue ||
            !itemQuantity ||
            !itemMeasure ||
            !itemPurpose ||
            !itemUsesValue
        ) {
            Swal.fire({
                icon: "error",
                title: "Incomplete Data",
                text: "Please fill in all required fields before saving.",
            });
            return;
        }

        // ✅ Get files from Dropzone
        const files = itemDropzone.getAcceptedFiles();
        const itemPurposeDescription = $("#itemPurpose option:selected").data("description") || $("#itemPurpose").val();

        // ✅ Build new item
        const newItem = {
            id: crypto.randomUUID(),
            item_id: itemSelectValue,
            item_name: itemSelectText,
            value: itemValue,
            quantity: itemQuantity,
            measure: itemMeasure,
            purpose: itemPurposeDescription,
            uses: itemUsesValue,
            files: files,
        };

        // ✅ Add to temporary array
        tempItems.push(newItem);

        initApplicationDetails()

        resetAddItemModal();

        // ✅ Hide modal
        const modalEl = document.getElementById("addItemModal");
        bootstrap.Modal.getInstance(modalEl).hide();

        // ✅ Trigger summary / submit update if needed
        summarySubmit();
    });
}

function resetAddItemModal() {
    // Reset plain input fields
    $("#itemValue").val("");
    $("#itemQuantity").val("");

    // Reset Select2 fields
    $("#itemSelect").val(null).trigger("change");
    $("#itemMeasure").val("").trigger("change");
    $("#itemPurpose").val("").trigger("change");
    $("#itemUses").val(null).trigger("change");

    // Clear Dropzone files
    if (itemDropzone) itemDropzone.removeAllFiles(true);
}


export async function application_reapply(application)
{
   

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
    $("#mdlAddItemBtn").on("click", async function () {
        await loadConsignmentSelection();
        saveConsignmentAttachment();

        
        let detail = application.detail
        if (typeof detail === 'string') {
            detail = JSON.parse(detail);
        }

        $('#itemValue').val(detail?.value ?? '');
        $('#itemQuantity').val(detail?.quantity ?? '');
    });

    console.log('from js application reapply', application)
    await loadConsignmentSelection()
    reapply(application)
    itemConsigment()
}