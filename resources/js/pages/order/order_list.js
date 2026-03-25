import { initTooltips } from "../../app";
import Swal from "sweetalert2";
import QRCode from "qrcode";

console.log("order list");

let orderListTable;
let permitQrModal;

const isInternal = window.AUTH_TYPE === "internal";

// Store current filter values
const orderFilters = {
    status: "",
    applicationType: "",
    startDate: "",
    endDate: "",
};

function escapeHtml(value) {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

async function data_table_init() {
    const [{ default: DataTable }] = await Promise.all([
        import("datatables.net-bs5"),
        import("datatables.net-responsive-bs5"),
        import("datatables.net-buttons-bs5"),
    ]);

    orderListTable = new DataTable("#orderListTable", {
        processing: true,
        serverSide: true,
        ajax: {
            url: "/order/list/data",
            data: function (d) {
                d.order_status = orderFilters.status;
                d.application_type = orderFilters.applicationType;
                d.start_date = orderFilters.startDate;
                d.end_date = orderFilters.endDate;
            },
        },

        columns: [
            { data: "order_number" },
            {
                data: "permit_number",
                render: function (data, type) {
                    const permitNumber = data || "-";

                    if (type !== "display") {
                        return permitNumber;
                    }

                    if (permitNumber === "-") {
                        return "-";
                    }

                    const encodedPermit = encodeURIComponent(permitNumber);
                    const safePermit = escapeHtml(permitNumber);

                    return `
                        <div class="permit-cell">
                            <span class="permit-text" title="${safePermit}">${safePermit}</span>
                            <button
                                type="button"
                                class="btn btn-sm btn-primary generatePermitQr permit-qr-btn"
                                data-permit-number="${encodedPermit}"
                                title="Generate QR"
                            >
                                <i class="ti ti-qrcode"></i>
                            </button>
                        </div>
                    `;
                },
            },
            { data: "status" },
            { data: "application_type" },
            ...(isInternal ? [{ data: "kod_transaksi" }] : []),
            { data: "payment_amount" },
            { data: "action", orderable: false, searchable: false },
        ],

        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [],
    });

    orderListTable.on("draw.dt", function () {
        initTooltips();
    });

    const permitQrModalElement = document.getElementById("permitQrModal");
    if (permitQrModalElement && window.bootstrap?.Modal) {
        permitQrModal = new window.bootstrap.Modal(permitQrModalElement);
    }

    // Apply Filters
    $("#btnOrderFilter").on("click", function (e) {
        e.preventDefault();

        orderFilters.status = $("#filterOrderStatus").val() || "";
        orderFilters.applicationType = $("#filterAppType").val() || "";
        orderFilters.startDate = $("#filterOrderStartDate").val() || "";
        orderFilters.endDate = $("#filterOrderEndDate").val() || "";

        orderListTable.ajax.reload();
    });

    // Reset Filters
    $("#btnResetOrderFilter").on("click", function (e) {
        e.preventDefault();

        $("#filterOrderStatus").val("");
        $("#filterAppType").val("");
        $("#filterOrderStartDate").val("");
        $("#filterOrderEndDate").val("");

        orderFilters.status = "";
        orderFilters.applicationType = "";
        orderFilters.startDate = "";
        orderFilters.endDate = "";

        orderListTable.ajax.reload();
    });

    // DELETE ORDER
    $(document).on("click", ".deleteApplication", async function () {
        const applicationId = $(this).data("id");

        const result = await Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#4c5359ff",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        });

        if (!result.isConfirmed) return;

        await fetch(`/internal/order/delete/${applicationId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                Accept: "application/json",
            },
        });

        Swal.fire("Deleted!", "Order deleted successfully", "success");
        orderListTable.ajax.reload(null, false);
    });

    // GENERATE PERMIT QR
    $(document).on("click", ".generatePermitQr", async function () {
        const encodedPermit = $(this).data("permit-number");
        const permitNumber = decodeURIComponent(encodedPermit || "");

        if (!permitNumber || permitNumber === "-") {
            return;
        }

        const qrImage = document.getElementById("permitQrImage");
        const qrLabel = document.getElementById("permitQrValue");

        if (!qrImage || !qrLabel) {
            return;
        }

        try {
            const qrDataUrl = await QRCode.toDataURL(permitNumber, {
                width: 260,
                margin: 1,
            });

            qrImage.src = qrDataUrl;
            qrLabel.textContent = permitNumber;

            if (permitQrModal) {
                permitQrModal.show();
            }
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "QR Generation Failed",
                text: "Unable to generate QR code for this permit number.",
            });
        }
    });

    initTooltips();
}

document.addEventListener("DOMContentLoaded", data_table_init);
