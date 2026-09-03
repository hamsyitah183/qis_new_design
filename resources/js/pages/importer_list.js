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
    savingImporter: { en: 'Saving importer...', bm: 'Menyimpan pengimport...' },
    pleaseWait: { en: 'Please wait', bm: 'Sila tunggu' },
    importerSaved: { en: 'Importer Saved!', bm: 'Pengimport Disimpan!' },
    failedToSaveImporter: { en: 'Failed to save importer. Please try again.', bm: 'Gagal menyimpan pengimport. Sila cuba lagi.' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    importerPermanentlyDeleted: { en: 'This importer will be permanently deleted.', bm: 'Pengimport ini akan dipadam secara kekal.' },
    confirmDeletion: { en: 'Confirm Deletion', bm: 'Sahkan Pemadaman' },
    actionCannotBeUndone: { en: 'This action cannot be undone.', bm: 'Tindakan ini tidak boleh dibatalkan.' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    failed: { en: 'Failed', bm: 'Gagal' },
    loadingImporterData: { en: 'Loading importer data...', bm: 'Memuatkan data pengimport...' },
    error: { en: 'Error', bm: 'Ralat' },
    failedToLoadImporterData: { en: 'Failed to load importer data.', bm: 'Gagal memuatkan data pengimport.' },
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}


function initAddImporterModal() {
    const modalEl = document.getElementById("addImporterModal");
    const modal = new bootstrap.Modal(modalEl);

    $("#addImporter").on("click", (e) => {
        e.preventDefault();
        $("#addImporterForm")[0].reset();
        $("#id").val("");
        $("#addImporterModalLabel").text("Add Importer");
        modal.show();
    });

    $("#addImporterBtn").on("click", (e) => {
        e.preventDefault();

        const routeUrl = $(e.currentTarget).data("route");
        const name = $("#addimpName").val().trim();
        // const phone_no = $("#addimpfonno").val();
        const phone_no = $("#addimpfonno").val().trim();
        const address1 = $("#addimpaddress1").val().trim();
        // const address2 = $("#addimpaddress2").val().trim();
        const full_address = `${address1}`;
        const country = $("#addimpcountry").val();
        const id = $('#id').val();

        console.log('Submitting importer data:', { name, phone_no, full_address, country, id });

        if (!name || !phone_no || !country) {
            return Swal.fire(getText("fillRequiredFields"));
        }

        Swal.fire({
            title: getText("savingImporter"),
            text: getText("pleaseWait"),
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
                applyTranslations(Swal.getHtmlContainer());
            }
        });

        $.ajax({
            url: '/public/store_consignment_importer',
            type: "POST",
            data: { name, phone_no, address: full_address, country, id },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: () => {
                Swal.fire({
                    icon: "success",
                    title: getText("importerSaved"),
                    text: "",
                    timer: 1800,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: "center",
                });

                // ✅ Refresh DataTable
                $("#importerTable").DataTable().ajax.reload(null, false);

                // Hide modal
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                } else {
                    modal.hide();
                }

                // Reset form
                $("#addImporterForm")[0].reset();
            },

            error: (xhr) => {
                console.error(xhr.responseText);
                Swal.fire(getText("failedToSaveImporter"));
            },
        });
    });
}

$(document).ready(function () {
    const table = $("#importerTable").DataTable({
        processing: true,
        responsive: true,
        ajax: {
            url: "/public/get_importers",
            type: "GET",
            data: function (d) {
                d.name = $("#filterImporterName").val() || "";
                d.country = [].concat($("#filterImporterCountry").val() || []).join(",");
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
            { data: "country_info.name", defaultContent: "-" },
            {
                data: "id",
                orderable: false,
                searchable: false,
                render: function (id) {
                    return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary editImporter" data-id="${id}">
                                <i class="ti ti-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger deleteImporter" data-id="${id}">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    `;
                },
            },
        ],
    });

    // Apply filters
    $("#btnImporterFilter").on("click", function (e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Reset filters
    $("#btnResetImporterFilter").on("click", function (e) {
        e.preventDefault();
        $("#filterImporterName").val("");
        $("#filterImporterCountry").val(null).trigger("change");
        table.ajax.reload();
    });

    // Initialize Select2 on filters
    autoInitFilterSelect2();

    // Initialize modal logic
    initAddImporterModal();

    $("#filterImporterName").val("");
    $("#filterImporterCountry").val(null).trigger("change");
    table.ajax.reload();
});

$(document).on("click", ".deleteImporter", function () {
    const importerId = $(this).data("id");

    // First confirmation
    Swal.fire({
        title: getText("areYouSure"),
        text: getText("importerPermanentlyDeleted"),
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
                        url: `/public/delete_importer/${importerId}`,
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
                            $("#importerTable")
                                .DataTable()
                                .ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: "error",
                                title: getText("failed"),
                                text:
                                    xhr.responseJSON?.message ||
                                    "Unable to delete importer.",
                            });
                        },
                    });
                }
            });
        }
    });
});

$(document).on("click", ".editImporter", function () {
    const id = $(this).data("id");

    console.log("Editing importer ID:", id);

    // Optional: show loading Swal
    Swal.fire({
        title: getText("loadingImporterData"),
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            applyTranslations(Swal.getHtmlContainer());
        },
    });

    const modalEl = document.getElementById("addImporterModal");
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    $("#addImporterModalLabel").text("Edit Importer");


    $.ajax({
        url: `/application/importer/${id}`,
        type: "GET",
        dataType: "json",
        success: function (data) {
            Swal.close(); // close loading
            modal.show();

            const importer = data.importer;

            console.log('data, importer', data, importer);

            // Populate modal fields
            $("#addimpName").val(importer.name);
            $("#addimpfonno").val(importer.phone_no);
            // Handle address split safely
            const addressParts = importer.address ? importer.address.split(" ") : ["", ""];
            $("#addimpaddress1").val(addressParts[0] || ""); 
            $("#addimpaddress2").val(addressParts.slice(1).join(" ") || ""); // Join rest of address
            $("#addimpcountry").val(importer.country);
            $('#id').val(importer.id);

        },
        error: function (xhr, status, error) {
            Swal.close();
            console.error("AJAX Error:", error);
            console.log(xhr.responseText);
            Swal.fire(getText("error"), getText("failedToLoadImporterData"), "error");
        },
    });
});


