import $ from "jquery";
import Swal from "sweetalert2";
import { initTooltips } from "../../../app";

console.log("list role");

let roleTable;

let allUsers = [];

async function loadInternalUsers() {
    const res = await fetch("/internal/user_internal/list/data");
    const json = await res.json();

    allUsers = json.data; // store all users
    // console.log("Loaded users:", allUsers);
}

async function role_list() {
    /**
     * Load DataTable + extensions (lazy)
     */
    async function data_table_init() {
        const [
            { default: DataTable },
            _dtBs5,
            _dtResponsive,
            _dtButtons,
            _dtButtonsHtml5,
            _dtButtonsPrint,
        ] = await Promise.all([
            import("datatables.net-bs5"),
            import("datatables.net-responsive-bs5"),
            import("datatables.net-buttons-bs5"),
            import("datatables.net-buttons/js/buttons.html5.mjs"),
            import("datatables.net-buttons/js/buttons.print.mjs"),
        ]);

        // Inject required CSS
        await Promise.all([
            import("datatables.net-bs5/css/dataTables.bootstrap5.min.css"),
            import("datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css"),
        ]);

        initTooltips();

        roleTable = new DataTable("#roleTable", {
            processing: true,
            serverSide: true,
            lengthChange: false,
            info: false,
            dom: "frt",
            ajax: "/internal/roles/list/data",
            columns: [
                { data: "name", name: "name" },
                {
                    data: "users",
                    name: "users",
                    orderable: false,
                    searchable: false,
                },
                {
                    data: "permissions",
                    name: "permissions",
                    orderable: false,
                    searchable: false,
                },
                {
                    data: "user_count",
                    orderable: false,
                    searchable: false,
                    visible: false,
                },
                {
                    data: "permission_names",
                    orderable: false,
                    searchable: false,
                    visible: false,
                },
            ],
            responsive: true,
        });

        roleTable.on("draw.dt", function () {
            initTooltips();
        });
    }

    // Click only FIRST <td> (Role Name)
    $("#roleTable").on("click", "tbody td:first-child", function () {
        const row = $(this).closest("tr");
        const rowData = roleTable.row(row).data();

        if (!rowData) return;

        // Highlight selected row
        $("#roleTable tbody tr").removeClass("active");
        row.addClass("active");

        // Build modal content
        const detailsHtml = `
        <div class="mb-2 fw-bold fs-6">${rowData.name}</div>
            <p><strong>users:</strong> ${listUser(rowData.user_count)}
            <p><strong>Permissions:</strong> ${listPermission(
                rowData.permission_names
            )}
        `;

        // OPEN MODAL for ALL VIEWPORTS
        $("#roleDetailsContentModal").html(detailsHtml);

        initTooltips();

        const modal = new bootstrap.Modal(
            document.getElementById("roleDetailsModal")
        );

        modal.show();
    });

    await data_table_init();
}

function listUser(users) {
    // Ensure we have an array
    if (typeof users === "string") {
        users = JSON.parse(users);
    }

    console.log("users", users);

    let userHTML = '<div class="avatar-list-stacked my-3">';

    if (users) {
        users.forEach((user) => {
            // Generate initials from fullname
            const initials = user.fullname
                .split(" ")
                .map((word) => word[0].toUpperCase())
                .join("");

            userHTML += `
            <span class="cursor-pointer avatar avatar-md avatar-rounded border border-white bg-primary text-fixed-white"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="${user.fullname}">
                ${initials}
            </span>
        `;
        });
    } else {
        userHTML += "";
    }

    userHTML += "</div>";
    return userHTML;
}

function listPermission(permissions) {
    console.log(permissions);

    let permissionHTML = `<div class="d-flex gap-2 flex-wrap">`;

    permissions.forEach((permission) => {
        permissionHTML += `<span class="badge bg-dark-transparent p-1">
            ${permission}
        </span>`;
    });

    permissionHTML += `</div>`;

    return permissionHTML;
}

function listUserModal() {
    const modalEl = document.getElementById("userModal");
    if (!modalEl) return console.error("Modal not found!");

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    $(document).on("click", ".userModal", async function (e) {
        e.preventDefault();

        const roleName = $(this).data("role");
        console.log("Clicked role:", roleName);

        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        // If your user list is already loaded in allUsers, you continue
        await new Promise((resolve) => setTimeout(resolve, 300)); // small delay for UX

        let html = `
            <div class="fw-bold mb-2">Assign Users to Role: ${roleName}</div>
            <div class="list-group">
        `;

        allUsers.forEach((user) => {
            const hasRole = user.roles.some((r) => r.name === roleName);

            html += `
                <label class="list-group-item d-flex align-items-center gap-2">
                    <input type="checkbox"
                        class="form-check-input user-check"
                        data-user="${user.uuid}"
                        ${hasRole ? "checked" : ""}>
                    <span>${user.fullname}</span>
                </label>
            `;
        });

        html += `</div>`;

        $("#userModal .modal-body").html(html);

        Swal.close();

        modal.show();
    });
}

loadInternalUsers();
role_list();
listUserModal();
initTooltips();
