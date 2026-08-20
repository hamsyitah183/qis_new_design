import jQuery from "jquery";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import "datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css";
import "quill/dist/quill.snow.css";
import Swal from "sweetalert2";

const $ = jQuery;
window.$ = window.jQuery = jQuery;

$(document).ready(function () {
    // Initialize Quill
    const quill = new Quill('#content-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'clean']
            ]
        }
    });

    const table = $('#announcementTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: `${window.baseUrl}/internal/announcements/data`,
        columns: [
            { data: 'title', name: 'title' },
            { data: 'released_by_name', name: 'released_by_name' },
            { data: 'valid_from', name: 'valid_from', render: data => data ? data : '-' },
            { data: 'valid_until', name: 'valid_until', render: data => data ? data : '-' },
            { 
                data: 'is_active', 
                name: 'is_active',
                render: function (data) {
                    return data 
                        ? `<span class="badge bg-success">Active</span>` 
                        : `<span class="badge bg-danger">Inactive</span>`;
                }
            },
            {
                data: 'id',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    const toggleClass = row.is_active ? 'btn-warning' : 'btn-info';
                    const toggleIcon = row.is_active ? 'ti-eye-off' : 'ti-eye';
                    const toggleTitle = row.is_active ? 'Deactivate' : 'Activate';

                    return `
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-info view-btn" data-id="${data}" title="View">
                                <i class="ti ti-file-description"></i>
                            </button>
                            <button class="btn btn-sm btn-primary edit-btn" data-id="${data}" title="Edit">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button class="btn btn-sm ${toggleClass} toggle-btn" data-id="${data}" title="${toggleTitle}">
                                <i class="ti ${toggleIcon}"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary share-email-btn" data-id="${data}" data-title="${row.title}" title="Share via Email">
                                <i class="ti ti-mail"></i>
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

    // Handle View Button
    $('#announcementTable').on('click', '.view-btn', function () {
        const id = $(this).data('id');
        
        // Fetch specific announcement data
        $.get(`${window.baseUrl}/internal/announcements/${id}`, function (data) {
            const currentLang = localStorage.getItem('language') || 'en';
            
            $('#view_title')
                .attr('data-en', data.title)
                .attr('data-bm', data.title_bm || data.title)
                .text(currentLang === 'bm' && data.title_bm ? data.title_bm : data.title);
            
            let dates_en = '';
            let dates_bm = '';
            if (data.valid_from && data.valid_until) {
                dates_en = `Valid: ${data.valid_from} to ${data.valid_until}`;
                dates_bm = `Sah: ${data.valid_from} hingga ${data.valid_until}`;
            } else if (data.valid_from) {
                dates_en = `Valid from: ${data.valid_from}`;
                dates_bm = `Sah dari: ${data.valid_from}`;
            } else if (data.valid_until) {
                dates_en = `Valid until: ${data.valid_until}`;
                dates_bm = `Sah sehingga: ${data.valid_until}`;
            } else {
                dates_en = 'No expiration date';
                dates_bm = 'Tiada tarikh luput';
            }
            
            $('#view_dates')
                .attr('data-en', dates_en)
                .attr('data-bm', dates_bm)
                .text(currentLang === 'bm' ? dates_bm : dates_en);
                
            $('#view_content')
                .attr('data-en', data.content)
                .attr('data-bm', data.content_bm || data.content)
                .html(currentLang === 'bm' && data.content_bm ? data.content_bm : data.content);
            
            if (data.attachments && data.attachments.length > 0) {
                $('#view_attachments_container').show();
                $('#view_attachments').empty();
                data.attachments.forEach(function(att) {
                    $('#view_attachments').append(`
                        <a href="javascript:void(0);" class="border rounded p-1 view-image-link text-center d-inline-block" data-src="/storage/${att.file_path}" data-name="${att.file_name}" style="width: 100%;">
                            <img src="/storage/${att.file_path}" style="max-height: 300px; max-width: 100%; object-fit: contain;" alt="${att.file_name}" title="${att.file_name}">
                        </a>
                    `);
                });
            } else {
                $('#view_attachments_container').hide();
                $('#view_attachments').empty();
            }
            
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('viewAnnouncementModal'));
            modal.show();
        }).fail(function () {
            Swal.fire('Error!', 'Failed to fetch data.', 'error');
        });
    });

    // Handle Image Click in View Modal
    $(document).on('click', '.view-image-link', function (e) {
        e.preventDefault();
        const src = $(this).data('src');
        const name = $(this).data('name');
        
        $('#modal_image_src').attr('src', src);
        
        // Hide the view announcement modal
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewAnnouncementModal'));
        if (viewModal) {
            viewModal.hide();
        }
        
        // Show the image modal
        const imageModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('imageViewModal'));
        imageModal.show();
    });
    
    // When image modal is closed, reopen the view announcement modal
    $('#imageViewModal').on('hidden.bs.modal', function () {
        const viewModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('viewAnnouncementModal'));
        viewModal.show();
    });

    // Handle Add Button
    $('#btnAddAnnouncement').on('click', function () {
        $('#announcementForm')[0].reset();
        $('#announcement_id').val('');
        quill.root.innerHTML = '';
        $('#is_active').prop('checked', true);
        $('#existing-attachments').empty();
        $('#new-attachments-preview').empty();
        $('#attachments').val('');
        
        $('#announcementModalLabel').text('Add Announcement');
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('announcementModal'));
        modal.show();
    });

    // Handle Edit Button
    $('#announcementTable').on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        
        // Fetch specific announcement data
        $.get(`${window.baseUrl}/internal/announcements/${id}`, function (data) {
            $('#announcement_id').val(data.id);
            $('#title').val(data.title);
            $('#valid_from').val(data.valid_from);
            $('#valid_until').val(data.valid_until);
            $('#is_active').prop('checked', data.is_active);
            quill.root.innerHTML = data.content;
            
            // Fetch and show attachments
            $('#existing-attachments').empty();
            $('#new-attachments-preview').empty();
            $('#attachments').val('');
            $.get(`${window.baseUrl}/internal/announcements/${id}/attachments`, function (attachments) {
                attachments.forEach(function(att) {
                    $('#existing-attachments').append(`
                        <div class="position-relative border rounded p-1 attachment-item" data-id="${att.id}">
                            <img src="/storage/${att.file_path}" style="max-height: 80px; max-width: 80px; object-fit: cover;" alt="${att.file_name}">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle rounded-circle py-0 px-1 btn-delete-attachment" data-id="${att.id}" style="font-size: 10px;">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    `);
                });
            });
            
            $('#announcementModalLabel').text('Edit Announcement');
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('announcementModal'));
            modal.show();
        }).fail(function() {
            Swal.fire('Error', 'Failed to fetch announcement details', 'error');
        });
    });

    // Handle New Attachment Previews
    $('#attachments').on('change', function() {
        $('#new-attachments-preview').empty();
        const files = this.files;
        if (files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#new-attachments-preview').append(`
                            <div class="position-relative border rounded p-1">
                                <img src="${e.target.result}" style="max-height: 80px; max-width: 80px; object-fit: cover;" alt="${file.name}" title="${file.name}">
                            </div>
                        `);
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // Handle Delete Attachment
    $('#existing-attachments').on('click', '.btn-delete-attachment', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const container = $(this).closest('.attachment-item');
        
        Swal.fire({
            title: 'Delete image?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${window.baseUrl}/internal/announcements/attachments/${id}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        container.remove();
                    }
                });
            }
        });
    });

    // Handle Save Form
    $('#btnSaveAnnouncement').on('click', function (e) {
        e.preventDefault();

        // Basic validation
        const title = $('#title').val();
        if (!title) {
            Swal.fire('Error', 'Title is required', 'error');
            return;
        }

        const content = quill.root.innerHTML;
        if (quill.getText().trim().length === 0) {
            Swal.fire('Error', 'Content is required', 'error');
            return;
        }
        
        const valid_from = $('#valid_from').val();
        const valid_until = $('#valid_until').val();
        
        if (valid_from && valid_until && valid_until < valid_from) {
            Swal.fire('Error', 'Valid Until date cannot be earlier than Valid From date', 'error');
            return;
        }

        const id = $('#announcement_id').val();
        const isEdit = id !== '';
        
        const formData = {
            title: title,
            content: content,
            valid_from: valid_from,
            valid_until: valid_until,
            is_active: $('#is_active').is(':checked')
        };

        const url = isEdit 
            ? `${window.baseUrl}/internal/announcements/${id}`
            : `${window.baseUrl}/internal/announcements`;
        
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
                const announcementId = response.id;
                
                // Upload files if any
                const fileInput = document.getElementById('attachments');
                if (fileInput.files.length > 0) {
                    const uploadData = new FormData();
                    for (let i = 0; i < fileInput.files.length; i++) {
                        uploadData.append('attachments[]', fileInput.files[i]);
                    }
                    
                    $.ajax({
                        url: `${window.baseUrl}/internal/announcements/${announcementId}/attachments`,
                        type: 'POST',
                        data: uploadData,
                        processData: false,
                        contentType: false,
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function() {
                            bootstrap.Modal.getInstance(document.getElementById('announcementModal')).hide();
                            Swal.fire('Success!', response.message, 'success');
                            table.ajax.reload();
                        }
                    });
                } else {
                    bootstrap.Modal.getInstance(document.getElementById('announcementModal')).hide();
                    Swal.fire('Success!', response.message, 'success');
                    table.ajax.reload();
                }
            },
            error: function (xhr) {
                let errorMsg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error!', errorMsg, 'error');
            }
        });
    });

    // Delete Action
    $('#announcementTable').on('click', '.delete-btn', function () {
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
                    url: `${window.baseUrl}/internal/announcements/${id}`,
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

    // Toggle Active Action
    $('#announcementTable').on('click', '.toggle-btn', function () {
        const id = $(this).data('id');
        
        $.ajax({
            url: `${window.baseUrl}/internal/announcements/${id}/toggle`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                const toastMessage = document.getElementById('toastMessage');
                if (toastMessage) {
                    toastMessage.textContent = response.message;
                    const toastElement = new bootstrap.Toast(document.getElementById('editToast'));
                    toastElement.show();
                } else {
                    Swal.fire('Success', response.message, 'success');
                }
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                Swal.fire('Error!', 'Something went wrong.', 'error');
            }
        });
    });

    // Share via Email Action
    let shareEmailAnnouncementId = null;

    $('#announcementTable').on('click', '.share-email-btn', function () {
        shareEmailAnnouncementId = $(this).data('id');
        const title = $(this).data('title');
        $('#share_email_title').text(title);
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('shareEmailModal'));
        modal.show();
    });

    $('#btnConfirmShareEmail').on('click', function () {
        if (!shareEmailAnnouncementId) return;

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Sending...');

        $.ajax({
            url: `${window.baseUrl}/internal/announcements/${shareEmailAnnouncementId}/share-email`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                bootstrap.Modal.getInstance(document.getElementById('shareEmailModal')).hide();
                Swal.fire('Sent!', response.message, 'success');
            },
            error: function () {
                Swal.fire('Error!', 'Failed to send emails. Please try again.', 'error');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i> Confirm Send');
                shareEmailAnnouncementId = null;
            }
        });
    });
});
