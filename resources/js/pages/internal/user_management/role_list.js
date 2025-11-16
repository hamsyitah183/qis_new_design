import $ from "jquery";
import Swal from "sweetalert2";

console.log("list role");

let roleTable;

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

        initTooltips();

        // Highlight selected row
        $("#roleTable tbody tr").removeClass("active");
        row.addClass("active");

        // Build modal content
        const detailsHtml = `
        <div class="mb-2 fw-bold fs-6">${rowData.name}</div>
        <p><strong>User Count:</strong> ${rowData.user_count}</p>
        <p><strong>Permissions:</strong> ${(
            rowData.permission_names || []
        ).join(", ")}</p>
    `;

        // OPEN MODAL for ALL VIEWPORTS
        $("#roleDetailsContentModal").html(detailsHtml);

        const modal = new bootstrap.Modal(
            document.getElementById("roleDetailsModal")
        );
        modal.show();
    });

    await data_table_init();
}

function initTooltips() {
    // remove old tooltip instances to avoid duplicates
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        if (bootstrap.Tooltip.getInstance(el)) {
            bootstrap.Tooltip.getInstance(el).dispose();
        }
        new bootstrap.Tooltip(el);
    });
}

role_list();
initTooltips();
