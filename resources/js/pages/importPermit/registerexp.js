import Dropzone from "dropzone";
import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import { applyTranslations, generateUUID, getAuthUser } from "../../app";
import "dropzone/dist/dropzone.css";
import { render } from "react-dom/cjs/react-dom.production.min";

// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

const ITEM_CONDITIONS = [
    {
        title_en: "General Import Requirements",
        title_bm: "Syarat Import Umum",
        title: '<span data-en="General Import Requirements" data-bm="Syarat Import Umum">General Import Requirements</span>',
        icon: "bi-box-seam",
        content: `
        <p>
            <span data-en="The importer shall ensure that all goods declared under this application are accurate, complete and compliant with the applicable import regulations." 
                  data-bm="Pengimport hendaklah memastikan bahawa semua barang yang diisytiharkan di bawah permohonan ini adalah tepat, lengkap dan mematuhi peraturan import yang berkenaan.">
            The importer shall ensure that all goods declared under this application are accurate, complete and compliant with the applicable import regulations.</span>
        </p>

        <p>
            <span data-en="Any false declaration, omission of information, misleading description, incorrect quantity or inaccurate valuation may result in rejection of the application, permit cancellation, investigation, enforcement action or prosecution." 
                  data-bm="Sebarang pengisytiharan palsu, peninggalan maklumat, keterangan yang mengelirukan, kuantiti yang tidak betul atau penilaian yang tidak tepat boleh mengakibatkan penolakan permohonan, pembatalan permit, penyiasatan, tindakan penguatkuasaan atau pendakwaan.">
            Any false declaration, omission of information, misleading description, incorrect quantity or inaccurate valuation may result in rejection of the application, permit cancellation, investigation, enforcement action or prosecution.</span>
        </p>

        <p>
            <span data-en="Importers are responsible for maintaining supporting documentation for inspection purposes for a minimum period required by the authority." 
                  data-bm="Pengimport bertanggungjawab untuk menyimpan dokumentasi sokongan untuk tujuan pemeriksaan bagi tempoh minimum yang diperlukan oleh pihak berkuasa.">
            Importers are responsible for maintaining supporting documentation for inspection purposes for a minimum period required by the authority.</span>
        </p>
    `,
    },
    {
        title_en: "Restricted Goods Declaration",
        title_bm: "Pengisytiharan Barang Terhad",
        title: '<span data-en="Restricted Goods Declaration" data-bm="Pengisytiharan Barang Terhad">Restricted Goods Declaration</span>',
        icon: "bi-exclamation-triangle",
        content: `
        <p>
            <span data-en="Certain goods may be subject to quantity limits, special approval requirements, quarantine inspections, laboratory testing, health certifications or additional permits." 
                  data-bm="Barang-barang tertentu mungkin tertakluk kepada had kuantiti, keperluan kelulusan khas, pemeriksaan kuarantin, ujian makmal, persijilan kesihatan atau permit tambahan.">
            Certain goods may be subject to quantity limits, special approval requirements, quarantine inspections, laboratory testing, health certifications or additional permits.</span>
        </p>

        <p>
            <span data-en="Submission of this application does not guarantee approval. Additional documents may be requested at any stage during evaluation." 
                  data-bm="Penyerahan permohonan ini tidak menjamin kelulusan. Dokumen tambahan mungkin diminta pada bila-bila masa semasa penilaian.">
            Submission of this application does not guarantee approval. Additional documents may be requested at any stage during evaluation.</span>
        </p>

        <p>
            <span data-en="The department reserves the right to suspend processing, request clarification or reject applications that do not meet regulatory requirements." 
                  data-bm="Jabatan berhak untuk menggantung pemprosesan, meminta penjelasan atau menolak permohonan yang tidak memenuhi keperluan pengawalseliaan.">
            The department reserves the right to suspend processing, request clarification or reject applications that do not meet regulatory requirements.</span>
        </p>
    `,
    },
    {
        title_en: "Applicant Declaration",
        title_bm: "Pengisytiharan Pemohon",
        title: '<span data-en="Applicant Declaration" data-bm="Pengisytiharan Pemohon">Applicant Declaration</span>',
        icon: "bi-file-earmark-text",
        content: `
        <p>
            <span data-en="By proceeding, the applicant confirms that all information submitted is true, accurate and complete to the best of their knowledge." 
                  data-bm="Dengan meneruskan, pemohon mengesahkan bahawa semua maklumat yang diserahkan adalah benar, tepat dan lengkap mengikut pengetahuan terbaik mereka.">
            By proceeding, the applicant confirms that all information submitted is true, accurate and complete to the best of their knowledge.</span>
        </p>

        <p>
            <span data-en="The applicant acknowledges that approval may be revoked if information provided is subsequently found to be inaccurate, misleading or fraudulent." 
                  data-bm="Pemohon mengakui bahawa kelulusan boleh dibatalkan jika maklumat yang diberikan kemudiannya didapati tidak tepat, mengelirukan atau fraud.">
            The applicant acknowledges that approval may be revoked if information provided is subsequently found to be inaccurate, misleading or fraudulent.</span>
        </p>

        <p>
            <span data-en="The applicant agrees to comply with all permit conditions, import restrictions and instructions issued by the authority." 
                  data-bm="Pemohon bersetuju untuk mematuhi semua syarat permit, sekatan import dan arahan yang dikeluarkan oleh pihak berkuasa.">
            The applicant agrees to comply with all permit conditions, import restrictions and instructions issued by the authority.</span>
        </p>

        <p>
            <span data-en="Failure to comply may result in permit suspension, permit revocation or other enforcement actions." 
                  data-bm="Kegagalan untuk mematuhi boleh mengakibatkan penggantungan permit, pembatalan permit atau tindakan penguatkuasaan lain.">
            Failure to comply may result in permit suspension, permit revocation or other enforcement actions.</span>
        </p>

        <p>
            <span data-en="The applicant further acknowledges that electronic submission constitutes a legally binding declaration." 
                  data-bm="Pemohon selanjutnya mengakui bahawa penyerahan elektronik merupakan pengisytiharan yang mengikat secara undang-undang.">
            The applicant further acknowledges that electronic submission constitutes a legally binding declaration.</span>
        </p>
    `,
    },
];

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
let currentItemFile = null;

