import jQuery from "jquery";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import "datatables.net-bs5/css/dataTables.bootstrap5.min.css";
import "datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css";
import Swal from "sweetalert2";
import { applyTranslations } from "../../../app";

const $ = jQuery;
window.$ = window.jQuery = jQuery;

$(document).ready(function () {
    // ─── DataTable ──────────────────────────────────────────────
    const table = $("#documentTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: `${window.baseUrl}/internal/documents/data`,
        columns: [
            { data: "id", name: "id", visible: false },
            { data: "module", name: "module" },
            { data: "name", name: "name" },
            { data: "description", name: "description" },
            { data: "required_badge", name: "is_required" },
            { data: "expiry_badge", name: "requires_expiry" },
            { data: "status_badge", name: "is_active", visible: false },
            {
                data: "id",
                name: "action",
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
                },
            },
        ],
    });

    // ─── Add Button ──────────────────────────────────────────────
    $("#btnAddDocument").on("click", function () {
        $("#documentForm")[0].reset();
        $("#document_id").val("");
        $("#docActive").prop("checked", true);
        // $("#addDocumentModalLabel").text("Add Document");
        const modal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById("addDocumentModal"),
        );
        modal.show();

        applyTranslations(modal);
    });

    // ─── Edit Button ─────────────────────────────────────────────
    $("#documentTable").on("click", ".edit-btn", function () {
        const id = $(this).data("id");
        window.location.href = `${window.baseUrl}/internal/documents/${id}/edit`;
    });

    // ─── Save Form ───────────────────────────────────────────────
    $("#btnSaveDocument").on("click", function (e) {
        e.preventDefault();

        const id = $("#document_id").val();
        const isEdit = id !== "";

        // Basic validation
        const module = $("#docModule").val();
        const name = $("#docName").val().trim();

        if (!module || !name) {
            Swal.fire(
                "Error",
                "Please fill in all required fields (Module and Name).",
                "error",
            );
            return;
        }

        const formData = {
            module: module,
            name: name,
            description: $("#docDescription").val().trim(),
            is_required: $("#docRequired").is(":checked"),
            requires_expiry: $("#docExpiry").is(":checked"),
            is_active: $("#docActive").is(":checked"),
        };

        const url = isEdit
            ? `${window.baseUrl}/internal/documents/${id}`
            : `${window.baseUrl}/internal/documents`;
        const method = isEdit ? "PUT" : "POST";

        $.ajax({
            url: url,
            type: method,
            data: JSON.stringify(formData),
            contentType: "application/json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                bootstrap.Modal.getInstance(
                    document.getElementById("addDocumentModal"),
                ).hide();
                Swal.fire("Success!", response.message, "success");
                table.ajax.reload();
            },
            error: function (xhr) {
                let errorMsg = "Something went wrong";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors)
                        .flat()
                        .join("\n");
                }
                Swal.fire("Error!", errorMsg, "error");
            },
        });
    });

    // ─── View Button ─────────────────────────────────────────────
    // ─── View Button (redirect to view page) ──────────────────────
    $("#documentTable").on("click", ".view-btn", function () {
        const id = $(this).data("id");
        window.location.href = `${window.baseUrl}/internal/documents/view/${id}`;
    });

    // ─── Delete Button ───────────────────────────────────────────
    $("#documentTable").on("click", ".delete-btn", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${window.baseUrl}/internal/documents/${id}`,
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                    },
                    success: function (response) {
                        Swal.fire("Deleted!", response.message, "success");
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    },
                });
            }
        });
    });
});
