import Dropzone from "dropzone";
import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import { applyTranslations, generateUUID, getAuthUser } from "../../app";
import "dropzone/dist/dropzone.css";

// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

Dropzone.autoDiscover = false;

// ------------------------- Global state -------------------------
let exporterListArray = [];
let entryName = null;
let exporter = null;
let importer = null;
let impAddrs = null;
let itemDropzone = null;

let change = false;

let tempItems = [];
let tempAttachments = [];
let itemPurpose = null;
let currentItemFile = null;
let itemFileOffcanvas = null;

let temporaryItemsAttachment = [];
let currentItemAttachments = []; // for the item details offcanvas
let currentItemAttachIndex = 0; // for navigation inside item attachment viewer
let itemAttachmentOffcanvas = null; // offcanvas instance for item attachments

// ---- Application-level attachments (added) ----
let applicationAttachments = [];
let applicationDropzones = {}; // keyed by doc.id

// ---- Unified attachment offcanvas (shared by application attachments AND
// item attachments — one piece of markup, one viewer for both) ----
let attachmentOffcanvas = null;
let currentAttachmentList = []; // normalized attachments currently being paged through
let currentAttachmentIndex = 0;
let currentAttachmentSource = null; // "application" | "item"

// Holds the normalized attachment list for whichever item's details are
// currently open in #ItemDetailsModal, so the "View" buttons in that modal
// know what to hand off to the shared offcanvas.
let pendingItemAttachmentList = [];

let editingItemId = null;

// --------if self apply -----------
async function selfImport() {
    if (
        window.location.pathname.includes(
            "public/inspection_certificates_application_self",
        )
    ) {
        importer = await getAuthUser();
        console.log("importer", importer);
    }
}

// ------------------------- Exporter List -------------------------
function fetchExporterList() {
    const $select = $("#selectexp");
    const url = "/public/get_exporters";

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
                $select.append(
                    `<option value="${exp.id}">${exp.name}</option>`,
                ),
            );

            if ($select.hasClass("xintra-select2")) {
                $select.trigger("change.select2");
            }

            $select.select2({
                width: "100%",
                placeholder: "-- Select Exporter --",
                allowClear: true,
            });
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
        summarySubmit();
    });
}

function clearExporterFields() {
    $("#expid, #expname, #expfonno, #expaddress1, #expaddress2").val("");
    $("#expcountryCode, #expcountry").val("");
}

// ------------------------- Consignment / Uses -------------------------
function loadConsignmentSelection() {
    // This function is intentionally left empty because itemSelect is a free-text field.
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

    fetch(`/public/all_consignment_uses/`)
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

        const name = $("#addexpName").val().trim();
        const phone_no = $("#addexpfonno").val().trim();
        const address1 = $("#addexpaddress1").val().trim();
        const address2 = $("#addexpaddress2").val().trim();
        const full_address = `${address1} ${address2}`;
        const country = $("#addexpcountry").val();

        if (!name || !phone_no || !country) {
            return Swal.fire("⚠️ Please fill in all required fields.");
        }

        Swal.fire({
            title: "Saving exporter...",
            text: "Please wait",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: "/public/store_exporter",
            method: "POST",
            data: {
                name,
                phone_no,
                address: full_address,
                country,
            },
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
                });

                modal.hide();

                $("#addexpcountry").val("").trigger("change");
                $("#addExporterForm")[0].reset();
            },
            error: (xhr) => {
                console.error(xhr.responseText);
                Swal.fire({
                    icon: "error",
                    title: "Failed!",
                    text: "Failed to save exporter. Please try again.",
                });
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
            Swal.fire({ icon: "warning", title: "Identity number is empty!" });
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
    const hideAll = () => {
        $("#searchresult, #doanotver, #emailnotver").hide();
        $(
            "#impname, #impid, #impfonno, #impaddress1, #impaddress2, #imp_id, #impemail",
        ).val("");
    };

    importer = null;
    importer = data.data;

    if (data.status !== "success") return hideAll();

    $("#searchresult, #doanotver, #emailnotver").hide();
    $("#impname").val(data.data.fullname);
    $("#impid").val(data.data.id);
    $("#impfonno").val(data.data.phone_number);
    $("#impaddress1").val(data.data.address_1);
    $("#impaddress2").val(data.data.address_2);
    $("#imp_id").val(data.data.id);
    $("#impemail").val(data.data.email);

    summarySubmit();
}

// -------------------------Permit details ------------------------
function loadEntryPoints(type, route, onLoaded) {
    if (!type || !route || route === "#") {
        $("#entryPoint").html(
            '<option value="">-- Select Entry Point --</option>',
        );
        if (onLoaded) onLoaded();
        return;
    }

    const url = `${route}?type=${encodeURIComponent(type)}`;

    $.ajax({
        url: url,
        type: "GET",
        dataType: "json",
        success: function (data) {
            Swal.close();
            let options = '<option value="">-- Select Entry Point --</option>';
            data.forEach(function (item) {
                options += `<option value="${item.id}" data-entry_name="${item.entry_display}">${item.entry_display}</option>`;
            });
            $("#entryPoint").html(options);

            if (onLoaded) onLoaded();
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error loading entry points:", error);
            Swal.close();
            Swal.fire("Error", "Failed to load entry points.", "error");
        },
    });
}

function permitDetails() {
    const inputs = ["#eta", "#trnptType"];
    inputs.forEach((id) => {
        $(document).on("change blur", id, function () {
            summarySubmit();
        });
    });

    const trnptType = document.getElementById("trnptType");
    const detailsSelect = document.getElementById("transportDetails");

    if (!trnptType) return;

    trnptType.addEventListener("change", function () {
        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        const value = this.value;
        const route = this.dataset.route;

        loadEntryPoints(value, route);
    });

    $("#entryPoint").on("change", function (e) {
        e.preventDefault();
        entryName = $(this).find("option:selected").data("entry_name");
        summarySubmit();
    });

    const etaInput = document.getElementById("eta");
    if (etaInput) {
        const today = new Date().toISOString().split("T")[0];
        etaInput.setAttribute("min", today);

        etaInput.addEventListener("change", function () {
            const selectedDate = new Date(this.value);
            const todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);

            if (selectedDate < todayDate) {
                Swal.fire({
                    icon: "warning",
                    title: "Invalid Date",
                    text: "Expected Inspection Date cannot be a past date. Please select today or a future date.",
                });
                this.value = "";
                this.classList.add("is-invalid");
            } else {
                this.classList.remove("is-invalid");
            }
        });
    }
}

