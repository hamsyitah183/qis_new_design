import Dropzone from "dropzone";
import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import { generateUUID, getAuthUser, applyTranslations } from "../../app";
import "dropzone/dist/dropzone.css";
import { render } from "react-dom/cjs/react-dom.production.min";

// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";
import { public_dashboard } from "../dashboard/public_dashboard";

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

// [FIX] categoryAmount now actually gets populated — see
// recalculateCategoryAmounts() near saveConsignmentAttachment().
let categoryAmount = [];

// Vehicle globals
let vehicleListArray = [];
let selectedVehicles = [];

// ─── New globals for advanced features ──────────────────
let measurementUnits = null;
let limit = null;
let limitMeasurement = null;
let currentItemCondition = null;
let editingItemId = null;
let applicationAttachments = [];
let applicationAttachmentDropzone = null;
let itemFileOffcanvas = null;
let currentItemFile = null;
let itemAttachmentOffcanvas = null;
let currentItemAttachments = [];
let currentItemAttachIndex = 0;
let attachmentOffcanvas = null;
let currentAttachmentIndex = 0;

// ─── Measurement Units ────────────────────────────────────
function measurementUnit() {
    return $.ajax({
        url: "/measurement",
        type: "GET",
        dataType: "json",
        cache: false,
        success: (data) => {
            measurementUnits = data;
            console.log("measurement", measurementUnits);
        },
        error: (xhr) => {
            console.error(
                "Failed to load measurement units:",
                xhr.responseText,
            );
        },
    });
}
measurementUnit();

// ─── Helper: convert quantity to kg ──────────────────────
function getKgForItem(item) {
    const qty = parseFloat(item.quantity) || 0;
    if (!qty || !measurementUnits || !measurementUnits.unit) return 0;
    const measure = item.measure || "";
    const unit = measurementUnits.unit.find(
        (u) => u.cate_code.toLowerCase() === measure.toLowerCase() && !u.is_del,
    );
    return unit ? qty * unit.conversion.conversion : 0;
}

// ─── Helper ──────────────────────────────────────────────
function getCurrentLang() {
    try {
        return localStorage.getItem("qis_lang") || "en";
    } catch {
        return "en";
    }
}

// ─── Self Import ──────────────────────────────────────────
async function selfImport() {
    if (
        window.location.pathname.includes(
            "public/consignment_certificate_application",
        )
    ) {
        importer = await getAuthUser();
        console.log("importer", importer);
    }
}

// ─── Exporter List ────────────────────────────────────────
function fetchExporterList() {
    const $select = $("#selectexp");
    const url = "/get_consignment_importers";

    return $.ajax({
        url,
        type: "GET",
        dataType: "json",
        cache: false,
        success: (data) => {
            exporterListArray = data.data || [];
            console.log("exporterListArray", exporterListArray);

            $select
                .empty()
                .append('<option value="">-- Select Importer --</option>');
            exporterListArray.forEach((exp) =>
                $select.append(
                    `<option value="${exp.id}">${exp.name}</option>`,
                ),
            );

            if ($select.hasClass("xintra-select2")) {
                $select.trigger("change.select2");
            }

            $select.select2({
                width: "100%",
                placeholder: "-- Select Importer --",
                allowClear: true,
            });
        },
        error: (xhr) => {
            console.error("Failed to load exporters:", xhr.responseText);
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
        $("#expcountryCode").val(exporter.country_info.code || "");
        $("#expcountry").val(exporter.country_info.name || "");

        change = 1;
        summarySubmit();
    });
}

function clearExporterFields() {
    $("#expid, #expname, #expfonno, #expaddress1, #expaddress2").val("");
    $("#expcountryCode, #expcountry").val("");
}

// ─── Vehicle Functions ────────────────────────────────────
function handleVehicleChange() {
    const $select = $("#selectVehicle");

    $select.on("change", function () {
        const selectedIds = $(this).val() || [];
        $("#vehicleIds").val(selectedIds.join(","));
        selectedVehicles = vehicleListArray.filter((v) =>
            selectedIds.includes(String(v.id)),
        );

        change = 1;
        summarySubmit();
    });
}

function fetchVehicleList() {
    const $select = $("#selectVehicle");
    const url = "/vehicle/data";

    return $.ajax({
        url,
        type: "GET",
        dataType: "json",
        cache: false,
        success: (data) => {
            vehicleListArray = data.vehicle || [];
            $select.empty();
            $select.append('<option value="">-- Select Vehicle(s) --</option>');
            vehicleListArray.forEach((v) => {
                $select.append(
                    `<option value="${v.id}">${v.vehicle_number}</option>`,
                );
            });

            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2("destroy");
            }
            $select.select2({
                width: "100%",
                placeholder: "-- Select Vehicle(s) --",
                allowClear: true,
                multiple: true,
            });

            const storedIds = $("#vehicleIds").val()
                ? $("#vehicleIds").val().split(",")
                : [];
            if (storedIds.length) {
                $select.val(storedIds).trigger("change");
            }
        },
        error: (xhr) => {
            console.error("Failed to load vehicles:", xhr.responseText);
        },
    });
}

// ─── Add Vehicle Modal ────────────────────────────────────
function initAddVehicleModal() {
    const modalEl = document.getElementById("addVehicleModal");
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    $("#openVehicleModalBtn").on("click", (e) => {
        e.preventDefault();
        modal.show();
    });

    $("#addVehicleBtn").on("click", function (e) {
        e.preventDefault();

        const vehicleNumber = $("#addVehicleNumber").val().trim();

        const validFrom = $("#addValidFrom").val();
        const validUntil = $("#addValidUntil").val();

        Swal.fire({
            title: "Saving vehicle...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: "/public/store_vehicle",
            type: "POST",
            data: {
                vehicle_number: vehicleNumber,

                valid_from: validFrom || null,
                valid_until: validUntil || null,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: (response) => {
                fetchVehicleList().then(() => {
                    const currentSelection = $("#selectVehicle").val() || [];
                    currentSelection.push(String(response.id));
                    $("#selectVehicle").val(currentSelection).trigger("change");

                    Swal.fire({
                        icon: "success",
                        title: "Vehicle Saved!",
                        timer: 1800,
                        showConfirmButton: false,
                    });
                    modal.hide();
                    $("#addVehicleForm")[0].reset();
                });
            },
            error: (xhr) => {
                console.error(xhr.responseText);
                Swal.fire(
                    "❌",
                    "Failed to save vehicle. Please try again.",
                    "error",
                );
            },
        });
    });
}

// ─── FIXED: loadCategory returns a Promise ──────────────
function loadCategory() {
    return new Promise((resolve, reject) => {
        const $select = $("#itemCategory");
        $select.empty().append('<option value="">-- Select Category --</option>');

        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2("destroy");
        }

        $select.prop("disabled", true);

        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        fetch(`/get_pbdata/consignment_category`)
            .then((res) => res.json())
            .then((data) => {
                $select.prop("disabled", false);
                data.data.forEach((row) => {
                    $select.append(
                        `<option value="${row.id}">${row.description}</option>`,
                    );
                });

                $select.select2({
                    width: "100%",
                    placeholder: "-- Select Item --",
                    allowClear: true,
                    dropdownParent: $("#addItemModal"),
                });

                Swal.close();
                resolve();
            })
            .catch((e) => {
                console.error("Error loading items:", e);
                $select.prop("disabled", false);
                Swal.fire("Error", "Failed to load consignment items.", "error");
                reject(e);
            });
    });
}

