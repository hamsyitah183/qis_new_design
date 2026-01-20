import { formatTime, initTooltips } from "../../app";
import Swal from "sweetalert2";

console.log("inspection list");
let inspectionListTable;

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

    inspectionListTable = new DataTable("#inspectionListTable", {
        processing: true,
        serverSide: true,
        ajax: "/inspection_certificates_list/data",
        columns: [
            {
                data: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "category" },
            { data: "importer" },
            { data: "exporter" },
            { data: "eta" },
            { data: "transport_type" },
            { data: "entry_point" },
            { data: "status" },
            { data: "action" },
        ],

        columnDefs: [
            { width: "50px", targets: 0 },
            { width: "150px", targets: 1 },
            { width: "150px", targets: 2 },
            { width: "150px", targets: 3 },
            { width: "100px", targets: 4 },
            { width: "100px", targets: 5 },
            { width: "100px", targets: 6 },
            { width: "100px", targets: 7 },
            { width: "120px", targets: 8 },
        ],

        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    inspectionListTable.on("draw.dt", function () {
        initTooltips();
    });

    // Admin actions (internal users): approve, reject, delete
    if (isInternal) {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        // Approve / Reject
        $(document).on("click", ".inspection-approve, .inspection-reject", async function () {
            const id = $(this).data("id");
            const isApprove = $(this).hasClass("inspection-approve");
            const targetStatus = isApprove ? "Approved" : "Rejected";

            const result = await Swal.fire({
                title: `Confirm ${targetStatus}?`,
                text: `This inspection application will be marked as ${targetStatus}.`,
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#4c5359ff",
                cancelButtonColor: "#d33",
                confirmButtonText: `Yes, ${targetStatus.toLowerCase()} it!`,
            });

            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`/internal/inspection/${id}/status`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ status: targetStatus }),
                });

                if (!res.ok) throw new Error("Status update failed");

                const json = await res.json();
                Swal.fire("Updated!", json.message, "success");
                inspectionListTable.ajax.reload(null, false);
            } catch (error) {
                console.error(error);
                Swal.fire("Error", "Failed to update status.", "error");
            }
        });
    }

    // Delete - Available for both internal and public
    $(document).on("click", ".deleteInspection", async function () {
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
            const res = await fetch(`/inspection/delete/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });

            if (!res.ok) {
                const errorData = await res.json();
                throw new Error(errorData.message || "Delete failed");
            }

            const json = await res.json();
            Swal.fire("Deleted!", json.message, "success");
            inspectionListTable.ajax.reload(null, false);
        } catch (error) {
            console.error(error);
            Swal.fire("Error", error.message || "Failed to delete inspection application.", "error");
        }
    });

    initTooltips();
}

document.addEventListener("DOMContentLoaded", data_table_init);
