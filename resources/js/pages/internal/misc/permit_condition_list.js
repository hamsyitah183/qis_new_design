import $ from "jquery";
import Swal from "sweetalert2";

let internalListTable;
let countryLookup = {};

// Fetch country list & build lookup map
function initCountryLookup() {
    return $.ajax({
        url: '/get_country',
        method: 'GET',
        success: function (response) {
            const list = response.data;
            list.forEach(item => {
                countryLookup[item.value] = item.name;
            });
            console.log("Country lookup ready:", countryLookup);
        },
        error: function (error) {
            console.error("Failed to load country list:", error);
        }
    });
} window.initCountryLookup = initCountryLookup;

function getCountryName(code) {
    return countryLookup[code] || code;
} window.getCountryName = getCountryName;

function normalizeToArray(value) {
    if (Array.isArray(value)) return value;
    try {
        const parsed = JSON.parse(value);
        if (Array.isArray(parsed)) return parsed;
    } catch (e) { }
    return value ? [value] : [];
} window.normalizeToArray = normalizeToArray;

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
            url: "/internal/permit_condition/data",
            type: "GET",
            dataSrc: "data"
        },
        columns: [
            {
                data: "item_name",
                title: "Item Name",
                render: function (data) {
                    return `<span class="text-wrap">${data}</span>` ?? "-";
                }
            },
            {
                data: "scientific_name",
                title: "Scientific Name",
                render: function (data) {
                    return `<span class="text-wrap">${data ?? ' '}</span>` ?? "-";
                }
            },
            {
                data: "condcategory.description",
                title: "Category",
                render: function (data) {
                    return data ?? "-";
                }
            },
            {
                data: "usage",
                title: "Usage",
                render: function (data) {
                    if (Array.isArray(data)) {
                        return data.join(", ");
                    }
                    try {
                        const parsed = JSON.parse(data);
                        return Array.isArray(parsed) ? parsed.join(", ") : parsed;
                    } catch {
                        return data ?? "-";
                    }
                }
            },
            {
                data: "id",
                title: "Action",
                orderable: false,
                searchable: false,
                render: function (id) {
                    return `
                        <a href="/internal/permit_edit_condition/${id}"
                            class="btn btn-sm btn-primary">
                            <i class="ri-edit-line"></i> Edit
                        </a>
                        <button type="button"
                            onclick="condiModal(${id})"
                            class="btn btn-sm btn-info">
                            Show Condition
                        </button>
                    `;
                }
            }
        ],
        responsive: true,
        pageLength: 10,
    });
}

// Populate Category dropdown from public_code (condition_category)
function initCategoryFilter() {
    return $.ajax({
        url: '/internal/get_pbdata/condition_category',
        method: 'GET',
        success: function (response) {
            const select = document.getElementById('filterPermitCategory');
            (response.data || []).forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.description;
                opt.textContent = item.description;
                select.appendChild(opt);
            });
        },
        error: function () {
            console.error('Failed to load category filter options.');
        }
    });
}

// Populate Usage dropdown from distinct values in ip_condition
function initUsageFilter() {
    return $.ajax({
        url: '/internal/permit_condition/usages',
        method: 'GET',
        success: function (response) {
            const select = document.getElementById('filterPermitUsage');
            (response.data || []).forEach(usage => {
                const opt = document.createElement('option');
                opt.value = usage;
                opt.textContent = usage;
                select.appendChild(opt);
            });
        },
        error: function () {
            console.error('Failed to load usage filter options.');
        }
    });
}

document.addEventListener("DOMContentLoaded", async function () {
    await initCountryLookup();
    await data_table_init();
    initCategoryFilter();
    initUsageFilter();

    function condiModal(id) {
        const modalelement = document.getElementById("showConditionModal");
        const modal = new bootstrap.Modal(modalelement);

        $.ajax({
            url: `/internal/permit_condition/getdata/${id}`,
            type: "GET",

            beforeSend: function () {
                Swal.fire({
                    title: "Loading...",
                    text: "Fetching permit condition data",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            },

            success: function (response) {
                Swal.close();
                const condition = response.data;
                let usageList = condition.usage;
                let countCode = [];

                if (Array.isArray(condition.country)) {
                    countCode = condition.country;
                } else {
                    try {
                        countCode = JSON.parse(condition.country);
                    } catch (e) {
                        countCode = [condition.country];
                    }
                }

                const countryNames = countCode.map(code => getCountryName(code));
                const namoong = "PERMIT CONDITION : " + condition.item_name;

                document.getElementById("modalTitle").textContent = namoong;
                document.getElementById("itemNameCell").textContent = condition.item_name;
                document.getElementById("categoryCell").textContent =
                    condition.condcategory ? condition.condcategory.description : "-";
                document.getElementById("usageCell").textContent =
                    normalizeToArray(usageList).join(", ");
                document.getElementById("countryCell").textContent =
                    countryNames.join(", ");
                document.getElementById("conditionHtml").innerHTML =
                    condition.addional_condition || "<i>No condition provided</i>";

                modal.show();
            },

            error: function () {
                Swal.fire({ icon: "error", title: "Error", text: "Failed to fetch permit condition data." });
            }
        });
    }

    window.condiModal = condiModal;

    // Filter functionality
    document.getElementById("btnPermitFilter").addEventListener("click", function () {
        const itemName = document.getElementById("filterPermitItemName").value;
        const category = document.getElementById("filterPermitCategory").value;
        const usage = document.getElementById("filterPermitUsage").value;

        internalListTable.column(0).search(itemName);

        if (category === "") {
            internalListTable.column(1).search("");
        } else {
            internalListTable.column(1).search(category);
        }

        if (usage === "") {
            internalListTable.column(2).search("");
        } else {
            internalListTable.column(2).search(usage);
        }

        internalListTable.draw();
        bootstrap.Dropdown.getInstance(document.getElementById('permitFilterDropdown')).hide();
    });

    // Reset filter
    document.getElementById("btnResetPermitFilter").addEventListener("click", function () {
        document.getElementById("filterPermitItemName").value = "";
        document.getElementById("filterPermitCategory").value = "";
        document.getElementById("filterPermitUsage").value = "";

        internalListTable.search("").columns().search("").draw();
        bootstrap.Dropdown.getInstance(document.getElementById('permitFilterDropdown')).hide();
    });
});
