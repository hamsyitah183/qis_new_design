import jQuery from "jquery";
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

    const announcementId = $('#announcement_id').val();
    const isEdit = announcementId !== '';

    // If Edit Mode, fetch and show attachments
    if (isEdit) {
        $.get(`${window.baseUrl}/internal/announcements/${announcementId}/attachments`, function (attachments) {
            attachments.forEach(function(att) {
                let previewContent = '';
                let fileType = att.file_type || '';
                let filePath = `/storage/${att.file_path}`;

                if (fileType.startsWith('image/')) {
                    previewContent = `
                        <div class="position-relative border rounded p-1 attachment-item me-2 mb-2 d-inline-block bg-white" data-id="${att.id}">
                            <img src="${filePath}" class="preview-file-click" data-type="image" data-src="${filePath}" style="height: 140px; width: 140px; object-fit: cover; cursor: pointer;" alt="${att.file_name}">
                            <button type="button" class="btn btn-icon btn-sm text-danger position-absolute top-0 end-0 m-1 bg-white shadow-sm btn-delete-attachment" data-id="${att.id}" style="border-radius: 50%; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; border: 1px solid #eee;">
                                <i class="ti ti-trash fs-16"></i>
                            </button>
                        </div>`;
                } else {
                    let iconClass = 'ti-file-description text-secondary';
                    let typeCategory = 'other';
                    if (fileType === 'application/pdf' || att.file_name.toLowerCase().endsWith('.pdf')) {
                        iconClass = 'ti-file-type-pdf text-danger';
                        typeCategory = 'pdf';
                    } else if (fileType.includes('word') || fileType.includes('document')) {
                        iconClass = 'ti-file-type-doc text-primary';
                    } else if (fileType.includes('excel') || fileType.includes('spreadsheet')) {
                        iconClass = 'ti-file-type-xls text-success';
                    }

                    previewContent = `
                        <div class="border rounded p-2 d-inline-flex align-items-center me-2 mb-2 attachment-item bg-white" data-id="${att.id}">
                            <div class="d-flex align-items-center preview-file-click" data-type="${typeCategory}" data-src="${filePath}" style="cursor: pointer;" title="${att.file_name}">
                                <i class="ti ${iconClass} fs-24"></i>
                                <span class="ms-2 text-dark text-truncate" style="max-width: 250px;">${att.file_name}</span>
                            </div>
                            <button type="button" class="btn btn-icon btn-sm text-danger ms-3 btn-delete-attachment" data-id="${att.id}" style="padding: 0; background: transparent; border: none; display: flex; align-items: center;">
                                <i class="ti ti-trash fs-20"></i>
                            </button>
                        </div>`;
                }

                $('#existing-attachments').append(previewContent);
            });
        });
    }

    // Handle New Attachment Previews
    $('#attachments').on('change', function() {
        $('#new-attachments-preview').empty();
        const files = this.files;
        if (files) {
            Array.from(files).forEach(file => {
                let previewContent = '';
                let fileType = file.type;

                if (fileType.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let previewContent = `
                            <div class="position-relative border rounded p-1 new-attachment-item me-2 mb-2 d-inline-block bg-white">
                                <img src="${e.target.result}" class="preview-file-click" data-type="image" data-src="${e.target.result}" style="height: 140px; width: 140px; object-fit: cover; cursor: pointer;" alt="${file.name}" title="${file.name}">
                                <button type="button" class="btn btn-icon btn-sm text-danger position-absolute top-0 end-0 m-1 bg-white shadow-sm remove-new-attachment" style="border-radius: 50%; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; border: 1px solid #eee;">
                                    <i class="ti ti-trash fs-16"></i>
                                </button>
                            </div>
                        `;
                        $('#new-attachments-preview').append(previewContent);
                    }
                    reader.readAsDataURL(file);
                } else {
                    let iconClass = 'ti-file-description text-secondary';
                    let typeCategory = 'other';
                    if (fileType === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
                        iconClass = 'ti-file-type-pdf text-danger';
                        typeCategory = 'pdf';
                    } else if (fileType.includes('word') || fileType.includes('document')) {
                        iconClass = 'ti-file-type-doc text-primary';
                    } else if (fileType.includes('excel') || fileType.includes('spreadsheet')) {
                        iconClass = 'ti-file-type-xls text-success';
                    }
                    
                    const objUrl = URL.createObjectURL(file);
                    
                    let previewContent = `
                        <div class="border rounded p-2 d-inline-flex align-items-center me-2 mb-2 new-attachment-item bg-white">
                            <div class="d-flex align-items-center preview-file-click" data-type="${typeCategory}" data-src="${objUrl}" style="cursor: pointer;" title="${file.name}">
                                <i class="ti ${iconClass} fs-24"></i>
                                <span class="ms-2 text-dark text-truncate" style="max-width: 250px;">${file.name}</span>
                            </div>
                            <button type="button" class="btn btn-icon btn-sm text-danger ms-3 remove-new-attachment" style="padding: 0; background: transparent; border: none; display: flex; align-items: center;">
                                <i class="ti ti-trash fs-20"></i>
                            </button>
                        </div>`;
                    $('#new-attachments-preview').append(previewContent);
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
            title: 'Delete file?',
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

    // Handle File Preview Click
    $(document).on('click', '.preview-file-click', function() {
        const src = $(this).data('src');
        const type = $(this).data('type');
        
        $('#previewImageModalSrc').addClass('d-none');
        $('#previewPdfModalSrc').addClass('d-none');
        $('#previewUnsupportedMessage').addClass('d-none');

        if (type === 'image') {
            $('#previewImageModalSrc').attr('src', src).removeClass('d-none');
        } else if (type === 'pdf') {
            $('#previewPdfModalSrc').attr('src', src).removeClass('d-none');
        } else {
            $('#previewDownloadLink').attr('href', src).attr('download', '');
            $('#previewUnsupportedMessage').removeClass('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
        modal.show();
    });

    // Handle Save Form
    $('#announcementForm').on('submit', function (e) {
        e.preventDefault();

        const btn = $('#btnSaveAnnouncement');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Saving...');

        // Basic validation
        const title = $('#title').val();
        const content = quill.root.innerHTML;
        if (quill.getText().trim().length === 0) {
            Swal.fire('Error', 'Content is required', 'error');
            btn.prop('disabled', false).html(originalText);
            return;
        }
        
        const valid_from = $('#valid_from').val();
        const valid_until = $('#valid_until').val();
        
        if (valid_from && valid_until && valid_until < valid_from) {
            Swal.fire('Error', 'Valid Until date cannot be earlier than Valid From date', 'error');
            btn.prop('disabled', false).html(originalText);
            return;
        }

        const formData = {
            title: title,
            content: content,
            valid_from: valid_from,
            valid_until: valid_until,
            is_active: $('#is_active').is(':checked'),
            pin_announcement: $('#pin_announcement').is(':checked')
        };

        const url = isEdit 
            ? `${window.baseUrl}/internal/announcements/${announcementId}`
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
                const currentId = response.id;
                
                // Upload files if any
                const fileInput = document.getElementById('attachments');
                if (fileInput.files.length > 0) {
                    const uploadData = new FormData();
                    for (let i = 0; i < fileInput.files.length; i++) {
                        uploadData.append('attachments[]', fileInput.files[i]);
                    }
                    
                    $.ajax({
                        url: `${window.baseUrl}/internal/announcements/${currentId}/attachments`,
                        type: 'POST',
                        data: uploadData,
                        processData: false,
                        contentType: false,
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function() {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                window.location.href = `${window.baseUrl}/internal/announcements`;
                            });
                        },
                        error: function() {
                            Swal.fire('Warning!', 'Announcement saved but files failed to upload.', 'warning').then(() => {
                                window.location.href = `${window.baseUrl}/internal/announcements`;
                            });
                        }
                    });
                } else {
                    Swal.fire('Success!', response.message, 'success').then(() => {
                        window.location.href = `${window.baseUrl}/internal/announcements`;
                    });
                }
            },
            error: function (xhr) {
                let errorMsg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error!', errorMsg, 'error');
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
