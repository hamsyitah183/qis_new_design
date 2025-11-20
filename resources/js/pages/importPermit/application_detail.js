import $ from "jquery";
import Swal from "sweetalert2";
import { getCountry, getEntryPoint } from "../../app";
let application = null;

/* -------------------------------
   Get application ID from URL
-------------------------------- */
function getApplicationIdFromUrl() {
    const url = window.location.pathname;
    const parts = url.split("/");
    return parts[2];
}

/* -------------------------------
   Load application data
-------------------------------- */
async function loadApplicationData() {
    const applicationId = getApplicationIdFromUrl();

    const res = await fetch(`/application/${applicationId}/data`);
    const json = await res.json();

    application = json;

    console.log("application", application);
}

async function fillInInput() {
    const country = await getCountry(application.exporter.country);
    const entryPoint = await getEntryPoint(application.entry_point.id);

    console.log("country", country.name);
    console.log("entry", entryPoint.entry_name);

    // Example: if returned JSON is { name: "Malaysia" }
    $("#expcountry").val(country.name);
    $("#sexpCountry").text(country.name);

    $("#entryPoint").val(entryPoint.entry_name);
    $("#sentryp").text(entryPoint.entry_name);
}

async function attachmentTable() {
    const tableBody = $("#summaryTable3 tbody");
    tableBody.empty(); // clear existing rows

    const permits = application.consignment_permits;

    if (!permits || permits.length === 0) {
        tableBody.append(`
            <tr>
                <td colspan="7" class="text-center text-muted">
                    No consignment items found.
                </td>
            </tr>
        `);
        return;
    }

    permits.forEach((permit, index) => {
        // Parse consignment_detail JSON
        let detail = {};
        try {
            detail = JSON.parse(permit.consignment_detail);
        } catch (e) {
            console.error(
                "Invalid JSON in consignment_detail:",
                permit.consignment_detail
            );
        }

        // 👉 Count attachments
        let attachmentCount = 0;
        if (permit.attachments && permit.attachments.length) {
            attachmentCount = permit.attachments.length;
        }

        tableBody.append(`
            <tr>

                <td>${detail.item_name ?? "—"}</td>

                <td>${detail.quantity ?? "—"} ${detail.measure ?? ""}</td>

                <td>${detail.uses ?? "—"}</td>

                <td>RM ${detail.value ?? "—"}</td>

                <td>
                    <div class = "btn btn-sm btn-primary-light btn-wave view-attachment" data-permit = "${
                        permit.id
                    }">
                        ${attachmentCount} attachment(s)
                    </div>
                </td>

                
            </tr>
        `);
    });
}

async function viewMore() {
    $(document).on("click", ".view-attachment", function (e) {
        e.preventDefault();

        let id = $(this).data("permit");
        let permits = application.consignment_permits;

        let permit = permits.find((p) => p.id == id);

        if (!permit) {
            console.warn("Permit not found!");
            return;
        }

        let attachments = permit.attachments || [];

        let detail = {};
        try {
            detail = JSON.parse(permit.consignment_detail);
        } catch (err) {
            console.error(
                "Invalid JSON in consignment_detail:",
                permit.consignment_detail
            );
        }

        console.log("FOUND PERMIT:", permit);
        console.log("attachments", attachments);

        // Build attachment table
        let attachmentContent = `
            <table class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        attachments.forEach((file) => {
            attachmentContent += `
                <tr>
                    <td>${file.file_name ?? "-"}</td>
                    <td>${file.file_type ?? "-"}</td>
                    <td>${file.description ?? "-"}</td>
                    <td>
                        <button class="btn btn-sm btn-primary view-file-btn" data-file="${
                            file.file_path
                        }">
                            View
                        </button>
                    </td>
                </tr>
            `;
        });

        attachmentContent += `
                </tbody>
            </table>
        `;

        // Build modal body
        let modalContent = `
            <div class="p-1">
                <p><strong>Item Name:</strong> ${detail.item_name ?? "-"}</p>
                <p><strong>Quantity:</strong> ${detail.quantity ?? "-"} ${
            detail.measure ?? ""
        }</p>
                <p><strong>Value:</strong> RM ${detail.value ?? "-"}</p>
                <p><strong>Purpose:</strong> ${detail.purpose ?? "-"}</p>
                <p><strong>Uses:</strong> ${detail.uses ?? "-"}</p>

                <p class="mt-3"><strong>Attachment(s)</strong></p>
                ${attachmentContent}
            </div>
        `;

        // Modal
        const modalEl = document.getElementById("consignmentModal");
        modalEl.querySelector(".modal-title").textContent =
            detail.item_name || "Consignment Details";

        modalEl.querySelector(".modal-body").innerHTML = modalContent;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
}

function viewAttachment() {
    $(document).on('click', '.view-file-btn', function (e) {
        e.preventDefault();

        let path = $(this).data('file');

        if (!path) {
            console.warn("No file path found!");
            return;
        }

        // Open in a new tab
        window.open(path, "_blank");
    });
}


/* -------------------------------
   Initializer (shows Swal first)
-------------------------------- */
async function initApplicationDetails() {
    Swal.fire({
        title: "Loading...",
        text: "Please wait while we fetch the application details.",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    await loadApplicationData();
    await fillInInput();
    await attachmentTable();

    viewMore();
    viewAttachment();

    Swal.close(); // Close after data is loaded
}

/* -------------------------------
   Run initializer
-------------------------------- */
initApplicationDetails();