// ============= item attachment (dropzone) =====================
function itemConsigment() {
    itemDropzone = new Dropzone("#itemDropzone", {
        url: "/",
        autoProcessQueue: false,
        paramName: "file",
        maxFilesize: 10,
        acceptedFiles: ".jpg,.jpeg,.png,.pdf",
        addRemoveLinks: true,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        processing: function (file) {
            Swal.fire({
                title: "Uploading...",
                html: "Please wait while your file is being uploaded.",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
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
                text:
                    message.error || "An unknown error occurred during upload.",
                footer: "Please try again.",
            });
            console.error("Dropzone Error:", message);
        },
        removedfile: function (file) {
            if (file.temp_id) {
                const indexToRemove = tempAttachments.findIndex(
                    (a) => a.id === file.temp_id,
                );
                if (indexToRemove > -1)
                    tempAttachments.splice(indexToRemove, 1);
            }
            const _ref = file.previewElement;
            if (_ref) _ref.parentNode.removeChild(_ref);

            groupPreview();
        },
    });

    itemDropzone.on("addedfile", function (file) {
        currentItemFile = file;
        showItemFilePreview(file);
        setTimeout(() => {
            addPreviewButtons(file);
        }, 100);
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
                    const $img = $preview.find(
                        ".dz-image img[data-dz-thumbnail]",
                    );
                    $img.attr("src", "/images/pdf-logo.png");
                    $img.css({
                        "object-fit": "contain",
                        width: "100%",
                        height: "100%",
                    });
                }
            }

            $deleteBtns.html('<i class="ti ti-trash"></i>');

            Swal.close();
        }, 100);
    });
}

// ============= Item declaration & agreement (added) =====================
/**
 * Shows a per-item declaration dialog the user must tick before the item
 * is accepted into tempItems. Mirrors registerexp.js's showItemAgreement,
 * without the bilingual scaffolding (inspection.js is English-only).
 * @param {object} item - the item payload about to be saved
 * @returns {Promise<boolean>} true if the user agreed and confirmed
 */
