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
    pleaseEnterDistrict: { en: 'Please enter a district name.', bm: 'Sila masukkan nama daerah.' },
    districtAdded: { en: 'District added!', bm: 'Daerah ditambah!' },
    error: { en: 'Error', bm: 'Ralat' },
    failedToAdd: { en: 'Failed to add district.', bm: 'Gagal menambah daerah.' },
    networkError: { en: 'Network error. Please try again.', bm: 'Ralat rangkaian. Sila cuba lagi.' },
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
                        <i class="ri-edit-line me-1"></i><span data-en="Manage" data-bm="Urus">Manage</span>
                    </button>`,
            },
        ],
        order: [[1, "asc"]],
        drawCallback: () => applyTranslations(document.body)
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
    const titleText = getLang() === "bm" 
        ? `Urus Daerah — ${currentStateName}` 
        : `Manage Districts — ${currentStateName}`;
    $("#modalStateTitle").text(titleText);
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
        drawCallback: () => applyTranslations(document.body)
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
        Swal.fire({ icon: "warning", title: getText("pleaseEnterDistrict"), timer: 1500, showConfirmButton: false });
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
                Swal.fire({ icon: "success", title: getText("districtAdded"), timer: 1200, showConfirmButton: false });
                districtsTable.ajax.reload();
                statesTable.ajax.reload(null, false);
            } else {
                Swal.fire(getText("error"), data.message || getText("failedToAdd"), "error");
            }
        })
        .catch(() => Swal.fire(getText("error"), getText("networkError"), "error"))
        .finally(() => $("#addDistrictBtn").prop("disabled", false));
}

//Delete District
function deleteDistrict(districtId, districtName) {
    Swal.fire({
        title: getLang() === "bm" ? `Padam "${districtName}"?` : `Delete "${districtName}"?`,
        text: getText("deleteActionWarning"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: getText("yesDelete"),
        confirmButtonColor: "#d33",
        cancelButtonText: getText("cancel"),
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
                    Swal.fire({ icon: "success", title: getText("deleted"), timer: 1200, showConfirmButton: false });
                    districtsTable.ajax.reload();
                    statesTable.ajax.reload(null, false);
                } else {
                    Swal.fire(getText("error"), data.message || getText("failedToDelete"), "error");
                }
            })
            .catch(() => Swal.fire(getText("error"), getText("networkError"), "error"));
    });
}
