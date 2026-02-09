
import { formatTime, initTooltips } from "../app";
import Swal from "sweetalert2";

console.log("boundary dashboard");

let boundaryTable;

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

    boundaryTable = new DataTable("#boundaryApplicationsTable", {
        // Client-side processing since data is rendered in Blade
        processing: true,
        stateSave: true, // Optional: save state (paging position etc)
        columns: [
            { width: "20%" }, // User Name
            { width: "25%" }, // Application Type
            { width: "15%" }, // Status
            { width: "10%", orderable: false, searchable: false }, // Action
        ],
        autoWidth: false,
        responsive: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[0, 'desc']], // Default sort undefined, let's say by User Name or keep HTML order? 
        // If sorting by date is needed, the date isn't a column. 
        // The controller sorts by Created At descending. 
        // If we want to maintain that, we should disable initial sort or ensure HTML order is respected.
        order: [], // Keep server-sent order
    });

    boundaryTable.on("draw.dt", function () {
        initTooltips();
    });
}

document.addEventListener("DOMContentLoaded", data_table_init);
