import { initTooltips } from "../../app";
import Swal from "sweetalert2";

console.log("order list");

let orderListTable;

const isInternal = window.AUTH_TYPE === "internal";

async function data_table_init() {
    const [{ default: DataTable }] = await Promise.all([
        import("datatables.net-bs5"),
        import("datatables.net-responsive-bs5"),
        import("datatables.net-buttons-bs5"),
    ]);

    orderListTable = new DataTable("#orderListTable", {
        processing: true,
        serverSide: true,
        ajax: "/order/list/data",

        columns: [

            { data: "order_number" },
            { data: "status" },
            { data: "application_type" },

            ...(isInternal ? [{ data: "kod_transaksi" }] : []),

            // { data: "kod_transaksi" },
            { data: "payment_amount" },
            { data: "action", orderable: false, searchable: false },
        ],


        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [],
    });

    orderListTable.on("draw.dt", function () {
        initTooltips();
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
        orderListTable.ajax.reload(null, false);
    });

    initTooltips();
}

document.addEventListener("DOMContentLoaded", data_table_init);
