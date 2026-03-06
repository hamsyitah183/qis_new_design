import { initTooltips } from "../app";

console.log("boundary dashboard");

const tableIds = [
    "#importPermitTable",
    "#inspectionCertTable",
    "#consignmentCertTable",
];

async function data_table_init() {
    const [
        { default: DataTable },
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

    tableIds.forEach((id) => {
        const table = new DataTable(id, {
            processing: true,
            stateSave: true,
            columns: [
                { width: "20%" }, // Application ID
                { width: "30%" }, // User Name
                { width: "20%" }, // Status
                { width: "15%", orderable: false, searchable: false }, // Action
            ],
            autoWidth: false,
            responsive: true,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            order: [], // Keep server-sent order
        });

        table.on("draw.dt", function () {
            initTooltips();
        });
    });
}

document.addEventListener("DOMContentLoaded", data_table_init);
