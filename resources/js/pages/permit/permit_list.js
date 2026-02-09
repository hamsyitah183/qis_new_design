
import Swal from "sweetalert2";

console.log("permit list");

let orderListTable;

async function data_table_init() {
    const [{ default: DataTable }] = await Promise.all([
        import("datatables.net-bs5"),
        import("datatables.net-responsive-bs5"),
        import("datatables.net-buttons-bs5"),
    ]);

    orderListTable = new DataTable("#permitListTable", {
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: "/permit/list/import/data",
        responsive: true,
        autoWidth: false,
        pageLength: 10,



        columns: [
            {
                data: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "permit_number" },
            { data: "item_name", orderable: false, searchable: true },
            { data: "importer" },
            { data: "action", orderable: false, searchable: false },
        ],

        columnDefs: [
            { width: "50px", targets: 0 },
            { width: "150px", targets: 1 },
            { width: "200px", targets: 2 },
            { width: "150px", targets: 3 },
            { width: "120px", targets: 4 },
        ],

        responsive: true,
        autoWidth: false,
        pageLength: 10,
    });
}

document.addEventListener("DOMContentLoaded", data_table_init);

function generatePermit() {
    $(document)
        .off("click", ".generatePermit")
        .on("click", ".generatePermit", function (e) {
            e.preventDefault();

            const id = $(this).data("permit");

            Swal.fire({
                title: "Generating Permit...",
                text: "Please wait",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            // Small delay so loading is visible
            setTimeout(() => {
                window.location.href = `/permit/generate/${id}`;
                Swal.close();
            }, 800);
        });
}


generatePermit();
