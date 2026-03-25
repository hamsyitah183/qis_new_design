
import { initTooltips } from "../../app";
import Swal from "sweetalert2";

console.log("finance dashboard");

let table;

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

    let hasFilters = false;

    table = new DataTable("#financeOrderTable", {
        processing: true,
        serverSide: true,
        deferLoading: 0, // Start with empty table
        ajax: {
            url: "/order/list/data",
            data: function (d) {
                d.order_number = document.getElementById("filterOrderNumber")?.value || "";

                d.start_date = document.getElementById("filterStartDate")?.value || "";
                d.end_date = document.getElementById("filterEndDate")?.value || "";
                d.fpx_reference = document.getElementById("filterFpxReference")?.value || "";
                d.application_type = document.getElementById("filterApplicationType")?.value || "";
                d.order_status = document.getElementById("filterOrderStatus")?.value || "";
            },
            beforeSend: function (xhr) {
                if (!hasFilters) {
                    xhr.abort();
                    return false;
                }
            },
        },
        dom: "Bfrtip", // Enable Buttons
        buttons: [
            {
                extend: "excelHtml5",
                text: '<i class="ti ti-file-spreadsheet"></i> Export Excel',
                className: "btn btn-success mb-3",
                exportOptions: {
                    columns: ":not(:last-child)", // Exclude action column
                    format: {
                        body: function (data, row, column, node) {
                            // For permit_number column, extract raw permits from data attribute
                            const wrapper = document.createElement("div");
                            wrapper.innerHTML = data;
                            const btn = wrapper.querySelector("[data-permits]");
                            if (btn) {
                                return btn.getAttribute("data-permits");
                            }
                            // Strip HTML tags for other columns like badges (Order Status)
                            return data ? data.replace(/<[^>]*>?/gm, '').trim() : data;
                        },
                    },
                },
            },
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
        // Initialize Bootstrap popovers for permit number buttons
        const popoverTriggerList = document.querySelectorAll('#financeOrderTable [data-bs-toggle="popover"]');
        popoverTriggerList.forEach(el => new bootstrap.Popover(el));
    });

    // Filter button
    document.getElementById("btnFilter")?.addEventListener("click", function () {
        const orderNum = document.getElementById("filterOrderNumber")?.value || "";

        const startDate = document.getElementById("filterStartDate")?.value || "";
        const endDate = document.getElementById("filterEndDate")?.value || "";
        const fpxRef = document.getElementById("filterFpxReference")?.value || "";
        const appType = document.getElementById("filterApplicationType")?.value || "";
        const orderStatus = document.getElementById("filterOrderStatus")?.value || "";

        if (!orderNum && !startDate && !endDate && !fpxRef && !appType && !orderStatus) {
            alert("Please fill in at least one filter before searching.");
            return;
        }

        hasFilters = true;
        table.ajax.reload(null, true);
    });

    // Reset button
    document.getElementById("btnReset")?.addEventListener("click", function () {
        document.getElementById("filterOrderNumber").value = "";

        document.getElementById("filterStartDate").value = "";
        document.getElementById("filterEndDate").value = "";
        document.getElementById("filterFpxReference").value = "";
        document.getElementById("filterApplicationType").value = "";
        document.getElementById("filterOrderStatus").value = "";
        hasFilters = false;

        // Temporarily disable serverSide so clear+draw doesn't trigger an AJAX call
        const settings = table.settings()[0];
        settings.oFeatures.bServerSide = false;
        table.clear().draw();
        settings.oFeatures.bServerSide = true;
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
        table.ajax.reload(null, false);
    });

    initTooltips();
}

document.addEventListener("DOMContentLoaded", data_table_init);