async function showItemAgreement(item) {
    const now = new Date();
    const timestamp = now.toLocaleString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });

    const result = await Swal.fire({
        title: "Item Declaration",
        width: 600,
        html: `
            <div style="text-align: left;">
                <p class="mb-3">
                    I confirm that the information provided for this item
                    <strong>"${item.item_name}"</strong> is accurate and complete.
                </p>
                <p class="mb-3">
                    I understand that any false declaration may result in rejection
                    of the application or permit cancellation.
                </p>
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="itemAgreeCheckbox">
                    <label class="form-check-label" for="itemAgreeCheckbox">
                        I agree to the above declaration
                    </label>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Confirm",
        cancelButtonText: "Cancel",
        allowOutsideClick: false,
        preConfirm: () => {
            const checked =
                document.getElementById("itemAgreeCheckbox").checked;
            if (!checked) {
                Swal.showValidationMessage(
                    "Please check the agreement box to continue.",
                );
                return false;
            }
            return true;
        },
    });

    if (result.isConfirmed) {
        item.agreedAt = timestamp;
        return true;
    }

    return false;
}

/**
 * Full terms-and-conditions dialog shown once before final submission.
 * The confirm checkbox only unlocks once the user scrolls to the bottom,
 * mirroring registerexp.js's showFinalAgreement.
 * @returns {Promise<boolean>}
 */
async function showFinalAgreement() {
    let agreed = false;

    const result = await Swal.fire({
        title: "Declaration & Agreement",
        width: 900,
        html: `
            <div id="agreementScroll" style="height:350px; overflow-y:auto; text-align:left; border:1px solid #ddd; padding:15px; border-radius:8px;">
                <h5 class="mb-3">Terms and Conditions</h5>
                <p>1. The applicant declares that all information provided in this application, including item descriptions, quantities, and values, is true, accurate, and complete.</p>
                <p>2. The applicant confirms that all goods declared are compliant with current import regulations and standards enforced by the relevant authority.</p>
                <p>3. The applicant acknowledges that all consignments are subject to physical inspection, verification, and sampling by authorized officers at any designated entry point.</p>
                <p>4. The applicant agrees to maintain all supporting documentation (invoices, certificates, permits) for audit purposes for the period required by law.</p>
                <p>5. The authority reserves the right to suspend or revoke any permit or application if information provided is found to be false, misleading, or fraudulent, and legal action may be taken accordingly.</p>
                <p><strong>END OF DECLARATION</strong></p>
            </div>
            <div class="form-check mt-3 text-start">
                <input class="form-check-input" type="checkbox" id="agreeCheckbox" disabled>
                <label class="form-check-label" for="agreeCheckbox">
                    I have read and agree to all conditions.
                </label>
            </div>
        `,
        didOpen: () => {
            const scrollBox = document.getElementById("agreementScroll");
            const checkbox = document.getElementById("agreeCheckbox");
            scrollBox.addEventListener("scroll", () => {
                const reachedBottom =
                    scrollBox.scrollTop + scrollBox.clientHeight >=
                    scrollBox.scrollHeight - 10;
                if (reachedBottom) {
                    checkbox.disabled = false;
                }
            });
        },
        preConfirm: () => {
            const checked = document.getElementById("agreeCheckbox").checked;
            if (!checked) {
                Swal.showValidationMessage(
                    "Please read the declaration and tick the agreement checkbox.",
                );
                return false;
            }
            agreed = true;
            return true;
        },
        allowOutsideClick: false,
        showCancelButton: true,
        confirmButtonText: "I Agree",
        cancelButtonText: "Cancel",
    });

    return result.isConfirmed && agreed;
}

// ============= Save consignment item (MODIFIED: now requires agreement) =====================
function saveConsignmentAttachment() {
    document
        .getElementById("saveBtn")
        .addEventListener("click", async function (e) {
            e.preventDefault();

            console.log("Saving consignment item...");

            const itemSelectValue = $("#itemSelect").val().trim();
            const itemSelectText = itemSelectValue; // free text = both ID and name
            const itemValue = $("#itemValue").val().trim();
            const itemQuantity = $("#itemQuantity").val().trim();
            const itemMeasure = $("#itemMeasure").val();
            const itemPurposeValue = $("#itemPurpose").val();
            const itemUsesValue = $("#itemUses").val();

            console.log(
                "items",
                itemSelectValue,
                itemSelectText,
                itemValue,
                itemQuantity,
                itemMeasure,
                itemPurposeValue,
                itemUsesValue,
            );

            if (
                !itemSelectValue ||
                !itemValue ||
                !itemQuantity ||
                !itemMeasure ||
                !itemPurposeValue ||
                !itemUsesValue
            ) {
                Swal.fire({
                    icon: "error",
                    title: "Incomplete Data",
                    text: "Please fill in all required fields before saving.",
                });
                return;
            }

            const files = itemDropzone.getAcceptedFiles();
            const itemPurposeDescription =
                $("#itemPurpose option:selected").data("description") ||
                $("#itemPurpose").val();

            const newItem = {
                id: generateUUID(),
                item_id: itemSelectValue,
                item_name: itemSelectText,
                value: itemValue,
                quantity: itemQuantity,
                measure: itemMeasure,
                purpose: itemPurposeDescription,
                uses: itemUsesValue,
                files: files,
                agreedAt: null,
            };

            // ✅ Require the user to confirm the item declaration before saving
            const agreed = await showItemAgreement(newItem);
            if (!agreed) {
                return;
            }

            tempItems.push(newItem);
            editingItemId = null; // reset editing state
            renderAllItems();
            resetAddItemModal();

            document
                .getElementById("addItemModal")
                .addEventListener("hidden.bs.modal", function () {
                    editingItemId = null;
                });

            summarySubmit();
        });
}

function resetAddItemModal() {
    $("#itemValue").val("");
    $("#itemQuantity").val("");

    $("#itemMeasure").val("").trigger("change");
    $("#itemPurpose").val("").trigger("change");
    $("#itemUses").val(null).trigger("change");

    $("#itemSelect").val(""); // instead of .val(null).trigger("change")

    if (itemDropzone) itemDropzone.removeAllFiles(true);
}

function renderAllItems() {
    const tableBody = document.querySelector("#itemListTbl tbody");
    tableBody.innerHTML = "";

     tempItems.forEach((item, index) => {
        tableBody.insertAdjacentHTML(
            "beforeend",
            `<tr id="item-row-${item.id}">
                <td class="text-wrap">${item.item_name}</td>
                <td class="text-wrap">${item.purpose}</td>
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
                        <button class="btn btn-icon btn-info-light edit-item"
                            data-id="${item.id}">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-icon btn-secondary-light copy-item"
                            data-id="${item.id}">
                            <i class="ti ti-copy"></i>
                        </button>
                    </div>
                </td>
            </tr>`,
        );
    });
    const hasItemsInput = document.getElementById("hasItems");
    if (hasItemsInput) {
        hasItemsInput.value = tempItems.length > 0 ? "true" : "";
    }
}

// ============= Item details / attachments viewer (MODIFIED: uses offcanvas) =====================
function viewMore() {
    $(document).on("click", ".view-more-item", function (e) {
        e.preventDefault();

        let id = $(this).data("id");
        if (!tempItems) return console.error("tempItems array not found");

        let item = tempItems.find((obj) => obj.id === id);
        if (!item) return console.warn("Item not found for id:", id);

        const detailsDiv = document.getElementById("itemDetailsInfo");
        const attachList = document.getElementById("pdAttachList");
        const attachmentCount = document.getElementById("attachmentCount");
        const conditionItem = document.getElementById("pdConditionItem");
        const conditionCount = document.getElementById("pdConditionCount");

        console.log("item added previously", item, tempItems);

        const agreementBanner = item.agreedAt
            ? `<div class="alert alert-success mb-3 d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>
                    <strong>
                        <span data-en="Declaration Confirmed" data-bm="Pengisytiharan Disahkan">Declaration Confirmed</span>
                    </strong>
                    <div class="small text-muted">
                        <span data-en="Agreed on:" data-bm="Dipersetujui pada:">Agreed on:</span> ${item.agreedAt}
                    </div>
                </div>
            </div>`
            : `<div class="alert alert-warning mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>
                    <span data-en="Pending Agreement" data-bm="Menunggu Persetujuan">Pending Agreement</span>
                </strong>
                - <span data-en="User has not confirmed this item yet." data-bm="Pengguna belum mengesahkan item ini lagi.">User has not confirmed this item yet.</span>
            </div>`;

        detailsDiv.innerHTML = `
            ${agreementBanner}

            <div class="pd-section-label mt-4 mb-2" data-en="Consignment Info" data-bm="Info Konsainan">Consignment Info</div>
            <div class="p-2 row" style="background: var(--gray-1); border: 1px solid var(--default-border); border-radius: 0.6rem;">
                <div class="col-12 col-lg-6">
                    <p>
                        <strong class="me-1">
                            <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-tag"></i></span>
                            <span data-en="Item Name:" data-bm="Nama Item:">Item Name:</span>
                        </strong> ${item.item_name}
                    </p>
                </div>
                <div class="col-12 col-lg-6">
                    <p>
                        <strong class="me-1">
                            <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-scale-balanced"></i></span>
                            <span data-en="Quantity:" data-bm="Kuantiti:">Quantity:</span>
                        </strong> ${item.quantity} ${item.measure}
                    </p>
                </div>
                <div class="col-12 col-lg-6">
                    <p>
                        <strong class="me-1">
                            <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-money-bill"></i></span>
                            <span data-en="Value:" data-bm="Nilai:">Value:</span>
                        </strong> ${item.value}
                    </p>
                </div>
                <div class="col-12 col-lg-6">
                    <p>
                        <strong class="me-1">
                            <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-pen-fancy"></i></span>
                            <span data-en="Purpose:" data-bm="Tujuan:">Purpose:</span>
                        </strong> ${item.purpose}
                    </p>
                </div>
                <div class="col-12">
                    <p>
                        <strong class="me-1">
                            <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-gear"></i></span>
                            <span data-en="Uses:" data-bm="Kegunaan:">Uses:</span>
                        </strong> ${item.uses}
                    </p>
                </div>
            </div>
        `;

        applyTranslations(detailsDiv);

        attachList.innerHTML = "";
        currentItemAttachments = item.files || [];

        if (!item.files || item.files.length === 0) {
            attachList.innerHTML = `
                <div class="text-muted text-center py-3">
                    <span data-en="No attachments" data-bm="Tiada lampiran">No attachments</span>
                </div>
            `;
            if (attachmentCount) attachmentCount.textContent = "0";
        } else {
            let chipsHTML = "";
            item.files.forEach((file, index) => {
                const displayName = file.displayName || file.name;
                const fileIcon =
                    file.type === "application/pdf"
                        ? "bi-file-earmark-pdf-fill"
                        : "bi-file-earmark-fill";
                const fileTypeClass =
                    file.type === "application/pdf" ? "is-pdf" : "is-file";
                const typeDisplay = file.type || "Unknown";

                chipsHTML += `
                    <div class="ipv-attach-chip ${fileTypeClass} view-item-attach-btn" data-index="${index}" style="cursor:pointer;">
                        <div class="ipv-attach-icon"><i class="bi ${fileIcon}"></i></div>
                        <div class="ipv-attach-info">
                            <div class="ipv-attach-name" title="${displayName}">${displayName}</div>
                            <div class="ipv-attach-size">
                                <span data-en="${typeDisplay}" data-bm="${typeDisplay}">${typeDisplay}</span>
                                · ${(file.size / 1024).toFixed(1)} KB
                            </div>
                        </div>
                    </div>
                `;
            });
            attachList.innerHTML = chipsHTML;
            if (attachmentCount)
                attachmentCount.textContent = item.files.length;

            applyTranslations(attachList);
        }

        const offcanvasEl = document.getElementById("ItemDetailsOffcanvas");
        if (offcanvasEl) {
            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
        }

        console.log("detailsDiv.innerHTML after set:", detailsDiv.innerHTML);
    });

    $(document).on("click", ".view-item-attach-btn", function (e) {
        e.preventDefault();
        e.stopPropagation();
        const index = $(this).data("index");
        if (currentItemAttachments.length > 0 && index !== undefined) {
            openItemAttachmentViewer(currentItemAttachments, index);
        }
    });
}

// ============= Item Attachment Offcanvas (NEW) =====================
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
        file.name.toLowerCase().endsWith(".pdf")
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
                <a href="${url}" target="_blank" download="${file.name}" class="btn btn-sm btn-primary">Download File</a>
            </div>
        `;
        viewerBody.dataset.objectUrl = url;
    }

    const fields = [
        { label: "File Name", value: displayName },
        { label: "Original Name", value: file.name },
        { label: "File Size", value: (file.size / 1024).toFixed(2) + " KB" },
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

// ============================================================
// END of Item Attachment Offcanvas section
// ============================================================

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

        tempItems.splice(index, 1);
        $("#item-card-" + id).remove();

        renderAllItems();

        console.log("Deleted item:", id, tempItems);
        summarySubmit();
    });
}

