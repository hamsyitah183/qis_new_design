import { formatTime, initTooltips } from "../../app";
import Swal from "sweetalert2";
import { activityLogDesign } from "../../appLog";

// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

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

    // Load filter data on page load
    await loadFilterData();

    inspectionListTable = new DataTable("#inspectionListTable", {
        processing: true,
        serverSide: true,
        ajax: {
            url: "/inspection_certificates_list/data",
            data: function (d) {
                d.status = $("#filterStatus").val();
                d.start_date = $("#filterStartDate").val();
                d.end_date = $("#filterEndDate").val();
                d.exporter_id = $("#filterExporter").val();
                d.importer_id = $("#filterImporter").val();
                if (isInternal) {
                    d.username = $("#filterUsername").val();
                    d.public_user_uuid = $("#filterPublicUser").val();
                }
            }
        },
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

    // Filter button
    $("#btnFilter").on("click", function () {
        inspectionListTable.ajax.reload();
    });

    // Reset button
    $("#btnResetFilter").on("click", function () {
        $("#filterStatus").val("");
        $("#filterStartDate").val("");
        $("#filterEndDate").val("");

        // Destroy Select2 instances before resetting
        if ($('#filterExporter').hasClass('select2-hidden-accessible')) {
            $('#filterExporter').select2('destroy');
        }
        if ($('#filterImporter').hasClass('select2-hidden-accessible')) {
            $('#filterImporter').select2('destroy');
        }
        if (isInternal && $('#filterPublicUser').hasClass('select2-hidden-accessible')) {
            $('#filterPublicUser').select2('destroy');
        }

        $("#filterExporter").html('<option value="">All Exporters</option>');
        $("#filterImporter").html('<option value="">All Importers</option>');

        if (isInternal) {
            $("#filterPublicUser").val("");
            $("#filterUsername").val("");
            // Reinitialize public user dropdown
            $('#filterPublicUser').select2({
                placeholder: 'Select a user',
                allowClear: true,
                width: '100%'
            }).trigger('change');
        } else {
            loadFilterData();
        }

        inspectionListTable.ajax.reload();
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

// ⬇️ Export inspection list modal logic
$(document).on("click", "#btnOpenExportModal", function (e) {
    e.preventDefault();
    const modal = new bootstrap.Modal("#inspectionExportModal");
    modal.show();
});

$(document).on("click", "#btnConfirmExportExcel", function (e) {
    e.preventDefault();
    exportInspections("excel");
});

$(document).on("click", "#btnConfirmExportPdf", function (e) {
    e.preventDefault();
    exportInspections("pdf");
});

function exportInspections(type) {
    const params = new URLSearchParams();
    params.append("status", $("#filterStatus").val() || "");
    params.append("start_date", $("#filterStartDate").val() || "");
    params.append("end_date", $("#filterEndDate").val() || "");
    params.append("exporter_id", $("#filterExporter").val() || "");
    params.append("importer_id", $("#filterImporter").val() || "");

    const isInternal = typeof $("#filterPublicUser").val() !== "undefined";
    if (isInternal) {
        params.append("username", $("#filterUsername").val() || "");
        params.append("public_user_uuid", $("#filterPublicUser").val() || "");
    }

    const url = type === "excel" ? "/inspection/export-excel" : "/inspection/export-pdf";
    window.location.href = `${url}?${params.toString()}`;

    // Close modal properly
    const modalEl = document.getElementById("inspectionExportModal");
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    }
}

document.addEventListener("DOMContentLoaded", data_table_init);

// Load filter data based on user type
async function loadFilterData() {
    if (isInternal) {
        try {
            const response = await fetch('/internal/api/filters/public-users');
            const users = await response.json();
            const $select = $('#filterPublicUser');
            $select.html('<option value="">All Users</option>');
            users.forEach(user => {
                $select.append(`<option value="${user.uuid}">${user.fullname} (${user.email})</option>`);
            });
            // Initialize Select2 for searchable dropdown
            $select.select2({
                placeholder: 'Select a user',
                allowClear: true,
                width: '100%'
            });

            // Use namespaced event to avoid removing Select2's internal handlers
            $select.off('change.customFilter').on('change.customFilter', async function () {
                const selectedUser = $(this).val();

                // Destroy existing Select2 instances before repopulating
                if ($('#filterExporter').hasClass('select2-hidden-accessible')) {
                    $('#filterExporter').select2('destroy');
                }
                if ($('#filterImporter').hasClass('select2-hidden-accessible')) {
                    $('#filterImporter').select2('destroy');
                }

                $('#filterExporter').html('<option value="">All Exporters</option>');
                $('#filterImporter').html('<option value="">All Importers</option>');

                if (selectedUser) {
                    const exportersResp = await fetch(`/internal/api/filters/user/${selectedUser}/exporters`);
                    const exporters = await exportersResp.json();
                    exporters.forEach(exp => $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`));
                    $('#filterExporter').select2({
                        placeholder: 'Select exporter',
                        allowClear: true,
                        width: '100%'
                    });
                    const importersResp = await fetch(`/internal/api/filters/user/${selectedUser}/importers`);
                    const importers = await importersResp.json();
                    importers.forEach(imp => $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`));
                    $('#filterImporter').select2({
                        placeholder: 'Select importer',
                        allowClear: true,
                        width: '100%'
                    });
                } else {
                    $('#filterExporter').select2({
                        placeholder: 'Select exporter',
                        allowClear: true,
                        width: '100%'
                    });
                    $('#filterImporter').select2({
                        placeholder: 'Select importer',
                        allowClear: true,
                        width: '100%'
                    });
                }
            });
        } catch (error) {
            console.error('Error loading public users:', error);
        }
    } else {
        try {
            $('#filterExporter').html('<option value="">All Exporters</option>');
            const exportersResp = await fetch('/public/api/filters/my-exporters');
            const exporters = await exportersResp.json();
            exporters.forEach(exp => $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`));
            $('#filterExporter').select2({
                placeholder: 'Select exporter',
                allowClear: true,
                width: '100%'
            });
            $('#filterImporter').html('<option value="">All Importers</option>');
            const importersResp = await fetch('/public/api/filters/my-importers');
            const importers = await importersResp.json();
            importers.forEach(imp => $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`));
            $('#filterImporter').select2({
                placeholder: 'Select importer',
                allowClear: true,
                width: '100%'
            });
        } catch (error) {
            console.error('Error loading filter data:', error);
        }
    }
}
