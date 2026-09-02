import { formatTime, initTooltips, applyTranslations } from "../../app";
import Swal from "sweetalert2";
import { activityLogDesign } from "../../appLog";

import { setupSelect2, autoInitFilterSelect2 } from "../../utils/select2Utils";

console.log("consignment list");
let consignmentListTable;

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
    consignmentAppLog: { en: 'Consignment Certificate Application Log', bm: 'Log Permohonan Sijil Konsainan' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    cannotRevert: { en: "You won't be able to revert this!", bm: 'Anda tidak akan dapat mengembalikan ini!' },
    yesDelete: { en: 'Yes, delete it!', bm: 'Ya, padamkan!' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    deleteFailed: { en: 'Failed to delete application.', bm: 'Gagal memadam permohonan.' },
    error: { en: 'Error!', bm: 'Ralat!' },
    somethingWentWrong: { en: 'Something went wrong.', bm: 'Sesuatu yang tidak kena berlaku.' },
    deleting: { en: 'Deleting...', bm: 'Memadam...' },
    loading: { en: 'Loading...', bm: 'Memuat...' }
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

    // Load filter data on page load (internal only)
    await loadFilterData();

    // Init Select2 on all static filter selects (those with class 'select2')
    autoInitFilterSelect2();

    consignmentListTable = new DataTable("#consignmentListTable", {
        processing: true,
        serverSide: true,
        ajax: {
            url: "/consignment/list/data",
            data: function (d) {
                d.status = [].concat($("#filterStatus").val() || []).join(',');
                d.start_date = $("#filterStartDate").val();
                d.end_date = $("#filterEndDate").val();
                d.exporter_id = [].concat($("#filterExporter").val() || []).join(',');
                d.importer_id = [].concat($("#filterImporter").val() || []).join(',');
                d.username = $("#filterUsername").val();
                d.public_user_uuid = [].concat($("#filterPublicUser").val() || []).join(',');
            }
        },
        columns: [
            {
                data: "DT_RowIndex",
                title: "#",
                orderable: false,
                searchable: false,
            },
            { data: "application_id", name: "application_id", title: "Application ID" },
            { data: "importer", title: "Importer" },
            { data: "exporter", title: "Exporter" },
            { data: "status", title: "Application Status" },
            { data: "permit_status", title: "Permit Status" },

            // 🔐 Only internal users see this
            ...(isInternal ? [{ data: "submitted_by", title: "Submitted By" }] : []),

            { data: "action", title: "Action" },
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

        $('#filterExporter').empty();
        $('#filterImporter').empty();

        if (isInternal) {
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
        } else {
            loadFilterData();
        }

        consignmentListTable.ajax.reload();
    });

    initTooltips();
    handleDelete();
}

function handleDelete() {
    $(document).on("click", ".delete-consignment", function (e) {
        e.preventDefault();
        const id = $(this).data("id");
        
        // Determine correct route based on current URL
        let deleteUrl = `/public/consignment_application/delete/${id}`;
        if (window.location.pathname.includes('/internal/')) {
            deleteUrl = `/internal/consignment/delete/${id}`;
        }

        Swal.fire({
            title: getText("areYouSure"),
            text: getText("cannotRevert"),
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: getText("yesDelete"),
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: getText("deleting"),
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        applyTranslations(Swal.getHtmlContainer());
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
                            getText("deleted"),
                            response.message || "Your application has been deleted.",
                            "success"
                        );
                        consignmentListTable.ajax.reload(); // Reload table
                    },
                    error: function (xhr) {
                        Swal.fire(
                            getText("error"),
                            xhr.responseJSON?.message || getText("somethingWentWrong"),
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
    if (isInternal) {
        try {
            // Load all exporters and importers initially (for consignment)
            await loadAllConsignmentFilters();

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
                setupSelect2('#filterExporter', getText('selectExporter'));
                setupSelect2('#filterImporter', getText('selectImporter'));
            });
        } catch (error) {
            console.error('Error loading public users:', error);
        }
    } else {
        try {
            $('#filterExporter').empty();
            const exportersResp = await fetch('/public/api/filters/my-consignment-exporters');
            const exporters = await exportersResp.json();
            exporters.forEach(exp => $('#filterExporter').append(`<option value="${exp.id}">${exp.name}</option>`));
            setupSelect2('#filterExporter', getText('selectExporter'));
            $('#filterImporter').empty();
            const importersResp = await fetch('/public/api/filters/my-consignment-importers');
            const importers = await importersResp.json();
            importers.forEach(imp => $('#filterImporter').append(`<option value="${imp.id}">${imp.name}</option>`));
            setupSelect2('#filterImporter', getText('selectImporter'));
        } catch (error) {
            console.error('Error loading filter data:', error);
        }
    }
}

// Load all consignment exporters and importers
async function loadAllConsignmentFilters() {
    try {
        // Load all exporters (PublicUsers)
        const exportersResp = await fetch('/internal/api/filters/consignment/exporters');
        const exporters = await exportersResp.json();
        const $exporterSelect = $('#filterExporter');
        $exporterSelect.empty();
        exporters.forEach(exp => {
            $exporterSelect.append(`<option value="${exp.id}">${exp.name}</option>`);
        });

        // Load all importers (ConsignmentImporter)
        const importersResp = await fetch('/internal/api/filters/consignment/importers');
        const importers = await importersResp.json();
        const $importerSelect = $('#filterImporter');
        $importerSelect.empty();
        importers.forEach(imp => {
            $importerSelect.append(`<option value="${imp.id}">${imp.name}</option>`);
        });

        // Initialize Select2 on both dropdowns
        setupSelect2('#filterExporter', getText('selectExporter'));
        setupSelect2('#filterImporter', getText('selectImporter'));
    } catch (error) {
        console.error('Error loading consignment filters:', error);
    }
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
            const titleEl = modalEl.querySelector(".modal-title");
            titleEl.textContent = getText('consignmentAppLog');
            titleEl.setAttribute('data-en', t.consignmentAppLog.en);
            titleEl.setAttribute('data-bm', t.consignmentAppLog.bm);

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

    const url = type === "excel" ? "/consignment/export-excel" : "/consignment/export-pdf";
    window.location.href = `${url}?${params.toString()}`;

    // Close modal properly
    const modalEl = document.getElementById("consignmentExportModal");
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    }
}

function generatePDF() {
    $(document).on('click', '.downloadApplication'  ,function (e) {
        e.preventDefault();

        const applicationId = $(this).data('id');

        if (!applicationId) {
            console.warn('No application id found on #printApplication (expected data-application attribute).');
            return;
        }

        window.open(`/consignment/application/${applicationId}/print`, '_blank');
    });
}

generatePDF();
