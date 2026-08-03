import { formatTime, initTooltips, applyTranslations } from "../../app";
import Swal from "sweetalert2";
import { activityLogDesign } from "../../appLog";

// Import Select2 module
import select2 from "select2";
select2(window.jQuery);
import "select2/dist/css/select2.min.css";

console.log("application list");

// ---------------------------------------------------------------
// Helper: get current language
// ---------------------------------------------------------------
function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

// ---------------------------------------------------------------
// Translation map – all user‑facing strings used in JS
// ---------------------------------------------------------------
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
    importPermitAppLog: { en: 'Import Permit Application Log', bm: 'Log Permohonan Permit Import' },
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
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}

// ---------------------------------------------------------------
// DataTable language strings (bilingual)
// ---------------------------------------------------------------
function getDataTableLanguage() {
    const lang = getLang();
    if (lang === 'bm') {
        return {
            "sEmptyTable": "Tiada data tersedia dalam jadual",
            "sInfo": "Menunjukkan _START_ hingga _END_ daripada _TOTAL_ entri",
            "sInfoEmpty": "Menunjukkan 0 hingga 0 daripada 0 entri",
            "sInfoFiltered": "(ditapis daripada _MAX_ jumlah entri)",
            "sInfoPostFix": "",
            "sInfoThousands": ",",
            "sLengthMenu": "Papar _MENU_ entri",
            "sLoadingRecords": "Memuatkan...",
            "sProcessing": "Sedang diproses...",
            "sSearch": "Cari:",
            "sZeroRecords": "Tiada rekod yang sepadan",
            
            "oAria": {
                "sSortAscending": ": aktifkan untuk menyusun lajur menaik",
                "sSortDescending": ": aktifkan untuk menyusun lajur menurun"
            },
            "select": {
                "rows": {
                    "_": "%d baris dipilih",
                    "0": "",
                    "1": "1 baris dipilih"
                }
            },
            "buttons": {
                "copy": "Salin",
                "copyTitle": "Salin ke papan klip",
                "copyKeys": "Tekan Ctrl atau Cmd + C untuk menyalin data jadual ke papan klip sistem. <br /><br />Untuk membatalkan, klik mesej ini atau tekan Esc.",
                "copySuccess": {
                    "_": "%d baris disalin",
                    "1": "1 baris disalin"
                },
                "print": "Cetak"
            }
        };
    }
    // Default English
    return {
        "sEmptyTable": "No data available in table",
        "sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
        "sInfoEmpty": "Showing 0 to 0 of 0 entries",
        "sInfoFiltered": "(filtered from _MAX_ total entries)",
        "sInfoPostFix": "",
        "sInfoThousands": ",",
        "sLengthMenu": "Show _MENU_ entries",
        "sLoadingRecords": "Loading...",
        "sProcessing": "Processing...",
        "sSearch": "Search:",
        "sZeroRecords": "No matching records found",
        "oPaginate": {
            "sFirst": "First",
            "sLast": "Last",
            "sNext": "Next",
            "sPrevious": "Previous"
        },
        "oAria": {
            "sSortAscending": ": activate to sort column ascending",
            "sSortDescending": ": activate to sort column descending"
        },
        "select": {
            "rows": {
                "_": "%d rows selected",
                "0": "",
                "1": "1 row selected"
            }
        },
        "buttons": {
            "copy": "Copy",
            "copyTitle": "Copy to clipboard",
            "copyKeys": "Press Ctrl or Cmd + C to copy table data to system clipboard. <br /><br />To cancel, click this message or press Esc.",
            "copySuccess": {
                "_": "%d rows copied",
                "1": "1 row copied"
            },
            "print": "Print"
        }
    };
}

// ---------------------------------------------------------------
// DataTable instances
// ---------------------------------------------------------------
let applicationListTable;
let reviewApplicationListTable;
let agentApplicationListTable;
let DataTable; // module-level variable to hold DataTable constructor

const isInternal = window.AUTH_TYPE === "internal";