// ─── Consignment / Uses ──────────────────────────────────
function loadConsignmentSelection(itemId) {
    const countryCode = $("#expcountryCode").val();

    const $select = $("#itemSelect");

    if (!countryCode) {
        $select.empty().append('<option value="">-- Select Item --</option>');
        $select.select2({
            width: "100%",
            placeholder: "-- Select Item --",
            allowClear: true,
            dropdownParent: $("#addItemModal"),
        });
        return Promise.resolve();
    }

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

    return fetch(`/consignment_condition/category/${itemId}/${countryCode}/data`)
        .then((res) => res.json())
        .then((data) => {
            $select.prop("disabled", false);

            // Prepend "Others" option
            $select.append(`<option value="others">Others</option>`);

            data.data.forEach((row) => {
                $select.append(
                    `<option value="${row.id}">${row.item_name}</option>`,
                );
            });

            $select.select2({
                width: "100%",
                placeholder: "-- Select Item --",
                allowClear: true,
                dropdownParent: $("#addItemModal"),
            });

            Swal.close();
        })
        .catch((e) => {
            console.error("Error loading items:", e);
            $select.prop("disabled", false);
            Swal.fire("Error", "Failed to load consignment items.", "error");
            return Promise.reject(e);
        });
}

function formatDate(dateString) {
    const options = { day: "2-digit", month: "short", year: "numeric" };
    return new Date(dateString).toLocaleDateString("en-GB", options);
}

function loadDetails(itemId) {
    fetch(`/public/get_item_details/${itemId}`)
        .then((res) => res.json())
        .then((data) => {
            console.log("data in details", data);

            let item = data.data;
            const today = new Date();
            const startDate = item.start_date
                ? new Date(item.start_date)
                : null;
            const endDate = item.end_date ? new Date(item.end_date) : null;

            const todayDate = new Date(
                today.getFullYear(),
                today.getMonth(),
                today.getDate(),
            );
            const startDay = startDate
                ? new Date(
                      startDate.getFullYear(),
                      startDate.getMonth(),
                      startDate.getDate(),
                  )
                : null;
            const endDay = endDate
                ? new Date(
                      endDate.getFullYear(),
                      endDate.getMonth(),
                      endDate.getDate(),
                  )
                : null;

            const isWithinLimitPeriod =
                item.quantity_limit &&
                startDay &&
                endDay &&
                todayDate >= startDay &&
                todayDate <= endDay;

            currentItemCondition = item.addional_condition || null;

            if (isWithinLimitPeriod) {
                limit = item.quantity_limit;
                limitMeasurement = item.measurement_unit;
                startLimitDate = item.start_date;
                endLimitDate = item.end_date;

                const alertHtml = `
                <div class="col-12 alert alert-primary">
                    <p>
                        <span data-en="The quantity allowed for ${item.item_name} is" 
                              data-bm="Kuantiti yang dibenarkan untuk ${item.item_name} ialah">
                              The quantity allowed for ${item.item_name} is
                        </span>
                        <span class="fw-bold">
                            ${item.quantity_limit} ${item.measurement_unit}
                        </span>
                        <span data-en="from" data-bm="dari">from</span> 
                        <span class="fw-bold">${formatDate(item.start_date)}</span> 
                        <span data-en="until" data-bm="sehingga">until</span> 
                        <span class="fw-bold">${formatDate(item.end_date)}</span>.
                    </p>
                </div>
                `;

                $("#addItemModal .modal-body .news").find(".alert").remove();
                $("#addItemModal .modal-body .news").prepend(alertHtml);
            } else {
                limit = null;
                limitMeasurement = null;
                $("#addItemModal .modal-body .news").find(".alert").remove();
            }
        })
        .catch((err) => {
            console.error("Failed to load details:", err);
        });
}

// ─── Add Exporter Modal ──────────────────────────────────
function initAddExporterModal() {
    console.log("this is the exporter modal");
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
        Swal.fire({
            title: "Saving exporter...",
            text: "Please wait",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            url: "/public/store_consignment_importer",
            type: "POST",
            data: { name, phone_no, address: full_address, country },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: (response) => {
                fetchExporterList().then(() => {
                    const newImporterId = response.id;
                    const $select = $("#selectexp");

                    $select.val(newImporterId).trigger("change");

                    Swal.fire({
                        icon: "success",
                        title: "Importer Saved!",
                        text: "The exporter has been successfully added to the list.",
                        timer: 1800,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: "center",
                    });
                    $(modalEl).modal("hide");
                    $("#addExporterForm")[0].reset();
                });
            },
            error: (xhr) => {
                console.error(xhr.responseText);
                Swal.fire("❌ Failed to save exporter. Please try again.");
            },
        });
    });
}

// ─── Importer Lookup ──────────────────────────────────────
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
            "#impname, #impid, #impfonno, #impaddress1, #impaddress2, #imp_id, #impemail",
        ).val("");
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

// ─── Permit Details ──────────────────────────────────────
function permitDetails() {
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

        if (!value || route === "#") {
            detailsSelect.innerHTML =
                '<option value="">-- Select Option --</option>';
            return;
        }

        const url = `${route}?type=${encodeURIComponent(value)}`;
        console.log(url);
        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (data) {
                console.log("something here");
                console.log(data);
                Swal.close();
                const detailsSelect = $("#entryPoint");
                let options =
                    '<option value="">-- Select Entry Point --</option>';
                data.forEach(function (item) {
                    options += `<option value="${item.id}" 
                    data-entry_name = "${item.entry_display}" 
                    
                    >${item.entry_display}</option>`;
                });
                detailsSelect.html(options);

                if (data.length > 0) {
                    detailsSelect.val(data[0].id).trigger("change");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                console.log(xhr.responseText);
                Swal.close();
                Swal.fire("Error", "Error", "error");
                console.error("ERROR RESPONSE:");
                console.error();
            },
        });
    });

    $("#entryPoint").on("change", function (e) {
        e.preventDefault();

        entryName = $(this).find("option:selected").data("entry_name");

        console.log("I picked entry:", entryName);

        summarySubmit();
    });

    // ─── ETA Date Validation ────────────────────────────
    const etaInput = document.getElementById("eta");
    const expiredInput = document.getElementById("expiredDate");

    if (etaInput) {
        const today = new Date().toISOString().split("T")[0];
        etaInput.setAttribute("min", today);

        function updateExpiredDate() {
            if (etaInput.value) {
                const etaDate = new Date(etaInput.value);
                etaDate.setDate(etaDate.getDate() + 3);
                const expiredDateStr = etaDate.toISOString().split("T")[0];
                expiredInput.value = expiredDateStr;
            } else {
                expiredInput.value = "";
            }
        }

        etaInput.addEventListener("change", function () {
            const selectedDate = new Date(this.value);
            const todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);

            if (selectedDate < todayDate) {
                Swal.fire({
                    icon: "warning",
                    title: "Invalid Date",
                    text: "Estimated Time Arrival cannot be a past date. Please select today or a future date.",
                });
                this.value = "";
                this.classList.add("is-invalid");
                expiredInput.value = "";
            } else {
                this.classList.remove("is-invalid");
                updateExpiredDate();
            }

            summarySubmit();
        });

        etaInput.addEventListener("input", function () {
            if (this.value) {
                const selectedDate = new Date(this.value);
                const todayDate = new Date();
                todayDate.setHours(0, 0, 0, 0);
                if (selectedDate >= todayDate) {
                    updateExpiredDate();
                }
            } else {
                expiredInput.value = "";
            }

            summarySubmit();
        });

        if (etaInput.value) {
            const selectedDate = new Date(etaInput.value);
            const todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);
            if (selectedDate >= todayDate) {
                updateExpiredDate();
                summarySubmit();
            }
        }
    }

    if (trnptType.value) {
        trnptType.dispatchEvent(new Event("change"));
    }
}

