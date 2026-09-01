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

// ─── Helper ──────────────────────────────────────────────
function getCurrentLang() {
    try {
        return localStorage.getItem("qis_lang") || "en";
    } catch {
        return "en";
    }
}

// ─── ITEM_CONDITIONS (already bilingual) ────────────────
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
    // ... (rest of conditions remain unchanged, they already have data-en/data-bm)
];

// ─── Dropzone ─────────────────────────────────────────────
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

let editingItemId = null;

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
            console.error("Failed to load exporters:", xhr.responseText);
        },
    });
}
measurementUnit();

// ─── Self Import ──────────────────────────────────────────
async function selfImport() {
    if (window.location.pathname.includes("public/import_permit_application")) {
        importer = await getAuthUser();
        console.log("importer", importer);
    }
}

// ─── Category Toggle ──────────────────────────────────────
function initCategoryToggle() {
    // Listen to changes on the category radio/select.
    // We assume there is an element with name="applCate" (radio or select).
    // The hidden input #app_cate already holds the value; we sync from that too.
    const $cateInput = $("#app_cate");

    // Function to apply category
    function applyCategory(cateValue) {
        const isSelf = parseInt(cateValue) === 0;
        if (isSelf) {
            // Self: fill importer with logged-in user
            const user = window.authUser?.user || null;
            if (user) {
                importer = user;
                $("#impname").val(user.fullname || "");
                $("#impid").val(user.id || "");
                $("#impfonno").val(user.phone_number || "");
                $("#impaddress1").val(user.address_1 || "");
                $("#impaddress2").val(user.address_2 || "");
                $("#imp_id").val(user.id || "");
                $("#impemail").val(user.email || "");
                $("#searchresult").hide();
            }
            // Disable the search button
            $("#btnFindImp").prop("disabled", true);
        } else {
            // Others: clear importer and enable search
            importer = null;
            clearImporterFields();
            $("#btnFindImp").prop("disabled", false);
        }
    }

    // Listen to changes on radio/select with name "applCate"
    $(document).on(
        "change",
        'input[name="applCate"], select[name="applCate"]',
        function () {
            const val = $(this).val();
            $cateInput.val(val); // keep hidden input in sync
            applyCategory(val);
            // trigger summary update if needed
            summarySubmit();
        },
    );

    // Also listen to direct changes on the hidden input (if set programmatically)
    $cateInput.on("change", function () {
        const val = $(this).val();
        if (val !== undefined && val !== null && val !== "") {
            applyCategory(val);
        }
    });

    // Initial state: read from hidden input
    const initialCate = parseInt($cateInput.val() || "0");
    applyCategory(initialCate);

    // If there is a radio that matches, set it to checked
    $(`input[name="applCate"][value="${initialCate}"]`).prop("checked", true);
}

function clearImporterFields() {
    $(
        "#impname, #impid, #impfonno, #impaddress1, #impaddress2, #imp_id, #impemail",
    ).val("");
    importer = null;
}

// ─── Exporter List ────────────────────────────────────────
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
                const en = exp.name_en || exp.name;
                const bm = exp.name_bm || exp.name;
                $select.append(
                    `<option value="${exp.id}" data-en="${en}" data-bm="${bm}">${exp.name}</option>`,
                );
            });

            // Re-init Select2 with bilingual placeholder
            const lang = getCurrentLang();
            const placeholder =
                lang === "bm"
                    ? "-- Pilih Pengeksport --"
                    : "-- Select Exporter --";
            $select.select2({
                width: "100%",
                placeholder: placeholder,
                allowClear: true,
            });
        },
        error: (xhr) => {
            console.error("Failed to load exporters:", xhr.responseText);
            Swal.fire({
                icon: "error",
                title: '<span data-en="Failed to Load Exporters" data-bm="Gagal Memuatkan Pengeksport">Failed to Load Exporters</span>',
                text: '<span data-en="Please try again or check your connection." data-bm="Sila cuba lagi atau periksa sambungan anda.">Please try again or check your connection.</span>',
                didOpen: (modal) => applyTranslations(modal),
            });
        },
    });
}