// ============= Application-level attachments (added) =====================
/**
 * Dropzone used for documents attached to the whole application
 * (as opposed to attachments tied to a single consignment item).
 */
function initApplicationAttachments() {
    document
        .querySelectorAll(".application-attachment-dropzone")
        .forEach((dropzoneEl) => {
            const docId = dropzoneEl.dataset.docId;
            const docName = dropzoneEl.dataset.docName;

            const dz = new Dropzone(`#${dropzoneEl.id}`, {
                url: "/",
                autoProcessQueue: false,
                addRemoveLinks: false,
                previewsContainer: false,
                clickable: true,
                acceptedFiles: ".jpg,.jpeg,.png,.pdf,.doc,.docx",
                maxFilesize: 15,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                init: function () {
                    this.on("addedfile", function (file) {
                        const attachment = {
                            id: generateUUID(),
                            file,
                            name: file.name,
                            displayName: file.name,
                            size: file.size,
                            type: file.type,
                            document_type: docName,
                            document_id: docId,
                            description: docName, // default description = document requirement name
                        };
                        file._attachmentId = attachment.id;
                        applicationAttachments.push(attachment);
                        renderApplicationAttachmentTable(docId);
                        updateDocFileCountBadge(docId);
                        updateAttachmentTable(); // if exists in inspection.js
                    });
                },
                error: function (file, message) {
                    console.error("Attachment Dropzone Error:", message);
                    if (file.previewElement) {
                        file.previewElement.remove();
                    }
                },
            });

            applicationDropzones[docId] = dz;
            renderApplicationAttachmentTable(docId);
        });
}

function updateDocFileCountBadge(docId) {
    const badge = document.querySelector(
        `.doc-file-count[data-doc-id="${docId}"]`,
    );
    if (!badge) return;
    const count = applicationAttachments.filter(
        (a) => String(a.document_id) === String(docId),
    ).length;
    badge.textContent = count > 0 ? `${count} file(s)` : "No files";
}

