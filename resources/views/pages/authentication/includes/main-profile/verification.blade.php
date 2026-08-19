<div class="border rounded-3 p-3">
    <div class="d-flex flex-column gap-2" id="document-list-container">
        
    </div>
</div>

<style>
    .doc-row-toggle {
        cursor: pointer;
    }

    .doc-row-toggle .doc-toggle-icon {
        transition: transform .2s ease;
    }

    .doc-row-toggle[aria-expanded="true"] .doc-toggle-icon {
        transform: rotate(180deg);
    }

    .doc-status-badge.has-files {
        background: rgba(var(--success-rgb, 30, 195, 121), .12) !important;
        color: rgb(var(--success-rgb, 30, 195, 121)) !important;
    }

    .file-drop-area.is-dragover {
        border-color: rgb(var(--primary-rgb)) !important;
        background: rgba(var(--primary-rgb), .05);
    }

    .file-list-item {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid var(--default-border);
        border-radius: 10px;
        padding: 8px 12px;
        background: var(--default-background);
    }

    .file-list-item i {
        font-size: 20px;
        color: rgb(var(--primary-rgb));
        flex-shrink: 0;
    }

    .file-list-item .file-meta {
        flex: 1;
        min-width: 0;
    }

    .file-list-item .file-meta .file-name {
        font-size: 13px;
        font-weight: 500;
        color: var(--default-text-color);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-list-item .file-meta .file-size {
        font-size: 11.5px;
        color: var(--text-muted);
    }

    .file-list-item .file-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }

    .file-list-item .file-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid var(--default-border);
        background: transparent;
        color: rgb(var(--primary-rgb));
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        border-radius: 6px;
        padding: 4px 8px;
        white-space: nowrap;
    }

    .file-list-item .file-view-btn:hover {
        background: rgba(var(--primary-rgb), .08);
    }

    .file-list-item .file-remove {
        border: none;
        background: transparent;
        color: var(--text-muted);
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
        padding: 2px 6px;
        flex-shrink: 0;
    }

    .file-list-item .file-remove:hover {
        color: var(--danger-color, #fb4242);
    }

    .file-list-item.existing-file i {
        color: var(--success-color, #1ec379);
    }
</style>

<script>
    (function() {
        document.querySelectorAll('.document-upload-section').forEach(function(section) {
            var docId = section.getAttribute('data-doc-id');
            var rowToggle = section.querySelector('.doc-row-toggle');
            var panel = section.querySelector('.doc-panel[data-doc-id="' + docId + '"]');

            if (rowToggle && panel) {
                rowToggle.addEventListener('click', function() {
                    var isOpen = !panel.classList.contains('d-none');
                    panel.classList.toggle('d-none', isOpen);
                    rowToggle.setAttribute('aria-expanded', String(!isOpen));
                });
            }
        });
    })();
</script>