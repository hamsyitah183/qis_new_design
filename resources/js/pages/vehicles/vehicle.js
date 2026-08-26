import jQuery from "jquery";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import "datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css";
import Swal from "sweetalert2";

const $ = jQuery;
window.$ = window.jQuery = jQuery;

$(document).ready(function () {
    // ─── DataTable ──────────────────────────────────────────────
    const table = $('#vehicleTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: `${window.baseUrl}/vehicles/data`,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'owner_name', name: 'owner_name' },
            { data: 'vehicle_name', name: 'vehicle_name', visible: false },
            { data: 'vehicle_number', name: 'vehicle_number' },
            { data: 'vehicle_type', name: 'vehicle_type', visible: false  },
            { data: 'vehicle_registration_number', name: 'vehicle_registration_number' , visible: false },
            { data: 'valid_from_formatted', name: 'valid_from' },
            { data: 'valid_until_formatted', name: 'valid_until' },
            {
                data: 'id',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-info view-btn" data-id="${data}" title="View">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-primary edit-btn" data-id="${data}" title="Edit">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${data}" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    // ─── Add Button ──────────────────────────────────────────────
    $('#btnAddVehicle').on('click', function () {
        $('#addVehicleForm')[0].reset();
        $('#vehicle_id').val('');
        $('#addVehicleModalLabel').text('Add Vehicle');
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addVehicleModal'));
        modal.show();
    });

    // ─── Edit Button ─────────────────────────────────────────────
    $('#vehicleTable').on('click', '.edit-btn', function () {
        const id = $(this).data('id');

        $.get(`${window.baseUrl}/vehicles/${id}`, function (data) {
            $('#vehicle_id').val(data.id);
            $('#vehicleName').val(data.vehicle_name);
            $('#vehicleNumber').val(data.vehicle_number);
            $('#vehicleType').val(data.vehicle_type);
            $('#vehicleRegNumber').val(data.vehicle_registration_number);
            $('#validFrom').val(data.valid_from);
            $('#validUntil').val(data.valid_until);

            $('#addVehicleModalLabel').text('Edit Vehicle');
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('addVehicleModal'));
            modal.show();
        }).fail(function() {
            Swal.fire('Error', 'Failed to fetch vehicle details', 'error');
        });
    });

    // ─── Save Form ───────────────────────────────────────────────
    $('#btnSaveVehicle').on('click', function (e) {
        e.preventDefault();

        const id = $('#vehicle_id').val();
        const isEdit = id !== '';

        // Basic validation
        const name = $('#vehicleName').val().trim();
        const number = $('#vehicleNumber').val().trim();
        const regNumber = $('#vehicleRegNumber').val().trim();

        if (!name || !number || !regNumber) {
            Swal.fire('Error', 'Please fill in all required fields (Name, Number, Registration Number).', 'error');
            return;
        }

        const formData = {

            vehicle_number: number,
          
            valid_from: $('#validFrom').val(),
            valid_until: $('#validUntil').val(),
        };

        const url = isEdit
            ? `${window.baseUrl}/vehicles/${id}`
            : `${window.baseUrl}/vehicles`;
        const method = isEdit ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                bootstrap.Modal.getInstance(document.getElementById('addVehicleModal')).hide();
                Swal.fire('Success!', response.message, 'success');
                table.ajax.reload();
            },
            error: function (xhr) {
                let errorMsg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                Swal.fire('Error!', errorMsg, 'error');
            }
        });
    });

    // ─── View Button ─────────────────────────────────────────────
    $('#vehicleTable').on('click', '.view-btn', function () {
        const id = $(this).data('id');

        $.get(`${window.baseUrl}/vehicles/${id}`, function (data) {
            $('#view_vehicle_name').text(data.vehicle_name);
            $('#view_vehicle_number').text(data.vehicle_number);
            $('#view_vehicle_type').text(data.vehicle_type || '—');
            $('#view_vehicle_reg_number').text(data.vehicle_registration_number);
            $('#view_valid_from').text(data.valid_from ? new Date(data.valid_from).toLocaleDateString('en-GB') : '—');
            $('#view_valid_until').text(data.valid_until ? new Date(data.valid_until).toLocaleDateString('en-GB') : '—');
            $('#view_owner').text(data.user ? data.user.fullname : '—');

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('viewVehicleModal'));
            modal.show();
        }).fail(function() {
            Swal.fire('Error', 'Failed to fetch vehicle details', 'error');
        });
    });

    // ─── Delete Button ───────────────────────────────────────────
    $('#vehicleTable').on('click', '.delete-btn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${window.baseUrl}/vehicles/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });
});