function renderApplicationAttachmentTable(docId) {
    const $tbody = $(
        `.application-attachment-table[data-doc-id="${docId}"] tbody`,
    );
    $tbody.empty();

    const docAttachments = applicationAttachments.filter(
        (a) => String(a.document_id) === String(docId),
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

function removeAttachmentFromDropzone(attachmentId) {
    const attachment = applicationAttachments.find((a) => a.id === attachmentId);
    if (!attachment) return false;

    const docId = attachment.document_id;
    const dz = applicationDropzones[docId];
    if (!dz) return false;

    const fileIndex = dz.files.findIndex(
        (fileItem) => fileItem._attachmentId === attachmentId,
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
        if (detailsBody) detailsBody.innerHTML = "";
        currentAttachmentList = [];
        currentAttachmentIndex = 0;
        currentAttachmentSource = null;
    });
}

/**
 * Normalizes applicationAttachments into the same shape used for item
 * attachments ({ source, raw, file, url, name, displayName, size, type })
 * so both can be paged through by the one shared offcanvas. `raw` keeps a
 * reference back to the original entry so renames stay in sync with the
 * application-attachment table.
 */
function buildApplicationAttachmentList() {
    return applicationAttachments.map((a) => ({
        source: "application",
        raw: a,
        file: a.file || null,
        url: a.url || null,
        name: a.name,
        displayName: a.displayName || a.name,
        size: a.size || 0,
        type: a.type || "",
    }));
}

/**
 * Opens the shared #attachmentOffcanvas against any normalized attachment
 * list — used for both application-level attachments and a single item's
 * attachments (existing + newly uploaded).
 */
function openAttachmentViewerAt(list, index, source) {
    if (!list || list.length === 0) return;

    currentAttachmentList = list;
    currentAttachmentSource = source;
    showAttachmentAt(index);

    if (attachmentOffcanvas) attachmentOffcanvas.show();
}

// Kept for application-attachment table buttons that only know the
// attachment's id (view/name-link clicks) — resolves the id to an index
// and delegates to the shared opener.
function openAttachmentViewer(attachmentId) {
    const index = applicationAttachments.findIndex(
        (item) => item.id === attachmentId,
    );
    if (index === -1) return;

    openAttachmentViewerAt(
        buildApplicationAttachmentList(),
        index,
        "application",
    );
}

function showAttachmentAt(index) {
    const attachment = currentAttachmentList[index];
    if (!attachment) return;

    const viewerTitle = document.getElementById("attachmentTitle");
    const viewerCounter = document.getElementById("attachmentCounter");
    const viewerBody = document.getElementById("attachmentViewer");
    const detailsBody = document.getElementById("attachmentDetails");

    if (!viewerTitle || !viewerCounter || !viewerBody || !detailsBody) return;

    currentAttachmentIndex = index;
    viewerTitle.textContent = attachment.displayName;
    viewerCounter.textContent = `${index + 1} / ${currentAttachmentList.length}`;

    if (viewerBody.dataset.objectUrl) {
        URL.revokeObjectURL(viewerBody.dataset.objectUrl);
        delete viewerBody.dataset.objectUrl;
    }
    viewerBody.innerHTML = "";

    if (attachment.file) {
        // Not-yet-persisted file — still a browser File object
        const file = attachment.file;

        if (file.type && file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = (e) => {
                viewerBody.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" alt="${attachment.displayName}">`;
            };
            reader.readAsDataURL(file);
        } else if (
            file.type === "application/pdf" ||
            attachment.name.toLowerCase().endsWith(".pdf")
        ) {
            const url = URL.createObjectURL(file);
            viewerBody.innerHTML = `<iframe src="${url}" class="w-100" style="height: calc(100vh - 220px); border: none;"></iframe>`;
            viewerBody.dataset.objectUrl = url;
        } else {
            const url = URL.createObjectURL(file);
            viewerBody.innerHTML = `
                <div class="text-center">
                    <i class="bi bi-file-earmark-fill fs-1 mb-3"></i>
                    <p class="mb-2">${attachment.name}</p>
                    <a href="${url}" target="_blank" download="${attachment.name}" class="btn btn-sm btn-primary">Download File</a>
                </div>
            `;
            viewerBody.dataset.objectUrl = url;
        }
    } else if (attachment.url) {
        // Already-persisted file — we have a direct URL, no File object
        const isPdf =
            (attachment.type || "").includes("pdf") ||
            attachment.url.toLowerCase().endsWith(".pdf");
        const isImage =
            (attachment.type || "").startsWith("image") ||
            /\.(jpg|jpeg|png|gif)$/i.test(attachment.url);

        if (isImage) {
            viewerBody.innerHTML = `<img src="${attachment.url}" class="img-fluid rounded" alt="${attachment.displayName}">`;
        } else if (isPdf) {
            viewerBody.innerHTML = `<iframe src="${attachment.url}" class="w-100" style="height: calc(100vh - 220px); border: none;"></iframe>`;
        } else {
            viewerBody.innerHTML = `
                <div class="text-center">
                    <i class="bi bi-file-earmark-fill fs-1 mb-3"></i>
                    <p class="mb-2">${attachment.name}</p>
                    <a href="${attachment.url}" target="_blank" class="btn btn-sm btn-primary">Open File</a>
                </div>
            `;
        }
    } else {
        viewerBody.innerHTML = `<div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br>No preview available</div>`;
    }

    const sizeLine = attachment.size
        ? `<div class="mb-3"><strong>File Size:</strong><div class="text-muted">${(attachment.size / 1024).toFixed(2)} KB</div></div>`
        : "";
    const originalNameLine =
        attachment.displayName !== attachment.name
            ? `<div class="mb-3"><strong>Original Name:</strong><div class="text-muted">${attachment.name}</div></div>`
            : "";

    detailsBody.innerHTML = `
        <div class="mb-3"><strong>File Name:</strong><div class="text-muted">${attachment.displayName}</div></div>
        ${originalNameLine}
        ${sizeLine}
        <div class="mb-3"><strong>File Type:</strong><div class="text-muted">${attachment.type || "Unknown"}</div></div>
    `;

    const prevBtn = document.getElementById("attachmentPrevBtn");
    const nextBtn = document.getElementById("attachmentNextBtn");
    if (prevBtn) prevBtn.disabled = index === 0;
    if (nextBtn) nextBtn.disabled = index === currentAttachmentList.length - 1;

    const editNameInput = document.getElementById("attachmentEditName");
    if (editNameInput) editNameInput.value = attachment.displayName;
}

function initAttachmentNavigation() {
    $(document).on("click", "#attachmentPrevBtn", function () {
        if (currentAttachmentIndex > 0) {
            showAttachmentAt(currentAttachmentIndex - 1);
        }
    });

    $(document).on("click", "#attachmentNextBtn", function () {
        if (currentAttachmentIndex < currentAttachmentList.length - 1) {
            showAttachmentAt(currentAttachmentIndex + 1);
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
    renderApplicationAttachmentTable();
    updateAttachmentTable();
});

$(document).on("click", ".delete-attachment-btn", function () {
    const attachmentId = $(this).data("id");
    const index = applicationAttachments.findIndex(
        (item) => item.id === attachmentId,
    );
    if (index === -1) return;

    removeAttachmentFromDropzone(attachmentId);
    applicationAttachments.splice(index, 1);

    renderApplicationAttachmentTable();
    updateAttachmentTable();

    Swal.fire({
        icon: "success",
        title: "Attachment removed",
        timer: 1000,
        showConfirmButton: false,
    });
});

// Renaming works for both sources — application attachments (kept in sync
// with their table + summary) and item attachments (kept in sync with the
// underlying file/existingFiles object so it reflects if the modal reopens).
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

    const attachment = currentAttachmentList[currentAttachmentIndex];
    if (!attachment) return;

    attachment.displayName = newName;
    if (attachment.raw) attachment.raw.displayName = newName;

    showAttachmentAt(currentAttachmentIndex);

    Swal.fire({
        icon: "success",
        title: "Saved",
        text: "File name updated successfully",
        timer: 1500,
        showConfirmButton: false,
    });

    if (attachment.source === "application") {
        renderApplicationAttachmentTable();
        updateAttachmentTable();
    }
});

/**
 * Mirrors the application-level attachment list into the review/summary
 * page's table, so it stays in sync every time an attachment changes.
 */
function updateAttachmentTable() {
    const attachmentTable = document.querySelector(
        "#summaryAttachmentTable tbody",
    );
    if (!attachmentTable) return;

    attachmentTable.innerHTML = "";

    if (!applicationAttachments || applicationAttachments.length === 0) {
        attachmentTable.insertAdjacentHTML(
            "beforeend",
            `
            <tr>
                <td colspan="6" class="text-center text-muted py-3">No attachments uploaded</td>
            </tr>
            `,
        );
        return;
    }

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
                <td class="text-wrap">${attachment.description || "-"}</td>
                <td>${sizeDisplay}</td>
                <td>${typeDisplay}</td>
                <td class="text-start">
                    <button type="button" class="btn btn-sm btn-primary view-attachment-btn" data-id="${attachment.id}">
                        <i class="ti ti-eye"></i> View
                    </button>
                </td>
            </tr>
            `,
        );
    });
}

// ============= attachment =====================

async function loadApplicationData(id) {
    if (!id) return;

    try {
        const response = await fetch(
            `${window.baseUrl}/public/inspection_application_data/${id}`,
        );
        const result = await response.json();

        if (result.status === "success") {
            const app = result.data;

            if (app.importer_detail) {
                handleImporterResponse({
                    status: "success",
                    data: app.importer_detail,
                });
            } else if (app.importer) {
                handleImporterResponse({
                    status: "success",
                    data: app.importer,
                });
            }

            if (app.exporter_id) {
                $("#selectexp").val(app.exporter_id).trigger("change");
            }

            $("#eta").val(app.eta ? app.eta.split("T")[0] : "");

            const transportType = app.transport_type ?? "";
            const route =
                document.getElementById("trnptType")?.dataset?.route ?? "#";
            $("#trnptType").val(transportType);

            if (transportType) {
                Swal.fire({
                    title: "Loading...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });
                loadEntryPoints(transportType, route, () => {
                    setTimeout(() => {
                        let entryPointVal = app.entry_point;
                        if (
                            typeof app.entry_point === "object" &&
                            app.entry_point !== null
                        ) {
                            entryPointVal = app.entry_point.id;
                        }

                        const savedEntryPoint = entryPointVal
                            ? String(entryPointVal)
                            : "";
                        if (savedEntryPoint) {
                            $("#entryPoint")
                                .val(savedEntryPoint)
                                .trigger("change");
                            entryName =
                                $("#entryPoint")
                                    .find("option:selected")
                                    .data("entry_name") || "";
                        }
                        summarySubmit();
                    }, 50);
                });
            }

            if (app.inspection_items && app.inspection_items.length > 0) {
                tempItems = app.inspection_items.map((item) => {
                    const data = item.consignment_detail || {};
                    return {
                        id: item.id || generateUUID(),
                        item_id: data.item_id || data.id,
                        item_name: data.item_name || data.entry_display,
                        value: item.value,
                        quantity: item.quantity,
                        measure: item.unit_measurement,
                        purpose: item.purpose,
                        uses: data.uses,
                        agreedAt: item.agreed_at || null,
                        existingFiles: (item.attachments || []).map((a) => ({
                            name: a.file_name,
                            url: a.file_path,
                            type: a.file_type,
                        })),
                        files: [],
                    };
                });
                renderAllItems();
                summarySubmit();
            }

            // Restore previously saved application-level attachments (view-only,
            // since we don't have the raw File object for already-uploaded files).
            if (app.attachments && app.attachments.length > 0) {
                applicationAttachments = app.attachments.map((a) => ({
                    id: a.id || generateUUID(),
                    file: null,
                    name: a.file_name,
                    displayName: a.file_name,
                    size: a.file_size || 0,
                    type: a.file_type || "",
                    url: a.file_path,
                }));
                renderApplicationAttachmentTable();
                updateAttachmentTable();
            }

            change = false;
        }
    } catch (error) {
        console.error("Error loading application data:", error);
    }
}

// ─── Copy Item ──────────────────────────────────────────────
function copyItem() {
    $(document).on("click", ".copy-item", async function (e) {
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
            agreedAt: null,
        };

        const agreed = await showItemAgreement(duplicated);

        if (!agreed) {
            // User cancelled or didn't tick the condition — don't add the copy
            return;
        }

        tempItems.splice(index + 1, 0, duplicated);
        renderAllItems();
        summarySubmit();

        console.log("Copied item:", id, "->", duplicated.id, tempItems);

        Swal.fire({
            icon: "success",
            title: '<span data-en="Item Copied" data-bm="Item Disalin">Item Copied</span>',
            html: '<span data-en="A duplicate of the item has been added." data-bm="Salinan item telah ditambah.">A duplicate of the item has been added.</span>',
            timer: 1800,
            showConfirmButton: false,
            didOpen: (modal) => applyTranslations(modal),
        });
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

        // ─── Fill the form with item data ──────────────────────────
        // Item name – plain text input
        $("#itemSelect").val(item.item_name);

        // Other fields
        $("#itemValue").val(item.value);
        $("#itemQuantity").val(item.quantity);
        $("#itemMeasure").val(item.measure).trigger("change");
        $("#itemPurpose").val(item.purposeValue || item.purpose).trigger("change");
        $("#itemUses").val(item.uses).trigger("change");

        // Optional: if you have a hidden field to store item_id, set it here
        // $("#selectedItemId").val(item.item_id || "");

        // ─── Restore files in Dropzone ──────────────────────────────
        if (itemDropzone) {
            itemDropzone.removeAllFiles(true);
            (item.files || []).forEach((file) => itemDropzone.addFile(file));
        }

        // Remove any custom-item-specific UI (since it's all free text)
        $(".attachmentInstruction").html("").hide();
    });
}


function saveapplication(isDraft = false, shouldRedirect = false) {
    const form =
        document.querySelector("#wizardForm") ||
        document.querySelector("#wizardFormOthers");
    if (!form) return console.error("Form not found");

    const formData = new FormData(form);

    formData.append("is_draft", isDraft ? 1 : 0);

    const appId = $("#applicationId").val();
    if (appId) {
        formData.append("applicationId", appId);
    }

    formData.append("exporterData", JSON.stringify(exporter));
    formData.append("importerData", JSON.stringify(importer));

    const livePermitDetails = {
        applCate: document.getElementById("app_cate")?.value ?? "",
        eta: document.getElementById("eta")?.value ?? "",
        tranType: document.getElementById("trnptType")?.value ?? "",
        entrypoint: document.getElementById("entryPoint")?.value ?? "",
    };
    formData.append("permitDetails", JSON.stringify(livePermitDetails));

    tempItems.forEach((item, index) => {
        const { files, existingFiles, ...otherData } = item;
        formData.append(`items[${index}][data]`, JSON.stringify(otherData));

        if (files && files.length > 0) {
            files.forEach((file) => {
                formData.append("files[]", file);
                formData.append("file_item_index[]", index);
            });
        }
    });

    // ✅ Application-level attachments
    applicationAttachments.forEach((attachment) => {
        if (attachment.file) {
            formData.append("application_files[]", attachment.file);
            formData.append("application_files_document_type[]", attachment.document_type || "");
            formData.append("application_files_description[]", attachment.description || "");
        }
    });

    Swal.fire({
        title: isDraft ? "Saving Draft..." : "Submitting...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: "/public/save_application_inspection",
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

            if (!isDraft || shouldRedirect) {
                setTimeout(() => {
                    window.location.href =
                        "/public/inspection_certificates_list";
                }, 1500);
            } else {
                window.location.reload();
            }
        },
        error: function (xhr) {
            Swal.fire(
                "Error",
                "Failed to save application: " +
                    (xhr.responseJSON?.message || "Unknown error"),
                "error",
            );
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
        await selfImport();

        await fetchExporterList();
        handleExporterChange();

        initAddExporterModal();
        initImporterSearch();
        permitDetails();
        itemConsigment();
        saveConsignmentAttachment();
        viewMore();
        deleteItem();
        copyItem()
        editItem();

        // ✅ Application attachments + the single shared attachment offcanvas
        // (used to view BOTH application-level and item-level attachments)
        initApplicationAttachments();
        initAttachmentOffcanvas();
        initAttachmentNavigation();

        // ✅ Item attachment offcanvas (NEW)
        initItemAttachmentOffcanvas();
        initItemAttachmentNavigation();

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
        });

        const appId = $("#applicationId").val();
        if (appId) {
            await loadApplicationData(appId);
        }

        const form = document.querySelector("#wizardForm");
        if (form) {
            form.addEventListener("input", () => {
                change = true;
            });
            form.addEventListener("change", () => {
                change = true;
            });
        }

        // Submit button handler — now requires the final declaration to be
        // agreed to before the application is actually saved.
        $(document).on("click", "#submitApps", async function (e) {
            e.preventDefault();
            console.log("Submit clicked!");

            if (tempItems.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "No Items",
                    text: "Please add at least one item before submitting.",
                });
                return;
            }

            const unagreedItems = tempItems.filter((item) => !item.agreedAt);
            if (unagreedItems.length > 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Incomplete Declarations",
                    text: "Please confirm the declaration for all items before submitting.",
                });
                return;
            }

            const agreed = await showFinalAgreement();
            if (!agreed) return;

            saveapplication(false);
        });

        $(document).on(
            "click",
            `#logoutButton, 
            .app-sidebar.sticky button, .app-sidebar.sticky a,
           
            .breadcrumb .breadcrumb-item a
            `,
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
                        saveapplication(true, true);
                    }
                });
            },
        );
    } catch (error) {
        console.error("Error during initialization:", error);
        Swal.fire(
            "Error",
            "Failed to initialize page. Check console for details.",
            "error",
        );
    } finally {
        Swal.close();
    }
});

// ------------------------------summary details ---------------------
export function summarySubmit() {
    const targetTable = document.querySelector("#summaryTable3 tbody");

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

    if (importer) {
        document.getElementById("importerName").textContent =
            importer.fullname || "";
        document.getElementById("importerPhoneno").textContent =
            importer.phone_number || "";
        document.getElementById("simpAdd").textContent = impAddrs;
    }

    if (exporter) {
        document.getElementById("sexpName").textContent = exporter.name || "";
        document.getElementById("sexpfonno").textContent =
            exporter.phone_no || "";
        document.getElementById("sexpAddress").textContent =
            exporter.address || "";
        document.getElementById("sexpCountry").textContent =
            exporter.country || "";
    }

    document.getElementById("seta").textContent = permitDetails.eta || "";
    document.getElementById("strty").textContent = permitDetails.tranType || "";
    document.getElementById("sentryp").textContent = entryName || "";

    targetTable.innerHTML = "";

    tempItems.forEach((item, index) => {
        let attachmentHTML = `
            <button class = "btn btn-sm btn-primary view-more-item" data-id = "${item.id}">
                View More
            </button>
            `;

        targetTable.insertAdjacentHTML(
            "beforeend",
            `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.item_name || ""}</td>
                    <td>${item.quantity || ""}  ${item.measure || ""}</td>
                    <td>${item.purpose || ""} </td>
                    <td>${item.uses || ""}</td>
                    <td>RM ${item.value || ""}</td>
                    <td>${attachmentHTML}</td>
                </tr>
            `,
        );
    });

    // ✅ Keep the application-level attachment summary in sync too
    updateAttachmentTable();
}

// ─── Inactivity Timeout: Draft-Save Hook ─────────────────────────────────────
$(document).ready(function () {
    const isInspectionPage = window.location.pathname.includes(
        "inspection_certificates_application",
    );
    if (!isInspectionPage) return;

    window.qisDraftSaver = function () {
        return new Promise((resolve, reject) => {
            const form =
                document.querySelector("#wizardForm") ||
                document.querySelector("#wizardFormOthers");
            if (!form) return resolve();

            const formData = new FormData(form);
            formData.append("is_draft", 1);

            const appId = $("#applicationId").val();
            if (appId) formData.append("applicationId", appId);

            formData.append("exporterData", JSON.stringify(exporter));
            formData.append("importerData", JSON.stringify(importer));

            const livePermitDetails = {
                applCate: document.getElementById("app_cate")?.value ?? "",
                eta: document.getElementById("eta")?.value ?? "",
                tranType: document.getElementById("trnptType")?.value ?? "",
                entrypoint: document.getElementById("entryPoint")?.value ?? "",
            };
            formData.append("permitDetails", JSON.stringify(livePermitDetails));

            tempItems.forEach((item, index) => {
                const { files, existingFiles, ...otherData } = item;
                formData.append(
                    `items[${index}][data]`,
                    JSON.stringify(otherData),
                );
                if (files && files.length > 0) {
                    files.forEach((file) => {
                        formData.append("files[]", file);
                        formData.append("file_item_index[]", index);
                    });
                }
            });

            // ✅ Application-level attachments
            applicationAttachments.forEach((attachment) => {
                if (attachment.file) {
                    formData.append("application_files[]", attachment.file);
                    formData.append("application_files_document_type[]", attachment.document_type || "");
                    formData.append("application_files_description[]", attachment.description || "");
                }
            });

            $.ajax({
                url: "/public/save_application_inspection",
                type: "POST",
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                processData: false,
                contentType: false,
                success: () => resolve(),
                error: (xhr) =>
                    reject(
                        new Error(
                            xhr.responseJSON?.message || "Draft save failed",
                        ),
                    ),
            });
        });
    };

    console.log("[QIS] Inspection draft saver registered.");
});

function showItemFilePreview(file) {
    const previewContainer = document.getElementById(
        "itemFilePreviewContainer",
    );
    const fileNameSpan = document.getElementById("itemFileName");
    const fileEditInput = document.getElementById("itemFileEditName");
    const fileDetailsDiv = document.getElementById("itemFileDetails");

    if (!previewContainer || !fileNameSpan) return;

    if (!itemFileOffcanvas) {
        itemFileOffcanvas = new bootstrap.Offcanvas(
            document.getElementById("itemFilePreviewOffcanvas"),
            { backdrop: true, keyboard: true, scroll: false },
        );
    }

    previewContainer.innerHTML = "";
    fileNameSpan.textContent = file.name;
    fileEditInput.value = file.name;

    // ---- Preview ----
    if (file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewContainer.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" alt="${file.name}">`;
        };
        reader.readAsDataURL(file);
    } else if (file.type === "application/pdf") {
        const url = URL.createObjectURL(file);
        previewContainer.innerHTML = `<iframe src="${url}" class="w-100" style="height: calc(100vh - 220px); border: none;"></iframe>`;
    } else {
        previewContainer.innerHTML = `<div class="text-center"><i class="bi bi-file-earmark-fill fs-1 mb-3"></i><p>${file.name}</p></div>`;
    }

    // ---- File Details (bilingual) ----
    const fileSize = (file.size / 1024).toFixed(2) + " KB";
    const fileType = file.type || "Unknown";

    fileDetailsDiv.innerHTML = `
        <div class="mb-3">
            <strong data-en="File Name:" data-bm="Nama Fail:">File Name:</strong>
            <div class="text-muted">${file.name}</div>
        </div>
        <div class="mb-3">
            <strong data-en="File Size:" data-bm="Saiz Fail:">File Size:</strong>
            <div class="text-muted">${fileSize}</div>
        </div>
        <div class="mb-3">
            <strong data-en="File Type:" data-bm="Jenis Fail:">File Type:</strong>
            <div class="text-muted">${fileType}</div>
        </div>
    `;

    // Apply current language to the newly added labels
    applyTranslations(fileDetailsDiv);

    itemFileOffcanvas.show();
}

