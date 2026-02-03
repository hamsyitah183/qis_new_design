import $ from "jquery";
window.$ = window.jQuery = $;
import Swal from "sweetalert2";
import "datatables.net-bs5";
import "datatables.net-responsive-bs5";
import { fetchVerificationCount, formatTime } from "../../../app"; // Ensure this matches your project structure

$(document).ready(function () {
    var verificationTable = $('#verificationTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/internal/user_public/verification/data",
        columns: [
            { data: 'fullname', name: 'fullname' },
            { data: 'verification_attachment', name: 'verification_attachment', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    // View Attachment (Opens Modal)
    $(document).on('click', '.view-attachment', function (e) {
        e.preventDefault();
        const userId = $(this).data('id');

        // Clear previous modal data
        $(".ic").text("");
        $("#userIC").empty();

        showLoader();

        fetchVerificationData(userId)
            .then((response) => {
                Swal.close();
                handleVerificationModal(response, userId);
            })
            .catch((error) => {
                console.error("Verification Load Error:", error); // Log to console
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.responseText || "Failed to load verification info. Check console for details.",
                });
            });
    });

    // Accept User (from Table)
    $(document).on('click', '.accept-btn', function () {
        const userId = $(this).data('id');
        handleAccept(userId, verificationTable);
    });

    // Reject User (from Table)
    $(document).on('click', '.reject-btn', function () {
        const userId = $(this).data('id');
        $('#rejectUserUuid').val(userId);
        $('#rejectReason').val('');
        const modal = new window.bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    });

    // Handle Reject Form Submit (from rejectModal)
    $('#rejectForm').on('submit', function (e) {
        e.preventDefault();
        const userId = $('#rejectUserUuid').val();
        const reason = $('#rejectReason').val();
        handleReject(userId, reason, verificationTable, 'rejectModal');
    });

    // Handle Modal "Verified" (Accept) Button
    $(document).on('click', '#verificationBtn', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        handleAccept(userId, verificationTable, 'verificationModal');
    });

    // Handle Modal "Reject" Button
    $(document).on('click', '#unverificationBtn', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        
        // Close verification modal
        const modalEl = document.getElementById("verificationModal");
        const modal = window.bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        // Open reject modal
        $('#rejectUserUuid').val(userId);
        $('#rejectReason').val('');
        const rejectModal = new window.bootstrap.Modal(document.getElementById('rejectModal'));
        rejectModal.show();
    });

});

// --- Helper Functions ---

function showLoader(text = "Loading...") {
    Swal.fire({
        title: text,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });
}

function fetchVerificationData(id) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/internal/verification/${id}`,
            method: "GET",
            success: resolve,
            error: reject,
        });
    });
}

function handleVerificationModal(response, id) {
    if (!response || !response.public_user) {
        Swal.fire('Error', 'No data found', 'error');
        return;
    }

    const modalEl = document.getElementById("verificationModal");
    if(!modalEl) {
        console.error("Modal element #verificationModal not found");
        return;
    }
    
    const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    const user = response.public_user;

    // Populate Data
    $(".ic").text(user.no_ic || "-");
    $('.updated_at').text(formatTime(response.updated_at));

    // Render Attachment
    renderAttachment(response.verification_attachment);

    // Render Status
    renderStatus(response.status);

    // Setup Buttons in Modal
    setupVerificationButtons(response, id);
}

function renderAttachment(fileUrl) {
    const container = $("#userIC");
    container.empty();

    if (!fileUrl) {
        container.html("<p>No attachment returned.</p>");
        return;
    }
    
    let path = fileUrl;
    if(path.includes('app/public/')) {
        path = path.replace('storage/app/public/', 'storage/');
    } else if (!path.startsWith('/') && !path.startsWith('http')) {
        path = '/' + path;
    }

    const fileExtension = path.split(".").pop().toLowerCase();

    if (["jpg", "jpeg", "png", "gif", "webp"].includes(fileExtension)) {
        container.append(`<img src="${path}" class="img-fluid" alt="Verification Attachment">`);
    } else if (fileExtension === "pdf") {
        container.append(`<iframe src="${path}" class="w-100" style="height:500px;" frameborder="0"></iframe>`);
    } else {
        container.append(`<p class="text-center"><a href="${path}" target="_blank" class="btn btn-primary">Download Attachment (${fileExtension})</a></p>`);
    }
}

function renderStatus(status) {
    let statusHtml = '';
    if (status?.toLowerCase().includes("waiting")) {
        statusHtml = `<div class="alert alert-warning mb-0"><i class="ti ti-alert-circle me-2"></i> ${status}</div>`;
    } else if (status?.toLowerCase().includes("approved")) {
        statusHtml = `<div class="alert alert-success mb-0"><i class="ti ti-check me-2"></i> ${status}</div>`;
    } else if (status?.toLowerCase().includes("rejected")) {
        statusHtml = `<div class="alert alert-danger mb-0"><i class="ti ti-x me-2"></i> ${status}</div>`;
    } else {
        statusHtml = `<div class="alert alert-info mb-0">${status || 'Unknown Status'}</div>`;
    }
    $(".status").html(statusHtml);
}

function setupVerificationButtons(response, id) {
    const vBtn = $("#verificationBtn");
    const uBtn = $("#unverificationBtn");
    
    vBtn.data('id', id);
    uBtn.data('id', id);

    // Match public_list logic:
    if (response.doa_verified === 1 || response.doa_verified === '1' || response.doa_verified === true || response.doa_verified === 'yes') {
       // user is verified. public_list changes button to "Unverify".
       // But in Verification List, we usually just want to approve/reject pending ones.
       // We'll hide them if verified to avoid confusion, or follow user preference.
       // User said "action button stays ( accept / reject )". 
       // We'll show them.
       vBtn.show();
       uBtn.show();
    } else {
        vBtn.show();
        uBtn.show();
    }
}

function handleAccept(userId, tableInstance, modalIdToHide = null) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to accept this verification.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, accept it!'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoader("Approving...");

            $.ajax({
                url: `/internal/verification/${userId}/save`,
                method: "POST",
                data: {
                    approved: "yes",
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Approved!",
                        text: response.message || "User verified.",
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    fetchVerificationCount()
                    
                    if (modalIdToHide) {
                        const modalEl = document.getElementById(modalIdToHide);
                        const modal = window.bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    
                    if (tableInstance) tableInstance.ajax.reload();
                },
                error: function (xhr) {
                    Swal.fire("Error", xhr.responseJSON?.message || "Action failed", "error");
                },
            });
        }
    });
}

function handleReject(userId, reason, tableInstance, modalIdToHide = null) {
    showLoader("Rejecting...");

    $.ajax({
        url: `/internal/verification/${userId}/save`,
        method: "POST",
        data: {
            approved: "no",
            reason: reason,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            Swal.fire({
                icon: "success",
                title: "Rejected",
                text: response.message || "User rejected.",
                timer: 2000,
                showConfirmButton: false,
            });

            if (modalIdToHide) {
                 const modalEl = document.getElementById(modalIdToHide);
                 const modal = window.bootstrap.Modal.getInstance(modalEl);
                 if (modal) modal.hide();
            }

            if (tableInstance) tableInstance.ajax.reload();
        },
        error: function (xhr) {
             Swal.fire("Error", xhr.responseJSON?.message || "Action failed", "error");
        },
    });
}

$('#confirmRejectBtn').on('click', function(e) {
    e.preventDefault()
    const userId = $('#rejectUserUuid').val();
    const reason = $('#rejectReason').val();
    handleReject(userId, reason, verificationTable, 'rejectModal');
})

