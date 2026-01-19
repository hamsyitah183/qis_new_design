import { formatTime, initTooltips } from "../../app";
import Swal from "sweetalert2";

console.log("inspection list");
let inspectionListTable;

const isInternal = window.AUTH_TYPE === "internal";

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

    inspectionListTable = new DataTable("#inspectionListTable", {
        processing: true,
        serverSide: true,
        ajax: "/inspection_certificates_list/data",
        columns: [
            {
                data: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "category" },
            { data: "importer" },
            { data: "exporter" },
            { data: "eta" },
            { data: "transport_type" },
            { data: "entry_point" },
            { data: "status" },
            { data: "action" },
        ],

        columnDefs: [
            { width: "50px", targets: 0 },
            { width: "150px", targets: 1 },
            { width: "150px", targets: 2 },
            { width: "150px", targets: 3 },
            { width: "100px", targets: 4 },
            { width: "100px", targets: 5 },
            { width: "100px", targets: 6 },
            { width: "100px", targets: 7 },
            { width: "120px", targets: 8 },
        ],

        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    inspectionListTable.on("draw.dt", function () {
        initTooltips();
    });

    initTooltips();
}

document.addEventListener("DOMContentLoaded", data_table_init);
