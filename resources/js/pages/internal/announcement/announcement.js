import jQuery from "jquery";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import "datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css";
import Swal from "sweetalert2";

const $ = jQuery;
window.$ = window.jQuery = jQuery;

$(document).ready(function () {

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
                    
                    const togglePinClass = row.pin_announcement ? 'btn-warning' : 'btn-danger';
                    const togglePinTitle = row.pin_announcement ? 'Unpin' : 'Pin';

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
                            <button class="btn btn-sm ${togglePinClass} toggle-pin-btn" data-id="${data}" title="${togglePinTitle}">
                                <i class="ti ti-pin"></i>
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
                    let previewContent = '';
                    let fileType = att.file_type || '';
                    let filePath = `/storage/${att.file_path}`;

                    if (fileType.startsWith('image/')) {
                        previewContent = `
                            <div class="border rounded p-1 d-inline-block text-center me-2 mb-2">
                                <img src="${filePath}" class="preview-file-click" data-type="image" data-src="${filePath}" style="height: 140px; width: 140px; object-fit: cover; cursor: pointer;" alt="${att.file_name}" title="${att.file_name}">
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
                            <div class="border rounded p-2 d-inline-flex align-items-center me-2 mb-2 preview-file-click" data-type="${typeCategory}" data-src="${filePath}" style="cursor: pointer; max-width: 100%;" title="${att.file_name}">
                                <i class="ti ${iconClass} fs-24"></i>
                                <span class="ms-2 text-dark text-truncate" style="max-width: 250px;">${att.file_name}</span>
                            </div>`;
                    }

                    $('#view_attachments').append(previewContent);
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

    // Handle File Preview Click inside View Modal
    $(document).on('click', '.preview-file-click', function (e) {
        e.preventDefault();
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

        // Hide the view announcement modal
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewAnnouncementModal'));
        if (viewModal) {
            viewModal.hide();
        }
        
        // Show the file preview modal
        const previewModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('filePreviewModal'));
        previewModal.show();
    });
    
    // When preview modal is closed, reopen the view announcement modal
    $('#filePreviewModal').on('hidden.bs.modal', function () {
        const viewModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('viewAnnouncementModal'));
        viewModal.show();
    });

    // Handle Edit Button
    $('#announcementTable').on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        window.location.href = `${window.baseUrl}/internal/announcements/${id}/edit`;
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

    // Toggle Pin Action
    $('#announcementTable').on('click', '.toggle-pin-btn', function () {
        const id = $(this).data('id');
        
        $.ajax({
            url: `${window.baseUrl}/internal/announcements/${id}/toggle-pin`,
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
