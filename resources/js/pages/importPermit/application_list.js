import { formatTime, initTooltips } from "../../app";
import Swal from "sweetalert2";
import { activityLogDesign } from "../../appLog";

// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

console.log("application list");
let applicationListTable;
let reviewApplicationListTable;
let agentApplicationListTable;

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

    applicationListTable = new DataTable("#applicationListTable", {
        processing: true,
        serverSide: true,
        ajax: {
            url: "/application/list/data",
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

    agentApplicationListTable = new DataTable("#agentApplicationListTable", {
        processing: true,
        serverSide: true,
        ajax: "/application/agent/list/data",
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

    agentApplicationListTable.on("draw.dt", function () {
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

    // Filter button
    $("#btnFilter").on("click", function () {
        applicationListTable.ajax.reload();
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

        $('#filterExporter').html('<option value="">All Exporters</option>');
        $('#filterImporter').html('<option value="">All Importers</option>');

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
            // Reload filter data for public users
            loadFilterData();
        }

        applicationListTable.ajax.reload();
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
            // QR scan entries are returned together with activity logs for timeline rendering.
            let qr_scan_logs = json.qr_scan_logs || [];



            const modalEl = document.getElementById("activityLogModal");
            modalEl.querySelector(".modal-title").textContent =
                "Import Permit Application Log" || "Activity Log";

            const cardBody = $('#activityLogModal .modal-body');
            cardBody.empty();
            cardBody.addClass('scroll-div');

            const html = activityLogDesign(activity_log, qr_scan_logs);
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

// Load filter data based on user type
async function loadFilterData() {
    if (isInternal) {
        // For internal users, load public users first
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

            // When a public user is selected, load their exporters/importers
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
                    // Load exporters for selected user
                    const exportersResp = await fetch(`/internal/api/filters/user/${selectedUser}/exporters`);
                    const exporters = await exportersResp.json();
                    exporters.forEach(exp => {
                        $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`);
                    });
                    // Initialize Select2 on exporter dropdown
                    $('#filterExporter').select2({
                        placeholder: 'Select exporter',
                        allowClear: true,
                        width: '100%'
                    });

                    // Load importers for selected user
                    const importersResp = await fetch(`/internal/api/filters/user/${selectedUser}/importers`);
                    const importers = await importersResp.json();
                    importers.forEach(imp => {
                        $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`);
                    });
                    // Initialize Select2 on importer dropdown
                    $('#filterImporter').select2({
                        placeholder: 'Select importer',
                        allowClear: true,
                        width: '100%'
                    });
                } else {
                    // Reinitialize empty dropdowns
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
        // For public users, load their exporters and importers directly
        try {
            // Load exporters
            $('#filterExporter').html('<option value="">All Exporters</option>');
            const exportersResp = await fetch('/public/api/filters/my-exporters');
            const exporters = await exportersResp.json();
            exporters.forEach(exp => {
                $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`);
            });
            // Initialize Select2 on exporter dropdown
            $('#filterExporter').select2({
                placeholder: 'Select exporter',
                allowClear: true,
                width: '100%'
            });

            // Load importers
            $('#filterImporter').html('<option value="">All Importers</option>');
            const importersResp = await fetch('/public/api/filters/my-importers');
            const importers = await importersResp.json();
            importers.forEach(imp => {
                $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`);
            });
            // Initialize Select2 on importer dropdown
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

// ⬇️ Export application list modal logic
$(document).on("click", "#btnOpenExportModal", function (e) {
    e.preventDefault();
    const modal = new bootstrap.Modal("#applicationExportModal");
    modal.show();
});

$(document).on("click", "#btnConfirmExportExcel", function (e) {
    e.preventDefault();
    exportApplications("excel");
});

$(document).on("click", "#btnConfirmExportPdf", function (e) {
    e.preventDefault();
    exportApplications("pdf");
});

function exportApplications(type) {
    const params = new URLSearchParams();
    params.append("status", $("#filterStatus").val() || "");
    params.append("start_date", $("#filterStartDate").val() || "");
    params.append("end_date", $("#filterEndDate").val() || "");
    params.append("exporter_id", $("#filterExporter").val() || "");
    params.append("importer_id", $("#filterImporter").val() || "");

    if (isInternal) {
        params.append("username", $("#filterUsername").val() || "");
        params.append("public_user_uuid", $("#filterPublicUser").val() || "");
    }

    const url = type === "excel" ? "/application/export-excel" : "/application/export-pdf";
    window.location.href = `${url}?${params.toString()}`;

    // Close modal properly
    const modalEl = document.getElementById("applicationExportModal");
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    }
}
