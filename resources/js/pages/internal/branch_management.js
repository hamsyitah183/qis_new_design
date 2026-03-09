import $ from "jquery";
window.$ = window.jQuery = $;
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import Swal from "sweetalert2";

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
        language: {
            emptyTable: "No branches found.",
            zeroRecords: "No matching branches.",
        },
    });

    // ─── Add Branch ──────────────────────────────────────────────────────
    $("#addBranchBtn").on("click", function () {
        $("#addBranchName").val("");
        bootstrap.Modal.getOrCreateInstance(document.getElementById("addBranchModal")).show();
    });

    $("#saveBranchBtn").on("click", function () {
        const name = $("#addBranchName").val().trim();
        if (!name) {
            Swal.fire({ icon: "warning", title: "Please enter a branch name.", timer: 1500, showConfirmButton: false });
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
                    Swal.fire({ icon: "success", title: "Branch added!", timer: 1500, showConfirmButton: false });
                    branchTable.ajax.reload();
                } else {
                    Swal.fire("Error", response.message || "Failed to add branch.", "error");
                }
            },
            error: (xhr) => Swal.fire("Error", xhr.responseJSON?.message || "An error occurred.", "error"),
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
            Swal.fire({ icon: "warning", title: "Please enter a branch name.", timer: 1500, showConfirmButton: false });
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
                    Swal.fire({ icon: "success", title: "Branch updated!", timer: 1500, showConfirmButton: false });
                    branchTable.ajax.reload();
                } else {
                    Swal.fire("Error", response.message || "Failed to update branch.", "error");
                }
            },
            error: (xhr) => Swal.fire("Error", xhr.responseJSON?.message || "An error occurred.", "error"),
            complete: () => $("#updateBranchBtn").prop("disabled", false),
        });
    });

    // ─── Delete Branch ───────────────────────────────────────────────────
    $("#branchTable").on("click", ".delete-branch-btn", function () {
        const id = $(this).data("id");
        const name = $(this).data("name");

        Swal.fire({
            title: `Delete "${name}"?`,
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#d33",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/internal/branch/delete/${id}`,
                type: "DELETE",
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                success: function (response) {
                    if (response.status === "success") {
                        Swal.fire({ icon: "success", title: "Deleted!", timer: 1200, showConfirmButton: false });
                        branchTable.ajax.reload();
                    } else {
                        Swal.fire("Error", response.message || "Failed to delete.", "error");
                    }
                },
                error: (xhr) => Swal.fire("Error", xhr.responseJSON?.message || "An error occurred.", "error"),
            });
        });
    });
});
