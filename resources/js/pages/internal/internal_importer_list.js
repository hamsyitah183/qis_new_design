import $ from "jquery";
window.$ = window.jQuery = $;
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";

$(document).ready(function () {
    const table = $("#internalImporterTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "/internal/importer_list/data",
            type: "GET",
            data: function (d) {
                d.name = $("#filterImporterName").val();
                d.country = $("#filterImporterCountry").val();
            },
        },
        columns: [
            { data: "DT_RowIndex", name: "DT_RowIndex", orderable: false, searchable: false },
            { data: "name", name: "name" },
            { data: "phone_no", name: "phone_no" },
            { data: "address", name: "address", defaultContent: "-" },
            { data: "country_name", name: "country_name", orderable: false, searchable: false },
            { data: "registered_by_name", name: "registered_by_name", orderable: false, searchable: false },
        ],
    });

    // Apply filter
    $("#btnImporterFilter").on("click", function () {
        table.ajax.reload();
    });

    // Reset filter
    $("#btnResetImporterFilter").on("click", function () {
        $("#filterImporterName").val("");
        $("#filterImporterCountry").val("");
        table.ajax.reload();
    });
});
