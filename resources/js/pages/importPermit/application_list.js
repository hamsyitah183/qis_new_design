let applicationListTable;

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
        ajax: "/public/application/list/data", // UPDATE THIS!
        columns: [
            { data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false },
            { data: "importer", name: "importer" },
            { data: "exporter", name: "exporter" },
            { data: "submitted_by", name: "submitted_by" },
            { data: "importer_type", name: "importer_type" },
            { data: "date", name: "date" },
            { data: "status", name: "status" },
            { data: "action", name: "action" },
        ],
        responsive: true,
        pageLength: 10,
    });
}

document.addEventListener("DOMContentLoaded", data_table_init);