// ─── Handle Exporter Change ──────────────────────────────
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
                title: '<span data-en="Change Exporter?" data-bm="Tukar Pengeksport?">Change Exporter?</span>',
                html: '<span data-en="Want to change the exporter? All the items will be removed!" data-bm="Adakah anda ingin menukar pengeksport? Semua item akan dipadamkan!">Want to change the exporter? All the items will be removed!</span>',
                showCancelButton: true,
                confirmButtonText:
                    '<span data-en="Yes, change it" data-bm="Ya, tukar">Yes, change it</span>',
                cancelButtonText:
                    '<span data-en="Cancel" data-bm="Batal">Cancel</span>',
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                didOpen: (modal) => applyTranslations(modal),
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

// ─── Toggle Custom Item Input ─────────────────────────────
function toggleCustomItemInput(show) {
    const $customWrapper = $("#customItemWrapper");
    if (show) {
        $customWrapper.show();
        $("#customItemName").val("").focus();
    } else {
        $customWrapper.hide();
        $("#customItemName").val("");
    }
}

// ─── Consignment / Uses ──────────────────────────────────
function loadConsignmentSelection() {
    limitMeasurement = null;
    limit = null;
    $("#addItemModal .modal-body .news").find(".alert").remove();

    const countryCode = $("#expcountryCode").val();
    const $select = $("#itemSelect");

    if (!countryCode) return;

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
        title: '<span data-en="Loading..." data-bm="Memuat...">Loading...</span>',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    return $.ajax({
        url: `/get_consignment/${countryCode}`,
        method: "GET",
        dataType: "json",
        success: function (data) {
            $select.prop("disabled", false);
            console.log("the loses data", data);

            // ─── Prepend "Others" option ───
            $select.append(
                `<option value="others" data-en="Others" data-bm="Lain-lain">Others</option>`,
            );

            data.forEach((row) => {
                const en = row.entry_en || row.entry_display;
                const bm = row.entry_bm || row.entry_display;
                const anotherNames = row.another_name
                    ? JSON.stringify(row.another_name)
                    : "[]";
                $select.append(
                    `<option value="${row.id}" data-en="${en}" data-bm="${bm}" data-another-names='${anotherNames}'>${row.entry_display}</option>`,
                );
            });

            const lang = getCurrentLang();
            const placeholder =
                lang === "bm" ? "-- Pilih Item --" : "-- Select Item --";
            $select.select2({
                width: "100%",
                placeholder: placeholder,
                allowClear: true,
                dropdownParent: $("#addItemModal"),
                matcher: function (params, data) {
                    if ($.trim(params.term) === "") {
                        return data;
                    }
                    const text = data.text || "";
                    const term = params.term.toLowerCase();
                    if (text.toLowerCase().indexOf(term) > -1) {
                        return data;
                    }
                    const element = data.element;
                    if (element) {
                        const anotherNamesAttr =
                            element.getAttribute("data-another-names");
                        if (anotherNamesAttr) {
                            try {
                                const names = JSON.parse(anotherNamesAttr);
                                if (Array.isArray(names)) {
                                    for (let name of names) {
                                        if (
                                            name.toLowerCase().indexOf(term) >
                                            -1
                                        ) {
                                            return data;
                                        }
                                    }
                                }
                            } catch (e) {
                                // ignore
                            }
                        }
                    }
                    return null;
                },
                templateResult: function (data) {
                    if (data.loading) return data.text;
                    const element = data.element;
                    if (!element) return data.text;
                    const anotherNamesAttr =
                        element.getAttribute("data-another-names");
                    if (anotherNamesAttr) {
                        try {
                            const names = JSON.parse(anotherNamesAttr);
                            if (Array.isArray(names) && names.length > 0) {
                                const mainText = data.text;
                                const others = names.join(", ");
                                // Wrap in a span with muted text for others
                                return $(
                                    `<span>${mainText} <span class="text-muted">(${others})</span></span>`,
                                );
                            }
                        } catch (e) {}
                    }
                    return data.text;
                },
                templateSelection: function (data) {
                    // For selection, just show the main text (without muted part)
                    return data.text;
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
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
                didOpen: (modal) => applyTranslations(modal),
            });
        },
    });
}

// ─── Load Uses (no itemId needed) ─────────────────────────
async function loadUses() {
    const $select = $("#itemUses");
    $select
        .empty()
        .append(
            '<option value="" data-en="-- Select Uses --" data-bm="-- Pilih Kegunaan --">-- Select Uses --</option>',
        );

    Swal.fire({
        title: '<span data-en="Loading..." data-bm="Memuat...">Loading...</span>',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    return fetch(`/consignment_uses`)
        .then((res) => res.json())
        .then((data) => {
            if (!data.data) return;
            console.log("load uses", data);
            data.data.forEach((row) => {
                const display = row.name;
                const value = row.name;
                $select.append(
                    `<option value="${value}" data-en="${display}" data-bm="${display}">${display}</option>`,
                );
            });

            const lang = getCurrentLang();
            const placeholder =
                lang === "bm" ? "-- Pilih Kegunaan --" : "-- Select Uses --";
            $select.select2({
                width: "100%",
                placeholder: placeholder,
                allowClear: true,
                dropdownParent: $("#addItemModal"),
            });
            console.log("the uses", data.data);
            Swal.close();
        })
        .catch((err) => {
            console.error("Failed to load uses:", err);
            Swal.fire({
                icon: "error",
                title: '<span data-en="Error" data-bm="Ralat">Error</span>',
                html: '<span data-en="Failed to load uses." data-bm="Gagal memuatkan kegunaan.">Failed to load uses.</span>',
                didOpen: (modal) => applyTranslations(modal),
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

        const name = $("#addexpName").val().trim();
        const phone_no = $("#addexpfonno").val().trim();
        const address1 = $("#addexpaddress1").val().trim();
        const address2 = $("#addexpaddress2").val().trim();
        const full_address = `${address1} ${address2}`;
        const country = $("#addexpcountry").val();

        if (!name || !phone_no || !country) {
            return Swal.fire({
                title: '<span data-en="⚠️ Please fill in all required fields." data-bm="⚠️ Sila isi semua ruangan wajib.">⚠️ Please fill in all required fields.</span>',
                didOpen: (modal) => applyTranslations(modal),
            });
        }

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
                    didOpen: (modal) => applyTranslations(modal),
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
                    didOpen: (modal) => applyTranslations(modal),
                });
            },
        });
    });

    // Init Select2 for country dropdown with bilingual placeholder
    const lang = getCurrentLang();
    const placeCountry =
        lang === "bm" ? "-- Pilih Negara --" : "-- Select Country --";
    $("#addexpcountry").select2({
        width: "100%",
        placeholder: placeCountry,
        dropdownParent: $("#addExporterModal"),
    });
}