function addPreviewButtons(file) {
    const preview = file.previewElement;
    if (!preview) return;

    const removeBtn = preview.querySelector(".dz-remove");
    if (!removeBtn) return;

    // Create the action group
    const attachmentGroup = document.createElement("div");
    attachmentGroup.className = "attachment-group";
    attachmentGroup.style.display = "flex";
    attachmentGroup.style.gap = "5px";
    // attachmentGroup.style.alignItems = "center";
    // attachmentGroup.style.justifyContent = "end";

    // View button
    const viewBtn = document.createElement("a");
    viewBtn.href = "#";
    viewBtn.innerHTML = "<i class='ti ti-eye'></i>";
    viewBtn.className = "btn btn-icon btn-info-light";
    viewBtn.onclick = function (e) {
        e.preventDefault();
        console.log("click item", file);
        currentItemFile = file;
        showItemFilePreview(file);
    };

    // Edit button
    const editBtn = document.createElement("a");
    editBtn.href = "#";
    editBtn.innerHTML = "<i class='ti ti-pencil'></i>";
    editBtn.className = "btn btn-icon btn-success-light";
    editBtn.onclick = function (e) {
        e.preventDefault();
        currentItemFile = file;
        showItemFilePreview(file);
        const modal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById("itemFilePreviewModal"),
        );
        modal.show();
    };

    // Delete button – custom handler that removes the file from Dropzone
    const deleteBtn = document.createElement("a");
    deleteBtn.href = "#";
    deleteBtn.innerHTML = "<i class='ti ti-trash'></i>";
    deleteBtn.className = "btn btn-icon btn-danger-light"; // or keep dz-remove class? We'll use our own
    deleteBtn.onclick = function (e) {
        e.preventDefault();
        if (itemDropzone) {
            itemDropzone.removeFile(file);
        }
    };

    attachmentGroup.appendChild(viewBtn);
    attachmentGroup.appendChild(editBtn);
    attachmentGroup.appendChild(deleteBtn);

    // Replace the original .dz-remove with our group
    removeBtn.parentNode.replaceChild(attachmentGroup, removeBtn);
}

