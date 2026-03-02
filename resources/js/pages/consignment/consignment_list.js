import { formatTime, initTooltips } from "../../app";
import Swal from "sweetalert2";
import { activityLogDesign } from "../../appLog";

// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";

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

    // Load filter data on page load (internal only)
    await loadFilterData();

    consignmentListTable = new DataTable("#consignmentListTable", {
        processing: true,
        serverSide: true,
        ajax: {
            url: "/consignment/list/data",
            data: function (d) {
                d.status = $("#filterStatus").val();
                d.start_date = $("#filterStartDate").val();
                d.end_date = $("#filterEndDate").val();
                d.exporter_id = $("#filterExporter").val();
                d.importer_id = $("#filterImporter").val();
                d.username = $("#filterUsername").val();
                d.public_user_uuid = $("#filterPublicUser").val();
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

    consignmentListTable.on("draw.dt", function () {
        initTooltips();
    });

    // Filter button
    $("#btnFilter").on("click", function () {
        consignmentListTable.ajax.reload();
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
        if ($('#filterPublicUser').hasClass('select2-hidden-accessible')) {
            $('#filterPublicUser').select2('destroy');
        }

        $("#filterPublicUser").val("");
        $("#filterUsername").val("");

        // Reinitialize public user dropdown
        $('#filterPublicUser').select2({
            placeholder: 'Select a user',
            allowClear: true,
            width: '100%'
        }).trigger('change');

        // Reload all exporters and importers
        loadAllConsignmentFilters();

        consignmentListTable.ajax.reload();
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

// Load filter data for internal users
async function loadFilterData() {
    try {
        // Load all exporters and importers initially (for consignment)
        await loadAllConsignmentFilters();

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
                // Load consignment-specific exporters and importers for selected user
                const exportersResp = await fetch(`/internal/api/filters/user/${selectedUser}/consignment/exporters`);
                const exporters = await exportersResp.json();
                exporters.forEach(exp => $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`));
                
                const importersResp = await fetch(`/internal/api/filters/user/${selectedUser}/consignment/importers`);
                const importers = await importersResp.json();
                importers.forEach(imp => $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`));
            } else {
                // Load all exporters and importers when no user is selected
                await loadAllConsignmentFilters();
            }

            // Initialize Select2 on both dropdowns
            $('#filterExporter').select2({
                placeholder: 'Select exporter',
                allowClear: true,
                width: '100%'
            }).trigger('change');
            $('#filterImporter').select2({
                placeholder: 'Select importer',
                allowClear: true,
                width: '100%'
            }).trigger('change');
        });
    } catch (error) {
        console.error('Error loading public users:', error);
    }
}

// Load all consignment exporters and importers
async function loadAllConsignmentFilters() {
    try {
        // Load all exporters (PublicUsers)
        const exportersResp = await fetch('/internal/api/filters/consignment/exporters');
        const exporters = await exportersResp.json();
        const $exporterSelect = $('#filterExporter');
        $exporterSelect.html('<option value="">All Exporters</option>');
        exporters.forEach(exp => {
            $exporterSelect.append(`<option value="${exp.id}">${exp.name}</option>`);
        });

        // Load all importers (ConsignmentImporter)
        const importersResp = await fetch('/internal/api/filters/consignment/importers');
        const importers = await importersResp.json();
        const $importerSelect = $('#filterImporter');
        $importerSelect.html('<option value="">All Importers</option>');
        importers.forEach(imp => {
            $importerSelect.append(`<option value="${imp.id}">${imp.name}</option>`);
        });

        // Initialize Select2 on both dropdowns
        $exporterSelect.select2({
            placeholder: 'Select exporter',
            allowClear: true,
            width: '100%'
        });
        $importerSelect.select2({
            placeholder: 'Select importer',
            allowClear: true,
            width: '100%'
        });
    } catch (error) {
        console.error('Error loading consignment filters:', error);
    }
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
            const res = await fetch(`/consignment_application/${id}/data`);

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
                "Consignment Certificate Application Log" || "Activity Log";

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

initTooltips();
activityLog();

// ⬇️ Export consignment list modal logic
$(document).on("click", "#btnOpenExportModal", function (e) {
    e.preventDefault();
    const modal = new bootstrap.Modal("#consignmentExportModal");
    modal.show();
});

$(document).on("click", "#btnConfirmExportExcel", function (e) {
    e.preventDefault();
    exportConsignments("excel");
});

$(document).on("click", "#btnConfirmExportPdf", function (e) {
    e.preventDefault();
    exportConsignments("pdf");
});

function exportConsignments(type) {
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

    const url = type === "excel" ? "/consignment/export-excel" : "/consignment/export-pdf";
    window.location.href = `${url}?${params.toString()}`;

    // Close modal properly
    const modalEl = document.getElementById("consignmentExportModal");
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    }
}
