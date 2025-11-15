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
            ],
            responsive: true,
        });
    }

    /**
     * Row Click Event
     */
    $("#roleTable").on("click", "tbody tr", function () {
        const rowData = roleTable.row(this).data();

        // Remove active from all rows
        $("#roleTable tbody tr").removeClass("active");

        // Add active to selected row
        $(this).addClass("active");

        if (!rowData) return; // avoid null errors on placeholder rows

        const detailsHtml = `
            <p><strong>Name:</strong> ${rowData.name}</p>
            <p><strong>User Count:</strong> ${rowData.user_count}</p>
            <p><strong>Permissions:</strong> ${(
                rowData.permission_names || []
            ).join(", ")}</p>
        `;

        // 📱 MOBILE — Show modal
        if (window.innerWidth < 992) {
            $("#roleDetailsContentModal").html(detailsHtml);

            const modal = new bootstrap.Modal(
                document.getElementById("roleDetailsModal")
            );
            modal.show();

            return;
        }

        // 🖥 DESKTOP — Show details panel
        $("#roleDetailsContentDesktop").html(detailsHtml);

        // Shrink left table
        $("#roleTableWrapper")
            .removeClass("table-expanded")
            .addClass("table-shrink");

        // Reveal right panel with fade animation
        $("#roleDetailsWrapper")
            .css({ display: "block", opacity: 0 })
            .animate({ opacity: 1 }, 200);
    });

    await data_table_init();
}

role_list();
