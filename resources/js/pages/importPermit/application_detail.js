import Dropzone from "dropzone";
import $ from "jquery";
import Swal from "sweetalert2";
import { formatTime, getCountry, getEntryPoint } from "../../app";


import "dropzone/dist/dropzone.css";
// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";
import { activityLogDesign } from "../../appLog";


Dropzone.autoDiscover = false;


let itemDropzone = null;


let tempItems = [];
let tempAttachments = [];
let itemPurpose = null;
let temporaryItemsAttachment = [];
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

        console.log('permit status', permit.status)
        let permitAction = "";
        if (applicationStatus === "Clerk Verified") {
            if (
                (permit.status === "processing" || permit.status === "reapplied" ) &&
                (roles.includes("admin") || roles.includes("officer") || roles.includes("superadmin"))
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

        // if (applicationStatus === "Officer Verification Completed") {
        // if (permit.status === "paid") {
        // permitAction = `
        // <div class="btn btn-sm btn btn-teal-light btn-wave generatePermit" data-permit="${permit.id}">
        // Download Permit
        // </div>
        // `;
        // }
        // }
        let count = ``;
        if(permit.print_calc > 0) {
            count =  `<span class = "badge ms-2 bg-success" >${permit.print_calc}</span>`
        } 

        if (permit.status === "paid" && (roles.includes("admin") || roles.includes("officer") || roles.includes("superadmin") || roles.includes('boundary officer'))) {
            permitAction = `
<div class="btn btn-sm btn btn-teal-light btn-wave generatePermit" data-permit="${permit.id}">
    Download Permit ${count}
</div>
`;
        }

        let permitStatus = "";

        let statuses = permit.status;
        let text = "";

        const s = statuses.toLowerCase();

        if (s.includes("completed")) {
            text = '<span class="badge bg-success fs-11 p-1">Completed</span>';

        }else if (s.includes("payment failed")) {
            text = '<span class="badge bg-danger fs-11 p-1">Payment Failed</span>';

        } 
        
        else if (s.includes("payment processing")) {
            text = '<span class="badge bg-info fs-11 p-1">Payment Processing</span>';

        } else if (s.includes("paid")) {
            text = '<span class="badge bg-success fs-11 p-1">Paid</span>';

        } else if (s.includes("processing")) {
            text = '<span class="badge bg-info fs-11 p-1">Processing</span>';

        } else if (s.includes("rejected")) {
            text = '<span class="badge bg-danger fs-11 p-1">Rejected</span>';

        } else if (s.includes("payment")) {
            text = '<span class="badge bg-warning fs-11 p-1">Pending For Payment</span>';

        } else if (s.includes("reapplied")) {
            text = '<span class="badge bg-info fs-11 p-1">Reapply</span>';

        }


        permitStatus = `<td>${text}</td>`;

        tableBody.append(`
<tr>
    <td class = "text-wrap">${detail.item_name ?? "—"}</td>

    <td class="text-wrap">${detail.purpose ?? "—"}</td>

    <td>${permit.permit_number ?? "—"}</td>

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

    const pendingPaymentPermits = permits.filter((p) =>
        ["pending for payment", "payment failed", "failed payment"].includes(
            p.status?.toLowerCase()
        )
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
                        data-permit-value="15"
                        ${permit.status?.includes('payment processing') ? 'disabled' : ''}
                    >
                </div>
            </td>
            <td>${permit.permit_number ?? '—'}</td>
            <td class = "text-wrap">${detail.item_name ?? '—'}</td>
            <td>RM 15</td>
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
                            // ✅ Show processing/loading Swal
                            Swal.fire({
                                title: "Processing...",
                                text: "Please wait while the permit is being accepted.",
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

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
                                    // Close loading Swal first
                                    Swal.close();

                                    // ✅ Show success Swal
                                    Swal.fire({
                                        icon: "success",
                                        title: "Accepted!",
                                        text: "The permit has been accepted.",
                                        timer: 2000,
                                        showConfirmButton: false,
                                    });

                                    // Refresh table or UI
                                    initApplicationDetails();
                                },
                                error: function (err) {
                                    Swal.close();

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

                // ✅ Show processing/loading Swal
                Swal.fire({
                    title: "Processing...",
                    text: "Please wait while the permit is being rejected.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    url: `/internal/permit/${id}`,
                    method: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        rejected: 1,
                        reason: result.value, // 👈 SEND REASON
                    },
                    success: function () {
                        // Close loading Swal first
                        Swal.close();

                        // ✅ Show success Swal
                        Swal.fire({
                            icon: "success",
                            title: "Rejected!",
                            text: "The permit has been rejected successfully.",
                            timer: 2000,
                            showConfirmButton: false,
                        });

                        // Refresh table or UI
                        initApplicationDetails();
                    },
                    error: function (err) {
                        // Close loading Swal first
                        Swal.close();

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

            $.ajax({
                url: `/permit/print`, // your route
                method: "POST",
                data: {
                    _token: $("meta[name='csrf-token']").attr("content"), // CSRF token
                    type: "Import Permit",
                    permit_number: id
                },
                success: function (res) {
                    console.log('response', res)

                    if(res.message == 'Need Response') {
                        Swal.fire({
                            title: "This Permit has been downloaded more than once",
                            text: "Please provide a reason for download it:",
                            icon: "warning",
                            input: "textarea",
                            inputPlaceholder: "Enter reason...",
                            showCancelButton: true,
                            confirmButtonText: "Submit",
                            cancelButtonText: "Cancel",
                            didOpen: () => {
                                const textarea = Swal.getInput();
                                textarea.style.fontSize = "12px";
                                textarea.style.lineHeight = "1.5";
                            },
                            inputValidator: (value) => {
                                if (!value || value.trim().length < 5) {
                                    return "Reason is required (min 5 characters).";
                                }
                            },
                        }).then((result) => {
                            if (!result.isConfirmed) return;

                            // ✅ Show processing/loading Swal
                            Swal.fire({
                                title: "Processing...",
                                text: "Please wait.",
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            $.ajax({
                                url: `/permit/print`, // your route
                                method: "POST",
                                data: {
                                    _token: $("meta[name='csrf-token']").attr("content"), // CSRF token
                                    type: "Import Permit",
                                    permit_number: id,
                                    reason: result.value
                                },
                                success: function () {
                                    // Close loading Swal first
                                    Swal.close();

                                    // ✅ Show success Swal
                                    Swal.fire({
                                        icon: "success",
                                        title: "Rejected!",
                                        text: "The reason submitted successfully.",
                                        timer: 2000,
                                        showConfirmButton: false,
                                    });

                                    // Refresh table or UI
                                    initApplicationDetails();

                                    // ✅ Trigger browser download
                                    let url = `/permit/generate/${id}`;
                                    window.open(url, "_blank");
                    
                                },
                                error: function (err) {
                                    // Close loading Swal first
                                    Swal.close();

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
                    } else {
                        
                        // ✅ Trigger browser download
                        let url = `/permit/generate/${id}`;
                        window.open(url, "_blank");
                    }

                  
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
    <div class = "table-responsive scroll-div" style = "max-height: 250px;" >
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
                "Import Permit Application Log" || "Activity Log";

            const cardBody = $('#activityLogModal .modal-body');
            cardBody.empty();
            cardBody.addClass('scroll-div');

            const html = activityLogDesign(activity_log);
            
            cardBody.html(html);
           
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
}
let totalPermit = 0;
// Function to sum selected permit values
function updateTotalValue() {
    let total = 0;
    $(".permit-checkbox:checked").each(function () {
        // Use .attr() to get the explicit value from the HTML attribute
        const value = parseFloat($(this).attr("data-permit-value")) || 0;
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


function reapply() {
    $(document)
        .off("click", ".reapply")
        .on("click", ".reapply", async function (e) {
            e.preventDefault();

            const id = $(this).data("permit");
            const permits = application.consignment_permits;
            const permit = permits.find(p => p.id == id);

            if (!permit) {
                console.warn("Permit not found!");
                return;
            }

            $('#saveBtn').data('id', id).attr('data-id', id);

            const detail = permit.consignment_detail;

            await loadConsignmentSelection(detail.item_id);

            // Show modal
            const modalEl = document.getElementById("addItemModal");
            const modal = new bootstrap.Modal(modalEl);

            modalEl.addEventListener(
                "shown.bs.modal",
                async () => {
                    
                    const $modal = $(modalEl);
                    itemConsigment($modal);

                    // Fill inputs
                    $modal.find('#itemValue').val(detail.value);
                    $modal.find('#itemQuantity').val(detail.quantity);

                    // PURPOSE (by data-description)
                    $modal.find('#itemPurpose option').each(function () {
                        if ($(this).data('description') === detail.purpose) {
                            $(this).prop('selected', true);
                        }
                    });
                    $modal.find('#itemPurpose').trigger('change');

                    // MEASUREMENT (by value)
                    $modal.find('#itemMeasure')
                        .val(detail.measure)
                        .trigger('change');

                    console.log('measure selected:', detail.measure);

                    // ✅ Load Uses AND select the value after data is loaded
                    const itemId = $('#itemSelect').val();
                    if (itemId) {
                        const $itemUses = $modal.find('#itemUses');

                        // Reset options
                        $itemUses.empty().append('<option value="">-- Select Uses --</option>');

                        try {
                            Swal.fire({
                                title: "Loading uses...",
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading(),
                            });

                            const res = await fetch(`/public/consignment_uses/${itemId}`);
                            const data = await res.json();
                            let uses = data.data ?? [];

                            // ✅ Remove duplicate uses
                            uses = [...new Set(uses)];

                            // Append unique options
                            uses.forEach((row) => {
                                $itemUses.append(`<option value="${row}">${row}</option>`);
                            });

                            // Initialize or refresh Select2
                            if ($itemUses.hasClass("select2-hidden-accessible")) {
                                $itemUses.trigger('change');
                            } else {
                                $itemUses.select2({
                                    width: "100%",
                                    placeholder: "-- Select Uses --",
                                    allowClear: true,
                                    dropdownParent: $modal,
                                });
                            }

                            // ✅ Auto-select the current use if available
                            if (detail.uses) {
                                $itemUses.val(detail.uses).trigger('change');
                            }

                            Swal.close();
                        } catch (err) {
                            console.error("Failed to load uses:", err);
                            Swal.close();
                        }
                    }

                    saveConsignmentAttachment();
                },
                { once: true }
            );

            modal.show();
        });
}


function itemConsigment($modal) {
    const dropzoneEl = $modal.find("#itemDropzone")[0];

    if (!dropzoneEl) {
        console.warn("itemDropzone not found");
        return;
    }

    if (dropzoneEl.dropzone) {
        dropzoneEl.dropzone.destroy();
    }

    itemDropzone = new Dropzone(dropzoneEl, {
        url: "/",
        autoProcessQueue: false,
        maxFilesize: 10,
        acceptedFiles: ".jpg,.jpeg,.png,.pdf",
        addRemoveLinks: true,
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]').content,
        },
        processing: function (file) {
            Swal.fire({
                title: "Uploading...",
                html: "Please wait while your file is being uploaded.",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
            groupPreview();
        },
        
    });

    itemDropzone.on("addedfile", function (file) {
        console.log('add file', itemDropzone)
        groupPreview();
    });
}


function groupPreview() {
    $(document).ready(function () {
        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        setTimeout(function () {
            const $dropzone = $("#itemDropzone");
            const $previews = $dropzone.find(".dz-preview");
            const $deleteBtns = $previews.find(".dz-remove");

            // Create group if it doesn't exist
            let $group = $dropzone.find(".dz-preview-group");
            if ($group.length === 0) {
                $group = $('<div class="dz-preview-group"></div>');
                $dropzone.find(".dz-message").after($group);
            }

            // Move all previews into the group
            $previews.appendTo($group);

            console.log('item dropzone', itemDropzone)

            // Replace PDF previews with PDF logo
            for (const file of itemDropzone.getAcceptedFiles()) {
                if (file.type === "application/pdf") {
                    const $preview = $(file.previewElement);
                    const $img = $preview.find(
                        ".dz-image img[data-dz-thumbnail]"
                    );

                    // Set your PDF logo path
                    $img.attr(
                        "src",
                        "/images/pdf-logo.png" // <-- replace with your actual PDF logo path
                    );
                    $img.css({
                        "object-fit": "contain",
                        width: "100%",
                        height: "100%",
                    });
                }
            }

            // Update delete buttons
            $deleteBtns.html('<i class="ti ti-trash"></i>');

            Swal.close();
        }, 100);
    });
}
let updateItem;
function saveConsignmentAttachment() {
    $(document)
        .off("click", "#saveBtn")
        .on("click", "#saveBtn", function (e) {
            e.preventDefault();

            const $modal = $("#addItemModal");
            const id = $(this).data("id");

            const itemSelectValue = $modal.find("#itemSelect").val();
            const itemSelectText  = $modal.find("#itemSelect option:selected").text();
            const itemValue       = $modal.find("#itemValue").val().trim();
            const itemQuantity    = $modal.find("#itemQuantity").val().trim();
            const itemMeasure     = $modal.find("#itemMeasure").val();
            const itemPurpose     = $modal.find("#itemPurpose  option:selected").text();
            const itemUsesValue   = $modal.find("#itemUses").val();

            if (
                !itemSelectValue ||
                !itemValue ||
                !itemQuantity ||
                !itemMeasure ||
                !itemPurpose ||
                !itemUsesValue
            ) {
                Swal.fire("Error", "Please fill all required fields", "error");
                return;
            }

            const files = itemDropzone?.getAcceptedFiles() || [];

      
            updateItem = {
                item_id: itemSelectValue,
                item_name: itemSelectText,
                value: itemValue,
                quantity: itemQuantity,
                measure: itemMeasure,
                purpose: itemPurpose,
                uses: itemUsesValue,
                files,
            };

            console.log("updateItem", updateItem);

            saveapplication(id);
            resetAddItemModal();
            bootstrap.Modal.getInstance($modal[0]).hide();
        });
}


function resetAddItemModal() {
    // Reset plain input fields
    $("#itemValue").val("");
    $("#itemQuantity").val("");

    // Reset Select2 fields
    $("#itemSelect").val(null).trigger("change");
    $("#itemMeasure").val("").trigger("change");
    $("#itemPurpose").val("").trigger("change");
    $("#itemUses").val(null).trigger("change");

    // Clear Dropzone files
    if (itemDropzone) itemDropzone.removeAllFiles(true);
}




function loadUses(itemId, selectedUse = null) {
    const $select = $("#itemUses");

    // Destroy Select2 if already initialized
    if ($select.hasClass("select2-hidden-accessible")) {
        $select.select2('destroy');
    }

    $select.empty().append('<option value="">-- Select Uses --</option>');

    Swal.fire({
        title: "Loading uses...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    fetch(`/public/consignment_uses/${itemId}`)
        .then(res => res.json())
        .then(data => {
            const uses = data.data ?? [];
            
            // Append options
            uses.forEach(row => {
                $select.append(`<option value="${row}">${row}</option>`);
            });

            console.log('Loaded uses:', uses);

            // Re-initialize Select2
            $select.select2({
                width: "100%",
                placeholder: "-- Select Uses --",
                allowClear: true,
                dropdownParent: $("#addItemModal"),
            });

            // If there is a pre-selected use (from detail), select it
            if (selectedUse) {
                $select.val(selectedUse).trigger('change');
            }

            Swal.close();
        })
        .catch(err => {
            console.error("Failed to load uses:", err);
            Swal.close();
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

    acceptPermit();
    rejectPermit();
    generatePermit();
    pendingPaymentTable();
    saveConsignmentAttachment();

    // application_reapply(application)
    reapply()
    // groupPreview()
    // reapplyInput()


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

    let checkoutLocked = false;

    $(document).on("click", "#checkoutPage", function (e) {
        e.preventDefault();

        if (checkoutLocked) return; // 🚫 stop repeated click
        checkoutLocked = true;

        const $btn = $(this);
        $btn.prop("disabled", true).text("Processing...");

        const selectedPermits = $(".permit-checkbox:checked")
            .map(function () {
                return $(this).val();
            })
            .get();

        if (selectedPermits.length === 0) {
            Swal.fire("Error!", "Choose the permit to continue.", "error");
            checkoutLocked = false;
            $btn.prop("disabled", false).text("Checkout");
            return;
        }

        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        // Recalculate total just to be safe
        let calculatedTotal = 0;
        $(".permit-checkbox:checked").each(function () {
             const value = parseFloat($(this).attr("data-permit-value")) || 0;
             calculatedTotal += value;
        });

        const finalTotal = Number(calculatedTotal).toFixed(2);

        $.ajax({
            url: "/payment/signed-url",
            method: "POST",
            data: {
                application_id: application.id,
                permit_ids: selectedPermits,
                total: finalTotal,
                type: "import_permit",
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                window.location.href = res.url; // 🔀 redirect
            },
            error: function () {
                Swal.fire("Error!", "Unable to proceed to checkout.", "error");

                // 🔓 unlock if failed
                checkoutLocked = false;
                $btn.prop("disabled", false).text("Checkout");
            },
        });
    });


}

function saveapplication(permitId) {
    if (!updateItem) {
        Swal.fire("Error", "No item to save", "error");
        return;
    }

    const form = document.querySelector("#wizardForm");
    if (!form) return console.error("Form not found");

    const formData = new FormData(form);

    const { files, ...otherData } = updateItem;

    // ✅ single item (index 0)
    formData.append("items[0][data]", JSON.stringify(otherData));

    if (files && files.length > 0) {
        files.forEach((file) => {
            formData.append("files[]", file);
            formData.append("file_item_index[]", 0);
        });
    }

    Swal.fire({
        title: "Submitting...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: "/public/save-permit/" + permitId,
        type: "POST",
        data: formData,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        processData: false,
        contentType: false,
        success: function () {
            Swal.fire({
                icon: "success",
                title: "Permit Reapply!",
                timer: 1500,
                showConfirmButton: false,
            });

            initApplicationDetails();
        },
        error: function () {
            Swal.fire("Error", "Failed to save permit", "error");
        },
    });
}



/* -------------------------------
    Run initializer
    -------------------------------- */
initApplicationDetails();

$(document).on("change", "#itemSelect", async function () {
    const itemId = $(this).val();
    const $modal = $("#addItemModal");
    const $itemUses = $modal.find("#itemUses");

    console.log('Selected item:', itemId);

    // Reset the uses dropdown
    $itemUses.empty().append('<option value="">-- Select Uses --</option>');

    if (!itemId) return;

    try {
        Swal.fire({
            title: "Loading uses...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        const res = await fetch(`/public/consignment_uses/${itemId}`);
        const data = await res.json();
        let uses = data.data ?? [];

       
        uses = [...new Set(uses)];

        // Append new options
        uses.forEach((row) => {
            $itemUses.append(`<option value="${row}">${row}</option>`);
        });

        console.log('Loaded uses (unique):', uses);

        // Re-init or refresh Select2
        if ($itemUses.hasClass("select2-hidden-accessible")) {
            $itemUses.trigger('change'); // refresh Select2 visually
        } else {
            $itemUses.select2({
                width: "100%",
                placeholder: "-- Select Uses --",
                allowClear: true,
                dropdownParent: $modal,
            });
        }

        Swal.close();
    } catch (err) {
        console.error("Failed to load uses:", err);
        Swal.close();
    }
});



async function loadConsignmentSelection(selectedItemId = null) {
    const countryCode = $("#expcountryCode").val();
    const $select = $("#itemSelect");

    if (!countryCode) return;

    $select.empty().append('<option value="">-- Select Item --</option>');

    if ($select.hasClass("select2-hidden-accessible")) {
        $select.select2("destroy");
    }

    $select.prop("disabled", true);

    Swal.fire({
        title: "Loading...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const res = await fetch(`/public/get_consignment/${countryCode}`);
        const data = await res.json();

        $select.prop("disabled", false);

        data.forEach(row => {
            $select.append(
                `<option value="${row.id}">${row.entry_display}</option>`
            );
        });

        $select.select2({
            width: "100%",
            placeholder: "-- Select Item --",
            allowClear: true,
            dropdownParent: $("#addItemModal"),
        });

        // ✅ AUTO SELECT + TRIGGER CHANGE
        if (selectedItemId) {
            $select
                .val(String(selectedItemId))
                .trigger('change'); // 🔥 THIS is the key
        }

        Swal.close();
    } catch (e) {
        console.error("Error loading items:", e);
        $select.prop("disabled", false);
        Swal.close();
    }
}
