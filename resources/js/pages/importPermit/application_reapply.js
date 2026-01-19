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

Dropzone.autoDiscover = false;

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


/* -------------------------------
Get application ID from URL
-------------------------------- */

async function loadConsignmentSelection() {
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

    fetch(`${window.baseUrl}/public/get_consignment/${countryCode}`)
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
            // Swal.fire("Error", "Failed to load consignment items.", "error");
        });
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



function reapply(application)
{
    $(document).on("click", ".reapply", function (e) {
    e.preventDefault();

    let id = $(this).data("permit");
    let permits = application.consignment_permits;

    let permit = permits.find((p) => p.id == id);

    if (!permit) {
        console.warn("Permit not found!");
        return;
    }

    let attachments = permit.attachments || [];

    let detail;
    try {
        // detail = JSON.parse(permit.consignment_detail);
        detail = permit.consignment_detail;
    } catch (err) {
        console.error(
            "Invalid JSON in consignment_detail:",
            permit.consignment_detail
        );
    }

    console.log("FOUND PERMIT:", permit);
    console.log("attachments", attachments);

   

    // Modal
    const modalEl = document.getElementById("addItemModal");
 

    const modal = new bootstrap.Modal(modalEl);
    modal.show();

   

});
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
});

    console.log('from js application reapply', application)
    await loadConsignmentSelection()
    reapply(application)
}