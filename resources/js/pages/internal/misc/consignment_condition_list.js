import $ from "jquery";
import Swal from "sweetalert2";
import { applyTranslations } from "../../../app";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    loading: { en: 'Loading...', bm: 'Memuatkan...' },
    fetchingData: { en: 'Fetching consignment condition data', bm: 'Mendapatkan data syarat konsainan' },
    error: { en: 'Error', bm: 'Ralat' },
    failedToFetch: { en: 'Failed to fetch consignment condition data.', bm: 'Gagal mendapatkan data syarat konsainan.' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    deleteWarning: { en: 'This consignment condition will be permanently deleted.', bm: 'Syarat konsainan ini akan dipadamkan secara kekal.' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    deletedSuccess: { en: 'Consignment condition has been deleted.', bm: 'Syarat konsainan telah dipadam.' },
    failedToDelete: { en: 'Failed to delete consignment condition.', bm: 'Gagal memadam syarat konsainan.' }
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}

import { setupSelect2 } from "../../../utils/select2Utils";

console.log("Loaded consignment_condition_list.js");

let internalListTable;
let countryLookup = {};

// Fetch country list & build lookup map
function initCountryLookup() {
    return $.ajax({
        url: "/get_country",
        method: "GET",
        success: function (response) {
            const list = response.data;
            list.forEach((item) => {
                countryLookup[item.value] = item.name;
            });
            console.log("Country lookup ready:", countryLookup);
        },
        error: function (error) {
            console.error("Failed to load country list:", error);
        },
    });
}
window.initCountryLookup = initCountryLookup;

function getCountryName(code) {
    return countryLookup[code] || code;
}
window.getCountryName = getCountryName;

function normalizeToArray(value) {
    if (Array.isArray(value)) return value;
    try {
        const parsed = JSON.parse(value);
        if (Array.isArray(parsed)) return parsed;
    } catch (e) {}
    return value ? [value] : [];
}
window.normalizeToArray = normalizeToArray;

async function data_table_init() {
    console.log("DataTable initialized");
    const [
        { default: DataTable },
        _bs5,
        _responsive,
        _buttons,
        _buttonsHtml5,
        _buttonsPrint,
    ] = await Promise.all([
        import("datatables.net-bs5"),
        import("datatables.net-responsive-bs5"),
        import("datatables.net-buttons-bs5"),
        import("datatables.net-buttons/js/buttons.html5.mjs"),
        import("datatables.net-buttons/js/buttons.print.mjs"),
    ]);

    await Promise.all([
        import("datatables.net-bs5/css/dataTables.bootstrap5.min.css"),
        import("datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css"),
    ]);

    internalListTable = new DataTable("#conditionTable", {
        processing: true,
        serverSide: false,
        ajax: {
            url: "/internal/consignment_condition/data",
            type: "GET",
            dataSrc: "data",
        },
        columns: [
            {
                data: "item_name",
                title: "Item Name",
                render: function (data) {
                    return `<span class="text-wrap">${data}</span>` ?? "-";
                },
            },
            {
                data: "scientific_name",
                title: "Scientific Name",
                render: function (data) {
                    return `<span class="text-wrap">${data ?? ""}</span>` ?? "-";
                },
            },
            {
                data: "condcategory.description",
                title: "Category",
                render: function (data) {
                    return data ?? "-";
                },
            },
            {
                data: "id",
                title: "Action",
                orderable: false,
                searchable: false,
                render: function (id) {
                    return `
                        <a href="/internal/consignment_condition/edit/${id}"
                            class="btn btn-sm btn-primary">
                            <i class="ri-edit-line"></i> <span data-en="Edit" data-bm="Kemaskini">Edit</span>
                        </a>
                        <button type="button"
                            onclick="condiModal(${id})"
                            class="btn btn-sm btn-info">
                            <span data-en="Show Condition" data-bm="Papar Syarat">Show Condition</span>
                        </button>
                    `;
                },
            },
        ],
        responsive: true,
        pageLength: 10,
    });
}

// Populate Category dropdown from public_code (consignment_category)
function initCategoryFilter() {
    return $.ajax({
        url: "/internal/get_pbdata/consignment_category",
        method: "GET",
        success: function (response) {
            console.log('response', response)
            const select = document.getElementById("filterConsignCategory");
            (response.data || []).forEach((item) => {
                const opt = document.createElement("option");
                opt.value = item.description;
                opt.textContent = item.description;
                select.appendChild(opt);
            });
            setupSelect2('#filterConsignCategory', 'All Categories');
        },
        error: function () {
            console.error("Failed to load category filter options.");
        },
    });
}

// Populate Usage dropdown – kept for UI but not used in filtering
function initUsageFilter() {
    return $.ajax({
        url: "/internal/consignment_condition/usages",
        method: "GET",
        success: function (response) {
            const select = document.getElementById("filterConsignUsage");
            (response.data || []).forEach((usage) => {
                const opt = document.createElement("option");
                opt.value = usage;
                opt.textContent = usage;
                select.appendChild(opt);
            });
            setupSelect2('#filterConsignUsage', 'All Usage');
        },
        error: function () {
            console.error("Failed to load usage filter options.");
        },
    });
}

document.addEventListener("DOMContentLoaded", async function () {
    await initCountryLookup();
    await data_table_init();
    initCategoryFilter();
    initUsageFilter();

    function deleteCondition(id) {
        Swal.fire({
            title: getText("areYouSure"),
            text: getText("deleteWarning"),
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/internal/consignment_condition/delete/${id}`,
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: "success",
                            title: getText("deleted"),
                            text:
                                res.message ||
                                getText("deletedSuccess"),
                        });
                        internalListTable.ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: "error",
                            title: getText("error"),
                            text:
                                xhr.responseJSON?.message ||
                                getText("failedToDelete"),
                        });
                    },
                });
            }
        });
    }

    window.deleteCondition = deleteCondition;

    function condiModal(id) {
        const modalelement = document.getElementById("showConditionModal");
        const modal = new bootstrap.Modal(modalelement);

        $.ajax({
            url: `/internal/consignment_condition/data/${id}`,
            type: "GET",

            beforeSend: function () {
                Swal.fire({
                    title: getText("loading"),
                    text: getText("fetchingData"),
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        applyTranslations(Swal.getHtmlContainer());
                    },
                });
            },

            success: function (response) {
                Swal.close();
                const condition = response.data;
                let usageList = condition.usage;
                let countCode = [];

                console.log("tespone", response);

                if (Array.isArray(condition.country)) {
                    countCode = condition.country;
                } else {
                    try {
                        countCode = JSON.parse(condition.country);
                    } catch (e) {
                        countCode = [condition.country];
                    }
                }

                const countryNames = countCode.map((code) =>
                    getCountryName(code),
                );
                const namoong = "PERMIT CONDITION : " + condition.item_name;

                document.getElementById("modalTitle").textContent = namoong;
                document.getElementById("itemNameCell").textContent =
                    condition.item_name;

                document.getElementById("scientificNameCell").textContent =
                    condition.scientific_name || "-";

                document.getElementById("categoryCell").textContent =
                    condition.condcategory
                        ? condition.condcategory.description
                        : "-";
                document.getElementById("usageCell").textContent =
                    normalizeToArray(usageList).join(", ");
                document.getElementById("countryCell").textContent =
                    countryNames.join(", ");

                let quantityDisplay = "-";
                if (condition.quantity_limit || condition.measurement_unit) {
                    const parts = [];
                    if (condition.quantity_limit)
                        parts.push(condition.quantity_limit);
                    if (condition.measurement_unit)
                        parts.push(condition.measurement_unit);
                    quantityDisplay = parts.join(" ");
                }
                document.getElementById("quantityLimit").textContent =
                    quantityDisplay;

                let dateDisplay = "-";
                if (condition.start_date && condition.end_date) {
                    const start = new Date(condition.start_date);
                    const end = new Date(condition.end_date);
                    const options = {
                        day: "2-digit",
                        month: "short",
                        year: "numeric",
                    };
                    dateDisplay =
                        start.toLocaleDateString("en-GB", options) +
                        " until " +
                        end.toLocaleDateString("en-GB", options);
                } else if (condition.start_date) {
                    const start = new Date(condition.start_date);
                    dateDisplay =
                        "From: " +
                        start.toLocaleDateString("en-GB", {
                            day: "2-digit",
                            month: "short",
                            year: "numeric",
                        });
                } else if (condition.end_date) {
                    const end = new Date(condition.end_date);
                    dateDisplay =
                        "Until: " +
                        end.toLocaleDateString("en-GB", {
                            day: "2-digit",
                            month: "short",
                            year: "numeric",
                        });
                }
                document.getElementById("date").textContent = dateDisplay;

                document.getElementById("conditionHtml").innerHTML =
                    condition.addional_condition ||
                    "<i>No condition provided</i>";

                modal.show();
            },

            error: function () {
                Swal.fire({
                    icon: "error",
                    title: getText("error"),
                    text: getText("failedToFetch"),
                });
            },
        });
    }

    window.condiModal = condiModal;

    // ─── FILTER: Apply ──────────────────────────────────────────
    document
        .getElementById("btnConsignCondFilter")
        .addEventListener("click", function () {
            const itemName = document.getElementById("filterConsignItemName").value;
            const category = document.getElementById("filterConsignCategory").value;

            // Column 0 = Item Name
            internalListTable.column(0).search(itemName);

            // Column 2 = Category (condcategory.description)
            if (!category || category.length === 0) {
                internalListTable.column(2).search("");
            } else {
                // If single category, just search exact match (or use regex)
                const categoryVals = [].concat(category);
                const categoryRegex = categoryVals.map(v => $.fn.dataTable.util.escapeRegex(v)).join('|');
                internalListTable.column(2).search(categoryRegex, true, false);
            }

            internalListTable.draw();

            // Close the dropdown
            bootstrap.Dropdown.getInstance(
                document.getElementById("consignCondFilterDropdown")
            )?.hide();
        });

    // ─── FILTER: Reset ──────────────────────────────────────────
    document
        .getElementById("btnResetConsignCondFilter")
        .addEventListener("click", function () {
            // Clear inputs
            document.getElementById("filterConsignItemName").value = "";
            $('#filterConsignCategory').val(null).trigger('change');
            $('#filterConsignUsage').val(null).trigger('change');

            // Clear all filters and redraw
            internalListTable.search("").columns().search("").draw();

            // Close the dropdown
            bootstrap.Dropdown.getInstance(
                document.getElementById("consignCondFilterDropdown")
            )?.hide();
        });
});