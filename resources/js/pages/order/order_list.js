import { initTooltips } from "../../app";
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
    error: { en: 'Error!', bm: 'Ralat!' },
    fetchFailed: { en: 'Failed to fetch data.', bm: 'Gagal mendapatkan data.' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    cannotRevert: { en: "You won't be able to revert this!", bm: 'Anda tidak akan dapat mengembalikannya!' },
    yesDeleteIt: { en: 'Yes, delete it!', bm: 'Ya, padamkannya!' },
    yesDelete: { en: 'Yes', bm: 'Ya' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    somethingWentWrong: { en: 'Something went wrong.', bm: 'Sesuatu yang tidak kena berlaku.' },
    success: { en: 'Success!', bm: 'Berjaya!' },
    successTitle: { en: 'Success', bm: 'Berjaya' },
    sent: { en: 'Sent!', bm: 'Dihantar!' },
    failedToSendEmail: { en: 'Failed to send emails. Please try again.', bm: 'Gagal menghantar e-mel. Sila cuba lagi.' },
    deleteFileTitle: { en: 'Delete file?', bm: 'Padam fail?' },
    contentRequired: { en: 'Content is required', bm: 'Kandungan diperlukan' },
    validDateError: { en: 'Valid Until date cannot be earlier than Valid From date', bm: 'Tarikh Sah Sehingga tidak boleh lebih awal daripada tarikh Sah Dari' },
    warning: { en: 'Warning!', bm: 'Amaran!' },
    filesFailedToUpload: { en: 'Announcement saved but files failed to upload.', bm: 'Pengumuman disimpan tetapi fail gagal dimuat naik.' },
    failedToFetchGallery: { en: 'Failed to fetch gallery details', bm: 'Gagal mendapatkan butiran galeri' },
    nameRequired: { en: 'Name is required', bm: 'Nama diperlukan' },
    orderDeletedSuccess: { en: 'Order deleted successfully', bm: 'Pesanan berjaya dipadam' },
    qrFailed: { en: 'QR Generation Failed', bm: 'Penjanaan QR Gagal' },
    qrUnable: { en: 'Unable to generate QR code for this permit number.', bm: 'Tidak dapat menjana kod QR untuk nombor permit ini.' },
    "Announcement created successfully.": { en: 'Announcement created successfully.', bm: 'Pengumuman berjaya dicipta.' },
    "Announcement updated successfully.": { en: 'Announcement updated successfully.', bm: 'Pengumuman berjaya dikemas kini.' },
    "Announcement deleted successfully.": { en: 'Announcement deleted successfully.', bm: 'Pengumuman berjaya dipadam.' },
    "Announcement pinned successfully.": { en: 'Announcement pinned successfully.', bm: 'Pengumuman berjaya disemat.' },
    "Announcement unpinned successfully.": { en: 'Announcement unpinned successfully.', bm: 'Semat pengumuman berjaya dibuang.' },
    "Gallery created successfully.": { en: 'Gallery created successfully.', bm: 'Galeri berjaya dicipta.' },
    "Gallery updated successfully.": { en: 'Gallery updated successfully.', bm: 'Galeri berjaya dikemas kini.' },
    "Gallery deleted successfully.": { en: 'Gallery deleted successfully.', bm: 'Galeri berjaya dipadam.' },
    "Order deleted successfully": { en: 'Order deleted successfully', bm: 'Pesanan berjaya dipadam' }
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}

import QRCode from "qrcode";
import { autoInitFilterSelect2 } from "../../utils/select2Utils";

console.log("order list");

let orderListTable;
let permitQrModal;
let isOrderListRefreshing = false;
let hasQrRealtimeListener = false;

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

        drawCallback: () => applyTranslations(document.body),
        columns: [
            { data: "order_number", title: "Order Number" },
            {
                data: "permit_number",
                title: "Permit Number",
                render: function (data, type) {
                    const permitNumber = data || "-";

                    if (type !== "display") {
                        return permitNumber;
                    }

                    return `<span title="${escapeHtml(permitNumber)}">${escapeHtml(permitNumber)}</span>`;
                },
            },
            { data: "status", title: "Status" },
            { data: "application_type", title: "Application Type" },
            ...(isInternal ? [{ data: "kod_transaksi", title: "Transaction Code" }] : []),
            { data: "payment_amount", title: "Amount" },
            { data: "created_at", title: "Date" },
            { data: "action", title: "Action", orderable: false, searchable: false },
        ],

        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [],
    });

    orderListTable.on("draw.dt", function () {
        initTooltips();
    });

    // Init Select2 on all static filter selects (those with class 'select2')
    autoInitFilterSelect2();

    const permitQrModalElement = document.getElementById("permitQrModal");
    if (permitQrModalElement && window.bootstrap?.Modal) {
        permitQrModal = new window.bootstrap.Modal(permitQrModalElement);
    }

    // Apply Filters
    $("#btnOrderFilter").on("click", function (e) {
        e.preventDefault();

        orderFilters.status = [].concat($("#filterOrderStatus").val() || []).join(",");
        orderFilters.applicationType = [].concat($("#filterAppType").val() || []).join(",");
        orderFilters.startDate = $("#filterOrderStartDate").val() || "";
        orderFilters.endDate = $("#filterOrderEndDate").val() || "";

        orderListTable.ajax.reload();
    });

    // Reset Filters
    $("#btnResetOrderFilter").on("click", function (e) {
        e.preventDefault();

        $('#filterOrderStatus').val(null).trigger('change');
        $('#filterAppType').val(null).trigger('change');
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
            title: getText("areYouSure"),
            text: getText("cannotRevert"),
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#4c5359ff",
            cancelButtonColor: "#d33",
            confirmButtonText: getText("yesDeleteIt"),
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

        Swal.fire(getText("deleted"), getText("orderDeletedSuccess"), "success");
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
            let qrPayload = permitNumber;

            // Internal users get encrypted QR payload, public users can still view plain QR.
            if (isInternal) {
                const encryptedEndpoint = window.ENCRYPTED_QR_PAYLOAD_URL;
                if (!encryptedEndpoint) {
                    throw new Error("Encrypted QR endpoint is not configured.");
                }

                const response = await fetch(
                    `${encryptedEndpoint}?permit_number=${encodeURIComponent(permitNumber)}`,
                    {
                        headers: {
                            Accept: "application/json",
                        },
                    },
                );

                if (!response.ok) {
                    throw new Error("Failed to get encrypted QR payload.");
                }

                const payload = await response.json();
                if (payload?.status !== "success" || !payload?.payload) {
                    throw new Error(
                        payload?.message ||
                            "Invalid encrypted QR payload response.",
                    );
                }

                qrPayload = payload.payload;
            }

            const qrDataUrl = await QRCode.toDataURL(qrPayload, {
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
                title: getText("qrFailed"),
                text: getText("qrUnable"),
            });
        }
    });

    initTooltips();

    if (isInternal) {
        setupQrRealtimeListener();
    }
}

document.addEventListener("DOMContentLoaded", data_table_init);
