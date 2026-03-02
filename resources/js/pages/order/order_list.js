import { initTooltips } from "../../app";
import Swal from "sweetalert2";

console.log("order list");

let orderListTable;

const isInternal = window.AUTH_TYPE === "internal";

// Store current filter values
const orderFilters = {
    status: "",
    applicationType: "",
    startDate: "",
    endDate: "",
};

async function data_table_init() {
    const [{ default: DataTable }] = await Promise.all([
        import("datatables.net-bs5"),
        import("datatables.net-responsive-bs5"),
        import("datatables.net-buttons-bs5"),
    ]);

    orderListTable = new DataTable("#orderListTable", {
        processing: true,
        serverSide: true,
        ajax: {
            url: "/order/list/data",
            data: function (d) {
                d.order_status = orderFilters.status;
                d.application_type = orderFilters.applicationType;
                d.start_date = orderFilters.startDate;
                d.end_date = orderFilters.endDate;
            },
        },

        columns: [
            { data: "order_number" },
            { data: "status" },
            { data: "application_type" },
            ...(isInternal ? [{ data: "kod_transaksi" }] : []),
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

    // Apply Filters
    $("#btnOrderFilter").on("click", function (e) {
        e.preventDefault();

        orderFilters.status = $("#filterOrderStatus").val() || "";
        orderFilters.applicationType = $("#filterAppType").val() || "";
        orderFilters.startDate = $("#filterOrderStartDate").val() || "";
        orderFilters.endDate = $("#filterOrderEndDate").val() || "";

        orderListTable.ajax.reload();
    });

    // Reset Filters
    $("#btnResetOrderFilter").on("click", function (e) {
        e.preventDefault();

        $("#filterOrderStatus").val("");
        $("#filterAppType").val("");
        $("#filterOrderStartDate").val("");
        $("#filterOrderEndDate").val("");

        orderFilters.status = "";
        orderFilters.applicationType = "";
        orderFilters.startDate = "";
        orderFilters.endDate = "";

        orderListTable.ajax.reload();
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