// Item attachment viewer state
let itemAttachmentOffcanvas = null;
let currentItemAttachments = [];
let currentItemAttachIndex = 0;
let itemFileOffcanvas = null;
let applicationAttachments = [];
let applicationAttachmentDropzone = null;
let itemPurpose = null;
let temporaryItemsAttachment = [];

let measurementUnits = null;
let news = null;
let limit = null;
let limitMeasurement = null;
let startLimitDate = null;
let endLimitDate = null;
let currentItemCondition = null;

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
            console.error("Failed to load exporters:", xhr.responseText);
        },
    });
}

measurementUnit();

// --------if self apply -----------
async function selfImport() {
    if (window.location.pathname.includes("public/import_permit_application")) {
        importer = await getAuthUser();
        console.log("importer", importer);
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
                .append(
                    '<option value="" data-en="-- Select Exporter --" data-bm="-- Pilih Pengeksport --">-- Select Exporter --</option>',
                );

            data.forEach((exp) => {
                // Adjust exp.name_en / exp.name_bm if your API keys differ
                const en = exp.name_en || exp.name;
                const bm = exp.name_bm || exp.name;

                $select.append(
                    `<option value="${exp.id}" data-en="${en}" data-bm="${bm}">${exp.name}</option>`,
                );
            });

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
        const $selectRef = $(this);

        const applyExporter = (id) => {
            if (!id) return clearExporterFields();
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

        if (tempItems.length > 0) {
            Swal.fire({
                icon: "warning",
                // Wrap text in a div to hold the data attributes for your translator
                title: '<span data-en="Change Exporter?" data-bm="Tukar Pengeksport?">Change Exporter?</span>',
                html: '<span data-en="Want to change the exporter? All the items will be removed!" data-bm="Adakah anda ingin menukar pengeksport? Semua item akan dipadamkan!">Want to change the exporter? All the items will be removed!</span>',
                showCancelButton: true,
                confirmButtonText:
                    '<span data-en="Yes, change it" data-bm="Ya, tukar">Yes, change it</span>',
                cancelButtonText:
                    '<span data-en="Cancel" data-bm="Batal">Cancel</span>',
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
            }).then((result) => {
                if (result.isConfirmed) {
                    tempItems.length = 0;
                    renderAllItems();
                    summarySubmit();
                    applyExporter(selectedId);
                } else {
                    $selectRef
                        .val(exporter?.id ?? "")
                        .trigger("change.select2");
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
    // itemCondition = null;
    $("#addItemModal .modal-body .news").find(".alert").remove();

    const countryCode = $("#expcountryCode").val();
    const $select = $("#itemSelect");

    if (!countryCode) return;

    // Reset select options with bilingual attributes
    $select
        .empty()
        .append(
            '<option value="" data-en="-- Select Item --" data-bm="-- Pilih Item --">-- Select Item --</option>',
        );

    if ($select.hasClass("select2-hidden-accessible")) {
        $select.select2("destroy");
    }

    $select.prop("disabled", true);

    Swal.fire({
        // Added span to handle translation
        title: '<span data-en="Loading..." data-bm="Memuat...">Loading...</span>',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: `/public/get_consignment/${countryCode}`,
        method: "GET",
        dataType: "json",
        success: function (data) {
            $select.prop("disabled", false);

            data.forEach((row) => {
                // Assuming your backend provides these fields, if not, map them accordingly
                const en = row.entry_en || row.entry_display;
                const bm = row.entry_bm || row.entry_display;

                $select.append(
                    `<option value="${row.id}" data-en="${en}" data-bm="${bm}">${row.entry_display}</option>`,
                );
            });

            $select.select2({
                width: "100%",
                placeholder: "-- Select Item --",
                allowClear: true,
                dropdownParent: $("#addItemModal"),
            });

            Swal.close();
        },
        error: function (xhr, status, error) {
            console.error("Error loading items:", error);
            $select.prop("disabled", false);

            Swal.fire({
                icon: "error",
                title: '<span data-en="Error" data-bm="Ralat">Error</span>',
                html: '<span data-en="Failed to load consignment items." data-bm="Gagal memuatkan item konsainan.">Failed to load consignment items.</span>',
            });
        },
    });
}
function loadUses(itemId) {
    const $select = $("#itemUses");
    // Updated default option with data attributes
    $select
        .empty()
        .append(
            '<option value="" data-en="-- Select Uses --" data-bm="-- Pilih Kegunaan --">-- Select Uses --</option>',
        );

    if (!itemId) return;

    Swal.fire({
        // Updated loading title for translation
        title: '<span data-en="Loading..." data-bm="Memuat...">Loading...</span>',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    fetch(`/public/consignment_uses/${itemId}`)
        .then((res) => res.json())
        .then((data) => {
            if (!data.data) return;

            data.data.forEach((row) => {
                // Assuming 'row' is the string value, using it for both en/bm
                $select.append(
                    `<option value="${row}" data-en="${row}" data-bm="${row}">${row}</option>`,
                );
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
            // Added Error handling with translation support
            Swal.fire({
                icon: "error",
                title: '<span data-en="Error" data-bm="Ralat">Error</span>',
                html: '<span data-en="Failed to load uses." data-bm="Gagal memuatkan kegunaan.">Failed to load uses.</span>',
            });
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
            let startDateText = ``;

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

                // Create the alert with bilingual spans
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

// ------------------------- Add Exporter Modal -------------------------
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

        const name = $("#addexpName").val().trim();
        const phone_no = $("#addexpfonno").val().trim();
        const address1 = $("#addexpaddress1").val().trim();
        const address2 = $("#addexpaddress2").val().trim();
        const full_address = `${address1} ${address2}`;
        const country = $("#addexpcountry").val();

        if (!name || !phone_no || !country) {
            return Swal.fire({
                title: '<span data-en="⚠️ Please fill in all required fields." data-bm="⚠️ Sila isi semua ruangan wajib.">⚠️ Please fill in all required fields.</span>',
            });
        }

        // 🔄 Loading Swal
        Swal.fire({
            title: '<span data-en="Saving exporter..." data-bm="Sedang menyimpan pengeksport...">Saving exporter...</span>',
            html: '<span data-en="Please wait" data-bm="Sila tunggu">Please wait</span>',
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
                    title: '<span data-en="Exporter Saved!" data-bm="Pengeksport Disimpan!">Exporter Saved!</span>',
                    html: '<span data-en="The exporter has been successfully added to the list." data-bm="Pengeksport telah berjaya ditambah ke dalam senarai.">The exporter has been successfully added to the list.</span>',
                    timer: 1800,
                    showConfirmButton: false,
                    timerProgressBar: true,
                });

                modal.hide();
                $("#addExporterForm")[0].reset();
            },
            error: (xhr) => {
                console.error(xhr.responseText);

                Swal.fire({
                    icon: "error",
                    title: '<span data-en="Failed!" data-bm="Gagal!">Failed!</span>',
                    html: '<span data-en="Failed to save exporter. Please try again." data-bm="Gagal menyimpan pengeksport. Sila cuba lagi.">Failed to save exporter. Please try again.</span>',
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
            Swal.fire({
                icon: "warning",
                title: '<span data-en="Identity number is empty!" data-bm="Nombor identiti kosong!">Identity number is empty!</span>',
            });
            return;
        }

        Swal.fire({
            title: '<span data-en="Searching..." data-bm="Sedang mencari...">Searching...</span>',
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
                    title: '<span data-en="Search Failed" data-bm="Carian Gagal">Search Failed</span>',
                    html: '<span data-en="Unable to fetch importer data. Please try again." data-bm="Tidak dapat mengambil data pengimport. Sila cuba lagi.">Unable to fetch importer data. Please try again.</span>',
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

    // If status is not success, show an error alert with bilingual support
    if (data.status !== "success") {
        hideAll();
        Swal.fire({
            icon: "error",
            title: '<span data-en="Importer Not Found" data-bm="Pengimport Tidak Ditemui">Importer Not Found</span>',
            html: '<span data-en="The importer details could not be retrieved." data-bm="Butiran pengimport tidak dapat diambil.">The importer details could not be retrieved.</span>',
        });
        return;
    }

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
        Swal.fire({
            title: '<span data-en="Loading..." data-bm="Memuat...">Loading...</span>',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        const value = this.value; // Air / Sea / Land
        const route = this.dataset.route; // /public/transport/options

        if (!value || route === "#") {
            detailsSelect.innerHTML =
                '<option value="" data-en="-- Select Option --" data-bm="-- Pilih Pilihan --">-- Select Option --</option>';
            return;
        }

        const url = `${route}?type=${encodeURIComponent(value)}`;
        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (data) {
                Swal.close();
                const detailsSelect = $("#entryPoint");

                // Add bilingual attributes to default option
                let options =
                    '<option value="" data-en="-- Select Entry Point --" data-bm="-- Pilih Pintu Masuk --">-- Select Entry Point --</option>';

                data.forEach(function (item) {
                    // Fallback to entry_display if specific translation fields don't exist
                    const en = item.entry_en || item.entry_display;
                    const bm = item.entry_bm || item.entry_display;

                    options += `<option value="${item.id}" 
                        data-entry_name="${item.entry_display}" 
                        data-en="${en}" 
                        data-bm="${bm}">
                        ${item.entry_display}
                        </option>`;
                });
                detailsSelect.html(options);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: '<span data-en="Error" data-bm="Ralat">Error</span>',
                    html: '<span data-en="An error occurred while loading entry points." data-bm="Ralat berlaku semasa memuatkan Pintu Masuk.">An error occurred while loading entry points.</span>',
                });
            },
        });
    });

    $("#entryPoint").on("change", function (e) {
        e.preventDefault();
        entryName = $(this).find("option:selected").data("entry_name");
        summarySubmit();
    });

    // ------------------- ETA Date Validation -------------------
    const etaInput = document.getElementById("eta");
    if (etaInput) {
        const today = new Date().toISOString().split("T")[0];
        etaInput.setAttribute("min", today);

        const validateETA = function () {
            const selectedDate = new Date(this.value);
            const todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);

            if (this.value && selectedDate < todayDate) {
                Swal.fire({
                    icon: "warning",
                    title: '<span data-en="Invalid Date" data-bm="Tarikh Tidak Sah">Invalid Date</span>',
                    html: '<span data-en="Estimated Time Arrival cannot be a past date. Please select today or a future date." data-bm="Anggaran Waktu Tiba tidak boleh berupa tarikh yang lalu. Sila pilih hari ini atau tarikh akan datang.">Estimated Time Arrival cannot be a past date. Please select today or a future date.</span>',
                });
                this.value = "";
                this.classList.add("is-invalid");
            } else {
                this.classList.remove("is-invalid");
            }
        };

        etaInput.addEventListener("change", validateETA);
        etaInput.addEventListener("blur", validateETA);
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
                title: '<span data-en="Uploading..." data-bm="Sedang memuat naik...">Uploading...</span>',
                html: '<span data-en="Please wait while your file is being uploaded." data-bm="Sila tunggu sementara fail anda dimuat naik.">Please wait while your file is being uploaded.</span>',
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
            groupPreview();

            Swal.fire({
                icon: "success",
                title: '<span data-en="Upload Successful!" data-bm="Muat Naik Berjaya!">Upload Successful!</span>',
                html: `<span data-en="${response.original_name} has been uploaded." data-bm="${response.original_name} telah dimuat naik.">${response.original_name} has been uploaded.</span>`,
                timer: 3000,
                showConfirmButton: false,
            });
        },
        // --- 3. SWAL ERROR AFTER LOAD ---
        error: (file, message, xhr) => {
            Swal.close();
            itemDropzone.removeFile(file);

            // Handle dynamic error messages
            const errText =
                message.error || "An unknown error occurred during upload.";
            const errTextBm =
                message.error ||
                "Ralat tidak diketahui berlaku semasa memuat naik.";

            Swal.fire({
                icon: "error",
                title: '<span data-en="Upload Failed" data-bm="Muat Naik Gagal">Upload Failed</span>',
                html: `<span data-en="${errText}" data-bm="${errTextBm}">${errText}</span>`,
                footer: '<span data-en="Please try again." data-bm="Sila cuba lagi.">Please try again.</span>',
            });
            console.error("Dropzone Error:", message);
        },
        // --- 4. HANDLE FILE REMOVAL ---
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

function addPreviewButtons(file) {
    const preview = file.previewElement;
    if (!preview) return;

    const removeBtn = preview.querySelector(".dz-remove");
    if (!removeBtn) return;

    // Create wrapper div for attachment buttons
    const attachmentGroup = document.createElement("div");
    attachmentGroup.className = "attachment-group";
    attachmentGroup.style.display = "flex";
    attachmentGroup.style.gap = "5px";
    attachmentGroup.style.alignItems = "center";
    attachmentGroup.style.justifyContent = "end";

    const viewBtn = document.createElement("a");
    viewBtn.href = "#";
    viewBtn.innerHTML = "<i class = 'ti ti-eye'></i>";
    viewBtn.className = "btn btn-icon btn-info-light";
    viewBtn.onclick = function (e) {
        e.preventDefault();
        currentItemFile = file;
        showItemFilePreview(file);
    };

    const editBtn = document.createElement("a");
    editBtn.href = "#";
    editBtn.innerHTML = "<i class = 'ti ti-pencil'></i>";
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

    // Move existing elements into the group
    attachmentGroup.appendChild(viewBtn);
    attachmentGroup.appendChild(editBtn);
    attachmentGroup.appendChild(removeBtn.cloneNode(true));

    // Replace the dz-remove with the new group
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

    // Initialize offcanvas if not already done
    if (!itemFileOffcanvas) {
        itemFileOffcanvas = new bootstrap.Offcanvas(
            document.getElementById("itemFilePreviewOffcanvas"),
            { backdrop: true, keyboard: true, scroll: false },
        );
    }

    previewContainer.innerHTML = "";
    fileNameSpan.textContent = file.name;
    fileEditInput.value = file.name;

    // Render preview
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

    // Render details
    const fields = [
        { label: "File Name", value: file.name },
        { label: "File Size", value: (file.size / 1024).toFixed(2) + " KB" },
        { label: "File Type", value: file.type || "Unknown" },
    ];
    fileDetailsDiv.innerHTML = fields
        .map(
            (field) => `
                <div class="mb-3">
                    <strong>${field.label}:</strong>
                    <div class="text-muted">${field.value}</div>
                </div>
            `,
        )
        .join("");

    // Show offcanvas
    itemFileOffcanvas.show();
}

$(document).on("click", "#itemFileSaveBtn", function () {
    const newName = document.getElementById("itemFileEditName").value.trim();

    if (!newName || !currentItemFile) {
        Swal.fire({
            icon: "warning",
            title: '<span data-en="Empty Name" data-bm="Nama Kosong">Empty Name</span>',
            html: '<span data-en="Please enter a file name" data-bm="Sila masukkan nama fail">Please enter a file name</span>',
        });
        return;
    }

    currentItemFile.displayName = newName;
    document.getElementById("itemFileName").textContent = newName;

    Swal.fire({
        icon: "success",
        title: '<span data-en="Saved" data-bm="Disimpan">Saved</span>',
        html: '<span data-en="File name updated" data-bm="Nama fail dikemas kini">File name updated</span>',
        timer: 1500,
        showConfirmButton: false,
    });
});

function initApplicationAttachments() {
    const dropzoneEl = document.getElementById("applicationAttachmentDropzone");
    if (!dropzoneEl) return;

    applicationAttachmentDropzone = new Dropzone(
        "#applicationAttachmentDropzone",
        {
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
                    };
                    file._attachmentId = attachment.id;
                    applicationAttachments.push(attachment);
                    renderApplicationAttachmentTable();
                });
            },
            error: function (file, message) {
                console.error("Attachment Dropzone Error:", message);
                if (file.previewElement) {
                    file.previewElement.remove();
                }
            },
        },
    );

    renderApplicationAttachmentTable();
}

function renderApplicationAttachmentTable() {
    const $tbody = $("#applicationAttachmentTable tbody");
    $tbody.empty();

    if (!applicationAttachments.length) {
        $tbody.append(`
            <tr>
                <td colspan="2" class="text-center text-muted py-3" data-en = "No attachments uploaded yet." data-bm = "Tiada lampiran dimuat naik lagi.">
                    No attachments uploaded yet.
                </td>
            </tr>
        `);
        return;
    }

    applicationAttachments.forEach((attachment, index) => {
        $tbody.append(`
            <tr data-id="${attachment.id}" data-index="${index}">
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
    if (!applicationAttachmentDropzone) return;
    const file = applicationAttachmentDropzone.files.find(
        (fileItem) => fileItem._attachmentId === attachmentId,
    );
    if (file) {
        applicationAttachmentDropzone.removeFile(file);
    }
}

let attachmentOffcanvas = null;
let currentAttachmentIndex = 0;

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
        container.innerHTML = `<div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br><span data-en="No preview available" data-bm="Tiada pratonton tersedia">No preview available</span></div>`;
        return;
    }

    if (file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
            container.innerHTML = `
                
                <img src="${e.target.result}" class="img-fluid rounded" alt="${attachment.displayName}">
                
            `;
        };
        reader.readAsDataURL(file);
    } else if (
        file.type === "application/pdf" ||
        attachment.name.toLowerCase().endsWith(".pdf")
    ) {
        const url = URL.createObjectURL(file);
        container.innerHTML = `
            
            <iframe src="${url}" class="w-100" style="height:calc(100vh - 220px); border:none;"></iframe>
            
        `;
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
});

$(document).on("click", "#attachmentSaveNameBtn", function () {
    const newName = document.getElementById("attachmentEditName").value.trim();

    if (!newName) {
        Swal.fire({
            icon: "warning",
            title: '<span data-en="Empty Name" data-bm="Nama Kosong">Empty Name</span>',
            html: '<span data-en="Please enter a file name" data-bm="Sila masukkan nama fail">Please enter a file name</span>',
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
            title: '<span data-en="Saved" data-bm="Disimpan">Saved</span>',
            html: '<span data-en="File name updated successfully" data-bm="Nama fail berjaya dikemas kini">File name updated successfully</span>',
            timer: 1500,
            showConfirmButton: false,
        });

        document.getElementById("attachmentEditName").value = "";
    }
});

// ============================================================
// ITEM ATTACHMENT VIEWER (for viewing attachments in ItemDetailsOffcanvas)
// ============================================================

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
            viewerBody.innerHTML = `<div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br><span data-en="Select an attachment" data-bm="Pilih lampiran">Select an attachment</span></div>`;
        }
        const detailsBody = document.getElementById("itemAttachDetails");
        if (detailsBody) {
            detailsBody.innerHTML = "";
        }
        // Don't clear currentItemAttachments - preserve for re-opening
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

    // Use displayName if available, otherwise use original name
    const displayName = file.displayName || file.name;

    currentItemAttachIndex = index;
    viewerTitle.textContent = displayName;
    viewerCounter.textContent = `${index + 1} / ${currentItemAttachments.length}`;

    // Render preview
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

    // Render details - show displayName if set, and original name separately
    const fields = file.displayName
        ? [
            { label: "File Name", value: file.displayName },
            { label: "Original Name", value: file.name },
            { label: "File Size", value: (file.size / 1024).toFixed(2) + " KB" },
            { label: "File Type", value: file.type || "Unknown" },
        ]
        : [
            { label: "File Name", value: file.name },
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

    // Update navigation buttons
    document.getElementById("itemAttachPrevBtn").disabled = index === 0;
    document.getElementById("itemAttachNextBtn").disabled =
        index === currentItemAttachments.length - 1;

    // Update edit name input with current display name
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

    // Click handler for attachment chips
    $(document).on("click", ".ipv-attach-chip", function () {
        const index = $(this).data("index");
        if (currentItemAttachments.length > 0 && index !== undefined) {
            openItemAttachmentViewer(currentItemAttachments, index);
        }
    });

    // Save edited attachment name
    $(document).on("click", "#itemAttachSaveNameBtn", function () {
        const newName = document.getElementById("itemAttachEditName").value.trim();

        if (!newName) {
            Swal.fire({
                icon: "warning",
                title: '<span data-en="Empty Name" data-bm="Nama Kosong">Empty Name</span>',
                html: '<span data-en="Please enter a file name" data-bm="Sila masukkan nama fail">Please enter a file name</span>',
            });
            return;
        }

        if (currentItemAttachIndex >= 0 && currentItemAttachIndex < currentItemAttachments.length) {
            const file = currentItemAttachments[currentItemAttachIndex];

            // File objects are immutable, so we store displayName as a custom property
            file.displayName = newName;

            // Update the title
            document.getElementById("itemAttachmentTitle").textContent = newName;

            // Re-render details with updated name
            const detailsBody = document.getElementById("itemAttachDetails");
            const fields = [
                { label: "File Name", value: newName },
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

            Swal.fire({
                icon: "success",
                title: '<span data-en="Saved" data-bm="Disimpan">Saved</span>',
                html: '<span data-en="File name updated successfully" data-bm="Nama fail berjaya dikemas kini">File name updated successfully</span>',
                timer: 1500,
                showConfirmButton: false,
            });
        }
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
                        ".dz-image img[data-dz-thumbnail]",
                    );

                    // Set your PDF logo path
                    $img.attr(
                        "src",
                        "/images/pdf-logo.png", // <-- replace with your actual PDF logo path
                    );
                    $img.css({
                        "object-fit": "contain",
                        width: "100%",
                        height: "100%",
                    });
                }
            }

            // Update delete buttons - check both .dz-remove and .attachment-group
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

function saveConsignmentAttachment() {
    document
        .getElementById("saveBtn")
        .addEventListener("click", async function (e) {
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

            // ✅ Get files from Dropzone
            const files = itemDropzone.getAcceptedFiles();
            const itemPurposeDescription =
                $("#itemPurpose option:selected").data("description") ||
                $("#itemPurpose").val();

            // ✅ Validation
            if (
                !itemSelectValue ||
                !itemValue ||
                !itemQuantity ||
                !itemMeasure ||
                !itemPurpose ||
                !itemUsesValue ||
                files.length === 0
            ) {
                Swal.fire({
                    icon: "error",
                    title: '<span data-en="Incomplete Data" data-bm="Data Tidak Lengkap">Incomplete Data</span>',
                    html: '<span data-en="Please fill in all required fields and upload an attachment before saving." data-bm="Sila isi semua ruangan wajib dan muat naik lampiran sebelum menyimpan.">Please fill in all required fields and upload an attachment before saving.</span>',
                    didOpen: (modal) => {
                        applyTranslations(modal);
                    },
                });
                return;
            }
            if (limitMeasurement) {
                // convert the limit to kg first
                let limitInKg = null;

                const selectedUnit = measurementUnits.unit.find(
                    (unit) =>
                        unit.cate_code.toLowerCase() ===
                            limitMeasurement.toLowerCase() &&
                        unit.is_del === false,
                );

                if (selectedUnit) {
                    limitInKg = limit * selectedUnit.conversion.conversion;
                }

                // convert the quantity of the selected item weight
                let selectedItemInKg = null;

                const selectedItemUnit = measurementUnits.unit.find(
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
                        title: '<span data-en="The item is over limit" data-bm="Item melebihi had">The item is over limit</span>',
                        html: '<span data-en="Please fill in again." data-bm="Sila isi semula.">Please fill in again.</span>',
                        didOpen: (modal) => {
                            applyTranslations(modal);
                        },
                    });
                    return;
                }
            }
            console.log("in save cons", currentItemCondition);
            // ✅ Build new item
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
                condition: currentItemCondition,
            };

            const agreed = await showItemAgreement(newItem);

            if (!agreed) {
                return;
            }

            // ✅ Add to temporary array
            tempItems.push(newItem);

            // ✅ Render the list table
            renderAllItems();

            // ✅ Reset modal fields
            resetAddItemModal();

            // ✅ Hide modal
            const modalEl = document.getElementById("addItemModal");
            bootstrap.Modal.getInstance(modalEl).hide();

            // ✅ Trigger summary / submit update if needed
            summarySubmit();
        });
}

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

    // Build condition scroll area only if there is a condition
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
        title: '<span data-en="Item Declaration" data-bm="Pengisytiharan Item">Item Declaration</span>',
        width: 600,
        html: `
            <div style="text-align: left;">
                <p class="mb-3">
                    <span data-en="I confirm that the information provided for this item" data-bm="Saya mengesahkan bahawa maklumat yang diberikan untuk item ini">I confirm that the information provided for this item</span> 
                    <strong>"${item.item_name}"</strong> 
                    <span data-en="is accurate and complete." data-bm="adalah tepat dan lengkap.">is accurate and complete.</span>
                </p>
                ${conditionHtml}
                <p class="mb-3">
                    <span data-en="I understand that any false declaration may result in rejection of the application or permit cancellation." data-bm="Saya faham bahawa sebarang pengisytiharan palsu boleh mengakibatkan penolakan permohonan atau pembatalan permit.">I understand that any false declaration may result in rejection of the application or permit cancellation.</span>
                </p>
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="itemAgreeCheckbox" ${hasCondition ? "disabled" : ""}>
                    <label class="form-check-label" for="itemAgreeCheckbox">
                        <span data-en="I agree to the above declaration" data-bm="Saya bersetuju dengan pengisytiharan di atas">I agree to the above declaration</span>
                    </label>
                </div>
            </div>
        `,
        didOpen: (modal) => {
            applyTranslations(modal);

            const checkbox = document.getElementById("itemAgreeCheckbox");
            if (!checkbox) return;

            if (hasCondition) {
                const scrollDiv = document.getElementById(
                    "itemConditionScroll",
                );
                if (scrollDiv) {
                    // Initially disabled
                    checkbox.disabled = true;
                    checkbox.classList.add("opacity-50");

                    // Scroll listener to enable checkbox at bottom
                    const handleScroll = () => {
                        const atBottom =
                            scrollDiv.scrollTop + scrollDiv.clientHeight >=
                            scrollDiv.scrollHeight - 5;
                        checkbox.disabled = !atBottom;
                        checkbox.classList.toggle("opacity-50", !atBottom);
                    };

                    scrollDiv.addEventListener("scroll", handleScroll);
                    // Check initial state
                    handleScroll();

                    // Prevent clicking when disabled with a warning
                    checkbox.addEventListener("click", function (e) {
                        if (this.disabled) {
                            e.preventDefault();
                            Swal.showValidationMessage(
                                '<span data-en="Please scroll to the bottom of the conditions to enable agreement." data-bm="Sila skrol ke bawah syarat untuk membolehkan persetujuan.">Please scroll to the bottom of the conditions to enable agreement.</span>',
                            );
                        }
                    });
                }
            } else {
                // No condition, enable immediately
                checkbox.disabled = false;
                checkbox.classList.remove("opacity-50");
            }
        },
        showCancelButton: true,
        confirmButtonText:
            '<span data-en="Confirm" data-bm="Sahkan">Confirm</span>',
        cancelButtonText:
            '<span data-en="Cancel" data-bm="Batal">Cancel</span>',
        allowOutsideClick: false,
        preConfirm: () => {
            const checked =
                document.getElementById("itemAgreeCheckbox").checked;
            if (!checked) {
                Swal.showValidationMessage(
                    '<span data-en="Please check the agreement box to continue." data-bm="Sila tandakan kotak persetujuan untuk meneruskan.">Please check the agreement box to continue.</span>',
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

function renderAllItems() {
    const tableBody = document.querySelector("#itemListTbl tbody");
    tableBody.innerHTML = ""; // Clear existing rows

    // Update validation input
    const countInput = document.getElementById("itemCountCheck");
    if (countInput) {
        countInput.value = tempItems.length > 0 ? "1" : "";
        if (tempItems.length > 0) {
            countInput.classList.remove("is-invalid");
            countInput.style.border = "";

            // Also remove red border from button
            const addBtn = document.getElementById("mdlAddItemBtn");
            if (addBtn) {
                addBtn.classList.remove("is-invalid");
                addBtn.style.setProperty("border", "", "important");
                addBtn.style.color = "";
            }
        }
    }

    tempItems.forEach((item, index) => {
        tableBody.insertAdjacentHTML(
            "beforeend",
            `<tr id="item-row-${item.id}">
              
                <td class = "text-wrap">${item.item_name}</td>
         
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
            </tr>`,
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

        const detailsDiv = document.getElementById("itemDetailsInfo");
        const attachList = document.getElementById("pdAttachList");
        const attachmentCount = document.getElementById("attachmentCount");

        // --- Agreement banner ---
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

        // --- Main details ---
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

        // --- Apply translations to the newly added details ---
        applyTranslations(detailsDiv);

        // --- Attachments (synchronous, no FileReader) ---
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
            // Build chips synchronously using file properties
            let chipsHTML = "";
            item.files.forEach((file, index) => {
                const fileIcon =
                    file.type === "application/pdf"
                        ? "bi-file-earmark-pdf-fill"
                        : "bi-file-earmark-fill";
                const fileTypeClass =
                    file.type === "application/pdf" ? "is-pdf" : "is-file";

                // File type text (use file.type or fallback)
                const typeDisplay = file.type || "Unknown";

                chipsHTML += `
                    <div class="ipv-attach-chip ${fileTypeClass} view-item-attach-btn" data-index="${index}" style="cursor:pointer;">
                        <div class="ipv-attach-icon"><i class="bi ${fileIcon}"></i></div>
                        <div class="ipv-attach-info">
                            <div class="ipv-attach-name" title="${file.name}">${file.name}</div>
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

            // --- Apply translations to the attachment list ---
            applyTranslations(attachList);
        }

        // --- Show offcanvas ---
        const offcanvasEl = document.getElementById("ItemDetailsOffcanvas");
        if (offcanvasEl) {
            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
        }
    });

    // --- Attachment viewer click handler (unchanged) ---
    $(document).on("click", ".view-item-attach-btn", function (e) {
        e.preventDefault();
        e.stopPropagation();
        const index = $(this).data("index");
        if (currentItemAttachments.length > 0 && index !== undefined) {
            openItemAttachmentViewer(currentItemAttachments, index);
        }
    });
}
function deleteItem() {
    $(document).on("click", ".delete-item", function (e) {
        e.preventDefault();

        let id = $(this).data("id");

        if (!tempItems) {
            console.error("tempItems array not found");
            return;
        }

        // Find item index in array
        let index = tempItems.findIndex((obj) => obj.id === id);

        if (index === -1) {
            console.warn("Item not found:", id);
            return;
        }

        // Remove from array
        tempItems.splice(index, 1);

        // Remove from UI
        $("#item-card-" + id).remove();

        renderAllItems();

        console.log("Deleted item:", id, tempItems);
        summarySubmit();
    });
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
        }
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

            setTimeout(() => {
                window.location.href = "/public/view_import_permit";
            }, 1500);
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
        // Initialize self import
        await selfImport();

        // Fetch exporter list and set change handler
        await fetchExporterList();
        handleExporterChange();

        // Initialize modals and search
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
            loadDetails(itemId);
        });

        // Expose loadConsignmentSelection globally if needed
        // loadConsignmentSelection();
        $("#mdlAddItemBtn").on("click", loadConsignmentSelection);

        // Submit button handler
        $(document).on("click", "#submitApps", async function (e) {
            e.preventDefault();
            console.log("Submit clicked!");

            // Check if there are items
            if (tempItems.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "No Items",
                    text: "Please add at least one item before submitting.",
                });
                return;
            }

            // Check if all items have been agreed
            const unagreedItems = tempItems.filter((item) => !item.agreedAt);
            if (unagreedItems.length > 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Incomplete Declarations",
                    text: "Please confirm the declaration for all items before submitting.",
                });
                return;
            }

            // Show item conditions and get user agreement
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
                        // Leave page
                        change = false;

                        if (target.tagName === "A") {
                            window.location.href = target.href;
                        } else {
                            target.click();
                        }
                    }

                    if (result.isDenied) {
                        saveapplication(true); // ✅ draft
                        window.location.href = "/public/view_import_permit";
                    }

                    // result.isDismissed → user clicked "Stay"
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
        console.log("item summary", item);
        // --- Build attachment list ---
        let attachmentHTML = "";

        attachmentHTML = `
            <button class = "btn btn-sm 
            btn-primary view-more-item" 
            data-id = "${item.id}" data-bm = "Lihat" 
            data-en = "View More">
                View More
            </button>
            `;

        // --- Insert summary row ---
        targetTable.insertAdjacentHTML(
            "beforeend",
            `
                <tr>
                    <td data-label="#">${index + 1}</td>
                    <td data-label="Item Name" class="text-wrap">${item.item_name || ""}</td>
                    <td data-label="Quantity">${item.quantity || ""}  ${item.measure || ""}</td>
                    <td data-label="Purpose">${item.purpose || ""}</td>
                    <td data-label="Uses">${item.uses || ""}</td>
                    <td data-label="Value">RM ${item.value || ""}</td>
                    <td data-label="Action">${attachmentHTML}</td>
                </tr>
            `,
        );
    });

    if (typeof applyTranslations === "function") {
        applyTranslations(targetTable);
    }

    // --- APPLICATION ATTACHMENTS SUMMARY ---
    const attachmentTable = document.querySelector(
        "#summaryAttachmentTable tbody",
    );
    if (attachmentTable) {
        attachmentTable.innerHTML = ""; // clear existing rows

        if (!applicationAttachments || applicationAttachments.length === 0) {
            attachmentTable.insertAdjacentHTML(
                "beforeend",
                `
                <tr>
                    <td colspan="4" class="text-center text-muted py-3" data-en="No attachments uploaded" data-bm="Tiada lampiran dimuat naik">
                        No attachments uploaded
                    </td>
                </tr>
                `,
            );
        } else {
            applicationAttachments.forEach((attachment, index) => {
                // Format file size
                const size = attachment.size || 0;
                let sizeDisplay = "";
                if (size < 1024) {
                    sizeDisplay = size + " B";
                } else if (size < 1024 * 1024) {
                    sizeDisplay = (size / 1024).toFixed(1) + " KB";
                } else {
                    sizeDisplay = (size / (1024 * 1024)).toFixed(1) + " MB";
                }

                // Determine file type display
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
                        <td data-label="#">${index + 1}</td>
                        <td data-label="File Name" class="text-wrap">${attachment.displayName || attachment.name || ""}</td>
                        <td data-label="Size">${sizeDisplay}</td>
                        <td data-label="Type">${typeDisplay}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-icon btn-success-light view-attachment-btn" data-id="${attachment.id}">
                                <i class="ti ti-eye"></i>
                            </button>
                           
                        </td>
                    </tr>
                    `,
                );
            });
        }

        if (typeof applyTranslations === "function") {
            applyTranslations(attachmentTable);
        }
    }
}

// ------------------------------summary details ---------------------

// ─── Inactivity Timeout: Draft-Save Hook ─────────────────────────────────────
// Registers a function that the inactivity timeout can call silently before
// auto-logging out the user. Only active on import permit application pages.
$(document).ready(function () {
    const isPermitPage =
        window.location.pathname.includes("import_permit_application") ||
        window.location.pathname.includes("import_assign_application");
    if (!isPermitPage) return;

    window.qisDraftSaver = function () {
        return new Promise((resolve, reject) => {
            const form = document.querySelector("#wizardForm");
            if (!form) return resolve(); // nothing to save

            const formData = new FormData(form);
            formData.append("is_draft", 1);
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
                const { files, ...otherData } = item;
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

            $.ajax({
                url: "/public/save-application",
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

    console.log("[QIS] Import permit draft saver registered.");
});

async function showItemConditions() {
    let currentPage = 0;

    // Get current language from localStorage
    let currentLang = "en";
    try {
        currentLang = localStorage.getItem("qis_lang") || "en";
    } catch (e) {
        currentLang = "en";
    }

    while (currentPage < ITEM_CONDITIONS.length) {
        const page = ITEM_CONDITIONS[currentPage];

        // Get translated title
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
                <div style="
                    text-align:left;
                    max-height:350px;
                    overflow:auto;
                    padding-right:10px;
                ">
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
            // 1. Apply Translations
            applyTranslations(modal);

            // 2. Existing Scroll logic
            const scrollBox = document.getElementById("agreementScroll");
            const checkbox = document.getElementById("agreeCheckbox");

            scrollBox.addEventListener("scroll", () => {
                const reachedBottom = scrollBox.scrollTop + scrollBox.clientHeight >= scrollBox.scrollHeight - 10;
                if (reachedBottom) {
                    checkbox.disabled = false;
                }
            });
        },
        preConfirm: () => {
            const checked = document.getElementById("agreeCheckbox").checked;
            if (!checked) {
                Swal.showValidationMessage("Please read the declaration and tick the agreement checkbox.");
                return false;
            }
            agreed = true;
            return true;
        },
        allowOutsideClick: false,
        showCancelButton: true,
        confirmButtonText: '<span data-en="I Agree" data-bm="Saya Setuju">I Agree</span>',
        cancelButtonText: '<span data-en="Cancel" data-bm="Batal">Cancel</span>',
    });

    return result.isConfirmed && agreed;
}
