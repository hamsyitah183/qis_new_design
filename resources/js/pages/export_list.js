import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import { autoInitFilterSelect2 } from "../utils/select2Utils";
import { applyTranslations } from "../app";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    fillRequiredFields: { en: 'Please fill in all required fields.', bm: 'Sila isi semua medan yang diperlukan.' },
    savingExporter: { en: 'Saving exporter...', bm: 'Menyimpan pengeksport...' },
    pleaseWait: { en: 'Please wait', bm: 'Sila tunggu' },
    exporterSaved: { en: 'Exporter Saved!', bm: 'Pengeksport Disimpan!' },
    failedToSaveExporter: { en: 'Failed to save exporter. Please try again.', bm: 'Gagal menyimpan pengeksport. Sila cuba lagi.' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    exporterPermanentlyDeleted: { en: 'This exporter will be permanently deleted.', bm: 'Pengeksport ini akan dipadam secara kekal.' },
    confirmDeletion: { en: 'Confirm Deletion', bm: 'Sahkan Pemadaman' },
    actionCannotBeUndone: { en: 'This action cannot be undone.', bm: 'Tindakan ini tidak boleh dibatalkan.' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    failed: { en: 'Failed', bm: 'Gagal' },
    loadingExporterData: { en: 'Loading exporter data...', bm: 'Memuatkan data pengeksport...' },
    error: { en: 'Error', bm: 'Ralat' },
    failedToLoadExporterData: { en: 'Failed to load exporter data.', bm: 'Gagal memuatkan data pengeksport.' },
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}


function initAddExporterModal() {
    console.log('this is the exporter modal');

    const modalEl = document.getElementById("addExporterModal");
    const modal = new bootstrap.Modal(modalEl);

    $("#openExporterModalBtn").on("click", (e) => {
        e.preventDefault();
        modal.show();
    });

    $("#addExporterbtn").on("click", (e) => {
        e.preventDefault();

        const id = $("#id").val();
        const name = $("#addexpName").val().trim();
        const phone_no = $("#addexpfonno").val().trim();
        const address1 = $("#addexpaddress1").val().trim();
        // const address2 = $("#addexpaddress2").val().trim();
        const full_address = `${address1}`;
        const country = $("#addexpcountry").val();

        if (!name || !phone_no || !country) {
            return Swal.fire(getText("fillRequiredFields"));
        }

        // 🔄 Loading Swal
        Swal.fire({
            title: getText("savingExporter"),
            text: getText("pleaseWait"),
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
                applyTranslations(Swal.getHtmlContainer());
            }
        });

        $.ajax({
            url: '/public/store_exporter',
            method: "POST",
            data: {
                id,
                name,
                phone_no,
                address: full_address,
                country
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: () => {
                $("#exporterTable")
                    .DataTable()
                    .ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: getText("exporterSaved"),
                    text: "",
                    timer: 1800,
                    showConfirmButton: false,
                    timerProgressBar: true,
                });

                modal.hide();
                $("#addExporterForm")[0].reset();
                $("#id").val('');
            },
            error: (xhr) => {
                console.error(xhr.responseText);

                Swal.fire({
                    icon: "error",
                    title: getText("failed"),
                    text: getText("failedToSaveExporter")
                });
            }
        });
    });
}

$(document).ready(function () {
    // ─── Clear filters on page load ──────────────────────────────────
    $("#filterExporterName").val("");
    if ($("#filterExporterCountry").length) {
        $("#filterExporterCountry").val(null).trigger("change");
    }

    const table = $("#exporterTable").DataTable({
        processing: true,
        responsive: true,
        ajax: {
            url: "/public/get_exporters",
            type: "GET",
            data: function (d) {
                d.name = $("#filterExporterName").val() || "";
                d.country = [].concat($("#filterExporterCountry").val() || []).join(",");
            },
            dataSrc: "",
        },
        columns: [
            {
                data: null,
                render: (data, type, row, meta) => meta.row + 1,
            },
            { data: "name" },
            { data: "phone_no" },
            { data: "address" },
            { data: "country" },
            {
                data: "id",
                orderable: false,
                searchable: false,
                render: function (id) {
                    return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary editExporter" data-id="${id}">
                                <i class="ti ti-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger deleteExporter" data-id="${id}">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    `;
                },
            },
        ],
    });

    // Apply filters
    $("#btnExporterFilter").on("click", function (e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Reset filters
    $("#btnResetExporterFilter").on("click", function (e) {
        e.preventDefault();
        $("#filterExporterName").val("");
        $("#filterExporterCountry").val(null).trigger("change");
        table.ajax.reload();
    });

    // Initialize Select2 on filters
    autoInitFilterSelect2();

    $(document).on("click", "#addExporter", function () {
        const modal = new bootstrap.Modal(
            document.getElementById("addExporterModal")
        );
        $("#addExporterForm")[0].reset();
        $("#id").val("");
        $("#addExporterModalLabel").text("Add Exporter");
        $("#addExporterbtn").text("Save Exporter");
        modal.show();
    });

    // Submit form
    initAddExporterModal();
});

$(document).on("click", ".deleteExporter", function () {
    const exporterId = $(this).data("id");

    // First confirmation
    Swal.fire({
        title: getText("areYouSure"),
        text: getText("exporterPermanentlyDeleted"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, continue",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            // Second confirmation
            Swal.fire({
                title: getText("confirmDeletion"),
                text: getText("actionCannotBeUndone"),
                icon: "error",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it",
                cancelButtonText: "Cancel",
            }).then((finalResult) => {
                if (finalResult.isConfirmed) {
                    $.ajax({
                        url: `/public/delete_exporter/${exporterId}`,
                        type: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        },
                        success: function (res) {
                            Swal.fire({
                                icon: "success",
                                title: getText("deleted"),
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false,
                            });

                            // ✅ Refresh DataTable
                            $("#exporterTable")
                                .DataTable()
                                .ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: "error",
                                title: getText("failed"),
                                text:
                                    xhr.responseJSON?.message ||
                                    "Unable to delete exporter.",
                            });
                        },
                    });
                }
            });
        }
    });
});

$(document).on("click", ".editExporter", function () {
    const id = $(this).data("id");

    console.log("Editing exporter ID:", id);

    // Optional: show loading Swal
    Swal.fire({
        title: getText("loadingExporterData"),
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            applyTranslations(Swal.getHtmlContainer());
        },
    });

    const modal = new bootstrap.Modal(document.getElementById("addExporterModal"));
    modal.show();

    $.ajax({
        url: `/application/exporter/${id}`,
        type: "GET",
        dataType: "json",
        success: function (data) {
            Swal.close(); // close loading

            const exporter = data.exporter;

            console.log('exporter', exporter);

            // Populate modal fields
            $("#addexpName").val(exporter.name);
            $("#addexpfonno").val(exporter.phone_no);
            $("#addexpaddress1").val(exporter.address.split(" ")[0]); // adjust as needed
            $("#addexpaddress2").val(exporter.address.split(" ")[1]); // adjust as needed
            $("#addexpcountry").val(exporter.country);
            $('#id').val(exporter.id);

            $("#addExporterModalLabel").text("Edit Exporter");
            $("#addExporterbtn").text("Update Exporter");



        },
        error: function (xhr, status, error) {
            Swal.close();
            console.error("AJAX Error:", error);
            console.log(xhr.responseText);
            Swal.fire(getText("error"), getText("failedToLoadExporterData"), "error");
        },
    });
});