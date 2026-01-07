import $ from "jquery";
import Swal from "sweetalert2";
import { formatTime, getCountry, getEntryPoint } from "../../app";
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
    const country = application.exporter.country_info ?? "";
    const entryPoint = application.entry_point ?? "";

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
        let detail;
        try {
            // detail = JSON.parse(permit.consignment_detail);
            detail = permit.consignment_detail;
            console.log("detail", detail);
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

        let detail;
        try {
            // detail = JSON.parse(permit.consignment_detail);
            detail = permit.consignment_detail;
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
        <div class = "table-responsive">
            <table class="table table-bordered table-responsive rounded">
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
            </div>
        `;

        // Build modal body
        let modalContent = `
            <div class="p-1 row">
                <div class = "col-12 col-md-6"><p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-tag"></i></span> Item Name:</strong> ${
                    detail.item_name ?? "-"
                }</p></div>
                <div class = "col-12 col-md-6"><p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-scale-balanced"></i></span> Quantity:</strong> ${
                    detail.quantity ?? "-"
                } ${detail.measure ?? ""}</p></div>
                <div class = "col-12 col-md-6"><p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-money-bill"></i></span> Value:</strong> RM ${
                    detail.value ?? "-"
                }</p></div>
                <div class = "col-12 col-md-6"><p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-pen-fancy"></i></span> Purpose:</strong> ${
                    detail.purpose ?? "-"
                }</p></div>
                <div class = "col-12 col-md-6"><p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-gear"></i></span> Uses:</strong> ${
                    detail.uses ?? "-"
                }</p></div>

                <p class="mt-3"><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i class="fa-solid fa-file"></i></span> Attachment(s)</strong></p>
                ${attachmentContent}
            </div>
        `;

        // Modal
        const modalEl = document.getElementById("consignmentModal");
        let titleHTML = ``;
        modalEl.querySelector(".modal-title").innerHTML =
            titleHTML + ` ` + detail.item_name || "Consignment Details";

        modalEl.querySelector(".modal-body").innerHTML = modalContent;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
}

function viewAttachment() {
    $(document).on("click", ".view-file-btn", function (e) {
        e.preventDefault();

        let path = $(this).data("file");

        if (!path) {
            console.warn("No file path found!");
            return;
        }

        // Open in a new tab
        window.open(path, "_blank");
    });
}

// verify application
function verifyApplication() {
    $("#verifyAppl").on("click", function (e) {
        e.preventDefault();

        let applicationId = application.application_id;

        Swal.fire({
            title: "Verify Application?",
            text: "Are you sure you want to verify this application?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, verify it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request to verify application
                $.ajax({
                    url: `/application/verify/${applicationId}`, // your route
                    method: "POST",
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"), // CSRF token
                        verified: 1,
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: "success",
                            title: "Application Verified!",
                            text:
                                res.message ||
                                "The application has been successfully verified.",
                            // timer: 1800,
                            showConfirmButton: false,
                            // timerProgressBar: true,
                            position: "center",
                        });
                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text:
                                err.responseJSON?.message ||
                                "Something went wrong.",
                        });
                    },
                });
            }
        });
    });
}

// reject application
function rejectApplication() {
    $("#rejectAppl").on("click", function (e) {
        e.preventDefault();

        let applicationId = application.application_id;

        Swal.fire({
            title: "Reject Application?",
            text: "Are you sure you want to reject this application?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, reject it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request to verify application
                $.ajax({
                    url: `/application/verify/${applicationId}`, // your route
                    method: "POST",
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"), // CSRF token
                        not_verified: 1,
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: "success",
                            title: "Application Not Approved!",
                            text: "The application has been successfully not verified.",
                            // timer: 1800,
                            showConfirmButton: false,
                            // timerProgressBar: true,
                            position: "center",
                        });

                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text:
                                err.responseJSON?.message ||
                                "Something went wrong.",
                        });
                    },
                });
            }
        });
    });
}
function adminRejectApplication() {
    $("#rejectAdminAppl").on("click", function (e) {
        e.preventDefault();

        let applicationId = application.application_id;

        Swal.fire({
            title: "Reject Application",
            html: `
                <p class="mb-2">Please provide a reason for rejection:</p>
                <textarea id="rejectReason"
                    class="swal2-textarea"
                    placeholder="Enter rejection reason..."></textarea>
            `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm",
            cancelButtonText: "Cancel",
            focusConfirm: false,

            preConfirm: () => {
                const reason = document.getElementById("rejectReason").value;

                if (!reason.trim()) {
                    Swal.showValidationMessage("Rejection reason is required");
                    return false;
                }

                return reason;
            },
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/application/verify/${applicationId}`,
                    method: "POST",
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"),
                        rejected: 1,
                        reason: result.value, // 🔥 send reason
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: "success",
                            title: "Application Rejected!",
                            text:
                            
                                "The application has been rejected.",
                            showConfirmButton: false,
                            timer: 2000,
                        });
                        window.location.reload();
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text:
                                err.responseJSON?.message ||
                                "Something went wrong.",
                        });
                    },
                });
            }
        });
    });
}

function acceptApplication() {
    $("#acceptAppl").on("click", function (e) {
        e.preventDefault();

        let applicationId = application.application_id;
        let accepted = 1;

        Swal.fire({
            title: "Accept Application?",
            text: "Are you sure you want to accept this application?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, accept it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request to verify application
                $.ajax({
                    url: `/application/verify/${applicationId}`, // your route
                    method: "POST",
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"), // CSRF token
                        accepted: accepted,
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: "success",
                            title: "Application Accepted!",
                            text:
                                res.message ||
                                "The application has been successfully accepted.",
                            // timer: 1800,
                            showConfirmButton: false,
                            // timerProgressBar: true,
                            position: "center",
                        });
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text:
                                err.responseJSON?.message ||
                                "Something went wrong.",
                        });
                    },
                });
            }
        });
    });
}

// application log
function applicationLog() {
    $("#applicationModal")
        .off("click")
        .on("click", function (e) {
            e.preventDefault();

            const tableBody = $("#applicationLogTable tbody");
            tableBody.empty(); // clear existing rows

            console.log("application", application.activity_log);
            let activity_log = application.activity_log;

            const modalEl = document.getElementById("activityLogModal");
            modalEl.querySelector(".modal-title").textContent =
                " Activity Log" || "Activity Log";

            activity_log.forEach((log, index) => {
                tableBody.append(`
                    <tr>
                        <td>${log.action}</td>
                        <td>${log.causer.fullname}</td>
                        <td>${log.remark}</td>
                        <td>${log.status}</td>
                        <td>${formatTime(log.created_at)}</td>
                    </tr>
                `);
            });

            const modal = new bootstrap.Modal(modalEl);
            modal.show();
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

    verifyApplication();
    rejectApplication();
    acceptApplication();
    adminRejectApplication();

    applicationLog();

    Swal.close(); // Close after data is loaded
}

/* -------------------------------
   Run initializer
-------------------------------- */
initApplicationDetails();