$(document).on("click", "#itemFileSaveBtn", function () {
    const newName = document.getElementById("itemFileEditName").value.trim();

    if (!newName || !currentItemFile) {
        Swal.fire({
            icon: "warning",
            title: '<span data-en="Empty Name" data-bm="Nama Kosong">Empty Name</span>',
            html: '<span data-en="Please enter a file name" data-bm="Sila masukkan nama fail">Please enter a file name</span>',
            didOpen: (modal) => applyTranslations(modal),
        });
        return;
    }

    // Update the file object
    currentItemFile.displayName = newName;

    // ----- NEW: Update Dropzone preview filename -----
    if (currentItemFile.previewElement) {
        const $preview = $(currentItemFile.previewElement);
        // Dropzone places filename in .dz-filename span
        const $filenameSpan = $preview.find(".dz-filename span");
        if ($filenameSpan.length) {
            $filenameSpan.text(newName);
        }
        // Also update the .dz-filename data-dz-name attribute if used
        const $filenameDiv = $preview.find(".dz-filename");
        if ($filenameDiv.length) {
            $filenameDiv.attr("data-dz-name", newName);
        }
    }

    // Update offcanvas title
    document.getElementById("itemFileName").textContent = newName;

    Swal.fire({
        icon: "success",
        title: '<span data-en="Saved" data-bm="Disimpan">Saved</span>',
        html: '<span data-en="File name updated" data-bm="Nama fail dikemas kini">File name updated</span>',
        timer: 1500,
        showConfirmButton: false,
        didOpen: (modal) => applyTranslations(modal),
    });
});

$(document).on("click", ".doc-details-btn", function () {
    const name = $(this).data("doc-name");
    const targetId = $(this).data("description-target");
    const sourceHtml = document.getElementById(targetId)?.innerHTML || "";

    $("#docDescriptionModalLabel").text(name);
    $(".doc-description-modal-body").html(
        sourceHtml.trim() || "<p class='text-muted mb-0'>No description available.</p>",
    );

    const modal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("docDescriptionModal"),
    );
    modal.show();
});