// ─── Item Dropzone ────────────────────────────────────────
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
                didOpen: () => {
                    Swal.showLoading();
                },
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

            console.log("temp", tempAttachments);
            console.log(
                "Latest uploaded file:",
                tempAttachments[tempAttachments.length - 1],
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

// ─── File Preview Offcanvas ──────────────────────────────
function addPreviewButtons(file) {
    const preview = file.previewElement;
    if (!preview) return;

    const removeBtn = preview.querySelector(".dz-remove");
    if (!removeBtn) return;

    const attachmentGroup = document.createElement("div");
    attachmentGroup.className = "attachment-group";
    attachmentGroup.style.display = "flex";
    attachmentGroup.style.gap = "5px";
    attachmentGroup.style.alignItems = "center";
    attachmentGroup.style.justifyContent = "end";

    const viewBtn = document.createElement("a");
    viewBtn.href = "#";
    viewBtn.innerHTML = "<i class='ti ti-eye'></i>";
    viewBtn.className = "btn btn-icon btn-info-light";
    viewBtn.onclick = function (e) {
        e.preventDefault();
        currentItemFile = file;
        showItemFilePreview(file);
    };

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

    const deleteBtn = document.createElement("a");
    deleteBtn.href = "#";
    deleteBtn.innerHTML = "<i class='ti ti-trash'></i>";
    deleteBtn.className = "btn btn-icon btn-danger-light";
    deleteBtn.onclick = function (e) {
        e.preventDefault();
        if (itemDropzone) {
            itemDropzone.removeFile(file);
        }
    };

    attachmentGroup.appendChild(viewBtn);
    attachmentGroup.appendChild(editBtn);
    attachmentGroup.appendChild(deleteBtn);

    removeBtn.parentNode.replaceChild(attachmentGroup, removeBtn);
}

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

    // applyTranslations would be here if available – we skip as not imported
    itemFileOffcanvas.show();
}

$(document).on("click", "#itemFileSaveBtn", function () {
    const newName = document.getElementById("itemFileEditName").value.trim();

    if (!newName || !currentItemFile) {
        Swal.fire({
            icon: "warning",
            title: "Empty Name",
            text: "Please enter a file name",
        });
        return;
    }

    currentItemFile.displayName = newName;

    if (currentItemFile.previewElement) {
        const $preview = $(currentItemFile.previewElement);
        const $filenameSpan = $preview.find(".dz-filename span");
        if ($filenameSpan.length) {
            $filenameSpan.text(newName);
        }
        const $filenameDiv = $preview.find(".dz-filename");
        if ($filenameDiv.length) {
            $filenameDiv.attr("data-dz-name", newName);
        }
    }

    document.getElementById("itemFileName").textContent = newName;

    Swal.fire({
        icon: "success",
        title: "Saved",
        text: "File name updated",
        timer: 1500,
        showConfirmButton: false,
    });
});

// ─── Application Attachments ─────────────────────────────
let applicationDropzones = {}; // keyed by doc.id

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
                        updateAttachmentTable();
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
    if (!applicationAttachmentDropzone) return false;
    const fileIndex = applicationAttachmentDropzone.files.findIndex(
        (fileItem) => fileItem._attachmentId === attachmentId,
    );
    if (fileIndex === -1) return false;
    const file = applicationAttachmentDropzone.files[fileIndex];
    try {
        applicationAttachmentDropzone.removeFile(file);
        return true;
    } catch (e) {
        applicationAttachmentDropzone.files.splice(fileIndex, 1);
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
        if (detailsBody) {
            detailsBody.innerHTML = "";
        }
    });
}

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
    renderApplicationAttachmentTable();
    updateAttachmentTable();
});

$(document).on("click", ".delete-attachment-btn", function () {
    const attachmentId = $(this).data("id");
    const index = applicationAttachments.findIndex(
        (item) => item.id === attachmentId,
    );
    if (index === -1) return;

    const docId = applicationAttachments[index].document_id;

    removeAttachmentFromDropzone(attachmentId, docId);
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

        renderApplicationAttachmentTable();
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

// ─── Group Preview ────────────────────────────────────────
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

            $previews.each(function () {
                const $preview = $(this);
                const $removeBtn = $preview.find(".dz-remove");
                if ($removeBtn.length && !$removeBtn.find("i").length) {
                    $removeBtn.html('<i class="ti ti-trash"></i>');
                }
            });

            Swal.close();
        }, 150);
    });
}

