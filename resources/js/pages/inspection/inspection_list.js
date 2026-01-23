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
            { data: "importer" },
            { data: "exporter" },
            { data: "status" },
            { data: "inspection_status" },

            // 🔐 Only internal users see this
            ...(isInternal ? [{ data: "submitted_by" }] : []),

            { data: "action" },
        ],

        columnDefs: [
            { width: "50px", targets: 0 },
            { width: "150px", targets: 1 },
            { width: "150px", targets: 2 },
            { width: "100px", targets: 3 },

            ...(isInternal ? [{ width: "150px", targets: 4 }] : []),

            { width: "120px", targets: isInternal ? 5 : 4 },
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
    $(document).on("click", ".deleteApplication", async function () {
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
    activityLog();
}

function activityLog() {
    $(document).on("click", ".activityLog", async function (e) {
        e.preventDefault();

        const id = $(this).data("log");
        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });
        try {
            const res = await fetch(`/inspection_application/${id}/data`);

            if (!res.ok) {
                throw new Error("Failed to fetch activity log");
            }

            const json = await res.json();

            Swal.close();

            const tableBody = $("#inspectionLogTable tbody");
            tableBody.empty(); // clear existing rows

            let activity_log = json.activity_log;

            const modalEl = document.getElementById("activityLogModal");
            modalEl.querySelector(".modal-title").textContent = " Inspection Activity Log";

            activity_log.forEach((log) => {
                tableBody.append(`
                    <tr>
                        <td>${log.action}</td>
                        <td>${log.causer ? log.causer.fullname : 'System'}</td>
                        <td>${log.remark || '-'}</td>
                        <td>${log.status || '-'}</td>
                        <td>${formatTime(log.created_at)}</td>
                    </tr>
                `);
            });

            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } catch (error) {
            console.error("Activity log error:", error);
            Swal.close();
        }
    });
}

document.addEventListener("DOMContentLoaded", data_table_init);
