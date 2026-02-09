import $ from "jquery";
import Swal from "sweetalert2";
import { formatTime, getCountry, getEntryPoint } from "../../app";
import "dropzone/dist/dropzone.css";
// Import Select2 module
import select2 from "select2";

// Force Select2 to attach to THIS jQuery:
select2(window.jQuery);

import "select2/dist/css/select2.min.css";
// import { application_reapply } from "./application_reapply";
let application = null;
let value = null;
let selectedPermits = [];


Dropzone.autoDiscover = false;


let itemDropzone = null;
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

    console.log('allication id', applicationId)

    const res = await fetch(`/inspection_application/${applicationId}/data`);
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
    $("#expcountry").val(application.exporter.country_info.name);
    $("#sexpCountry").text(application.exporter.country_info.name);

    $("#entryPoint").val(entryPoint.entry_name);
    $("#sentryp").text(entryPoint.entry_name);
}

async function attachmentTable() {
    console.log("Running attachment table...");
    const tableBody = $("#summaryTable3 tbody");
    const table = $('#summaryTable3')
    tableBody.empty();

    const permits = application.inspection_items;
    const applicationStatus = application.status;
    let roles = window.authUser.roles.map((role) => role.name);

    // ❌ If any permit is rejected → block action
    const hasRejectedPermit = permits.some(p =>
        (p.status || '').toLowerCase() === 'rejected'
    );

    // ✅ Only allow if at least one permit is reapplied / processing
    const hasAllowedPermit = permits.some(p =>
        ['reapplied', 'processing'].includes((p.status || '').toLowerCase())
    );

    let buttonAction = "";

    if (
        applicationStatus === "Clerk Verified" &&
        !hasRejectedPermit &&
        hasAllowedPermit &&
        (roles.includes("admin") || roles.includes("officer") || roles.includes("superadmin"))
    ) {
        buttonAction = `
            <div class="d-flex align-items-center justify-content-between w-100 mt-2">
                <div class="btn btn-sm btn-primary-light btn-wave accept me-2"
                    data-application="${application.application_id}">
                    Approved
                </div>
                <div class="btn btn-sm btn-danger-light btn-wave reject"
                    data-application="${application.application_id}">
                    Rejected
                </div>
            </div>
        `;
    }
    else if( applicationStatus === "Completed" &&
  
        (roles.includes("admin") || roles.includes("boundary officer") || roles.includes("superadmin")))
    {
        buttonAction = `
        <div class="btn btn-sm btn btn-teal-light btn-wave generatePermit mt-2" 
        data-permit="${application.application_id}"  data-type = "${application.application_type}">
            Download Permit
        </div>
    `;
    }
    else {
        buttonAction = '';
    }

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

        let roles = window.authUser?.roles?.map((role) => role.name) || [];
        let type = window.authUser?.type || '';
        console.log("is it", roles);

        selectedPermits.push(permit.id)

        let permitAction = null;

        if (permit.status === "rejected" &&
            (type.includes('public'))  &&  (application.user.uuid == window.authUser.uuid) ) {
            permitAction = `<div class = "btn btn-sm btn-danger-light btn-wave reapply"  data-permit = "${permit.id}" >Reapply</div>`
        } else {
            permitAction= ``
        }

        let permitStatus = "";

        let statuses = permit.status;
        let text = "";

        const s = statuses.toLowerCase();

        if (s.includes("completed")) {
            text = '<span class="badge bg-success fs-11 p-1">Completed</span>';

        } else if (s.includes("payment failed")) {
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

    table.append(`${buttonAction}`)
}
async function pendingPaymentTable() {
    console.log("Running attachment table...");
    const tableBody = $("#summaryTable4 tbody");
    tableBody.empty();

    const permits = application.inspection_items || [];

    const pendingPaymentPermits = permits.filter((p) => {
        const s = p.status?.toLowerCase() || '';
        return s === "pending for payment" || s === "payment failed";
    });


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
           
            <td>${permit.permit_number ?? '—'}</td>
            <td class = "text-wrap">${detail.item_name ?? '—'}</td>
       
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
            // const id = $(this).data("permit");
            const id = $(this).data("application");
        

            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to accept all these inspection item?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, proceed",
                cancelButtonText: "Cancel",
            }).then((firstResult) => {
                if (firstResult.isConfirmed) {
                    Swal.fire({
                        title: "Please Confirm Again",
                        text: "This action cannot be undone. Accept all the inspection item?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, accept it",
                        cancelButtonText: "Cancel",
                    }).then((secondResult) => {
                        if (secondResult.isConfirmed) {
                            $.ajax({
                                url: `/internal/inspection_item/${id}/accept`,
                                method: "POST",
                                data: {
                                    _token: $("meta[name='csrf-token']").attr(
                                        "content"
                                    ),
                                },
                                success: function () {
                                    Swal.fire(
                                        "Accepted!",
                                        "The inspection item has been accepted.",
                                        "success"
                                    );
                                    // Refresh table
                                    initApplicationDetails();
                                    attachmentTable()
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

            // const id = $(this).data("permit");
            const id = $(this).data("application");

            Swal.fire({
                title: "Reject Inspection Item",
                text: "Please provide a reason for rejecting this inspection item:",
                icon: "warning",
                input: "textarea",
                inputPlaceholder: "Enter rejection reason...",
                showCancelButton: true,
                confirmButtonText: "Reject Item",
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
                    url: `/internal/inspection_item/${id}/reject`,
                    method: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        reason: result.value, // 👈 SEND REASON
                    },
                    success: function () {
                        Swal.fire(
                            "Rejected!",
                            "The inspection item has been rejected successfully.",
                            "success"
                        );
                        initApplicationDetails();
                        attachmentTable()
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
                window.location.href = `/inspection/generate/${id}`;
                Swal.close();
            }, 800);
        });
}


generatePermit();

async function viewMore() {
    $(document).on("click", ".view-attachment", function (e) {
        e.preventDefault();

        let id = $(this).data("permit");
        let permits = application.inspection_items;

        console.log('permit', permits);

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
    <div class = "table-responsive scroll-div" style = "max-height: 250px;">
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
                            class="fa-solid fa-tag"></i></span> Item Name:</strong> ${detail.item_name ?? "-"
            }</p>
        </div>
        <div class = "col-12 col-md-6">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-scale-balanced"></i></span> Quantity:</strong> ${detail.quantity ?? "-"
            } ${detail.measure ?? ""}</p>
        </div>
        <div class = "col-12 col-md-6">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-money-bill"></i></span> Value:</strong> RM ${detail.value ?? "-"
            }</p>
        </div>
        <div class = "col-12 col-md-6">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-pen-fancy"></i></span> Purpose:</strong> ${detail.purpose ?? "-"
            }</p>
        </div>
        <div class = "col-12 col-md-6">
            <p><strong class = "me-1"><span class = "avatar avatar-sm avatar-rounded  bd-gray-500"><i
                            class="fa-solid fa-gear"></i></span> Uses:</strong> ${detail.uses ?? "-"
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
            titleHTML + ` ` + detail.item_name || "Inspection Details";

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
                    url: `/public/inspection/${applicationId}/status`,
                    method: "POST",
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"),
                        status: 'Clerk review in-progress',
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
        case "submitted":
            badgeClass = "bg-info";
            label = "Processing";
            break;

        case "pending":
        case "pending for payment":
            badgeClass = "bg-warning";
            label = "Pending For Payment";
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
                    url: `/internal/inspection/${applicationId}/status`,
                    method: "POST",
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"),
                        status: 'Rejected',
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
                    url: `/internal/inspection/${applicationId}/status`,
                    method: "POST",
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"),
                        status: 'Rejected',
                        reason: result.value,
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
                    url: `/internal/inspection/${applicationId}/status`,
                    method: "POST",
                    data: {
                        _token: $("meta[name='csrf-token']").attr("content"),
                        status: 'Clerk Verified',
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
   

    // Update the totalValue element
    $("#totalValue").text(
        "RM 30.00"
        
    );

    totalPermit = 30;

}


function reapply() {
    $(document)
        .off("click", ".reapply")
        .on("click", ".reapply", async function (e) {
            e.preventDefault();

            const id = $(this).data("permit");
            const permits = application.inspection_items;
            const permit = permits.find(p => p.id == id);

            if (!permit) {
                console.warn("Permit not found!");
                return;
            }

            $('#saveBtn').data('id', id).attr('data-id', id);

            const detail = permit.consignment_detail;


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
                    $modal.find('#itemSelect').val(detail.item_name);

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

let updateItem;
function saveConsignmentAttachment() {
    $(document)
        .off("click", "#saveBtn")
        .on("click", "#saveBtn", function (e) {
            e.preventDefault();

            const $modal = $("#addItemModal");
            const id = $(this).data("id");

            const itemSelectValue = $modal.find("#itemSelect").val();
            const itemSelectText = $modal.find("#itemSelect").val();
            const itemValue = $modal.find("#itemValue").val().trim();
            const itemQuantity = $modal.find("#itemQuantity").val().trim();
            const itemMeasure = $modal.find("#itemMeasure").val();
            const itemPurpose = $modal.find("#itemPurpose  option:selected").text();
            const itemUsesValue = $modal.find("#itemUses").val();

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
        url: "/public/save-inspection/" + permitId,
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

    reapply();


    pendingPaymentTable();
    updateTotalValue()

    // application_reapply(application)


    Swal.close(); // Close after data is loaded



    let checkoutLocked = false;

    $(document).on("click", "#checkoutPage", function (e) {
        e.preventDefault();

        if (checkoutLocked) return; // 🚫 stop repeated click
        checkoutLocked = true;

        const $btn = $(this);
        $btn.prop("disabled", true).text("Processing...");


        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        totalPermit = Number(totalPermit).toFixed(2);

        

        $.ajax({
            url: "/payment/signed-url",
            method: "POST",
            data: {
                application_id: application.id,
                permit_ids: selectedPermits,
                total: totalPermit,
                type: "inspection",
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

/* -------------------------------
    Run initializer
    -------------------------------- */
initApplicationDetails();
