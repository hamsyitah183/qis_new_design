console.log("application list");
let applicationListTable;
let reviewApplicationListTable;


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

    applicationListTable = new DataTable("#applicationListTable", {
        processing: true,
        serverSide: true,
        ajax: "/application/list/data",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "importer", name: "importer" },
            { data: "exporter", name: "exporter" },
            { data: "importer_type", name: "importer_type" },
            { data: "date", name: "date" },
            { data: "status", name: "status" },
            { data: "submitted_by", name: "submitted_by" },
            { data: "action", name: "action" },
        ],

        columnDefs: [
            { width: "50px", targets: 0 }, // #
            { width: "150px", targets: 1 }, // Importer
            { width: "150px", targets: 2 }, // Exporter
            { width: "120px", targets: 3 }, // Importer Type
            { width: "100px", targets: 4 }, // ETA
            { width: "100px", targets: 5 }, // Status
            { width: "150px", targets: 6 }, // Submitted By
            { width: "120px", targets: 7 }, // Action
        ],

        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    reviewApplicationListTable = new DataTable("#reviewApplicationListTable", {
        processing: true,
        serverSide: true,
        ajax: "/application/review/list/data",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "importer", name: "importer" },
            { data: "exporter", name: "exporter" },
            { data: "importer_type", name: "importer_type" },
            { data: "date", name: "date" },
            { data: "status", name: "status" },
            { data: "submitted_by", name: "submitted_by" },
            { data: "action", name: "action" },
        ],

        columnDefs: [
            { width: "50px", targets: 0 }, // #
            { width: "150px", targets: 1 }, // Importer
            { width: "150px", targets: 2 }, // Exporter
            { width: "120px", targets: 3 }, // Importer Type
            { width: "100px", targets: 4 }, // ETA
            { width: "100px", targets: 5 }, // Status
            { width: "150px", targets: 6 }, // Submitted By
            { width: "120px", targets: 7 }, // Action
        ],

        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });
}

document.addEventListener("DOMContentLoaded", data_table_init);