// ─── Importer Search ──────────────────────────────────────
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
                didOpen: (modal) => applyTranslations(modal),
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
                    didOpen: (modal) => applyTranslations(modal),
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
        importer = null;
    };

    if (data.status !== "success") {
        hideAll();
        Swal.fire({
            icon: "error",
            title: '<span data-en="Importer Not Found" data-bm="Pengimport Tidak Ditemui">Importer Not Found</span>',
            html: '<span data-en="The importer details could not be retrieved." data-bm="Butiran pengimport tidak dapat diambil.">The importer details could not be retrieved.</span>',
            didOpen: (modal) => applyTranslations(modal),
        });
        return;
    }

    // ✅ Set global importer with the found data
    importer = data.data;

    $("#searchresult, #doanotver, #emailnotver").hide();
    $("#impname").val(importer.fullname);
    $("#impid").val(importer.id);
    $("#impfonno").val(importer.phone_number);
    $("#impaddress1").val(importer.address_1);
    $("#impaddress2").val(importer.address_2);
    $("#imp_id").val(importer.id);
    $("#impemail").val(importer.email);

    // Optional success message
    Swal.fire({
        icon: "success",
        title: '<span data-en="Importer Found!" data-bm="Pengimport Ditemui!">Importer Found!</span>',
        timer: 1500,
        showConfirmButton: false,
        didOpen: (modal) => applyTranslations(modal),
    });
}

