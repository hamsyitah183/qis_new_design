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
        ajax: "/internal/boundary/list/data",
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

  

  

    initTooltips();
}

function resetBoundaryModal() {
    $('#trnptType')
        .prop('disabled', false)
        .val('');

    $('#entryPoint')
        .prop('disabled', false)
        .html('<option value="">-- None --</option>');

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
            didOpen: () => Swal.showLoading()
        });


        const userId = $(this).data('id');
        const userName = $(this).data('name');

        const modalEl = document.getElementById("boundaryModal");

        // Basic fields
        $('#boundaryModal input[name="id"]').val(userId).prop('readonly', true);
        $('#boundaryModal input[name="name"]').val(userName).prop('readonly', true);

        $('#saveBtn').hide();

        resetBoundaryModal();

        try {
            const response = await $.ajax({
                url: `/internal/boundary/${userId}`,
                type: "GET",
                dataType: "json",
            });

            console.log('data', response);
       

            // ✅ FIX: correct data path
            const entryPoint = response.data.entry_point;

            if (!entryPoint) {
                $('#trnptType').val('');
                $('#entryPoint').html('<option value="">-- None --</option>');
            } else {
                // 1️⃣ select transport
                $('#trnptType').val(entryPoint.transport_type);

                // 2️⃣ load entry points
                await loadEntryPoints(entryPoint.transport_type);

                // 3️⃣ select entry point
                $('#entryPoint').val(entryPoint.id);
            }

            setViewMode();

            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            Swal.close()

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
            didOpen: () => Swal.showLoading()
        });

        const userId = $(this).data('id');
        const userName = $(this).data('name');

        const modalEl = document.getElementById("boundaryModal");

        // Basic fields
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

            console.log('data', response);

          

            // ✅ correct API path
            const entryPoint = response.data.entry_point;

            if (!entryPoint) {
                $('#trnptType').val('');
                $('#entryPoint').html('<option value="">-- None --</option>');
            } else {
                // 1️⃣ auto-pick transport
                $('#trnptType').val(entryPoint.transport_type);

                // 2️⃣ load ALL entry points first
                await loadEntryPoints(entryPoint.transport_type);

                // 3️⃣ auto-pick entry point
                $('#entryPoint').val(entryPoint.id);
            }

            setEditMode();

            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            Swal.close()

        } catch (error) {
            console.error("AJAX Error:", error);
            Swal.fire("Error", "Failed to load boundary data", "error");
        }
    });
}


function saveData() {
    $('#saveBtn').on('click', function (e) {
        e.preventDefault();

        let id = $(this).data("id");

        let transport = $('#trnptType').val();
        let entryPoint = $('#entryPoint').val();

        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: `/internal/boundary/${id}/save`,
            type: 'POST',
            data: {
                _token: $("meta[name='csrf-token']").attr("content"),
                transport: transport,
                entryPoint: entryPoint,
            },
            success: function (data) {
                Swal.fire({
                    icon: "success",
                    title: "Boundary Officer Information Saved!",
                });

                // ✅ CLOSE MODAL (existing instance)
                const modalEl = document.getElementById('boundaryModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                modalInstance.hide();

                // ✅ RELOAD TABLE WITHOUT RESET PAGINATION
                boundaryTable.ajax.reload(null, false);
            },
            error: (xhr) => {
                Swal.fire({
                    icon: "error",
                    title: "Failed!",
                    text: "Failed to save boundary officer."
                });
            }
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
        const url = `${route}?type=${encodeURIComponent(transportType)}`;

        $.ajax({
            url,
            type: "GET",
            dataType: "json",
            success: function (data) {
                let options = '<option value="">-- None --</option>';
                data.forEach(item => {
                    options += `<option value="${item.id}">
                        ${item.entry_display}
                    </option>`;
                });

                $('#entryPoint').html(options);
                resolve();
            },
            error: reject
        });
    });
}

function permitDetails() {
    $('#trnptType').on('change', function () {
        loadEntryPoints(this.value);
    });
}



document.addEventListener("DOMContentLoaded", data_table_init);
viewData()
editData()
saveData()
permitDetails()