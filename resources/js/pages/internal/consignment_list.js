import { formatTime, initTooltips } from "../../app";
import Swal from "sweetalert2";

console.log("consignment list (internal)");
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
            { data: "status" },
            { data: "permit_status" },
            { data: "submitted_by" },
            { data: "action" },
        ],

        columnDefs: [
            { width: "50px", targets: 0 },
            { width: "250px", targets: 1 },      // Importer
            { width: "250px", targets: 2 },      // Exporter
            { width: "200px", targets: 3 },      // Application Status
            { width: "150px", targets: 4 },      // Permit Status
            { width: "150px", targets: 5 },      // Submitted By
            { width: "100px", targets: 6 },      // Action
        ],

        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    consignmentListTable.on("draw.dt", function () {
        initTooltips();
    });

    // Admin actions (internal users): approve, reject, delete
    if (isInternal) {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        // Approve / Reject
        $(document).on("click", ".consignment-approve, .consignment-reject", async function () {
            const id = $(this).data("id");
            const isApprove = $(this).hasClass("consignment-approve");
            const targetStatus = isApprove ? "Approved" : "Rejected";

            const result = await Swal.fire({
                title: `Confirm ${targetStatus}?`,
                text: `This consignment application will be marked as ${targetStatus}.`,
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#4c5359ff",
                cancelButtonColor: "#d33",
                confirmButtonText: `Yes, ${targetStatus.toLowerCase()} it!`,
            });

            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`/internal/consignment/${id}/status`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ status: targetStatus }),
                });

                const contentType = res.headers.get("content-type");
                let json;
                
                if (contentType && contentType.includes("application/json")) {
                    json = await res.json();
                } else {
                    const text = await res.text();
                    console.error("Non-JSON response:", text);
                    throw new Error("Server returned non-JSON response");
                }

                if (!res.ok) {
                    throw new Error(json.message || "Status update failed");
                }

                Swal.fire("Updated!", json.message || "Status updated successfully", "success");
                consignmentListTable.ajax.reload(null, false);
            } catch (error) {
                console.error("Error updating status:", error);
                Swal.fire("Error", error.message || "Failed to update status.", "error");
            }
        });

        // Delete
        $(document).on("click", ".delete-consignment", async function () {
            const id = $(this).data("id");
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

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

            try {
                const res = await fetch(`/internal/consignment/delete/${id}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                });

                const contentType = res.headers.get("content-type");
                let json;
                
                if (contentType && contentType.includes("application/json")) {
                    json = await res.json();
                } else {
                    const text = await res.text();
                    console.error("Non-JSON response:", text);
                    throw new Error("Server returned non-JSON response");
                }

                if (!res.ok) {
                    throw new Error(json.message || "Delete failed");
                }

                Swal.fire("Deleted!", json.message || "Application deleted successfully", "success");
                consignmentListTable.ajax.reload(null, false);
            } catch (error) {
                console.error("Error deleting:", error);
                Swal.fire("Error", error.message || "Failed to delete consignment application.", "error");
            }
        });
    }

    initTooltips();
}

document.addEventListener("DOMContentLoaded", data_table_init);

