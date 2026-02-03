import { formatTime, initTooltips } from "../../app";
import Swal from "sweetalert2";
import { activityLogDesign } from "../../appLog";

console.log("application list");
let applicationListTable;
let reviewApplicationListTable;

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

    applicationListTable = new DataTable("#applicationListTable", {
        processing: true,
        serverSide: true,
        ajax: "/application/list/data",
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

    applicationListTable.on("draw.dt", function () {
        initTooltips();
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
            // { data: "importer_type", name: "importer_type" },
            // { data: "date", name: "date" },
            { data: "application_type", name: "application_type" },

            { data: "status", name: "status" },
            { data: "submitted_by", name: "submitted_by" },
            { data: "action", name: "action" },
        ],

        columnDefs: [
            { width: "50px", targets: 0 }, // #
            { width: "150px", targets: 1 }, // Importer
            { width: "150px", targets: 2 }, // Exporter
            // { width: "120px", targets: 3 }, // Importer Type
            // { width: "100px", targets: 4 }, // ETA
            { width: "100px", targets: 3 }, // Status
            { width: "150px", targets: 4 }, // Submitted By
            { width: "120px", targets: 5 }, // Action
        ],

        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    reviewApplicationListTable.on("draw.dt", function () {
        initTooltips();
    });

    $(document).on("click", ".deleteApplication", async function () {
        const applicationId = $(this).data("id");
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        const Swal = await import("sweetalert2").then((m) => m.default);

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

        fetch(`/internal/application/delete/${applicationId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error("Delete failed");
                return res.json();
            })
            .then((data) => {
                Swal.fire("Deleted!", data.message, "success");
                applicationListTable.ajax.reload(null, false);
                reviewApplicationListTable.ajax.reload(null, false);
            })
            .catch((err) => {
                console.error(err);
                Swal.fire("Error!", "Failed to delete application.", "error");
            });
    });

    initTooltips();
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
            const res = await fetch(`/application/${id}/data`);

            if (!res.ok) {
                throw new Error("Failed to fetch activity log");
            }

            const json = await res.json();

            console.log("id:", id);
            console.log("response:", json.activity_log);

            Swal.close();

            const tableBody = $("#applicationLogTable tbody");
            tableBody.empty(); // clear existing rows

            // console.log("application", application.activity_log);
            let activity_log = json.activity_log



            const modalEl = document.getElementById("activityLogModal");
            modalEl.querySelector(".modal-title").textContent =
                "Import Permit Application Log" || "Activity Log";

            const cardBody = $('#activityLogModal .modal-body');
            cardBody.empty();
            cardBody.addClass('scroll-div');

            const html = activityLogDesign(activity_log);
            cardBody.html(html);

            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } catch (error) {
            console.error("Activity log error:", error);
            Swal.close();
        }
    });
}

document.addEventListener("DOMContentLoaded", data_table_init);

initTooltips();
activityLog();
