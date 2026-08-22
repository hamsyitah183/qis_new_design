import jQuery from "jquery";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import "datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css";

const $ = jQuery;
window.$ = window.jQuery = jQuery;

$(document).ready(function () {
    const documentName = window.documentName;

    $('#attachmentTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: `${window.baseUrl}/internal/documents/${window.documentId}/attachments/data`,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user_name', name: 'user_name' },
            { data: 'original_file_name', name: 'original_file_name' },
            { data: 'file_type', name: 'file_type' },
            { data: 'file_size_formatted', name: 'file_size' },
            { data: 'valid_from_formatted', name: 'valid_from' },
            { data: 'valid_until_formatted', name: 'valid_until' },
            { data: 'created_at', name: 'created_at' },
            {
                data: 'id',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <a href="${window.baseUrl}/storage/${row.file_path}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="ti ti-download"></i> Download
                        </a>
                    `;
                }
            }
        ],
        columnDefs: [
            {
                targets: 7,
                render: function (data) {
                    return data ? new Date(data).toLocaleString('en-GB') : '—';
                }
            }
        ]
    });
});