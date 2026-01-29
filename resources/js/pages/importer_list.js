import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";

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
        const phone_no = $("#addimpfonno").val().trim();
        const address1 = $("#addimpaddress1").val().trim();
        const address2 = $("#addimpaddress2").val().trim();
        const full_address = `${address1} ${address2}`;
        const country = $("#addimpcountry").val();
        const id = $('#id').val();

        if (!name || !phone_no || !country) {
            return Swal.fire("⚠️ Please fill in all required fields.");
        }

        Swal.fire({
            title: "Saving exporter...",
            text: "Please wait",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
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
                    title: "Importer Saved!",
                    text: "The Importer has been successfully saved.",
                    timer: 1800,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: "center",
                });

                // ✅ Refresh DataTable
                $("#importerTable").DataTable().ajax.reload(null, false);

                // Hide modal
                modal.hide(); // Bootstrap 5 instance hide

                // Reset form
                $("#addImporterForm")[0].reset();
            },

            error: (xhr) => {
                console.error(xhr.responseText);
                Swal.fire("❌ Failed to save importer. Please try again.");
            },
        });
    });
}

$(document).ready(function () {
    $("#importerTable").DataTable({
        processing: true,
        responsive: true,
        ajax: {
            url: "/public/get_importers",
            type: "GET",
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

    // Initialize modal logic
    initAddImporterModal();
});

$(document).on("click", ".deleteImporter", function () {
    const importerId = $(this).data("id");

    // First confirmation
    Swal.fire({
        title: "Are you sure?",
        text: "This importer will be permanently deleted.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, continue",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            // Second confirmation
            Swal.fire({
                title: "Confirm Deletion",
                text: "This action cannot be undone.",
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
                                title: "Deleted!",
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
                                title: "Failed",
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
        title: "Loading importer data...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    const modalEl = document.getElementById("addImporterModal");
    const modal = new bootstrap.Modal(modalEl);

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
            Swal.fire("Error", "Failed to load importer data.", "error");
        },
    });
});


