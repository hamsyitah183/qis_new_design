
import { formatTime, initTooltips } from "../../app";
import Swal from "sweetalert2";
import { activityLogDesign } from "../../appLog";

let boundaryTable = null;

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

    boundaryTable = new DataTable("#boundaryTable", {
        processing: true,
        serverSide: true,
        ajax: {
            url: "/internal/boundary/list/data",
            data: function (d) {
                d.name  = $("#filterBoundaryName").val()  || "";
                d.place = $("#filterBoundaryPlace").val() || "";
            },
        },
        columns: [
            { data: "name" },
            { data: "place" },
            { data: "action" },
        ],
        columnDefs: [
            { width: "150px", targets: 0 },
            { width: "150px", targets: 1 },
            { width: "100px", targets: 2 },
        ],
        autoWidth: false,
        responsive: true,
        pageLength: 10,
    });

    boundaryTable.on("draw.dt", function () {
        initTooltips();
    });

    $("#btnBoundaryFilter").on("click", function (e) {
        e.preventDefault();
        boundaryTable.ajax.reload();
    });

    $("#btnResetBoundaryFilter").on("click", function (e) {
        e.preventDefault();
        $("#filterBoundaryName").val("");
        $("#filterBoundaryPlace").val("");
        boundaryTable.ajax.reload();
    });

    initTooltips();
}

function resetBoundaryModal() {
    $('#trnptType').prop('disabled', false).val('');
    $('#entryPoint').prop('disabled', false).html('<option value="">-- None --</option>');
    $('#saveBtn').hide();
}

function setViewMode() {
    $('#trnptType').prop('disabled', true);
    $('#entryPoint').prop('disabled', true);
    $('#saveBtn').hide();
}

function setEditMode() {
    $('#trnptType').prop('disabled', false);
    $('#entryPoint').prop('disabled', false);
    $('#saveBtn').show();
}

function viewData() {
    $(document).on('click', '.viewBoundaryUser', async function (e) {
        e.preventDefault();

        Swal.fire({
            title: "Loading...",
            text: "Please wait",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        const userId   = $(this).data('id');
        const userName = $(this).data('name');
        const modalEl  = document.getElementById("boundaryModal");

        $('#boundaryModal input[name="id"]').val(userId).prop('readonly', true);
        $('#boundaryModal input[name="name"]').val(userName).prop('readonly', true);

        resetBoundaryModal();

        try {
            const response = await $.ajax({
                url: `/internal/boundary/${userId}`,
                type: "GET",
                dataType: "json",
            });

            const entryPoint = response.data.entry_point;

            if (!entryPoint) {
                $('#trnptType').val('');
                $('#entryPoint').html('<option value="">-- None --</option>');
            } else {
                $('#trnptType').val(entryPoint.transport_type);
                await loadEntryPoints(entryPoint.transport_type);
                $('#entryPoint').val(entryPoint.id);
            }

            setViewMode();
            new bootstrap.Modal(modalEl).show();
            Swal.close();

        } catch (error) {
            console.error("AJAX Error:", error);
            Swal.fire("Error", "Failed to load boundary data", "error");
        }
    });
}

function editData() {
    $(document).on('click', '.editBoundaryUser', async function (e) {
        e.preventDefault();

        Swal.fire({
            title: "Loading...",
            text: "Please wait",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        const userId   = $(this).data('id');
        const userName = $(this).data('name');
        const modalEl  = document.getElementById("boundaryModal");

        $('#boundaryModal input[name="id"]').val(userId).prop('readonly', true);
        $('#boundaryModal input[name="name"]').val(userName).prop('readonly', true);
        $('#saveBtn').show().data('id', userId);

        resetBoundaryModal();

        try {
            const response = await $.ajax({
                url: `/internal/boundary/${userId}`,
                type: "GET",
                dataType: "json",
            });

            const entryPoint = response.data.entry_point;

            if (!entryPoint) {
                $('#trnptType').val('');
                $('#entryPoint').html('<option value="">-- None --</option>');
            } else {
                $('#trnptType').val(entryPoint.transport_type);
                await loadEntryPoints(entryPoint.transport_type);
                $('#entryPoint').val(entryPoint.id);
            }

            setEditMode();
            new bootstrap.Modal(modalEl).show();
            Swal.close();

        } catch (error) {
            console.error("AJAX Error:", error);
            Swal.fire("Error", "Failed to load boundary data", "error");
        }
    });
}

function saveData() {
    $('#saveBtn').on('click', function (e) {
        e.preventDefault();

        const id         = $(this).data("id");
        const transport  = $('#trnptType').val();
        const entryPoint = $('#entryPoint').val();

        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: `/internal/boundary/${id}/save`,
            type: 'POST',
            data: {
                _token:     $("meta[name='csrf-token']").attr("content"),
                transport:  transport,
                entryPoint: entryPoint,
            },
            success: function () {
                Swal.fire({ icon: "success", title: "Boundary Officer Information Saved!" });
                const modalEl       = document.getElementById('boundaryModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                modalInstance.hide();
                boundaryTable.ajax.reload(null, false);
            },
            error: function () {
                Swal.fire({ icon: "error", title: "Failed!", text: "Failed to save boundary officer." });
            },
        });
    });
}

function loadEntryPoints(transportType) {
    return new Promise((resolve, reject) => {
        if (!transportType) {
            $('#entryPoint').html('<option value="">-- None --</option>');
            return resolve();
        }

        const route = $('#trnptType').data('route');
        const url   = `${route}?type=${encodeURIComponent(transportType)}`;

        $.ajax({
            url,
            type: "GET",
            dataType: "json",
            success: function (data) {
                let options = '<option value="">-- None --</option>';
                data.forEach(item => {
                    options += `<option value="${item.id}">${item.entry_display}</option>`;
                });
                $('#entryPoint').html(options);
                resolve();
            },
            error: reject,
        });
    });
}

function permitDetails() {
    $('#trnptType').on('change', function () {
        loadEntryPoints(this.value);
    });
}

document.addEventListener("DOMContentLoaded", data_table_init);
viewData();
editData();
saveData();
permitDetails();
