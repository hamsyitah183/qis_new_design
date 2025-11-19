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
    return parts[3];
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

async function fillInInput() 
{
    const country = await getCountry(application.exporter.country);
    const entryPoint = await getEntryPoint(application.entry_point.id);

    console.log('country', country.name)
    console.log('entry', entryPoint.entry_name);

    // Example: if returned JSON is { name: "Malaysia" }
    $('#expcountry').val(country.name);
    $('#sexpCountry').text(country.name)

    $('#entryPoint').val(entryPoint.entry_name);
    $('#sentryp').text(entryPoint.entry_name)
}

async function attachmentTable() 
{
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
            console.error("Invalid JSON in consignment_detail:", permit.consignment_detail);
        }

        // 👉 Count attachments
        let attachmentCount = 0;
        if (permit.attachments && permit.attachments.length) {
            attachmentCount = permit.attachments.length;
        }

        tableBody.append(`
            <tr>
                <td>${index + 1}</td>
                <td>${detail.item_name ?? "—"}</td>

                <td>${detail.quantity ?? "—"} ${detail.measure ?? ""}</td>

                <td>${detail.uses ?? "—"}</td>

                <td>RM ${detail.value ?? "—"}</td>

                <td>
                    <div class = "btn btn-sm btn-primary view-attachment" data-permit = "${permit.id}">
                        ${attachmentCount} attachment(s)
                    </div>
                </td>

                <td>
                    <a class="btn btn-sm btn-info">Edit Consignment</a><br>
                    <a class="btn btn-sm btn-danger mt-2">Remove</a>
                </td>
            </tr>
        `);
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
    await attachmentTable()

    Swal.close(); // Close after data is loaded
}



/* -------------------------------
   Run initializer
-------------------------------- */
initApplicationDetails();
