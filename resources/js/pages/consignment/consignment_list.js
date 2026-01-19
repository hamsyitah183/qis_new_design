import { formatTime, initTooltips } from "../../app";
import Swal from "sweetalert2";

console.log("consignment list");
let consignmentListTable;

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

    consignmentListTable = new DataTable("#consignmentListTable", {
        processing: true,
        serverSide: true,
        ajax: "/consignment/list/data",
        columns: [
            {
                data: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "importer" },
            { data: "exporter" },
            { data: "eta" },
            { data: "transport_type" },
            { data: "entry_point" },
            { data: "category_application" },
            { data: "importer_verify" },
            { data: "permit_status" },
            { data: "created_at" },

            // 🔐 Only internal users see this
            ...(isInternal ? [{ data: "submitted_by" }] : []),

            { data: "action" },
        ],

        columnDefs: [
            { width: "50px", targets: 0 },       // #
            { width: "150px", targets: 1 },      // Importer
            { width: "150px", targets: 2 },      // Exporter
            { width: "100px", targets: 3 },      // ETA
            { width: "100px", targets: 4 },      // Transport Type
            { width: "150px", targets: 5 },      // Entry Point
            { width: "80px", targets: 6 },       // Category
            { width: "120px", targets: 7 },      // Application Status (importer_verify)
            { width: "120px", targets: 8 },      // Permit Status
            { width: "120px", targets: 9 },      // Created Date

            ...(isInternal ? [{ width: "150px", targets: 10 }] : []),  // Submitted By

            { width: "100px", targets: isInternal ? 11 : 10 },  // Action
        ],

        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    consignmentListTable.on("draw.dt", function () {
        initTooltips();
    });

    initTooltips();
}

document.addEventListener("DOMContentLoaded", data_table_init);

initTooltips();
