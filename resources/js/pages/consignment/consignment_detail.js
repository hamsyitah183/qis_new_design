import $ from "jquery";
import Swal from "sweetalert2";
import { formatTime, getCountry, getEntryPoint } from "../../app";

let application = null;
let value = null;

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

    const res = await fetch(`/consignment_application/${applicationId}/data`);
    const json = await res.json();

    application = json;

    console.log("application", application);
}

async function fillInInput() {
    const country = application.importer_detail.country_info ?? "";
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
    console.log("Running attachment table...");
    const tableBody = $("#summaryTable3 tbody");
    tableBody.empty();

    const permits = application.consignment_permits;
    const applicationStatus = application.status;

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
        let detail = permit.consignment_detail || {};
        let attachmentCount = permit.attachments?.length || 0;

        console.log("user role?", window.authUser);

        let roles = window.authUser.roles.map((role) => role.name);
        let type = window.authUser.type;
        console.log("is it", roles);

        let permitAction = "";
        if (applicationStatus === "Clerk Verified") {
            if (
                permit.status === "processing" &&
                (roles.includes("admin") || roles.includes("officer"))
            ) {
                permitAction = `
                <div class="btn btn-sm btn-primary-light btn-wave accept" data-permit="${permit.id}">
                    Approved
                </div>
                <div class="btn btn-sm btn-danger-light btn-wave reject" data-permit="${permit.id}">
                    Rejected
                </div>`;
            } 
           
        }

        if( permit.status === "rejected" &&
            (type.includes('public'))) {
            permitAction = `<div class = "btn btn-sm btn-danger-light btn-wave reapply"  data-permit = "${permit.id}" >Reapply</div>`
        }

        // if (applicationStatus === "Fully Processed") {
        // if (permit.status === "paid") {
        // permitAction = `
        // <div class="btn btn-sm btn btn-teal-light btn-wave generatePermit" data-permit="${permit.id}">
        // Download Permit
        // </div>
        // `;
        // }
        // }

        if (permit.status === "paid") {
            permitAction = `
<div class="btn btn-sm btn btn-teal-light btn-wave generatePermit" data-permit="${permit.id}">
    Download Permit
</div>
`;
        }

        let permitStatus = "";

        let statuses = permit.status;
        let text = "";

        const s = statuses.toLowerCase();

        if (s.includes("completed")) {
            text = '<span class="badge bg-success fs-11 p-1">Completed</span>';

        } else if (s.includes("payment processing")) {
            text = '<span class="badge bg-info fs-11 p-1">Payment Processing</span>';

        } else if (s.includes("paid")) {
            text = '<span class="badge bg-success fs-11 p-1">Paid</span>';

        } else if (s.includes("processing")) {
            text = '<span class="badge bg-info fs-11 p-1">Processing</span>';

        } else if (s.includes("rejected")) {
            text = '<span class="badge bg-danger fs-11 p-1">Rejected</span>';

        } else if (s.includes("payment")) {
            text = '<span class="badge bg-warning fs-11 p-1">Pending For Payment</span>';
        }

        permitStatus = `<td>${text}</td>`;

        tableBody.append(`
<tr>
    <td class = "text-wrap">${detail.item_name ?? "—"}</td>
    <td>${detail.quantity ?? "—"} ${detail.measure ?? ""}</td>
    <td class="text-wrap">${detail.purpose ?? "—"}</td>
    <td>RM ${detail.value ?? "—"}</td>
    ${permitStatus}
    <td>
        <div class="d-flex gap-2 align-items-center">
            <div class="btn btn-sm btn-success-light btn-wave view-attachment"
                data-permit="${permit.id}">
                <i class="ti ti-eye"></i>
            </div>
            ${permitAction}
        </div>
    </td>
</tr>
`);
    });
}
async function pendingPaymentTable() {
    console.log("Running attachment table...");
    const tableBody = $("#summaryTable4 tbody");
    tableBody.empty();

    const permits = application.consignment_permits || [];

    const pendingPaymentPermits = permits.filter(
        (p) => p.status?.toLowerCase() === "pending for payment"
    );

    if (!pendingPaymentPermits || pendingPaymentPermits.length === 0) {
        tableBody.append(`
<tr>
    <td colspan="7" class="text-center text-muted">
        No consignment items found.
    </td>
</tr>
`);
        return;
    }

    pendingPaymentPermits.forEach((permit, index) => {
        let detail = permit.consignment_detail || {};

   

        tableBody.append(`
        <tr>
            <td>
                <div class="form-check">
                    <input
                        class="form-check-input permit-checkbox"
                        type="checkbox"
                        value="${permit.id}"
                        data-permit-value="30"
                        ${permit.status?.includes('payment processing') ? 'disabled' : ''}
                    >
                </div>
            </td>
            <td>${permit.permit_number ?? '—'}</td>
            <td class = "text-wrap">${detail.item_name ?? '—'}</td>
            <td>RM 30</td>
        </tr>
        `);

    });

    $("#checkAllPermits").prop("checked", false);
}

