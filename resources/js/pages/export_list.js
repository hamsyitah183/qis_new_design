import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";

function initAddExporterModal() {
    const modalEl = document.getElementById("addExporterModal");
    const modal = new bootstrap.Modal(modalEl);

    $("#openExporterModalBtn").on("click", (e) => {
        e.preventDefault();
        modal.show();
    });

    $("#addExporterbtn").on("click", (e) => {
        e.preventDefault();

        const routeUrl = $(e.currentTarget).data("route");
        const name = $("#addexpName").val().trim();
        const phone_no = $("#addexpfonno").val().trim();
        const address1 = $("#addexpaddress1").val().trim();
        const address2 = $("#addexpaddress2").val().trim();
        const full_address = `${address1} ${address2}`;
        const country = $("#addexpcountry").val();
        const id = $('#id').val();

        if (!name || !phone_no || !country) {
            return Swal.fire("⚠️ Please fill in all required fields.");
        }

        $.ajax({
            url: routeUrl,
            type: "POST",
            data: { name, phone_no, address: full_address, country, id },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: () => {
                Swal.fire({
                    icon: "success",
                    title: "Exporter Saved!",
                    text: "The exporter has been successfully added to the list.",
                    timer: 1800,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: "center",
                });

                // ✅ Refresh DataTable
                $("#exporterTable").DataTable().ajax.reload(null, false);

                // Hide modal
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                modalInstance.hide();

                // Reset form
                $("#addExporterForm")[0].reset();
            },

            error: (xhr) => {
                console.error(xhr.responseText);
                Swal.fire("❌ Failed to save exporter. Please try again.");
            },
        });
    });
}

$(document).ready(function () {
    $("#exporterTable").DataTable({
        processing: true,
        responsive: true,
        ajax: {
            url: "/public/get_exporters",
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

    $(document).on("click", "#addExporter", function () {
        const modal = new bootstrap.Modal(
            document.getElementById("addExporterModal")
        );
        $("#addExporterForm")[0].reset();
        modal.show();
    });

    // Submit form
    initAddExporterModal();
});

$(document).on("click", ".deleteExporter", function () {
    const exporterId = $(this).data("id");

    // First confirmation
    Swal.fire({
        title: "Are you sure?",
        text: "This exporter will be permanently deleted.",
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
                        url: `/public/delete_exporter/${exporterId}`,
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
                            $("#exporterTable")
                                .DataTable()
                                .ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Failed",
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
        title: "Loading exporter data...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
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
            $('#id').val(exporter.id)

           
           
        },
        error: function (xhr, status, error) {
            Swal.close();
            console.error("AJAX Error:", error);
            console.log(xhr.responseText);
            Swal.fire("Error", "Failed to load exporter data.", "error");
        },
    });
});


