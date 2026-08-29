import jQuery from "jquery";
import Quill from "quill";
import "quill/dist/quill.snow.css";
import Swal from "sweetalert2";

const $ = jQuery;
window.$ = window.jQuery = jQuery;

// ─── Custom Blot: inline downloadable file card ───────────────
const BlockEmbed = Quill.import('blots/block/embed');

class FileCardBlot extends BlockEmbed {
    static create(value) {
        const node = super.create();
        node.setAttribute('href', value.url);
        node.setAttribute('target', '_blank');
        node.setAttribute('download', value.name);
        node.setAttribute('contenteditable', 'false');
        node.classList.add('doc-file-card');
        node.innerHTML = `
            <i class="ti ti-file-type-pdf fs-24 me-2"></i>
            <span class="doc-file-card-name">${value.name}</span>
            <i class="ti ti-download ms-2"></i>
        `;
        return node;
    }

    static value(node) {
        return {
            url: node.getAttribute('href'),
            name: node.querySelector('.doc-file-card-name').textContent,
        };
    }
}
FileCardBlot.blotName = 'fileCard';
FileCardBlot.tagName = 'a';
Quill.register(FileCardBlot);

$(document).ready(function () {
    const quill = new Quill('#content-editor', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link', 'image', 'fileAttach'],
                    ['clean'],
                ],
                handlers: {
                    image: () => uploadHandler(true),
                    fileAttach: () => uploadHandler(false),
                },
            },
        },
    });

    // Rename the custom toolbar button icon (Quill renders unknown format names as blank buttons)
    const fileBtn = document.querySelector('.ql-fileAttach');
    if (fileBtn) {
        fileBtn.innerHTML = '<i class="ti ti-paperclip"></i>';
        fileBtn.title = 'Attach file';
    }

    // ─── Shared upload handler (image or document) ─────────────
    function uploadHandler(imageOnly) {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute(
            'accept',
            imageOnly ? 'image/*' : '.pdf,.doc,.docx,.xls,.xlsx,image/*'
        );
        input.click();

        input.onchange = () => {
            const file = input.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('Error', 'File must be smaller than 5MB', 'error');
                return;
            }

            const range = quill.getSelection(true);
            const placeholder = 'Uploading…';
            quill.insertText(range.index, placeholder, { italic: true });

            const formData = new FormData();
            formData.append('file', file);

            $.ajax({
                url: `${window.baseUrl}/internal/documents/upload-file`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (response) {
                    quill.deleteText(range.index, placeholder.length);

                    if (response.is_image) {
                        quill.insertEmbed(range.index, 'image', response.url);
                    } else {
                        quill.insertEmbed(range.index, 'fileCard', {
                            url: response.url,
                            name: response.name,
                        });
                    }
                    quill.setSelection(range.index + 1);
                },
                error: function (xhr) {
                    quill.deleteText(range.index, placeholder.length);
                    let msg = 'Upload failed';
                    if (xhr.responseJSON?.errors?.file) {
                        msg = xhr.responseJSON.errors.file[0];
                    }
                    Swal.fire('Error', msg, 'error');
                },
            });
        };
    }

    // ─── Update Form ───────────────────────────────────────────
    $('#documentForm').on('submit', function (e) {
        e.preventDefault();

        const module = $('#docModule').val();
        const name = $('#docName').val().trim();

        if (!module || !name) {
            Swal.fire('Error', 'Please fill in all required fields.', 'error');
            return;
        }

        const id = $('#document_id').val();

        const formData = {
            module: module,
            name: name,
            description: quill.root.innerHTML,
            is_required: $('#docRequired').is(':checked'),
            requires_expiry: $('#docExpiry').is(':checked'),
            is_active: $('#docActive').is(':checked'),
        };

        $.ajax({
            url: `${window.baseUrl}/internal/documents/${id}`,
            type: 'PUT',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                Swal.fire('Success!', response.message, 'success').then(() => {
                    window.location.href = `${window.baseUrl}/internal/documents`;
                });
            },
            error: function (xhr) {
                let errorMsg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                Swal.fire('Error!', errorMsg, 'error');
            },
        });
    });
});