function acceptPermit() {
    $(document)
        .off("click", ".accept")
        .on("click", ".accept", function (e) {
            e.preventDefault();
            const id = $(this).data("permit");

            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to accept this permit?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, proceed",
                cancelButtonText: "Cancel",
            }).then((firstResult) => {
                if (firstResult.isConfirmed) {
                    Swal.fire({
                        title: "Please Confirm Again",
                        text: "This action cannot be undone. Accept the permit?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, accept it",
                        cancelButtonText: "Cancel",
                    }).then((secondResult) => {
                        if (secondResult.isConfirmed) {
                            $.ajax({
                                url: `/internal/permit/${id}`,
                                method: "POST",
                                data: {
                                    _token: $("meta[name='csrf-token']").attr(
                                        "content"
                                    ),
                                    accepted: 1,
                                },
                                success: function () {
                                    Swal.fire(
                                        "Accepted!",
                                        "The permit has been accepted.",
                                        "success"
                                    );
                                    // Refresh table
                                    initApplicationDetails();
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
                }
            });
        });
}

function rejectPermit() {
    $(document)
        .off("click", ".reject")
        .on("click", ".reject", function (e) {
            e.preventDefault();

            const id = $(this).data("permit");

            Swal.fire({
                title: "Reject Permit",
                text: "Please provide a reason for rejecting this permit:",
                icon: "warning",
                input: "textarea",
                inputPlaceholder: "Enter rejection reason...",
                showCancelButton: true,
                confirmButtonText: "Reject Permit",
                cancelButtonText: "Cancel",
                didOpen: () => {
                    const textarea = Swal.getInput();
                    textarea.style.fontSize = "12px";
                    textarea.style.lineHeight = "1.5";
                },
                inputValidator: (value) => {
                    if (!value || value.trim().length < 5) {
                        return "Rejection reason is required (min 5 characters).";
                    }
                },
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/internal/permit/${id}`,
                    method: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        rejected: 1,
                        reason: result.value, // 👈 SEND REASON
                    },
                    success: function () {
                        Swal.fire(
                            "Rejected!",
                            "The permit has been rejected successfully.",
                            "success"
                        );
                        initApplicationDetails();
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
            });
        });
}

function generatePermit() {
    $(document)
        .off("click", ".generatePermit")
        .on("click", ".generatePermit", function (e) {
            e.preventDefault();

            const id = $(this).data("permit");

            // ✅ Trigger browser download
            window.location.href = `/permit/generate/${id}`;
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
    <div class = "table-responsive" style = "max-height: 250px;">
        <table class="table table-bordered table-responsive rounded">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Type</th>

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

                    <td>
                        <button class="btn btn-sm btn-primary view-file-btn"
                            data-file="${file.file_path}">
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
    <div class="d-flex justify-content-start flex-wrap gap-1 mb-2">
        ${renderPermitBadge(permit.status.toLowerCase(), permit.remark)}
    </div>


    <div class="p-1 row">
        <div class = "col-12 col-md-6">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-tag"></i></span> Item Name:</strong> ${
                                detail.item_name ?? "-"
                            }</p>
        </div>
        <div class = "col-12 col-md-6">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-scale-balanced"></i></span> Quantity:</strong> ${
                                detail.quantity ?? "-"
                            } ${detail.measure ?? ""}</p>
        </div>
        <div class = "col-12 col-md-6">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-money-bill"></i></span> Value:</strong> RM ${
                                detail.value ?? "-"
                            }</p>
        </div>
        <div class = "col-12 col-md-6">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-pen-fancy"></i></span> Purpose:</strong> ${
                                detail.purpose ?? "-"
                            }</p>
        </div>
        <div class = "col-12 col-md-6">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-gear"></i></span> Uses:</strong> ${
                                detail.uses ?? "-"
                            }</p>
        </div>
        <div class = "col-12 ">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-gear"></i></span> Certificate No (MyGAP or myOrganic): 
                            </strong> ${
                                permit.mygap_myorganic_no ?? "-"
                            }</p>
        </div>

        <p class="mt-3"><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                        class="fa-solid fa-file"></i></span> Attachment(s)</strong></p>
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

export function renderPermitBadge(status, remark = "") {
    let badgeClass = "";
    let label = "";
    let remarkHtml = "";

    switch (status) {
        case "processing":
            badgeClass = "bg-info";
            label = "Processing";
            break;

        case "pending for payment":
            badgeClass = "bg-warning";
            label = "Pending Payment";
            break;

        case "paid":
            badgeClass = "bg-success";
            label = "Paid";
            break;

        case "rejected":
            badgeClass = "bg-danger";
            label = "Rejected";

            if (remark) {
                remarkHtml = `
    <div class="mt-1">
        <strong class = "fs-12">Reason:</strong> <span class = "text-muted">${remark}</span>
    </div>
    `;
            }
            break;

        default:
            badgeClass = "bg-secondary";
            label = status ?? "Unknown";
    }

    return `
    <div>
        <span class="badge badge-md ${badgeClass}">
            ${label}
        </span>
        ${remarkHtml}
    </div>
    `;
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
    <textarea id="rejectReason" class="swal2-textarea" placeholder="Enter rejection reason..."></textarea>
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
                            text: "The application has been rejected.",
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
                        initApplicationDetails();
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
let totalPermit = 0;
// Function to sum selected permit values
function updateTotalValue() {
    let total = 0;
    $(".permit-checkbox:checked").each(function () {
        const value = parseFloat($(this).data("permit-value")) || 0;
        total += value;
    });

    // Update the totalValue element
    $("#totalValue").text(
        "RM " +
            total.toLocaleString("en-US", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
    );

    totalPermit = total;

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

    acceptPermit();
    rejectPermit();
    generatePermit();
    pendingPaymentTable();

    // application_reapply(application)


    Swal.close(); // Close after data is loaded

    // When "Check All" is toggled
    $(document).on("change", "#checkAllPermits", function () {
        const isChecked = $(this).is(":checked");

        // Toggle all row checkboxes
        $(".permit-checkbox").prop("checked", isChecked);

        // Enable/disable the checkout button
        $("#checkoutPage").prop("disabled", !isChecked);

        // Update total value
        updateTotalValue();
    });

    // When individual checkboxes are toggled
    $(document).on("change", ".permit-checkbox", function () {
        const total = $(".permit-checkbox").length;
        const checked = $(".permit-checkbox:checked").length;

        $("#checkoutPage").prop("disabled", checked === 0);
        $("#checkAllPermits").prop("checked", total > 0 && total === checked);

        // Update total value
        updateTotalValue();
    });

    // When checkout button is clicked
    $(document).on("click", "#checkoutPage", function (e) {
        e.preventDefault();

        const selectedPermits = $(".permit-checkbox:checked")
            .map(function () {
                return $(this).val();
            })
            .get();

        if (selectedPermits.length === 0) {
            Swal.fire("Error!", "Choose the permit to continue.", "error");
            return;
        }

        Swal.fire({
            title: "Loading...",

            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        totalPermit = Number(totalPermit).toFixed(2);

        $.ajax({
            url: "/payment/signed-url",
            method: "POST",
            data: {
                application_id: application.id,
                permit_ids: selectedPermits,
                total: totalPermit,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                window.location.href = res.url;
            },
            error: function () {
                Swal.fire("Error!", "Unable to proceed to checkout.", "error");
            },
        });
    });
}

/* -------------------------------
    Run initializer
    -------------------------------- */
initApplicationDetails();
