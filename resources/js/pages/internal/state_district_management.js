import $ from "jquery";
window.$ = window.jQuery = $;
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import Swal from "sweetalert2";

let statesTable = null;
let districtsTable = null;
let currentStateId = null;
let currentStateName = null;

$(document).ready(function () {

    //States DataTable 
    statesTable = $("#statesTable").DataTable({
        processing: true,
        responsive: true,
        ajax: {
            url: "/api/states",
            dataSrc: "",
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
                data: "districts_count",
                name: "districts_count",
                className: "text-center",
                render: (data) =>
                    `<span class="badge bg-info-transparent text-info fs-11">${data ?? 0}</span>`,
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: "text-center",
                render: (data, type, row) =>
                    `<button class="btn btn-sm btn-primary manage-state-btn"
                        data-id="${row.id}" data-name="${row.name}">
                        <i class="ri-edit-line me-1"></i>Manage
                    </button>`,
            },
        ],
        order: [[1, "asc"]],
        language: {
            emptyTable: "No states found.",
            zeroRecords: "No matching states found.",
        },
    });

    // Manage button  
    $("#statesTable").on("click", ".manage-state-btn", function () {
        currentStateId = $(this).data("id");
        currentStateName = $(this).data("name");
        openDistrictModal();
    });

    // Add new district
    $("#addDistrictBtn").on("click", addDistrict);
    $("#newDistrictInput").on("keypress", (e) => {
        if (e.key === "Enter") addDistrict();
    });
});

//Open District Modal 
function openDistrictModal() {
    $("#modalStateTitle").text(`Manage Districts — ${currentStateName}`);
    $("#newDistrictInput").val("");

    if (districtsTable) {
        districtsTable.destroy();
        districtsTable = null;
        $("#districtsTable tbody").empty();
    }

    // Show modal
    bootstrap.Modal.getOrCreateInstance(
        document.getElementById("districtManagementModal")
    ).show();

    // Initialize districts DataTable with current state
    districtsTable = $("#districtsTable").DataTable({
        processing: true,
        responsive: true,
        ajax: {
            url: `/api/districts/${currentStateId}`,
            dataSrc: "",
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
                    `<button class="btn btn-sm btn-danger-light delete-district-btn"
                        data-id="${row.id}" data-name="${row.name}">
                        <i class="ri-delete-bin-line"></i>
                    </button>`,
            },
        ],
        order: [[1, "asc"]],
        language: {
            emptyTable: "No districts found for this state.",
            zeroRecords: "No matching districts.",
        },
    });

    // Delete button
    $("#districtsTable").on("click", ".delete-district-btn", function () {
        deleteDistrict($(this).data("id"), $(this).data("name"));
    });
}

// Add District
function addDistrict() {
    const name = $("#newDistrictInput").val().trim();
    if (!name) {
        Swal.fire({ icon: "warning", title: "Please enter a district name.", timer: 1500, showConfirmButton: false });
        return;
    }

    $("#addDistrictBtn").prop("disabled", true);

    fetch("/api/districts", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ name, state_id: currentStateId }),
    })
        .then((r) => r.json())
        .then((data) => {
            if (data.success) {
                $("#newDistrictInput").val("");
                Swal.fire({ icon: "success", title: "District added!", timer: 1200, showConfirmButton: false });
                districtsTable.ajax.reload();
                statesTable.ajax.reload(null, false);
            } else {
                Swal.fire("Error", data.message || "Failed to add district.", "error");
            }
        })
        .catch(() => Swal.fire("Error", "Network error. Please try again.", "error"))
        .finally(() => $("#addDistrictBtn").prop("disabled", false));
}

//Delete District
function deleteDistrict(districtId, districtName) {
    Swal.fire({
        title: `Delete "${districtName}"?`,
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
        confirmButtonColor: "#d33",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (!result.isConfirmed) return;

        fetch(`/api/districts/${districtId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Content-Type": "application/json",
            },
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.success) {
                    Swal.fire({ icon: "success", title: "Deleted!", timer: 1200, showConfirmButton: false });
                    districtsTable.ajax.reload();
                    statesTable.ajax.reload(null, false);
                } else {
                    Swal.fire("Error", data.message || "Failed to delete.", "error");
                }
            })
            .catch(() => Swal.fire("Error", "Network error. Please try again.", "error"));
    });
}