// ─── Permit Details ──────────────────────────────────────
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

                let options =
                    '<option value="" data-en="-- Select Entry Point --" data-bm="-- Pilih Pintu Masuk --">-- Select Entry Point --</option>';

                data.forEach(function (item) {
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
                    didOpen: (modal) => applyTranslations(modal),
                });
            },
        });
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

        const validateETA = function () {
            const selectedDate = new Date(this.value);
            const todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);

            if (this.value && selectedDate < todayDate) {
                Swal.fire({
                    icon: "warning",
                    title: '<span data-en="Invalid Date" data-bm="Tarikh Tidak Sah">Invalid Date</span>',
                    html: '<span data-en="Estimated Time Arrival cannot be a past date. Please select today or a future date." data-bm="Anggaran Waktu Tiba tidak boleh berupa tarikh yang lalu. Sila pilih hari ini atau tarikh akan datang.">Estimated Time Arrival cannot be a past date. Please select today or a future date.</span>',
                    didOpen: (modal) => applyTranslations(modal),
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
                title: '<span data-en="Uploading..." data-bm="Sedang memuat naik...">Uploading...</span>',
                html: '<span data-en="Please wait while your file is being uploaded." data-bm="Sila tunggu sementara fail anda dimuat naik.">Please wait while your file is being uploaded.</span>',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    applyTranslations(Swal.getHtmlContainer());
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

            Swal.fire({
                icon: "success",
                title: '<span data-en="Upload Successful!" data-bm="Muat Naik Berjaya!">Upload Successful!</span>',
                html: `<span data-en="${response.original_name} has been uploaded." data-bm="${response.original_name} telah dimuat naik.">${response.original_name} has been uploaded.</span>`,
                timer: 3000,
                showConfirmButton: false,
                didOpen: (modal) => applyTranslations(modal),
            });
        },
        error: (file, message, xhr) => {
            Swal.close();
            itemDropzone.removeFile(file);

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
                didOpen: (modal) => applyTranslations(modal),
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

// ─── Application Attachments ─────────────────────────────
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
                    file._attachmentId = attachment.id; // <-- this must be set
                    applicationAttachments.push(attachment);
                    renderApplicationAttachmentTable();
                    updateAttachmentTable(); // also update summary immediately
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
                <td colspan="2" class="text-center text-muted py-3" data-en="No attachments uploaded yet." data-bm="Tiada lampiran dimuat naik lagi.">
                    <span data-en = "No attachments uploaded yet." data-bm = "Tiada lampiran"></span>
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
    if (!applicationAttachmentDropzone) {
        console.warn("Dropzone not initialized");
        return false;
    }
    // Find the file by our custom _attachmentId
    const fileIndex = applicationAttachmentDropzone.files.findIndex(
        (fileItem) => fileItem._attachmentId === attachmentId,
    );
    if (fileIndex === -1) {
        console.warn(
            `File with attachmentId ${attachmentId} not found in Dropzone`,
        );
        return false;
    }
    const file = applicationAttachmentDropzone.files[fileIndex];
    try {
        applicationAttachmentDropzone.removeFile(file);
        return true;
    } catch (e) {
        console.error("Error removing file from Dropzone:", e);
        // Fallback: manually remove from the files array
        applicationAttachmentDropzone.files.splice(fileIndex, 1);
        return true;
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
            viewerBody.innerHTML = `<div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br><span data-en="Select an attachment" data-bm="Pilih lampiran">Select an attachment</span></div>`;
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
                    <span data-en="Download File" data-bm="Muat Turun Fail">Download File</span>
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

    // Remove from Dropzone (if possible)
    removeAttachmentFromDropzone(attachmentId);

    // Remove from our array
    applicationAttachments.splice(index, 1);

    // Re-render both tables
    renderApplicationAttachmentTable();
    updateAttachmentTable(); // <-- ensure summary table updates too

    // Optional: show a quick feedback
    Swal.fire({
        icon: "success",
        title: '<span data-en="Attachment removed" data-bm="Lampiran dibuang">Attachment removed</span>',
        timer: 1000,
        showConfirmButton: false,
        didOpen: (modal) => applyTranslations(modal),
    });
});

$(document).on("click", "#attachmentSaveNameBtn", function () {
    const newName = document.getElementById("attachmentEditName").value.trim();

    if (!newName) {
        Swal.fire({
            icon: "warning",
            title: '<span data-en="Empty Name" data-bm="Nama Kosong">Empty Name</span>',
            html: '<span data-en="Please enter a file name" data-bm="Sila masukkan nama fail">Please enter a file name</span>',
            didOpen: (modal) => applyTranslations(modal),
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
            didOpen: (modal) => applyTranslations(modal),
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
            viewerBody.innerHTML = `<div class="text-muted text-center"><i class="bi bi-file-earmark-fill fs-1"></i><br><span data-en="Select an attachment" data-bm="Pilih lampiran">Select an attachment</span></div>`;
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
                    <span data-en="Download File" data-bm="Muat Turun Fail">Download File</span>
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
                title: '<span data-en="Empty Name" data-bm="Nama Kosong">Empty Name</span>',
                html: '<span data-en="Please enter a file name" data-bm="Sila masukkan nama fail">Please enter a file name</span>',
                didOpen: (modal) => applyTranslations(modal),
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
                title: '<span data-en="Saved" data-bm="Disimpan">Saved</span>',
                html: '<span data-en="File name updated successfully" data-bm="Nama fail berjaya dikemas kini">File name updated successfully</span>',
                timer: 1500,
                showConfirmButton: false,
                didOpen: (modal) => applyTranslations(modal),
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

// ─── Save Consignment Attachment ──────────────────────────
function saveConsignmentAttachment() {
    document
        .getElementById("saveBtn")
        .addEventListener("click", async function (e) {
            e.preventDefault();

            console.log("Saving consignment item...");

            const itemSelectValue = $("#itemSelect").val();
            const itemSelectText = $("#itemSelect option:selected").text();
            const itemValue = $("#itemValue").val().trim();
            const itemQuantity = $("#itemQuantity").val().trim();
            const itemMeasure = $("#itemMeasure").val();
            const itemPurposeValue = $("#itemPurpose").val();
            const itemUsesValue = $("#itemUses").val();

            const existingItem = editingItemId
                ? tempItems.find((obj) => obj.id === editingItemId)
                : null;

            let files = itemDropzone.getAcceptedFiles();
            if (files.length === 0 && existingItem) {
                files = existingItem.files || [];
            }

            // ─── Custom item detection ────────────────────────────────
            const isCustom = itemSelectValue === "others";
            let itemName = isCustom
                ? $("#customItemName").val().trim()
                : itemSelectText;
            let itemId = isCustom ? null : itemSelectValue;

            // Validate custom item name
            if (isCustom && !itemName) {
                Swal.fire({
                    icon: "error",
                    title: "Custom Item Name Required",
                    text: "Please enter a custom item name.",
                });
                return;
            }

            // For custom items, attachment is mandatory
            if (isCustom && files.length === 0) {
                Swal.fire({
                    icon: "error",
                    title: "Attachment Required",
                    text: "Please upload an image/document for the custom item.",
                });
                return;
            }

            const itemPurposeDescription =
                $("#itemPurpose option:selected").data("description") ||
                $("#itemPurpose").val();

            // ─── Validation ────────────────────────────────────────────
            if (
                (!isCustom && !itemSelectValue) ||
                !itemValue ||
                !itemQuantity ||
                !itemMeasure ||
                !itemPurposeValue ||
                !itemUsesValue ||
                files.length === 0
            ) {
                Swal.fire({
                    icon: "error",
                    title: '<span data-en="Incomplete Data" data-bm="Data Tidak Lengkap">Incomplete Data</span>',
                    html: '<span data-en="Please fill in all required fields and upload an attachment before saving." data-bm="Sila isi semua ruangan wajib dan muat naik lampiran sebelum menyimpan.">Please fill in all required fields and upload an attachment before saving.</span>',
                    didOpen: (modal) => applyTranslations(modal),
                });
                return;
            }

            if (limitMeasurement) {
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
                        didOpen: (modal) => applyTranslations(modal),
                    });
                    return;
                }
            }

            const itemPayload = {
                id: existingItem ? existingItem.id : generateUUID(),
                item_id: itemId,
                item_name: itemName,
                value: itemValue,
                quantity: itemQuantity,
                measure: itemMeasure,
                purpose: itemPurposeDescription,
                purposeValue: itemPurposeValue,
                uses: itemUsesValue,
                files: files,
                agreedAt: null,
                condition: currentItemCondition,
                isCustom: isCustom, // <-- flag added
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
                                '<span data-en="Please scroll to the bottom of the conditions to enable agreement." data-bm="Sila skrol ke bawah syarat untuk membolehkan persetujuan.">Please scroll to the bottom of the conditions to enable agreement.</span>',
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

// ─── Reset Add Item Modal ──────────────────────────────────
function resetAddItemModal() {
    $("#itemValue").val("");
    $("#itemQuantity").val("");
    $("#itemSelect").val(null).trigger("change");
    $("#itemMeasure").val("").trigger("change");
    $("#itemPurpose").val("").trigger("change");
    $("#itemUses").val(null).trigger("change");
    // Reset custom fields
    $("#customItemName").val("");
    toggleCustomItemInput(false);
    $(".attachmentInstruction").html("").hide();

    if (itemDropzone) itemDropzone.removeAllFiles(true);
}

// ─── Render Items ──────────────────────────────────────────
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

        // ─── Conditions ────────────────────────────────
        const hasCondition = item.condition && item.condition.trim() !== "";

        if (conditionItem) {
            conditionItem.innerHTML = hasCondition
                ? `<div style="white-space: pre-wrap; word-break: break-word;">${item.condition}</div>`
                : `<span data-en="No special conditions for this item." data-bm="Tiada syarat khas untuk item ini.">No special conditions for this item.</span>`;
            applyTranslations(conditionItem);
        }

        if (conditionCount) {
            conditionCount.textContent = hasCondition ? "1" : "0";
        }

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

// ─── Edit Item (FIX: handle custom items) ─────────────────
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
            title: '<span data-en="Loading item..." data-bm="Memuat item...">Loading item...</span>',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        loadConsignmentSelection()
            .done(() => {
                // ─── Set the selected value or "others" ──────────────
                if (item.isCustom) {
                    // It's a custom item – select "others" in the dropdown
                    $("#itemSelect").val("others").trigger("change");
                    // Show custom input and fill with saved name
                    toggleCustomItemInput(true);
                    $("#customItemName").val(item.item_name);
                    // Show the attachment instruction
                    $(".attachmentInstruction")
                        .html(
                            `<span style="color:red;" data-en="Attachment is mandatory for custom items. Please upload the item image or document"
                               data-bm="Lampiran adalah wajib untuk item yang tiada dalam senarai. Sila muat naik gambar atau dokumen">
                            * Attachment is mandatory for custom items. Please upload the item image or document.</span>`,
                        )
                        .show();
                } else {
                    // Normal item: select the saved item_id
                    $("#itemSelect").val(item.item_id).trigger("change");
                    toggleCustomItemInput(false);
                    $(".attachmentInstruction").html("").hide();
                }

                // ─── Load uses and then set the selected value ──────
                loadUses().then(() => {
                    if (item.uses) {
                        $("#itemUses").val(item.uses).trigger("change");
                    }
                });

                // ─── Fill other fields ──────────────────────────────
                $("#itemValue").val(item.value);
                $("#itemQuantity").val(item.quantity);
                $("#itemMeasure").val(item.measure).trigger("change");
                $("#itemPurpose")
                    .val(item.purposeValue || item.purpose)
                    .trigger("change");

                // ─── Restore files in Dropzone ──────────────────────
                if (itemDropzone) {
                    itemDropzone.removeAllFiles(true);
                    (item.files || []).forEach((file) =>
                        itemDropzone.addFile(file),
                    );
                }

                Swal.close();
            })
            .fail(() => {
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: '<span data-en="Error" data-bm="Ralat">Error</span>',
                    html: '<span data-en="Failed to load item data for editing." data-bm="Gagal memuatkan data item untuk disunting.">Failed to load item data for editing.</span>',
                    didOpen: (modal) => applyTranslations(modal),
                });
            });
    });
}

// ─── Save Application ──────────────────────────────────────
function saveapplication(isDraft = false) {
    const form = document.querySelector("#wizardForm");
    if (!form) return console.error("Form not found");

    const formData = new FormData(form);
    formData.append("is_draft", isDraft ? 1 : 0);
    formData.append("exporterData", JSON.stringify(exporter));
    formData.append("importerData", JSON.stringify(importer));
    formData.append("permitDetails", JSON.stringify(permitDetails));

    // ─── Send each item ──────────────────────────────────────────────
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

    // ─── Application attachments ────────────────────────────────────
    applicationAttachments.forEach((attachment) => {
        if (attachment.file) {
            formData.append("application_files[]", attachment.file);
        }
    });

    // ─── NEW: Check if any custom item exists ──────────────────────
    const hasNewItem = tempItems.some((item) => item.isCustom === true) ? 1 : 0;
    formData.append("hasNewItem", hasNewItem);

    Swal.fire({
        title: isDraft
            ? '<span data-en="Saving Draft..." data-bm="Menyimpan Draf...">Saving Draft...</span>'
            : '<span data-en="Submitting..." data-bm="Menghantar...">Submitting...</span>',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            applyTranslations(Swal.getHtmlContainer());
        },
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
                title: isDraft
                    ? '<span data-en="Draft Saved" data-bm="Draf Disimpan">Draft Saved</span>'
                    : '<span data-en="Application Submitted!" data-bm="Permohonan Dihantar!">Application Submitted!</span>',
                timer: 1500,
                showConfirmButton: false,
                didOpen: (modal) => applyTranslations(modal),
            });

            setTimeout(() => {
                window.location.href = "/public/view_import_permit";
            }, 1500);
        },
        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: '<span data-en="Error" data-bm="Ralat">Error</span>',
                text: '<span data-en="Failed to save application" data-bm="Gagal menyimpan permohonan">Failed to save application</span>',
                didOpen: (modal) => applyTranslations(modal),
            });
        },
    });
}

