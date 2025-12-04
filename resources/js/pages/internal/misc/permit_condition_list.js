import $ from "jquery";
import Swal from "sweetalert2";



    let internalListTable;
    let countryLookup = {};

    // Fetch country list & build lookup map
    function initCountryLookup() {
        return $.ajax({
            url: '/get_country',
            method: 'GET',
            success: function(response) {

                const list = response.data;

                // Build lookup dictionary: { "AE": "United Arab Emirates", ... }
                list.forEach(item => {
                    countryLookup[item.value] = item.name;
                });

                console.log("Country lookup ready:", countryLookup);
            },
            error: function(error) {
                console.error("Failed to load country list:", error);
            }
        });
    } window.initCountryLookup = initCountryLookup;

    // Lookup helper function
    function getCountryName(code) {
        return countryLookup[code] || code; 
    }window.getCountryName = getCountryName;

    function normalizeToArray(value) {
        if (Array.isArray(value)) return value;

        try {
            const parsed = JSON.parse(value);
            if (Array.isArray(parsed)) return parsed;
        } catch (e) {}

        return value ? [value] : [];
    }window.normalizeToArray = normalizeToArray;


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

        internalListTable = new DataTable("#internalUsersTable", {
            processing: true,
            serverSide: true,
            ajax: "/internal/permit_condition/data",
            columns: [
                {data: null, title: "#",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: "item_name", title: "Item Name"},
                {data: "condcategory.description",
                    title: "Category",
                    render: function (data, type, row) {
                        return data ?? "-";
                    }
                },
                {data: "usage", title: "Usage",
                    render: function (data, type, row) {
                        // case 1: real array
                        if (Array.isArray(data)) {
                            return data.join(", ");
                        }

                        // case 2: stringified JSON → parse it
                        try {
                            const parsed = JSON.parse(data);
                            return Array.isArray(parsed) ? parsed.join(", ") : parsed;
                        } catch {
                            return data; // not JSON
                        }
                    }
                },
                
                {data: "id",
                    title: "Action",
                    orderable: false,
                    searchable: false,
                    render: function (id) {
                        return `
                            <a href="/internal/permit_edit_condition/${id}" 
                            class="btn btn-sm btn-primary">
                                <i class="ri-edit-line"></i> Edit
                            </a>
                            
                            <a type="button" id="conditionModal" onclick="condiModal(${id})"  class="btn btn-sm btn-info">
                                Show Condition
                            </a>
                            `;
                    }
                }
            ],
            responsive: true,
            pageLength: 10,
        });
    }

document.addEventListener("DOMContentLoaded", async function () {
    await initCountryLookup();
    await data_table_init();

    function condiModal(id) {
        const theId = id;
        console.log("Condition Modal Function", theId);
        const modalelement = document.getElementById("showConditionModal");
        const modal = new bootstrap.Modal(modalelement);
        $.ajax({
            url: `/internal/permit_condition/getdata/${theId}`,
            type: "GET",
            success: function (response) {
                const condition = response.data;
                let usageList = condition.usage;               

                let countCode = [];
                // Case 1: already array
                if (Array.isArray(condition.country)) {
                    countCode = condition.country;
                }
                // Case 2: JSON string → parse safely
                else {
                    try {
                        countCode = JSON.parse(condition.country);
                    } catch (e) {
                        countCode = [condition.country]; // not JSON → return as is
                    }
                }
                const countryNames = countCode.map(code => getCountryName(code));
                const namoong = "PERMIT CONDITION : " + condition.item_name ;

                document.getElementById("modalTitle").textContent = namoong;
                document.getElementById("itemNameCell").textContent = condition.item_name;
                document.getElementById("categoryCell").textContent = condition.condcategory ? condition.condcategory.description : "-";
                document.getElementById("usageCell").textContent = normalizeToArray(usageList);
                document.getElementById("countryCell").textContent = countryNames.join(", ");
                document.getElementById("conditionHtml").innerHTML = condition.addional_condition || "<i>No condition provided</i>";
                // document.getElementById("").textContent = 
                modal.show();
            },
            error: function (xhr, status, error) {
                console.error("Error fetching condition data:", error);
            }
        });

        

    } window.condiModal = condiModal;

    
});



