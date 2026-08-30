import jQuery from "jquery";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import "datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css";
import Swal from "sweetalert2";
import { applyTranslations } from "../../../app";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    error: { en: 'Error!', bm: 'Ralat!' },
    fetchFailed: { en: 'Failed to fetch data.', bm: 'Gagal mendapatkan data.' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    cannotRevert: { en: "You won't be able to revert this!", bm: 'Anda tidak akan dapat mengembalikannya!' },
    yesDeleteIt: { en: 'Yes, delete it!', bm: 'Ya, padamkannya!' },
    yesDelete: { en: 'Yes', bm: 'Ya' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    somethingWentWrong: { en: 'Something went wrong.', bm: 'Sesuatu yang tidak kena berlaku.' },
    success: { en: 'Success!', bm: 'Berjaya!' },
    successTitle: { en: 'Success', bm: 'Berjaya' },
    sent: { en: 'Sent!', bm: 'Dihantar!' },
    failedToSendEmail: { en: 'Failed to send emails. Please try again.', bm: 'Gagal menghantar e-mel. Sila cuba lagi.' },
    deleteFileTitle: { en: 'Delete file?', bm: 'Padam fail?' },
    contentRequired: { en: 'Content is required', bm: 'Kandungan diperlukan' },
    validDateError: { en: 'Valid Until date cannot be earlier than Valid From date', bm: 'Tarikh Sah Sehingga tidak boleh lebih awal daripada tarikh Sah Dari' },
    warning: { en: 'Warning!', bm: 'Amaran!' },
    filesFailedToUpload: { en: 'Announcement saved but files failed to upload.', bm: 'Pengumuman disimpan tetapi fail gagal dimuat naik.' },
    failedToFetchGallery: { en: 'Failed to fetch gallery details', bm: 'Gagal mendapatkan butiran galeri' },
    nameRequired: { en: 'Name is required', bm: 'Nama diperlukan' },
    orderDeletedSuccess: { en: 'Order deleted successfully', bm: 'Pesanan berjaya dipadam' },
    qrFailed: { en: 'QR Generation Failed', bm: 'Penjanaan QR Gagal' },
    qrUnable: { en: 'Unable to generate QR code for this permit number.', bm: 'Tidak dapat menjana kod QR untuk nombor permit ini.' },
    "Announcement created successfully.": { en: 'Announcement created successfully.', bm: 'Pengumuman berjaya dicipta.' },
    "Announcement updated successfully.": { en: 'Announcement updated successfully.', bm: 'Pengumuman berjaya dikemas kini.' },
    "Announcement deleted successfully.": { en: 'Announcement deleted successfully.', bm: 'Pengumuman berjaya dipadam.' },
    "Announcement pinned successfully.": { en: 'Announcement pinned successfully.', bm: 'Pengumuman berjaya disemat.' },
    "Announcement unpinned successfully.": { en: 'Announcement unpinned successfully.', bm: 'Semat pengumuman berjaya dibuang.' },
    "Gallery created successfully.": { en: 'Gallery created successfully.', bm: 'Galeri berjaya dicipta.' },
    "Gallery updated successfully.": { en: 'Gallery updated successfully.', bm: 'Galeri berjaya dikemas kini.' },
    "Gallery deleted successfully.": { en: 'Gallery deleted successfully.', bm: 'Galeri berjaya dipadam.' },
    "Order deleted successfully": { en: 'Order deleted successfully', bm: 'Pesanan berjaya dipadam' }
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}


const $ = jQuery;
window.$ = window.jQuery = jQuery;

$(document).ready(function () {
    // ─── DataTable ──────────────────────────────────────────────
    const table = $('#galleryTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: `${window.baseUrl}/internal/galleries/data`,
        drawCallback: () => applyTranslations(document.body),
        columns: [
            { data: 'thumbnail', name: 'thumbnail', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description' },
            { data: 'uploaded_by', name: 'uploaded_by' },
            { data: 'created_at', name: 'created_at' },
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
    $('#btnAddGallery').on('click', function () {
        $('#galleryForm')[0].reset();
        $('#gallery_id').val('');
        $('#description').val('');
        $('#existing_image_preview').empty().hide();
        $('#new_image_preview').empty().hide();
        $('#image').val('');

        $('#galleryModalLabel').text('Add Gallery Image');
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('galleryModal'));
        modal.show();
    });

    // ─── Edit Button ─────────────────────────────────────────────
    $('#galleryTable').on('click', '.edit-btn', function () {
        const id = $(this).data('id');

        $.get(`${window.baseUrl}/internal/galleries/${id}`, function (data) {
            $('#gallery_id').val(data.id);
            $('#name').val(data.name);
            $('#description').val(data.description);

            // Show existing image preview
            $('#existing_image_preview').empty().show();
            if (data.path) {
                $('#existing_image_preview').append(`
                    <div class="position-relative border rounded p-1 d-inline-block">
                        <img src="/storage/${data.path}" style="max-height: 150px; max-width: 150px; object-fit: cover;" alt="${data.name}">
                    </div>
                `);
            }

            $('#new_image_preview').empty().hide();
            $('#image').val('');

            $('#galleryModalLabel').text('Edit Gallery Image');
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('galleryModal'));
            modal.show();
        }).fail(function() {
            Swal.fire(getText("error"), getText("failedToFetchGallery"), 'error');
        });
    });

    // ─── File Preview ────────────────────────────────────────────
    $('#image').on('change', function() {
        $('#new_image_preview').empty().show();
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#new_image_preview').html(`
                    <div class="border rounded p-1 d-inline-block">
                        <img src="${e.target.result}" style="max-height: 150px; max-width: 150px; object-fit: cover;" alt="${file.name}" title="${file.name}">
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        } else {
            $('#new_image_preview').hide();
        }
    });

    // ─── Save Form ───────────────────────────────────────────────
    $('#btnSaveGallery').on('click', function (e) {
        e.preventDefault();

        const id = $('#gallery_id').val();
        const isEdit = id !== '';

        // Basic validation
        const name = $('#name').val().trim();
        if (!name) {
            Swal.fire(getText("error"), getText("nameRequired"), 'error');
            return;
        }

        const description = $('#description').val().trim();
        const fileInput = document.getElementById('image');
        const file = fileInput.files[0];

        // For edit, if no new file is selected and no existing image, allow? 
        // It's optional to update image; if no file and no existing, we can still save.
        // But we'll allow it.

        const formData = new FormData();
        formData.append('name', name);
        formData.append('description', description);
        if (file) {
            formData.append('image', file);
        }

        const url = isEdit
            ? `${window.baseUrl}/internal/galleries/${id}`
            : `${window.baseUrl}/internal/galleries`;
        const method = isEdit ? 'POST' : 'POST'; // Use POST with _method=PUT for edit
        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                bootstrap.Modal.getInstance(document.getElementById('galleryModal')).hide();
                Swal.fire(getText("success"), getText(response.message), 'success');
                table.ajax.reload();
            },
            error: function (xhr) {
                let errorMsg = getText("somethingWentWrong");
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire(getText("error"), errorMsg, 'error');
            }
        });
    });

    // ─── View Button ─────────────────────────────────────────────
    $('#galleryTable').on('click', '.view-btn', function () {
        const id = $(this).data('id');

        $.get(`${window.baseUrl}/internal/galleries/${id}`, function (data) {
            $('#view_name').text(data.name);
            $('#view_description').text(data.description || 'No description');
            if (data.path) {
                $('#view_image').attr('src', `/storage/${data.path}`).show();
            } else {
                $('#view_image').hide();
            }

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('viewGalleryModal'));
            modal.show();
        }).fail(function() {
            Swal.fire(getText("error"), getText("failedToFetchGallery"), 'error');
        });
    });

    // ─── Delete Button ───────────────────────────────────────────
    $('#galleryTable').on('click', '.delete-btn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: getText("areYouSure"),
            text: getText("cannotRevert"),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: getText("yesDeleteIt")
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${window.baseUrl}/internal/galleries/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire(getText("deleted"), getText(response.message), 'success');
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire(getText("error"), getText("somethingWentWrong"), 'error');
                    }
                });
            }
        });
    });
});