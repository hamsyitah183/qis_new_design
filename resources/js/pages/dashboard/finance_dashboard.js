
import { initTooltips } from "../../app";
import Swal from "sweetalert2";

console.log("finance dashboard");

let tables = [];

// Import modules including Buttons
async function data_table_init() {
    const [{ default: DataTable }] = await Promise.all([
        import("datatables.net-bs5"),
        import("datatables.net-responsive-bs5"),
        import("datatables.net-buttons-bs5"),
        import("datatables.net-buttons/js/buttons.html5.mjs"), // Required for Excel export
        import("jszip"), // Required for Excel export
    ]);

    // Expose JSZip globally for DataTables to use
    window.JSZip = (await import("jszip")).default;

    const tableConfigs = [
        { id: "#importPermitOrderTable", type: "import_permit" },
        { id: "#inspectionCertOrderTable", type: "inspection" },
        { id: "#consignmentCertOrderTable", type: "consignment" },
    ];

    tableConfigs.forEach(({ id, type }) => {
        const table = new DataTable(id, {
            processing: true,
            serverSide: true,
            ajax: {
                url: "/order/list/data",
                data: function (d) {
                    d.application_type = type;
                },
            },
            dom: 'Bfrtip', // Enable Buttons
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="ti ti-file-spreadsheet"></i> Export Excel',
                    className: 'btn btn-success mb-3',
                    exportOptions: {
                        columns: ':not(:last-child)' // Exclude action column
                    }
                }
            ],
            columns: [
                { data: "order_number" },
                { data: "transaction_date" },
                { data: "fpx_reference" },
                { data: "user_name" },
                { data: "permit_number" },
                { data: "status" },
                { data: "application_type" },
                { data: "transaction_data" },
                { data: "payment_amount" },
                { data: "action", orderable: false, searchable: false },
            ],
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [],
        });

        table.on("draw.dt", function () {
            initTooltips();
        });

        tables.push(table);
    });

    // DELETE ORDER
    $(document).on("click", ".deleteApplication", async function () {
        const applicationId = $(this).data("id");

        const result = await Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#4c5359ff",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        });

        if (!result.isConfirmed) return;

        await fetch(`/internal/order/delete/${applicationId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                Accept: "application/json",
            },
        });

        Swal.fire("Deleted!", "Order deleted successfully", "success");
        tables.forEach((t) => t.ajax.reload(null, false));
    });

    initTooltips();
}

document.addEventListener("DOMContentLoaded", data_table_init);
