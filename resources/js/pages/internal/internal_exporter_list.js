import $ from "jquery";
window.$ = window.jQuery = $;
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import { autoInitFilterSelect2 } from "../../utils/select2Utils";


$(document).ready(function () {
    const table = $("#internalExporterTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "/internal/exporter_list/data",
            type: "GET",
            data: function (d) {
                d.name = $("#filterExporterName").val();
                d.country = $("#filterExporterCountry").val();
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

    // Init Select2 on all static filter selects (those with class 'select2')
    autoInitFilterSelect2();

    // Apply filter
    $("#btnExporterFilter").on("click", function () {
        table.ajax.reload();
    });

    // Reset filter
    $("#btnResetExporterFilter").on("click", function () {
        $("#filterExporterName").val("");
        $('#filterExporterCountry').val('').trigger('change.select2');
        table.ajax.reload();
    });
});