// ---------------------------------------------------------------
// Create / refresh DataTables with current language
// ---------------------------------------------------------------
async function createDataTables() {
    // Destroy existing instances if any
    if (applicationListTable) {
        applicationListTable.destroy();
        applicationListTable = null;
    }
    if (reviewApplicationListTable) {
        reviewApplicationListTable.destroy();
        reviewApplicationListTable = null;
    }
    if (agentApplicationListTable) {
        agentApplicationListTable.destroy();
        agentApplicationListTable = null;
    }

    const lang = getDataTableLanguage();

    applicationListTable = new DataTable("#applicationListTable", {
        processing: true,
        serverSide: true,
        language: lang,
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
        applyTranslations(document.querySelector('#applicationListTable_wrapper'));
    });

    reviewApplicationListTable = new DataTable("#reviewApplicationListTable", {
        processing: true,
        serverSide: true,
        language: lang,
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
            { data: "application_type", name: "application_type" },
            { data: "status", name: "status" },
            { data: "submitted_by", name: "submitted_by" },
            { data: "action", name: "action" },
        ],
        columnDefs: [
            { width: "50px", targets: 0 },
            { width: "150px", targets: 1 },
            { width: "150px", targets: 2 },
            { width: "100px", targets: 3 },
            { width: "150px", targets: 4 },
            { width: "120px", targets: 5 },
        ],
        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    reviewApplicationListTable.on("draw.dt", function () {
        initTooltips();
        applyTranslations(document.querySelector('#reviewApplicationListTable_wrapper'));
    });

    agentApplicationListTable = new DataTable("#agentApplicationListTable", {
        processing: true,
        serverSide: true,
        language: lang,
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
            { data: "application_type", name: "application_type" },
            { data: "status", name: "status" },
            { data: "submitted_by", name: "submitted_by" },
            { data: "action", name: "action" },
        ],
        columnDefs: [
            { width: "50px", targets: 0 },
            { width: "150px", targets: 1 },
            { width: "150px", targets: 2 },
            { width: "100px", targets: 3 },
            { width: "150px", targets: 4 },
            { width: "120px", targets: 5 },
        ],
        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    agentApplicationListTable.on("draw.dt", function () {
        initTooltips();
        applyTranslations(document.querySelector('#agentApplicationListTable_wrapper'));
    });
}

// ---------------------------------------------------------------
// DataTable initialization and language change listener
// ---------------------------------------------------------------
async function data_table_init() {
    const [
        { default: dt },
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

    // Assign DataTable to the module-level variable
    DataTable = dt;

    await Promise.all([
        import("datatables.net-bs5/css/dataTables.bootstrap5.min.css"),
        import("datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css"),
    ]);

    // Load filter data on page load
    await loadFilterData();

    // Create tables
    await createDataTables();

    // ---------------------------------------------
    // Delete application handler
    // ---------------------------------------------
    $(document).on("click", ".deleteApplication", async function () {
        const applicationId = $(this).data("id");
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        const result = await Swal.fire({
            title: getText('areYouSure'),
            text: getText('cannotRevert'),
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#4c5359ff",
            cancelButtonColor: "#d33",
            confirmButtonText: getText('yesDelete'),
            cancelButtonText: getText('cancel'),
            didOpen: (modal) => applyTranslations(modal),
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
                Swal.fire({
                    icon: 'success',
                    title: getText('deleted'),
                    text: data.message,
                    didOpen: (modal) => applyTranslations(modal),
                });
                applicationListTable.ajax.reload(null, false);
                reviewApplicationListTable.ajax.reload(null, false);
            })
            .catch((err) => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: getText('error'),
                    text: getText('deleteFailed'),
                    didOpen: (modal) => applyTranslations(modal),
                });
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

        // Re-populate with empty options and re-init Select2 with bilingual placeholders
        $('#filterExporter').html(`<option value="">${getText('allExporters')}</option>`);
        $('#filterImporter').html(`<option value="">${getText('allImporters')}</option>`);
        $('#filterExporter').select2({
            placeholder: getText('selectExporter'),
            allowClear: true,
            width: '100%'
        });
        $('#filterImporter').select2({
            placeholder: getText('selectImporter'),
            allowClear: true,
            width: '100%'
        });

        if (isInternal) {
            $("#filterPublicUser").val("");
            $("#filterUsername").val("");
            $('#filterPublicUser').select2({
                placeholder: getText('selectUser'),
                allowClear: true,
                width: '100%'
            }).trigger('change');
        } else {
            loadFilterData();
        }

        applicationListTable.ajax.reload();
    });

    // Listen to language changes (via html lang attribute)
    const observer = new MutationObserver(() => {
        // Refresh DataTables with new language
        createDataTables();
        // Also re-apply translations to filters etc.
        applyTranslations(document.querySelector('.filter-dropdown'));
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

    initTooltips();
}

// ---------------------------------------------------------------
// Activity Log Modal
// ---------------------------------------------------------------
function activityLog() {
    $(document).on("click", ".activityLog", async function (e) {
        e.preventDefault();

        const id = $(this).data("log");
        Swal.fire({
            title: getText('loading'),
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
                applyTranslations(Swal.getHtmlContainer());
            },
        });
        try {
            const res = await fetch(`/application/${id}/data`);

            if (!res.ok) {
                throw new Error("Failed to fetch activity log");
            }

            const json = await res.json();

            Swal.close();

            const tableBody = $("#applicationLogTable tbody");
            tableBody.empty();

            let activity_log = json.activity_log
            let qr_scan_logs = json.qr_scan_logs || [];

            const modalEl = document.getElementById("activityLogModal");
            const titleEl = modalEl.querySelector(".modal-title");
            const titleText = getText('importPermitAppLog');
            titleEl.textContent = titleText;
            titleEl.setAttribute('data-en', t.importPermitAppLog.en);
            titleEl.setAttribute('data-bm', t.importPermitAppLog.bm);

            const cardBody = $('#activityLogModal .modal-body');
            cardBody.empty();
            cardBody.addClass('scroll-div');

            const html = activityLogDesign(activity_log, qr_scan_logs);
            cardBody.html(html);
            applyTranslations(cardBody[0]);

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

// ---------------------------------------------------------------
// Load filter data based on user type
// ---------------------------------------------------------------
async function loadFilterData() {
    if (isInternal) {
        try {
            const response = await fetch('/internal/api/filters/public-users');
            const users = await response.json();

            const $select = $('#filterPublicUser');
            $select.html(`<option value="">${getText('allUsers')}</option>`);
            users.forEach(user => {
                $select.append(`<option value="${user.uuid}">${user.fullname} (${user.email})</option>`);
            });

            $select.select2({
                placeholder: getText('selectUser'),
                allowClear: true,
                width: '100%'
            });

            $select.off('change.customFilter').on('change.customFilter', async function () {
                const selectedUser = $(this).val();

                if ($('#filterExporter').hasClass('select2-hidden-accessible')) {
                    $('#filterExporter').select2('destroy');
                }
                if ($('#filterImporter').hasClass('select2-hidden-accessible')) {
                    $('#filterImporter').select2('destroy');
                }

                $('#filterExporter').html(`<option value="">${getText('allExporters')}</option>`);
                $('#filterImporter').html(`<option value="">${getText('allImporters')}</option>`);

                if (selectedUser) {
                    const exportersResp = await fetch(`/internal/api/filters/user/${selectedUser}/exporters`);
                    const exporters = await exportersResp.json();
                    exporters.forEach(exp => {
                        $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`);
                    });
                    $('#filterExporter').select2({
                        placeholder: getText('selectExporter'),
                        allowClear: true,
                        width: '100%'
                    });

                    const importersResp = await fetch(`/internal/api/filters/user/${selectedUser}/importers`);
                    const importers = await importersResp.json();
                    importers.forEach(imp => {
                        $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`);
                    });
                    $('#filterImporter').select2({
                        placeholder: getText('selectImporter'),
                        allowClear: true,
                        width: '100%'
                    });
                } else {
                    $('#filterExporter').select2({
                        placeholder: getText('selectExporter'),
                        allowClear: true,
                        width: '100%'
                    });
                    $('#filterImporter').select2({
                        placeholder: getText('selectImporter'),
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
            $('#filterExporter').html(`<option value="">${getText('allExporters')}</option>`);
            const exportersResp = await fetch('/public/api/filters/my-exporters');
            const exporters = await exportersResp.json();
            exporters.forEach(exp => {
                $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`);
            });
            $('#filterExporter').select2({
                placeholder: getText('selectExporter'),
                allowClear: true,
                width: '100%'
            });

            $('#filterImporter').html(`<option value="">${getText('allImporters')}</option>`);
            const importersResp = await fetch('/public/api/filters/my-importers');
            const importers = await importersResp.json();
            importers.forEach(imp => {
                $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`);
            });
            $('#filterImporter').select2({
                placeholder: getText('selectImporter'),
                allowClear: true,
                width: '100%'
            });
        } catch (error) {
            console.error('Error loading filter data:', error);
        }
    }
}

// ---------------------------------------------------------------
// Export application list modal logic
// ---------------------------------------------------------------
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

    const modalEl = document.getElementById("applicationExportModal");
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    }
}