// ─── Show Item Agreement ──────────────────────────────────
async function showItemAgreement(item) {
    const now = new Date();
    const timestamp = now.toLocaleString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });

    const hasCondition = item.condition && item.condition.trim() !== "";

    const conditionHtml = hasCondition
        ? `
            <div id="itemConditionScroll" class="mb-2" style="
                white-space: pre-wrap;
                word-break: break-word;
                max-height: 300px;
                overflow-y: auto;
                background: #f8f9fa;
                padding: 12px;
                border-radius: 5px;
                border: 1px solid #dee2e6;
                font-size: 0.9rem;
            ">${item.condition}</div>
            <small class="text-muted d-block mb-3" data-en="Scroll to read all conditions to enable agreement" data-bm="Skrol untuk membaca semua syarat untuk membolehkan persetujuan">
                Scroll to read all conditions to enable agreement
            </small>
        `
        : "";

    const result = await Swal.fire({
        title: "Item Declaration",
        width: 600,
        html: `
            <div style="text-align: left;">
                <p class="mb-3">
                    I confirm that the information provided for this item
                    <strong>"${item.item_name}"</strong>
                    is accurate and complete.
                </p>
                ${conditionHtml}
                <p class="mb-3">
                    I understand that any false declaration may result in rejection of the application or permit cancellation.
                </p>
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="itemAgreeCheckbox" >
                    <label class="form-check-label" for="itemAgreeCheckbox">
                        I agree to the above declaration
                    </label>
                </div>
            </div>
        `,
        didOpen: (modal) => {
            const checkbox = document.getElementById("itemAgreeCheckbox");
            if (!checkbox) return;

            if (hasCondition) {
                const scrollDiv = document.getElementById(
                    "itemConditionScroll",
                );
                if (scrollDiv) {
                    checkbox.disabled = true;
                    checkbox.classList.add("opacity-50");

                    const handleScroll = () => {
                        const atBottom =
                            scrollDiv.scrollTop + scrollDiv.clientHeight >=
                            scrollDiv.scrollHeight - 5;
                        checkbox.disabled = !atBottom;
                        checkbox.classList.toggle("opacity-50", !atBottom);
                    };

                    scrollDiv.addEventListener("scroll", handleScroll);
                    handleScroll();

                    checkbox.addEventListener("click", function (e) {
                        if (this.disabled) {
                            e.preventDefault();
                            Swal.showValidationMessage(
                                "Please scroll to the bottom of the conditions to enable agreement.",
                            );
                        }
                    });
                }
            } else {
                checkbox.disabled = false;
                checkbox.classList.remove("opacity-50");
            }
        },
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

// ─── Category Pricing ──────────────────────────────────────
const CATEGORY_TIER_KG = 3000;
const CATEGORY_TIER_BASE_PRICE = 10;

function calculateCategoryPrice(totalQuantity) {
    const tierCount = Math.ceil(totalQuantity / CATEGORY_TIER_KG);
    if (tierCount <= 0) return 0;
    // Linear pricing: each tier costs BASE_PRICE
    return tierCount * CATEGORY_TIER_BASE_PRICE;
}

function recalculateCategoryAmounts() {
    const totals = {};

    tempItems.forEach((item) => {
        const cat = item.category || "Uncategorized";
        const qty = parseFloat(item.quantity) || 0;
        totals[cat] = (totals[cat] || 0) + qty;
    });

    categoryAmount = Object.keys(totals).map((categoryName) => {
        const quantity = totals[categoryName];
        return {
            category_name: categoryName,
            quantity,
            price: calculateCategoryPrice(quantity),
        };
    });

    console.log("categoryAmount", categoryAmount);
    return categoryAmount;
}

// Optional helper (if you need it elsewhere)
function categoryPrice(categoryName, quantity) {
    // This is just a wrapper if you want to call it directly
    return calculateCategoryPrice(quantity);
}

function tablePrice() {
    const table = document.getElementById("summaryTable4");
    if (!table) return;
    const tbody = table.querySelector("tbody");
    if (!tbody) return;

    // Clear existing rows
    tbody.innerHTML = "";

    // No data – show placeholder
    if (!categoryAmount || categoryAmount.length === 0) {
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.colSpan = 4;
        td.textContent = "No categories found";
        td.className = "text-center";
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }

    // Loop through each category
    categoryAmount.forEach((cat, index) => {
        const tr = document.createElement("tr");

        const tdIndex = document.createElement("td");
        tdIndex.textContent = index + 1;
        tr.appendChild(tdIndex);

        const tdCategory = document.createElement("td");
        tdCategory.textContent = cat.category_name || "Uncategorized";
        tr.appendChild(tdCategory);

        const tdQuantity = document.createElement("td");
        tdQuantity.textContent = cat.quantity || 0;
        tr.appendChild(tdQuantity);

        const tdPrice = document.createElement("td");
        tdPrice.textContent = cat.price || 0;
        tr.appendChild(tdPrice);

        tbody.appendChild(tr);
    });

    // ─── TOTAL ROW (single merged cell) ───
    const totalQty = categoryAmount.reduce(
        (sum, cat) => sum + (cat.quantity || 0),
        0,
    );
    const totalPrice = categoryAmount.reduce(
        (sum, cat) => sum + (cat.price || 0),
        0,
    );

    const trTotal = document.createElement("tr");
    const tdTotal = document.createElement("td");
    tdTotal.colSpan = 4; // merge all columns
    tdTotal.className = "fw-bold text-end"; // bold + right-aligned (optional)
    tdTotal.textContent = `Total: ${totalQty} kg  |  Total Price: RM ${totalPrice}`;
    trTotal.appendChild(tdTotal);
    tbody.appendChild(trTotal);
}

function toggleCustomItemInput(showCustom) {
    const $customWrapper = $("#customItemWrapper");

    if (showCustom) {
        $customWrapper.show();
        $("#customItemName").val("").focus();
    } else {
        $customWrapper.hide();
        $("#customItemName").val("");
    }
}

// ─── Save Consignment Attachment ──────────────────────────
function saveConsignmentAttachment() {
    document
        .getElementById("saveBtn")
        .addEventListener("click", async function (e) {
            e.preventDefault();

            console.log("Saving consignment item...");

            const selected = $("#itemCategory").select2("data");
            const itemCategoryText = selected[0]?.text;
            const itemSelectValue = $("#itemSelect").val();
            const itemSelectText = $("#itemSelect option:selected").text();
            const itemQuantity = $("#itemQuantity").val().trim();
            const itemMeasure = "kg";
            const certificateNo = $("#certificateNo").val().trim();

            // [OTHERS] Determine if custom item
            const isCustom = itemSelectValue === "others";
            let itemName = isCustom
                ? $("#customItemName").val().trim()
                : itemSelectText;
            let itemId = isCustom ? null : itemSelectValue;

            // [OTHERS] Validate custom item name
            if (isCustom && !itemName) {
                Swal.fire({
                    icon: "error",
                    title: "Custom Item Name Required",
                    text: "Please enter a custom item name.",
                });
                return;
            }

            const existingItem = editingItemId
                ? tempItems.find((obj) => obj.id === editingItemId)
                : null;

            let files = itemDropzone.getAcceptedFiles();
            if (files.length === 0 && existingItem) {
                files = existingItem.files || [];
            }

            // [OTHERS] Attachment compulsory for custom items
            if (isCustom && files.length === 0) {
                Swal.fire({
                    icon: "error",
                    title: "Attachment Required",
                    text: "Please upload an image/document for the custom item.",
                });
                return;
            }

            // Normal validation (existing)
            if (
                !itemSelectValue ||
                !itemQuantity ||
                !itemMeasure ||
                !certificateNo
            ) {
                // But if custom, itemSelectValue may be "others", so we need to treat it as valid
                // The condition above would fail for custom because itemSelectValue === "others" is truthy so it passes.
                // However we already validated custom name and attachment, so it's fine.
                // But we still need to check if itemSelectValue is empty or "others" and we have name.
                // Let's rewrite validation:
                if (!isCustom && (!itemSelectValue || itemSelectValue === "")) {
                    Swal.fire({
                        icon: "error",
                        title: "Incomplete Data",
                        text: "Please select an item.",
                    });
                    return;
                }
                if (!itemQuantity || !itemMeasure || !certificateNo) {
                    Swal.fire({
                        icon: "error",
                        title: "Incomplete Data",
                        text: "Please fill in all required fields before saving.",
                    });
                    return;
                }
            }

            // Measurement limit check (same as before)
            if (limitMeasurement) {
                let limitInKg = null;
                const selectedUnit = measurementUnits?.unit.find(
                    (unit) =>
                        unit.cate_code.toLowerCase() ===
                            limitMeasurement.toLowerCase() &&
                        unit.is_del === false,
                );
                if (selectedUnit) {
                    limitInKg = limit * selectedUnit.conversion.conversion;
                }

                let selectedItemInKg = null;
                const selectedItemUnit = measurementUnits?.unit.find(
                    (unit) =>
                        unit.cate_code.toLowerCase() ===
                            itemMeasure.toLowerCase() && unit.is_del === false,
                );
                if (selectedItemUnit) {
                    selectedItemInKg =
                        itemQuantity * selectedItemUnit.conversion.conversion;
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

            const itemPayload = {
                id: existingItem ? existingItem.id : generateUUID(),
                item_id: itemId,
                item_name: itemName,
                quantity: itemQuantity,
                measure: itemMeasure,
                certificateNo: certificateNo,
                category: itemCategoryText,
                value: 0,
                files: files,
                agreedAt: null,
                condition: currentItemCondition,
                // [OTHERS] flag to identify custom items if needed later
                isCustom: isCustom,
            };

            const agreed = await showItemAgreement(itemPayload);

            if (!agreed) {
                return;
            }

            if (existingItem) {
                const index = tempItems.findIndex(
                    (obj) => obj.id === existingItem.id,
                );
                if (index !== -1) {
                    tempItems[index] = itemPayload;
                }
            } else {
                tempItems.push(itemPayload);
            }

            editingItemId = null;
            renderAllItems();
            resetAddItemModal();

            const modalEl = document.getElementById("addItemModal");
            bootstrap.Modal.getInstance(modalEl).hide();

            document
                .getElementById("addItemModal")
                .addEventListener("hidden.bs.modal", function () {
                    editingItemId = null;
                });

            summarySubmit();
        });
}

function resetAddItemModal() {
    $("#itemSelect").val(null).trigger("change");
    $("#itemQuantity").val("");
    $("#itemMeasure").val("").trigger("change");
    $("#certificateNo").val("");
    if ($("#itemPurpose").length) $("#itemPurpose").val("").trigger("change");
    if ($("#itemUses").length) $("#itemUses").val(null).trigger("change");
    if ($("#itemValue").length) $("#itemValue").val("");

    // Custom item state – hide and clear
    $("#customItemName").val("");
    toggleCustomItemInput(false);
    $(".attachmentInstruction").html("");

    if (itemDropzone) itemDropzone.removeAllFiles(true);
}

// ─── Render Items ──────────────────────────────────────────
function renderAllItems() {
    recalculateCategoryAmounts();

    const tableBody = document.querySelector("#itemListTbl tbody");
    tableBody.innerHTML = "";

    let totalQty = 0;
    let totalKg = 0;

    tempItems.forEach((item, index) => {
        const qty = parseFloat(item.quantity) || 0;
        totalQty += qty;
        totalKg += getKgForItem(item);

        tableBody.insertAdjacentHTML(
            "beforeend",
            `<tr id="item-row-${item.id}">
            
                <td>${item.category}</td>
                <td>${item.item_name}</td>
                <td class="text-wrap">${qty} ${item.measure || ""}</td>
              
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

    const table = document.querySelector("#itemListTbl");
    let tfoot = table.querySelector("tfoot");
    if (!tfoot) {
        tfoot = document.createElement("tfoot");
        table.appendChild(tfoot);
    }
    if (tempItems.length > 0) {
        tfoot.innerHTML = `
            <tr style="font-weight: bold; background-color: var(--gray-2);">
                <td colspan="2" style="text-align: right; font-weight: 800">Total:</td>
                <td class="text-center">
                     ${totalKg.toFixed(2)} kg
                </td>
            </tr>
        `;
    } else {
        tfoot.innerHTML = "";
    }
}

// ─── View More Item ────────────────────────────────────────
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

        const agreementBanner = item.agreedAt
            ? `<div class="alert alert-success mb-3 d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>
                    <strong>Declaration Confirmed</strong>
                    <div class="small text-muted">Agreed on: ${item.agreedAt}</div>
                </div>
            </div>`
            : `<div class="alert alert-warning mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Pending Agreement</strong>
                - User has not confirmed this item yet.
            </div>`;

        let detailsRows = `
            <div class="col-12 col-lg-6">
                <p>
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-scale-balanced"></i></span>
                        Category:
                    </strong> ${item.category}
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p>
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-tag"></i></span>
                        Item Name:
                    </strong> ${item.item_name}
                </p>
            </div>
            <div class="col-12 col-lg-6">
                <p>
                    <strong class="me-1">
                        <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-scale-balanced"></i></span>
                        Quantity:
                    </strong> ${item.quantity} ${item.measure}
                </p>
            </div>
            
        `;

        if (item.certificateNo) {
            detailsRows += `
                <div class="col-12 col-lg-6">
                    <p>
                        <strong class="me-1">
                            <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-certificate"></i></span>
                            Certificate No:
                        </strong> ${item.certificateNo}
                    </p>
                </div>
            `;
        }

        if (item.value && item.value != 0) {
            detailsRows += `
                <div class="col-12 col-lg-6">
                    <p>
                        <strong class="me-1">
                            <span class="avatar avatar-sm avatar-rounded bd-gray-500"><i class="fa-solid fa-money-bill"></i></span>
                            Value:
                        </strong> RM ${item.value}
                    </p>
                </div>
            `;
        }

        detailsDiv.innerHTML = `
            ${agreementBanner}

            <div class="pd-section-label mt-4 mb-2">Consignment Info</div>
            <div class="p-2 row" style="background: var(--gray-1); border: 1px solid var(--default-border); border-radius: 0.6rem;">
                ${detailsRows}
            </div>
        `;

        const hasCondition = item.condition && item.condition.trim() !== "";
        if (conditionItem) {
            conditionItem.innerHTML = hasCondition
                ? `<div style="white-space: pre-wrap; word-break: break-word;">${item.condition}</div>`
                : `No special conditions for this item.`;
        }
        if (conditionCount) {
            conditionCount.textContent = hasCondition ? "1" : "0";
        }

        attachList.innerHTML = "";
        currentItemAttachments = item.files || [];

        if (!item.files || item.files.length === 0) {
            attachList.innerHTML = `
                <div class="text-muted text-center py-3">
                    No attachments
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
                                ${typeDisplay}
                                · ${(file.size / 1024).toFixed(1)} KB
                            </div>
                        </div>
                    </div>
                `;
            });
            attachList.innerHTML = chipsHTML;
            if (attachmentCount)
                attachmentCount.textContent = item.files.length;
        }

        const offcanvasEl = document.getElementById("ItemDetailsOffcanvas");
        if (offcanvasEl) {
            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
        }
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

// ─── Delete Item ───────────────────────────────────────────
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
            return;
        }

        tempItems.splice(index + 1, 0, duplicated);
        renderAllItems();
        summarySubmit();

        console.log("Copied item:", id, "->", duplicated.id, tempItems);

        Swal.fire({
            icon: "success",
            title: "Item Copied",
            text: "A duplicate of the item has been added.",
            timer: 1800,
            showConfirmButton: false,
        });
    });
}

// ─── Populate Edit Form ──────────────────────────────────
function populateEditForm(item) {
    const $select = $("#itemSelect");
    const existsInDropdown = $select.find(`option[value="${item.item_id}"]`).length > 0;

    if (item.isCustom || !existsInDropdown) {
        $("#itemSelect").val("others").trigger("change");
        toggleCustomItemInput(true);
        $("#customItemName").val(item.item_name);
        $(".attachmentInstruction")
            .html(`<span style="color:red;" data-en="Attachment is mandatory for custom items. Please upload the item image or document"
                   data-bm="Lampiran adalah wajib untuk item yang tiada dalam senarai. Sila muat naik gambar atau dokumen">
                * Attachment is mandatory for custom items. Please upload the item image or document.</span>`)
            .show();
    } else {
        $("#itemSelect").val(item.item_id).trigger("change");
        toggleCustomItemInput(false);
        $(".attachmentInstruction").html("").hide();
    }

    $("#itemQuantity").val(item.quantity);
    $("#certificateNo").val(item.certificateNo || "");

    // ─── Measurement Unit ─────────────────────────────────────────
    if (item.measure) {
        $("#itemMeasure").val(item.measure).trigger("change");
    } else {
        $("#itemMeasure").val("").trigger("change");
    }

    // ─── Value ─────────────────────────────────────────────────────
    if (item.value !== undefined && item.value !== null) {
        $("#itemValue").val(item.value);
    } else {
        $("#itemValue").val("");
    }

    // ─── Purpose ──────────────────────────────────────────────────
    const $purposeSelect = $("#itemPurpose");
    let purposeSet = false;
    if (item.purpose) {
        // Try to match by description (data-description)
        $purposeSelect.find("option").each(function() {
            const desc = $(this).data("description");
            if (desc && desc.trim() === item.purpose.trim()) {
                $purposeSelect.val($(this).val()).trigger("change");
                purposeSet = true;
                return false;
            }
        });
        // If not found, try matching by text content
        if (!purposeSet) {
            $purposeSelect.find("option").each(function() {
                if ($(this).text().trim() === item.purpose.trim()) {
                    $purposeSelect.val($(this).val()).trigger("change");
                    purposeSet = true;
                    return false;
                }
            });
        }
    }
    if (!purposeSet) {
        $purposeSelect.val("").trigger("change");
    }

    // ─── Uses ──────────────────────────────────────────────────────
    if (item.uses) {
        $("#itemUses").val(item.uses).trigger("change");
    } else {
        $("#itemUses").val(null).trigger("change");
    }

    // ─── Restore files in Dropzone ──────────────────────────────
    if (itemDropzone) {
        itemDropzone.removeAllFiles(true);
        if (item.files && item.files.length > 0) {
            item.files.forEach((file) => {
                itemDropzone.addFile(file);
            });
        }
    }

    Swal.close();
}

// ─── FIXED: Edit Item ──────────────────────────────────────
function editItem() {
    $(document).on("click", ".edit-item", function (e) {
        e.preventDefault();

        const id = $(this).data("id");
        const item = tempItems.find((obj) => obj.id === id);
        if (!item) return console.warn("Item not found for id:", id);

        editingItemId = id;
        resetAddItemModal();

        console.log("Editing item:", id, item);

        const modalEl = document.getElementById("addItemModal");
        bootstrap.Modal.getOrCreateInstance(modalEl).show();

        Swal.fire({
            title: "Loading item...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        loadCategory()
            .then(() => {
                const categoryText = item.category;
                if (categoryText) {
                    const $catSelect = $("#itemCategory");
                    let optionValue = null;
                    $catSelect.find('option').each(function() {
                        if ($(this).text().trim() === categoryText.trim()) {
                            optionValue = $(this).val();
                            return false;
                        }
                    });
                    if (optionValue) {
                        $catSelect.val(optionValue).trigger('change');
                    }
                }

                const categoryId = $("#itemCategory").val();
                const countryCode = $("#expcountryCode").val();
                if (categoryId && countryCode) {
                    return loadConsignmentSelection(categoryId);
                } else {
                    return Promise.resolve();
                }
            })
            .then(() => {
                populateEditForm(item);
                Swal.close();
            })
            .catch((err) => {
                console.error("Error loading item data:", err);
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to load item data for editing.",
                });
            });
    });
}

// ─── Validate Required Documents ──────────────────────────
function validateRequiredDocuments() {
    let allUploaded = true;
    const $requiredBlocks = $('.consignment-doc-block[data-required="true"]');

    if ($requiredBlocks.length === 0) {
        return true; // no required documents, skip validation
    }

    $requiredBlocks.each(function() {
        const docId = $(this).data('doc-id');
        const hasAttachment = applicationAttachments.some(
            (a) => String(a.document_id) === String(docId)
        );
        if (!hasAttachment) {
            allUploaded = false;
            return false; // break the loop
        }
    });

    return allUploaded;
}

// ─── Intercept "Next" button clicks (step 1 validation) ──
$(document).on('click', '.wizard-btn.next', function(e) {
    const $activeStep = $('.wizard-step.active');
    // Only validate when moving from step 1 (Certificate Details)
    if ($activeStep.length && $activeStep.data('step') === 1) {
        if (!validateRequiredDocuments()) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const lang = getCurrentLang();
            const title = lang === 'bm' ? 'Dokumen Wajib' : 'Required Documents';
            const message = lang === 'bm'
                ? 'Sila muat naik semua dokumen yang diperlukan sebelum meneruskan.'
                : 'Please upload all required documents before proceeding.';

            Swal.fire({
                icon: 'warning',
                title: title,
                text: message,
                confirmButtonText: lang === 'bm' ? 'OK' : 'OK',
            });
            return false;
        }
    }
});

// ─── Save Application ──────────────────────────────────────
function saveapplication(isDraft = false) {
    const form = document.querySelector("#wizardForm");
    if (!form) return console.error("Form not found");

    const formData = new FormData(form);

    formData.append("is_draft", isDraft ? 1 : 0);

    const currentPermitDetails = {
        eta: $("#eta").val(),
        tranType: $("#trnptType").val(),
        entrypoint: $("#entryPoint").val(),
        applCate: $("#app_cate").val(),
        vehicle_ids: $("#selectVehicle").val() || [],
        ptnNumber: $("#ptnNumber").val() || "",
    };

    formData.append("exporterData", JSON.stringify(importer));
    formData.append("importerData", JSON.stringify(exporter));
    formData.append("permitDetails", JSON.stringify(currentPermitDetails));

    tempItems.forEach((item, index) => {
        const { files, ...otherData } = item;
        formData.append(`items[${index}][data]`, JSON.stringify(otherData));

        if (files && files.length > 0) {
            files.forEach((file) => {
                formData.append("files[]", file);
                formData.append("file_item_index[]", index);
            });
        }
    });

    applicationAttachments.forEach((attachment) => {
        if (attachment.file) {
            formData.append("application_files[]", attachment.file);
            formData.append(
                "application_files_document_type[]",
                attachment.document_type || "",
            );
            formData.append(
                "application_files_description[]",
                attachment.description || "",
            );
        }
    });

    formData.append("categoryAmount", JSON.stringify(categoryAmount));

    Swal.fire({
        title: isDraft ? "Saving Draft..." : "Submitting...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: "/public/save_application_consignment",
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
                    window.location.href = "/public/view_all_consignment";
                }, 1500);
            } else {
                window.location.reload();
            }
        },
        error: function (xhr) {
            const errorMessage =
                xhr.responseJSON?.message ||
                "Failed to save application. Please check your connection and try again.";
            Swal.fire("Error", errorMessage, "error");
        },
    });
}

// ─── Summary Submit ──────────────────────────────────────
export function summarySubmit() {
    const targetTable = document.querySelector("#summaryTable3 tbody");

    // --- IMPORTER (selected) & EXPORTER (auto-filled) ---
    const exporterName = importer ? importer.fullname : "";
    const exporterPhone = importer ? importer.phone_number : "";
    const exporterAddress = importer
        ? [importer.address_1, importer.address_2]
              .filter((x) => x && x.trim() !== "")
              .join(", ")
        : "";

    const importerName = exporter ? exporter.name : "";
    const importerPhone = exporter ? exporter.phone_no : "";
    const importerAddress = exporter ? exporter.address : "";
    const importerCountry = exporter ? exporter.country : "";

    document.getElementById("importerName").textContent = importerName;
    document.getElementById("importerPhoneno").textContent = importerPhone;
    document.getElementById("simpAdd").textContent = importerAddress;

    document.getElementById("sexpName").textContent = exporterName;
    document.getElementById("sexpfonno").textContent = exporterPhone;
    document.getElementById("sexpAddress").textContent = exporterAddress;
    document.getElementById("sexpCountry").textContent = importerCountry;

    // --- PERMIT DETAILS ---
    const permitDetails = {
        applCate: document.getElementById("app_cate").value,
        eta: document.getElementById("eta").value,
        tranType: document.getElementById("trnptType").value,
        entrypoint: document.getElementById("entryPoint").value,
        ptnNumber: $("#ptnNumber").val() || "",
    };

    document.getElementById("seta").textContent = permitDetails.eta;
    document.getElementById("strty").textContent = permitDetails.tranType;
    document.getElementById("sentryp").textContent = entryName || "";
    document.getElementById("sptnnumber").textContent = permitDetails.ptnNumber;

    // --- VEHICLES ---
    const selectedIds = $("#selectVehicle").val() || [];
    const selectedVehicles = vehicleListArray.filter((v) =>
        selectedIds.includes(String(v.id)),
    );
    const vehicleListStr = selectedVehicles
        .map((v) => v.vehicle_number)
        .join(", ");
    const sVehicleEl = document.getElementById("svehicle");
    if (sVehicleEl) {
        sVehicleEl.textContent = vehicleListStr || "-";
    }

    // --- CONSIGNMENT ITEMS ---
    targetTable.innerHTML = "";

    tempItems.forEach((item, index) => {
        const attachmentHTML = `
            <button class="btn btn-sm btn-primary view-more-item" data-id="${item.id}">
                View More
            </button>
        `;

        targetTable.insertAdjacentHTML(
            "beforeend",
            `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.item_name || ""}</td>
                    <td>${item.quantity || ""} ${item.measure || ""}</td>
                    <td>${attachmentHTML}</td>
                </tr>
            `,
        );
    });

    const table3 = document.querySelector("#summaryTable3");
    let tfoot3 = table3.querySelector("tfoot");
    if (!tfoot3) {
        tfoot3 = document.createElement("tfoot");
        table3.appendChild(tfoot3);
    }
    if (tempItems.length > 0) {
        let totalQty = 0;
        let totalKg = 0;
        tempItems.forEach((item) => {
            const qty = parseFloat(item.quantity) || 0;
            totalQty += qty;
            totalKg += getKgForItem(item);
        });
        tfoot3.innerHTML = `
            <tr style="font-weight: bold; background-color: var(--gray-2);">
                <td colspan="2" style="text-align: right; font-weight: 800;">Total:</td>
                <td>${totalKg.toFixed(2)} kg</td>
                <td></td>
            </tr>
        `;
    } else {
        tfoot3.innerHTML = "";
    }

    updateAttachmentTable();
    renderCategoryPriceSummary();
    tablePrice();
}

function renderCategoryPriceSummary() {
    const container = document.getElementById("summaryCategoryPrices");
    if (!container) return;

    if (!categoryAmount.length) {
        container.innerHTML = `<p class="text-muted mb-0">No items added yet.</p>`;
        return;
    }

    const total = categoryAmount.reduce((sum, c) => sum + c.price, 0);

    container.innerHTML = `
        <table class="table table-sm mb-2">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-end">Total Quantity (kg)</th>
                    <th class="text-end">Price (RM)</th>
                </tr>
            </thead>
            <tbody>
                ${categoryAmount
                    .map(
                        (c) => `
                    <tr>
                        <td>${c.category_name}</td>
                        <td class="text-end">${c.quantity.toLocaleString()}</td>
                        <td class="text-end">${c.price.toFixed(2)}</td>
                    </tr>
                `,
                    )
                    .join("")}
            </tbody>
            <tfoot>
                <tr style="font-weight: 800;">
                    <td colspan="2" class="text-end">Total:</td>
                    <td class="text-end">RM ${total.toFixed(2)}</td>
                </tr>
            </tfoot>
        </table>
    `;
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
                        <td class="text-wrap">${attachment.description || "—"}</td>
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

// ─── Initialize ──────────────────────────────────────────
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

        if ($("#selectVehicle").length) {
            await fetchVehicleList();
            handleVehicleChange();
            initAddVehicleModal();
        }

        $("#wizardForm").on(
            "change input",
            "input, select, textarea",
            function () {
                change = 1;
            },
        );

        initAddExporterModal();
        initImporterSearch();
        permitDetails();
        initApplicationAttachments();
        initAttachmentOffcanvas();
        initAttachmentNavigation();
        initItemAttachmentOffcanvas();
        initItemAttachmentNavigation();
        itemConsigment();
        saveConsignmentAttachment();
        viewMore();
        deleteItem();
        copyItem();
        editItem();

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

        if ($("#itemPurpose").length) {
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
        }

        $("#itemCategory").on("change", function () {
            const itemId = $(this).val();
            loadConsignmentSelection(itemId);
        });

        // ===== [OTHERS] Item Select change handler – detect "others" =====
        // ===== [OTHERS] Item Select change handler – show/hide custom input =====
        $("#itemSelect").on("change", function () {
            const selectedVal = $(this).val();
            const $itemUses = $("#itemUses");

            $itemUses
                .empty()
                .append('<option value="">-- Select Uses --</option>');

            if (!selectedVal) {
                toggleCustomItemInput(false);
                $(".attachmentInstruction").html("");
                return;
            }

            if (selectedVal === "others") {
                // Show custom input, keep select visible
                toggleCustomItemInput(true);
                $(".attachmentInstruction").html(
                    `<span style="color:red;" data-en="Attachment is mandatory for custom items. Please upload the item image or document"
                   data-bm="Lampiran adalah wajib untuk item yang tiada dalam senarai. Sila muat naik gambar atau dokumen">
                * Attachment is mandatory for custom items. Please upload the item image or document.</span>`,
                );
                return;
            }

            // Normal item selected – hide custom input
            toggleCustomItemInput(false);
            $(".attachmentInstruction").html("");
            loadDetails(selectedVal);
        });

        $("#mdlAddItemBtn").on("click", function (e) {
            e.preventDefault();
            loadCategory();
            loadConsignmentSelection();
        });

        // [OTHERS] Reset custom state when modal is closed
        $("#addItemModal").on("hidden.bs.modal", function () {
            resetAddItemModal();
        });

        // Submit button handler
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

            const agreed = await showItemConditions();
            if (!agreed) {
                return;
            }

            saveapplication(false);
        });

        $(document).on(
            "click",
            `#logoutButton, 
            .app-sidebar.sticky button, .app-sidebar.sticky a,
            .breadcrumb .breadcrumb-item a`,
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
                    }
                });
            },
        );

        $(document).on("click", ".doc-details-btn", function () {
            const name = $(this).data("doc-name");
            const targetId = $(this).data("description-target");
            const sourceHtml =
                document.getElementById(targetId)?.innerHTML || "";

            $("#docDescriptionModalLabel").text(name);
            $(".doc-description-modal-body").html(
                sourceHtml.trim() ||
                    "<p class='text-muted mb-0'>No description available.</p>",
            );

            const modal = bootstrap.Modal.getOrCreateInstance(
                document.getElementById("docDescriptionModal"),
            );
            modal.show();
        });

        $(document).on("input", ".attachment-description-input", function () {
            const attachmentId = $(this).data("id");
            const attachment = applicationAttachments.find(
                (a) => a.id === attachmentId,
            );
            if (attachment) {
                attachment.description = $(this).val();
            }
        });
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

// ─── ITEM_CONDITIONS (bilingual) ────────────────────────
const ITEM_CONDITIONS = [
    {
        title_en: "General Consignment Requirements",
        title_bm: "Syarat Konsainan Umum",
        title: '<span data-en="General Consignment Requirements" data-bm="Syarat Konsainan Umum">General Consignment Requirements</span>',
        icon: "bi-box-seam",
        content: `
        <p>
            <span data-en="The applicant shall ensure that all items declared under this consignment application are accurate, complete and compliant with the applicable import/export regulations." 
                  data-bm="Pemohon hendaklah memastikan bahawa semua item yang diisytiharkan di bawah permohonan konsainan ini adalah tepat, lengkap dan mematuhi peraturan import/eksport yang berkenaan.">
            The applicant shall ensure that all items declared under this consignment application are accurate, complete and compliant with the applicable import/export regulations.</span>
        </p>
        <p>
            <span data-en="Any false declaration, omission of information, misleading description, incorrect quantity or inaccurate valuation may result in rejection of the application, permit cancellation, investigation, enforcement action or prosecution." 
                  data-bm="Sebarang pengisytiharan palsu, peninggalan maklumat, keterangan yang mengelirukan, kuantiti yang tidak betul atau penilaian yang tidak tepat boleh mengakibatkan penolakan permohonan, pembatalan permit, penyiasatan, tindakan penguatkuasaan atau pendakwaan.">
            Any false declaration, omission of information, misleading description, incorrect quantity or inaccurate valuation may result in rejection of the application, permit cancellation, investigation, enforcement action or prosecution.</span>
        </p>
        <p>
            <span data-en="Applicants are responsible for maintaining supporting documentation for inspection purposes for a minimum period required by the authority." 
                  data-bm="Pemohon bertanggungjawab untuk menyimpan dokumentasi sokongan untuk tujuan pemeriksaan bagi tempoh minimum yang diperlukan oleh pihak berkuasa.">
            Applicants are responsible for maintaining supporting documentation for inspection purposes for a minimum period required by the authority.</span>
        </p>
    `,
    },
];

async function showItemConditions() {
    let currentPage = 0;
    let currentLang = getCurrentLang();

    while (currentPage < ITEM_CONDITIONS.length) {
        const page = ITEM_CONDITIONS[currentPage];
        const titleText =
            currentLang === "bm" ? page.title_bm : page.title_en || page.title;
        const iconClass = page.icon || "bi-info-circle";

        const result = await Swal.fire({
            title: `
                <span data-en="${page.title_en || page.title}" data-bm="${page.title_bm || page.title}">
                    <i class="bi ${iconClass} me-2 text-primary"></i>${titleText}
                </span>
            `,
            width: 900,
            html: `
                <div style="text-align:left; max-height:350px; overflow:auto; padding-right:10px;">
                    ${page.content}
                </div>
                <div class="mt-3 text-muted">
                    <span data-en="Page ${currentPage + 1} of ${ITEM_CONDITIONS.length}" data-bm="Halaman ${currentPage + 1} daripada ${ITEM_CONDITIONS.length}">Page ${currentPage + 1} of ${ITEM_CONDITIONS.length}</span>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText:
                currentPage === ITEM_CONDITIONS.length - 1
                    ? '<span data-en="Continue" data-bm="Teruskan">Continue</span>'
                    : '<span data-en="Next" data-bm="Seterusnya">Next</span>',
            cancelButtonText:
                '<span data-en="Cancel" data-bm="Batal">Cancel</span>',
            allowOutsideClick: false,
            didOpen: (modal) => {
                applyTranslations(modal);
                applyTranslations(Swal.getConfirmButton().parentElement);
            },
        });

        if (!result.isConfirmed) {
            return false;
        }

        currentPage++;
    }

    return await showFinalAgreement();
}

async function showFinalAgreement() {
    let agreed = false;

    const result = await Swal.fire({
        title: '<span data-en="Declaration & Agreement" data-bm="Pengisytiharan & Persetujuan">Declaration & Agreement</span>',
        width: 900,
        html: `
            <div id="agreementScroll" style="height:350px; overflow-y:auto; text-align:left; border:1px solid #ddd; padding:15px; border-radius:8px;">
                <h5 class="mb-3"><span data-en="Terms and Conditions" data-bm="Terma dan Syarat">Terms and Conditions</span></h5>
                <p>
                    <span data-en="1. The applicant declares that all information provided in this application, including item descriptions, quantities, and values, is true, accurate, and complete." 
                          data-bm="1. Pemohon mengisytiharkan bahawa semua maklumat yang diberikan dalam permohonan ini, termasuk perihalan item, kuantiti dan nilai, adalah benar, tepat dan lengkap.">
                    1. The applicant declares that all information provided in this application is true, accurate, and complete.</span>
                </p>
                <p>
                    <span data-en="2. The applicant confirms that all goods declared are compliant with current import regulations and standards enforced by the relevant authority." 
                          data-bm="2. Pemohon mengesahkan bahawa semua barang yang diisytiharkan mematuhi peraturan import dan piawaian semasa yang dikuatkuasakan oleh pihak berkuasa berkaitan.">
                    2. The applicant confirms that all goods declared are compliant with current import regulations and standards.</span>
                </p>
                <p>
                    <span data-en="3. The applicant acknowledges that all consignments are subject to physical inspection, verification, and sampling by authorized officers at any designated entry point." 
                          data-bm="3. Pemohon mengakui bahawa semua konsainan tertakluk kepada pemeriksaan fizikal, pengesahan dan pengambilan sampel oleh pegawai yang diberi kuasa di mana-mana pintu masuk yang ditetapkan.">
                    3. The applicant acknowledges that all consignments are subject to inspection and sampling by authorized officers.</span>
                </p>
                <p>
                    <span data-en="4. The applicant agrees to maintain all supporting documentation (invoices, certificates, permits) for audit purposes for the period required by law." 
                          data-bm="4. Pemohon bersetuju untuk menyimpan semua dokumen sokongan (invois, sijil, permit) bagi tujuan audit untuk tempoh yang ditetapkan oleh undang-undang.">
                    4. The applicant agrees to maintain all supporting documentation for audit purposes.</span>
                </p>
                <p>
                    <span data-en="5. The authority reserves the right to suspend or revoke any permit or application if information provided is found to be false, misleading, or fraudulent, and legal action may be taken accordingly." 
                          data-bm="5. Pihak berkuasa berhak untuk menggantung atau membatalkan sebarang permit atau permohonan jika maklumat yang diberikan didapati palsu, mengelirukan atau berunsur penipuan, dan tindakan undang-undang boleh diambil sewajarnya.">
                    5. The authority reserves the right to revoke any application if information provided is found to be false or misleading.</span>
                </p>
                <p><strong><span data-en="END OF DECLARATION" data-bm="TAMAT PENGISYTIHARAN">END OF DECLARATION</span></strong></p>
            </div>
            <div class="form-check mt-3 text-start">
                <input class="form-check-input" type="checkbox" id="agreeCheckbox" disabled>
                <label class="form-check-label" for="agreeCheckbox">
                    <span data-en="I have read and agree to all conditions." data-bm="Saya telah membaca dan bersetuju dengan semua syarat.">I have read and agree to all conditions.</span>
                </label>
            </div>
        `,
        didOpen: (modal) => {
            applyTranslations(modal);
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
                    '<span data-en="Please read the declaration and tick the agreement checkbox." data-bm="Sila baca pengisytiharan dan tandakan kotak persetujuan.">Please read the declaration and tick the agreement checkbox.</span>',
                );
                return false;
            }
            agreed = true;
            return true;
        },
        allowOutsideClick: false,
        showCancelButton: true,
        confirmButtonText:
            '<span data-en="I Agree" data-bm="Saya Setuju">I Agree</span>',
        cancelButtonText:
            '<span data-en="Cancel" data-bm="Batal">Cancel</span>',
    });

    return result.isConfirmed && agreed;
}