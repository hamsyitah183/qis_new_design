import { formatTime, initTooltips, applyTranslations } from "../../app";
import Swal from "sweetalert2";
import { activityLogDesign } from "../../appLog";

import { setupSelect2, autoInitFilterSelect2 } from "../../utils/select2Utils";

console.log("inspection list");
let inspectionListTable;

const isInternal = window.AUTH_TYPE === "internal";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    allStatuses: { en: 'All Statuses', bm: 'Semua Status' },
    draft: { en: 'Draft', bm: 'Draf' },
    clerkReview: { en: 'Clerk Review In-Progress', bm: 'Semakan Kerani Dalam Proses' },
    clerkVerified: { en: 'Clerk Verified', bm: 'Disahkan Kerani' },
    clerkRejected: { en: 'Clerk Rejected', bm: 'Ditolak Kerani' },
    officerCompleted: { en: 'Officer Verification Completed', bm: 'Pengesahan Pegawai Selesai' },
    notApproved: { en: 'Not Approved', bm: 'Tidak Diluluskan' },
    waitApproval: { en: 'Wait for Company Approval', bm: 'Menunggu Kelulusan Syarikat' },
    completed: { en: 'Completed', bm: 'Selesai' },
    allUsers: { en: 'All Users', bm: 'Semua Pengguna' },
    allExporters: { en: 'All Exporters', bm: 'Semua Pengeksport' },
    allImporters: { en: 'All Importers', bm: 'Semua Pengimport' },
    selectUser: { en: 'Select a user', bm: 'Pilih pengguna' },
    selectExporter: { en: 'Select exporter', bm: 'Pilih pengeksport' },
    selectImporter: { en: 'Select importer', bm: 'Pilih pengimport' },
    enterUsername: { en: 'Enter username', bm: 'Masukkan nama pengguna' },
    status: { en: 'Status', bm: 'Status' },
    publicUser: { en: 'Public User', bm: 'Pengguna Awam' },
    exporter: { en: 'Exporter', bm: 'Pengeksport' },
    importer: { en: 'Importer', bm: 'Pengimport' },
    submittedBy: { en: 'Submitted By', bm: 'Dihantar Oleh' },
    startDate: { en: 'Start Date', bm: 'Tarikh Mula' },
    endDate: { en: 'End Date', bm: 'Tarikh Tamat' },
    filter: { en: 'Filter', bm: 'Penapis' },
    reset: { en: 'Reset', bm: 'Set Semula' },
    apply: { en: 'Apply', bm: 'Guna' },
    downloadReport: { en: 'Download Report', bm: 'Muat Turun Laporan' },
    action: { en: 'Action', bm: 'Tindakan' },
    inspectionAppLog: { en: 'Inspection Certificate Application Log', bm: 'Log Permohonan Sijil Pemeriksaan' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    cannotRevert: { en: "You won't be able to revert this!", bm: 'Anda tidak akan dapat mengembalikan ini!' },
    yesDelete: { en: 'Yes, delete it!', bm: 'Ya, padamkan!' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    deleteFailed: { en: 'Failed to delete application.', bm: 'Gagal memadam permohonan.' },
    exportFormat: { en: 'Select the format for your exported report. The current filters will be applied.', bm: 'Pilih format untuk laporan yang dieksport. Penapis semasa akan digunakan.' },
    excelCsv: { en: 'Excel (CSV)', bm: 'Excel (CSV)' },
    pdfDoc: { en: 'PDF Document', bm: 'Dokumen PDF' },
    cancel: { en: 'Cancel', bm: 'Batal' },
    close: { en: 'Close', bm: 'Tutup' },
    loading: { en: 'Loading...', bm: 'Memuat...' },
    error: { en: 'Error!', bm: 'Ralat!' },
    updated: { en: 'Updated!', bm: 'Telah Dikemas kini!' },
    statusUpdateFailed: { en: 'Failed to update status.', bm: 'Gagal mengemas kini status.' },
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}
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

    // Init Select2 on all static filter selects (those with class 'select2')
    autoInitFilterSelect2();

    inspectionListTable = new DataTable("#inspectionListTable", {
        processing: true,
        serverSide: true,
        ajax: {
            url: "/inspection_certificates_list/data",
            data: function (d) {
                d.status = [].concat($("#filterStatus").val() || []).join(',');
                d.start_date = $("#filterStartDate").val();
                d.end_date = $("#filterEndDate").val();
                d.exporter_id = [].concat($("#filterExporter").val() || []).join(',');
                d.importer_id = [].concat($("#filterImporter").val() || []).join(',');
                if (isInternal) {
                    d.username = $("#filterUsername").val();
                    d.public_user_uuid = [].concat($("#filterPublicUser").val() || []).join(',');
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
                Swal.fire(getText("updated"), json.message, "success");
                inspectionListTable.ajax.reload(null, false);
            } catch (error) {
                console.error(error);
                Swal.fire(getText("error"), getText("statusUpdateFailed"), "error");
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
            title: getText("areYouSure"),
            text: getText("cannotRevert"),
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#4c5359ff",
            cancelButtonColor: "#d33",
            confirmButtonText: getText("yesDelete"),
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
            Swal.fire(getText("deleted"), json.message, "success");
            inspectionListTable.ajax.reload(null, false);
        } catch (error) {
            console.error(error);
            Swal.fire(getText("error"), error.message || getText("deleteFailed"), "error");
        }
    });

    // Filter button
    $("#btnFilter").on("click", function () {
        inspectionListTable.ajax.reload();
    });

    // Reset button
    $("#btnResetFilter").on("click", function () {
        $('#filterStatus').val(null).trigger('change');
        $("#filterStartDate").val("");
        $("#filterEndDate").val("");

        // Destroy Select2 instances before resetting dynamic dropdowns
        if ($('#filterExporter').hasClass('select2-hidden-accessible')) {
            $('#filterExporter').select2('destroy');
        }
        if ($('#filterImporter').hasClass('select2-hidden-accessible')) {
            $('#filterImporter').select2('destroy');
        }
        if (isInternal && $('#filterPublicUser').hasClass('select2-hidden-accessible')) {
            $('#filterPublicUser').select2('destroy');
        }

        $("#filterExporter").empty();
        $("#filterImporter").empty();

        if (isInternal) {
            $("#filterPublicUser").val("");
            $("#filterUsername").val("");
            // Reinitialize public user dropdown
            setupSelect2('#filterPublicUser', getText('selectUser'));
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
            title: getText("loading"),
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
                applyTranslations(Swal.getHtmlContainer());
            }
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
            const titleEl = modalEl.querySelector(".modal-title");
            titleEl.textContent = getText('inspectionAppLog');
            titleEl.setAttribute('data-en', t.inspectionAppLog.en);
            titleEl.setAttribute('data-bm', t.inspectionAppLog.bm);

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
    params.append("status", [].concat($("#filterStatus").val() || []).join(","));
    params.append("start_date", $("#filterStartDate").val() || "");
    params.append("end_date", $("#filterEndDate").val() || "");
    params.append("exporter_id", [].concat($("#filterExporter").val() || []).join(","));
    params.append("importer_id", [].concat($("#filterImporter").val() || []).join(","));

    const isInternal = typeof $("#filterPublicUser").val() !== "undefined";
    if (isInternal) {
        params.append("username", $("#filterUsername").val() || "");
        params.append("public_user_uuid", [].concat($("#filterPublicUser").val() || []).join(","));
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
            $select.empty();
            users.forEach(user => {
                $select.append(`<option value="${user.uuid}">${user.fullname} (${user.email})</option>`);
            });
            // Initialize Select2 for searchable dropdown
            setupSelect2($select, getText('selectUser'));

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

                $('#filterExporter').empty();
                $('#filterImporter').empty();

                if (selectedUser) {
                    const exportersResp = await fetch(`/internal/api/filters/user/${selectedUser}/exporters`);
                    const exporters = await exportersResp.json();
                    exporters.forEach(exp => $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`));
                    setupSelect2('#filterExporter', getText('selectExporter'));
                    
                    const importersResp = await fetch(`/internal/api/filters/user/${selectedUser}/importers`);
                    const importers = await importersResp.json();
                    importers.forEach(imp => $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`));
                    setupSelect2('#filterImporter', getText('selectImporter'));
                } else {
                    setupSelect2('#filterExporter', getText('selectExporter'));
                    setupSelect2('#filterImporter', getText('selectImporter'));
                }
            });
        } catch (error) {
            console.error('Error loading public users:', error);
        }
    } else {
        try {
            $('#filterExporter').empty();
            const exportersResp = await fetch('/public/api/filters/my-exporters');
            const exporters = await exportersResp.json();
            exporters.forEach(exp => $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`));
            setupSelect2('#filterExporter', getText('selectExporter'));

            $('#filterImporter').empty();
            const importersResp = await fetch('/public/api/filters/my-importers');
            const importers = await importersResp.json();
            importers.forEach(imp => $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`));
            setupSelect2('#filterImporter', getText('selectImporter'));
        } catch (error) {
            console.error('Error loading filter data:', error);
        }
    }
}
