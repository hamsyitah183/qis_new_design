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
            { data: "category_application" },
            { data: "importer" },
            { data: "exporter" },
            { data: "eta" },
            { data: "transport_type" },
            { data: "entry_point" },
            { data: "status" },

            // 🔐 Only internal users see this
            ...(isInternal ? [{ data: "submitted_by" }] : []),

            { data: "action" },
        ],

        columnDefs: [
            { width: "50px", targets: 0 },       // #
            { width: "120px", targets: 1 },      // Category
            { width: "150px", targets: 2 },      // Importer
            { width: "150px", targets: 3 },      // Exporter
            { width: "100px", targets: 4 },      // ETA
            { width: "100px", targets: 5 },      // Transport Type
            { width: "200px", targets: 6 },      // Entry Point
            { width: "100px", targets: 7 },      // Status

            ...(isInternal ? [{ width: "150px", targets: 8 }] : []),  // Submitted By

            { width: "100px", targets: isInternal ? 9 : 8 },  // Action
        ],

        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    consignmentListTable.on("draw.dt", function () {
        initTooltips();
    });

    initTooltips();
    handleDelete();
}

function handleDelete() {
    $(document).on("click", ".delete-consignment", function (e) {
        e.preventDefault();
        const id = $(this).data("id");
        const deleteUrl = `/public/consignment_application/delete/${id}`;

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: "Deleting...",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    url: deleteUrl,
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {
                        Swal.fire(
                            "Deleted!",
                            "Your application has been deleted.",
                            "success"
                        );
                        consignmentListTable.ajax.reload(); // Reload table
                    },
                    error: function (xhr) {
                        Swal.fire(
                            "Error!",
                            xhr.responseJSON?.message || "Something went wrong.",
                            "error"
                        );
                    },
                });
            }
        });
    });
}
document.addEventListener("DOMContentLoaded", data_table_init);

initTooltips();

