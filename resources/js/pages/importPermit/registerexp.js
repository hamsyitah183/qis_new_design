import $ from "jquery";
import Swal from "sweetalert2";

// Global state
let exporterListArray = [];
let entryName = null;
let exporter = null;
let importer = null;
let importerData = null;
let impAddrs = null;

// --------if self apply -----------
function selfImport() {
    if (window.location.pathname.includes("public/import_permit_application")) {
        importer = window.authUser;
        console.log('importer', importer)
    }
}

// ------------------------- Exporter List -------------------------
function fetchExporterList() {
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

            if ($select.hasClass("xintra-select2")) {
                $select.trigger("change.select2");
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

        loadConsignmentSelection();
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

    console.log("country code", countryCode);

    $select.empty().append('<option value="">-- Select Item --</option>');

    if (!countryCode) return;

    fetch(`${window.baseUrl}/public/get_consignment/${countryCode}`)
        .then((res) => res.json())
        .then((data) => {
            data.forEach((row) =>
                $select.append(
                    `<option value="${row.id}">${row.entry_display}</option>`
                )
            );
        });
}

function loadUses(id) {
    const $select = $("#itemUses");
    $select.empty().append('<option value="">-- Select Item --</option>');

    if (!id) return;

    fetch(`${window.baseUrl}/public/consignment_uses/${id}`)
        .then((res) => res.json())
        .then((data) => {
            data.data.forEach((row) =>
                $select.append(`<option value="${row}">${row}</option>`)
            );
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
    const detailsSelect = document.getElementById("transportDetails");

    if (!trnptType) return;

    trnptType.addEventListener("change", function () {
        const value = this.value; // Air / Sea / Land
        const route = this.dataset.route; // /public/transport/options

        if (!value || route === "#") {
            detailsSelect.innerHTML =
                '<option value="">-- Select Option --</option>';
            return;
        }

        // build URL with the selected value as query param
        const url = `${route}?type=${encodeURIComponent(value)}`;
        console.log(url);
        $.ajax({
            url: url, // the same URL you built earlier: route + ?type=value
            type: "GET",
            dataType: "json",
            success: function (data) {
                console.log("something here");
                console.log(data);

                // rebuild next dropdown
                const detailsSelect = $("#entryPoint"); // if using jQuery
                let options =
                    '<option value="">-- Select Entry Point --</option>';
                data.forEach(function (item) {
                    options += `<option value="${item.id}" 
                    data-entry_name = "${item.entry_display}" 
                    
                    >${item.entry_display}</option>`;
                });
                detailsSelect.html(options);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                console.log(xhr.responseText); // helpful for Laravel debug messages
            },
        });
    });

    $("#entryPoint").on("change", function (e) {
        e.preventDefault();

        // get the selected <option>
        entryName = $(this).find("option:selected").data("entry_name");

        console.log("I picked entry:", entryName);

        summarySubmit();
    });
}

// ------------------------------summary details ---------------------
export function summarySubmit() {
    const generateBtn = document.getElementById("generateSummary");

    const sourceTable = document.querySelector("#itemListTbl tbody");
    const targetTable = document.querySelector("#summaryTable3 tbody");

    const impAddrs = importer
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
    console.log(permitDetails);

    document.getElementById("importerName").textContent = importer.fullname;
    document.getElementById("importerPhoneno").textContent =
        importer.phone_number;
    document.getElementById("simpAdd").textContent = impAddrs;

    document.getElementById("sexpName").textContent = exporter.name;
    document.getElementById("sexpfonno").textContent = exporter.phone_no;
    document.getElementById("sexpAddress").textContent = exporter.address;
    document.getElementById("sexpCountry").textContent = exporter.country;

    document.getElementById("seta").textContent = permitDetails.eta;
    document.getElementById("strty").textContent = permitDetails.tranType;
    document.getElementById("sentryp").textContent = entryName;

    // ✅ Clear existing rows in summary table
    targetTable.innerHTML = "";

    // ✅ Copy each row from source table
    const rows = sourceTable.querySelectorAll("tr");
    rows.forEach((row, index) => {
        const cols = row.querySelectorAll("td");
        console.log(cols);

        // Extract text content from each column
        const rowData = Array.from(cols).map((td) => td.textContent.trim());

        // Build new row for summary table (excluding "Action" column)
        targetTable.insertAdjacentHTML(
            "beforeend",
            `
            <tr>
            <td>${index + 1}</td>
            <td>${rowData[1] || ""}</td>
            <td>${rowData[2] || ""}</td>
            <td>${rowData[3] || ""}</td>
            <td>${rowData[4] || ""}</td>
            <td>${rowData[5] || ""}</td>
            <td>${rowData[6] || ""}</td>
            </tr>
        `
        );
    });

    // ✅ Optional: Scroll to or highlight summary section
}

function saveapplication() {
    const form = document.querySelector("#wizardForm");
    if (!form) {
        console.error("Form not found:", form);
        return;
    }

    const formData = new FormData(form);

    document
        .querySelector("#summaryTable3")
        .scrollIntoView({ behavior: "smooth" });

    formData.append("exporterData", JSON.stringify(exporter));
    formData.append("importerData", JSON.stringify(importer));
    formData.append("permitDetails", JSON.stringify(permitDetails));
    formData.append("items", JSON.stringify(tempItems));
    console.log(" Summary  generated!");

    Swal.fire({
        title: "Loading...",
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
        processData: false, // IMPORTANT: do not convert to string
        contentType: false, // IMPORTANT: allow multipart/form-data
        success: function (response) {
            // console.log("SUCCESS RESPONSE:");
            // console.log(response);
            Swal.fire({
                icon: "success",
                title: "Application submited!",
                text: "The exporter has been successfully added to the list.",
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true,
                position: "center",
            });
            setTimeout(() => {
                window.location.href = "/public/view_all_application";
            }, 1500);
        },
        error: function (xhr) {
            Swal.close();
            Swal.fire("Error", "Error", "error");
            console.error("ERROR RESPONSE:");
            console.error();
        },
    });
}

// ------------------------- Initialize -------------------------
$(document).ready(function () {
    fetchExporterList();
    handleExporterChange();
    initAddExporterModal();
    initImporterSearch();
    permitDetails();

    selfImport();

    $("#itemSelect").on("change", function () {
        loadUses($(this).val());
    });
    // Make globally accessible
    window.loadConsignmentSelection = loadConsignmentSelection;

    $(document).on("click", "#submitApps", function () {
        console.log("submit clicked!");
        saveapplication();
    });
});
