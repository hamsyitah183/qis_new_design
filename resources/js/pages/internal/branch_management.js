import $ from "jquery";
window.$ = window.jQuery = $;
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import Swal from "sweetalert2";
import { applyTranslations } from "../../app";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    enterBranchName: { en: 'Please enter a branch name.', bm: 'Sila masukkan nama cawangan.' },
    branchAdded: { en: 'Branch added!', bm: 'Cawangan ditambah!' },
    error: { en: 'Error', bm: 'Ralat' },
    failedToAdd: { en: 'Failed to add branch.', bm: 'Gagal menambah cawangan.' },
    networkError: { en: 'An error occurred.', bm: 'Satu ralat telah berlaku.' },
    branchUpdated: { en: 'Branch updated!', bm: 'Cawangan dikemas kini!' },
    failedToUpdate: { en: 'Failed to update branch.', bm: 'Gagal mengemas kini cawangan.' },
    deleteActionWarning: { en: 'This action cannot be undone.', bm: 'Tindakan ini tidak boleh dibatalkan.' },
    yesDelete: { en: 'Yes, delete', bm: 'Ya, padam' },
    cancel: { en: 'Cancel', bm: 'Batal' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    failedToDelete: { en: 'Failed to delete.', bm: 'Gagal memadam.' }
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}


let branchTable = null;

$(document).ready(function () {

    // ─── Branch DataTable ────────────────────────────────────────────────
    branchTable = $("#branchTable").DataTable({
        processing: true,
        responsive: true,
        ajax: {
            url: "/internal/branches",
            dataSrc: "data",
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (data, type, row, meta) => meta.row + 1,
            },
            { data: "name", name: "name" },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: "text-center",
                render: (data, type, row) =>
                    `<div class="hstack gap-2 justify-content-center">
                        <button class="btn btn-sm btn-info-light edit-branch-btn"
                            data-id="${row.id}" data-name="${row.name.replace(/'/g, "&#39;")}">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-sm btn-danger-light delete-branch-btn"
                            data-id="${row.id}" data-name="${row.name.replace(/'/g, "&#39;")}">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>`,
            },
        ],
        order: [[1, "asc"]],
        drawCallback: () => applyTranslations(document.body)
    });

    // ─── Add Branch ──────────────────────────────────────────────────────
    $("#addBranchBtn").on("click", function () {
        $("#addBranchName").val("");
        bootstrap.Modal.getOrCreateInstance(document.getElementById("addBranchModal")).show();
    });

    $("#saveBranchBtn").on("click", function () {
        const name = $("#addBranchName").val().trim();
        if (!name) {
            Swal.fire({ icon: "warning", title: getText("enterBranchName"), timer: 1500, showConfirmButton: false });
            return;
        }

        $(this).prop("disabled", true);
        const fd = new FormData();
        fd.append("name", name);

        $.ajax({
            url: "/internal/branch/add",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            success: function (response) {
                bootstrap.Modal.getInstance(document.getElementById("addBranchModal")).hide();
                if (response.status === "success") {
                    Swal.fire({ icon: "success", title: getText("branchAdded"), timer: 1500, showConfirmButton: false });
                    branchTable.ajax.reload();
                } else {
                    Swal.fire(getText("error"), response.message || getText("failedToAdd"), "error");
                }
            },
            error: (xhr) => Swal.fire(getText("error"), xhr.responseJSON?.message || getText("networkError"), "error"),
            complete: () => $("#saveBranchBtn").prop("disabled", false),
        });
    });

    // Allow Enter key
    $("#addBranchName").on("keypress", (e) => { if (e.key === "Enter") $("#saveBranchBtn").trigger("click"); });

    // ─── Edit Branch ─────────────────────────────────────────────────────
    $("#branchTable").on("click", ".edit-branch-btn", function () {
        $("#editBranchId").val($(this).data("id"));
        $("#editBranchName").val($(this).data("name"));
        bootstrap.Modal.getOrCreateInstance(document.getElementById("editBranchModal")).show();
    });

    $("#updateBranchBtn").on("click", function () {
        const id = $("#editBranchId").val();
        const name = $("#editBranchName").val().trim();
        if (!name) {
            Swal.fire({ icon: "warning", title: getText("enterBranchName"), timer: 1500, showConfirmButton: false });
            return;
        }

        $(this).prop("disabled", true);
        const fd = new FormData();
        fd.append("id", id);
        fd.append("name", name);

        $.ajax({
            url: "/internal/branch/update",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            success: function (response) {
                bootstrap.Modal.getInstance(document.getElementById("editBranchModal")).hide();
                if (response.status === "success") {
                    Swal.fire({ icon: "success", title: getText("branchUpdated"), timer: 1500, showConfirmButton: false });
                    branchTable.ajax.reload();
                } else {
                    Swal.fire(getText("error"), response.message || getText("failedToUpdate"), "error");
                }
            },
            error: (xhr) => Swal.fire(getText("error"), xhr.responseJSON?.message || getText("networkError"), "error"),
            complete: () => $("#updateBranchBtn").prop("disabled", false),
        });
    });

    // ─── Delete Branch ───────────────────────────────────────────────────
    $("#branchTable").on("click", ".delete-branch-btn", function () {
        const id = $(this).data("id");
        const name = $(this).data("name");

        Swal.fire({
            title: getLang() === "bm" ? `Padam "${name}"?` : `Delete "${name}"?`,
            text: getText("deleteActionWarning"),
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: getText("yesDelete"),
            confirmButtonColor: "#d33",
            cancelButtonText: getText("cancel"),
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/internal/branch/delete/${id}`,
                type: "DELETE",
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                success: function (response) {
                    if (response.status === "success") {
                        Swal.fire({ icon: "success", title: getText("deleted"), timer: 1200, showConfirmButton: false });
                        branchTable.ajax.reload();
                    } else {
                        Swal.fire(getText("error"), response.message || getText("failedToDelete"), "error");
                    }
                },
                error: (xhr) => Swal.fire(getText("error"), xhr.responseJSON?.message || getText("networkError"), "error"),
            });
        });
    });
});