// ─── Summary Submit ──────────────────────────────────────
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

    targetTable.innerHTML = "";

    tempItems.forEach((item, index) => {
        let attachmentHTML = `
            <button class="btn btn-sm btn-primary view-more-item" 
                data-id="${item.id}" 
                data-bm="Lihat" 
                data-en="View More">
                View More
            </button>
        `;

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

    updateAttachmentTable();
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
                    <td colspan="4" class="text-center text-muted py-3" data-en="No attachments uploaded" data-bm="Tiada lampiran dimuat naik">
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
                        <td data-label="#">${index + 1}</td>
                        <td data-label="File Name" class="text-wrap">${attachment.displayName || attachment.name || ""}</td>
                        <td data-label="Size">${sizeDisplay}</td>
                        <td data-label="Type">${typeDisplay}</td>
                        <td class="text-end">
                            <button type="button" 
                            data-bm="Lihat" 
                            data-en="View More"
                            class="btn btn-sm btn-primary view-attachment-btn" data-id="${attachment.id}">
                                View More
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

// ─── Inactivity Timeout ────────────────────────────────────
$(document).ready(function () {
    const isPermitPage =
        window.location.pathname.includes("import_permit_application") ||
        window.location.pathname.includes("import_assign_application");
    if (!isPermitPage) return;

    window.qisDraftSaver = function () {
        return new Promise((resolve, reject) => {
            const form = document.querySelector("#wizardForm");
            if (!form) return resolve();

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

// ─── Show Item Conditions (final) ──────────────────────────
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

// ─── DOCUMENT READY ──────────────────────────────────────
$(document).ready(async function () {
    // Show loading
    Swal.fire({
        title: '<span data-en="Loading..." data-bm="Memuat...">Loading...</span>',
        html: '<span data-en="Please wait while the page initializes." data-bm="Sila tunggu sementara halaman dimulakan.">Please wait while the page initializes.</span>',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            applyTranslations(Swal.getHtmlContainer());
        },
    });

    try {
        await selfImport();
        await fetchExporterList();
        handleExporterChange();

        // ✅ Initialize category toggle
        initCategoryToggle();

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

        // ─── Select2 for Measurement ────────────────────
        const lang = getCurrentLang();
        const placeMeasure =
            lang === "bm"
                ? "-- Pilih Unit Ukuran --"
                : "-- Select Measurement Unit --";
        $("#itemMeasure").select2({
            width: "100%",
            placeholder: placeMeasure,
            dropdownParent: $("#addItemModal"),
        });

        // ─── Select2 for Purpose ────────────────────────
        const placePurpose =
            lang === "bm" ? "-- Pilih Tujuan --" : "-- Select Purpose --";
        $("#itemPurpose").select2({
            width: "100%",
            placeholder: placePurpose,
            dropdownParent: $("#addItemModal"),
        });

        // ─── Item Purpose change ─────────────────────────
        $("#itemPurpose").on("change", function () {
            const selectedOption = $(this).find("option:selected");
            itemPurpose = selectedOption.data("description") || "";
            console.log("Item purpose selected:", itemPurpose);
        });

        // ─── Item Select change ─────────────────────────
        $("#itemSelect").on("change", function () {
            const selectedVal = $(this).val();
            const $itemUses = $("#itemUses");
            $itemUses
                .empty()
                .append('<option value="">-- Select Uses --</option>');

            if (!selectedVal) {
                toggleCustomItemInput(false);
                $(".attachmentInstruction").html("").hide();
                return;
            }

            if (selectedVal === "others") {
                toggleCustomItemInput(true);
                $(".attachmentInstruction")
                    .html(
                        `<span style="color:red;" data-en="Attachment is mandatory for custom items. Please upload the item image or document"
                   data-bm="Lampiran adalah wajib untuk item yang tiada dalam senarai. Sila muat naik gambar atau dokumen">
                * Attachment is mandatory for custom items. Please upload the item image or document.</span>`,
                    )
                    .show();
                // ─── Load the uses list (static) ─────────────────────────
                loadUses(); // <-- ensures uses are loaded even for custom
                return;
            }

            // Normal item selected – hide custom input
            toggleCustomItemInput(false);
            $(".attachmentInstruction").html("").hide();
            loadDetails(selectedVal);
            // ─── Load the uses list (static) ─────────────────────────
            loadUses();
        });

        $("#mdlAddItemBtn").on("click", loadConsignmentSelection);

        // ─── Submit button ──────────────────────────────
        $(document).on("click", "#submitApps", async function (e) {
            e.preventDefault();
            console.log("Submit clicked!");

            if (tempItems.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: '<span data-en="No Items" data-bm="Tiada Item">No Items</span>',
                    html: '<span data-en="Please add at least one item before submitting." data-bm="Sila tambah sekurang-kurangnya satu item sebelum menghantar.">Please add at least one item before submitting.</span>',
                    didOpen: (modal) => applyTranslations(modal),
                });
                return;
            }

            const unagreedItems = tempItems.filter((item) => !item.agreedAt);
            if (unagreedItems.length > 0) {
                Swal.fire({
                    icon: "warning",
                    title: '<span data-en="Incomplete Declarations" data-bm="Pengisytiharan Tidak Lengkap">Incomplete Declarations</span>',
                    html: '<span data-en="Please confirm the declaration for all items before submitting." data-bm="Sila sahkan pengisytiharan untuk semua item sebelum menghantar.">Please confirm the declaration for all items before submitting.</span>',
                    didOpen: (modal) => applyTranslations(modal),
                });
                return;
            }

            const agreed = await showItemConditions();
            if (!agreed) {
                return;
            }

            saveapplication(false);
        });

        // ─── Unsaved changes warning ────────────────────
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
                    title: '<span data-en="Unsaved Changes" data-bm="Perubahan Belum Disimpan">Unsaved Changes</span>',
                    text: '<span data-en="You have unsaved changes. What would you like to do?" data-bm="Anda mempunyai perubahan yang belum disimpan. Apa yang anda ingin lakukan?">You have unsaved changes. What would you like to do?</span>',
                    icon: "warning",
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText:
                        '<span data-en="Yes, leave" data-bm="Ya, tinggalkan">Yes, leave</span>',
                    denyButtonText:
                        '<span data-en="Save as Draft" data-bm="Simpan sebagai Draf">Save as Draft</span>',
                    cancelButtonText:
                        '<span data-en="Stay" data-bm="Kekal">Stay</span>',
                    didOpen: (modal) => applyTranslations(modal),
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
            },
        );
    } catch (error) {
        console.error("Error during initialization:", error);
        Swal.fire({
            icon: "error",
            title: '<span data-en="Error" data-bm="Ralat">Error</span>',
            html: '<span data-en="Failed to initialize page. Check console for details." data-bm="Gagal memulakan halaman. Periksa konsol untuk butiran.">Failed to initialize page. Check console for details.</span>',
            didOpen: (modal) => applyTranslations(modal),
        });
    } finally {
        Swal.close();
    }
});
