import $ from "jquery";
import Swal from "sweetalert2";

let user = null;

export async function loadProfile() {
    $(".uploadAgain").hide();
    console.log("call the load profile function");
    const allInputs = document.querySelectorAll(
        "input, select, button, textarea"
    );
    allInputs.forEach((el) => (el.disabled = true));

    Swal.fire({
        title: "Loading Profile...",
        text: "Please wait a moment.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const response = await $.ajax({
            url: `/data`,
            type: "GET",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        user = response.user; // important: populate global user
        user["type"] = response.type;
        fillTheData(user, response.type);

        if (user.type === "public") {
            publicUserAddUpdate(user);
        }

        Swal.close();
    } catch (error) {
        console.error("Error loading profile:", error);
        Swal.fire({
            icon: "error",
            title: "Failed to Load",
            text: "Failed to load profile data. Please try again.",
        });
    } finally {
        allInputs.forEach((el) => (el.disabled = false));
    }
}

function fillTheData(user, type) {
    console.log("user id", user);
    const fullAddress =
        user.office ??
        user.address_1 + (user.address_2 ? `, ${user.address_2}` : "");
    let badgeVerification = "";
    $(".type").val(type);
    $(".uuid").val(user.uuid);
    $("#uploadBtn").attr("data-id", user.uuid);
    $(".fullname").val(user.fullname).text(user.fullname);
    $(".address").val(fullAddress).text(fullAddress);
    $(".phone_number")
        .val(user.phone ?? user.phone_number)
        .text(user.phone ?? user.phone_number);
    $(".email").val(user.email).text(user.email);
    $(".ic")
        .val(user.no_ic ?? user.ic)
        .text(user.ic ?? user.no_ic)
        .prop("readonly", true);
    $(".position").val(user.position).text(user.position);
    $(".address_1").val(user.address_1);
    $(".address_2").val(user.address_2);
    $(".office_number").val(user.office_number);
    $(".district").val(user.district);
    $(".postcode").val(user.postcode);
    $(".state").val(user.state);
    $("#account_type").val(user.account_type);

    if (type === "internal") {
        $(".email").prop("readonly", true);
        $(".ic").prop("readonly", true);
    }

    if (type === "public") {
        const approved = user.approved ?? {}; // ✅ Fallback to empty object

        // 🔹 Handle DOA verification badge
        let badgeVerification = `<span class="badge bg-dark-transparent ms-1" title="Not verified by DOA">
                Not Verified by DOA
            </span>`;
        if (approved.doa_verified) {
            badgeVerification = `
            <span class="badge bg-success-transparent ms-1" title="Verified by DOA">
                Verified by DOA
            </span>`;
        } else {
            badgeVerification = `
            <span class="badge bg-dark-transparent ms-1" title="Not verified by DOA">
                Not Verified by DOA
            </span>`;
        }

        // 🔹 Handle attachment
        const container = $("#imgLink");
        container.empty();

        const fileUrl = approved.verification_attachment ?? null;

        if (fileUrl) {
            const ext = fileUrl.split(".").pop().toLowerCase();

            if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) {
                container.append(
                    `<img src="${fileUrl}" class="img-fluid" alt="Verification Attachment">`
                );
            } else if (ext === "pdf") {
                container.append(
                    `<iframe src="${fileUrl}" class="w-100" style="height:500px;" frameborder="0"></iframe>`
                );
            } else {
                container.append(`<p>Unsupported file format: ${ext}</p>`);
            }

            $(".hasImage").css("display", "block");
            $(".hasNoImage").css("display", "none");

            if (approved.status?.toLowerCase().includes("rejected")) {
                $(".rejectedBtn").html(`
                    <div class = "btn btn-sm btn-warning uploadAgain">
                        Upload Again
                    </div>
                `);
            }

            // $('.approvedBy').text(approved)
        } else {
            container.append("<p>No attachment uploaded yet.</p>");
            $(".hasImage").css("display", "none");
            $(".hasNoImage").css("display", "block");
        }

        // 🔹 Dates and approver info
        $(".submittedVerification").text(
            approved.updated_at ? formatTime(approved.updated_at) : "N/A"
        );

        $(".approvedBy").text(approved.approver?.fullname ?? "N/A");

        $(".approvedDate").text(
            approved.doa_approved_time
                ? `on (${formatTime(approved.doa_approved_time)})`
                : ""
        );

        // 🔹 Status display
        let statusText = "";
        let reason = "";

        if (approved.status?.toLowerCase().includes("waiting")) {
            statusText = `
            <div class="alert alert-warning" role="alert">
                <i class="ti ti-alert-circle me-2 fs-16"></i>
                ${approved.status}
            </div>`;
        } else if (approved.status?.toLowerCase().includes("approved")) {
            statusText = `
            <div class="alert alert-success" role="alert">
                <i class="ti ti-rosette-discount-check me-2 fs-16"></i>
                ${approved.status}
            </div>`;
        } else if (approved.status?.toLowerCase().includes("rejected")) {
            statusText = `
            <div class="alert alert-danger" role="alert">
                <i class="ti ti-rosette-discount-x me-2 fs-16"></i>
                ${approved.status}
            </div>`;

            reason = `
               <div class = "me-2 mt-2 border rounded-3 p-3">
                <span class = "fw-bold">Reason: </span> 
                <span class = "text-muted">${approved.reason}</span>
               </div>`;
        } else {
            statusText = `
            <div class="alert alert-secondary" role="alert">
                No verification status available.
            </div>`;
        }

        $(".status").html(statusText);
        $(".reason").html(reason);
    }

    $(".mainFullName").html(badgeVerification);
}

$(document).on("click", ".uploadAgain", function () {
    // Hide the container showing existing image
    $(".hasImage").css("display", "none");

    // Show the container for uploading new image
    $(".hasNoImage").css("display", "block");

    // Optional: clear the previous Dropzone files if needed
    if (typeof verificationDropzone !== "undefined") {
        verificationDropzone.removeAllFiles(true);
    }
});

function editProfile() {
    $("#edit-profile-tab-pane")
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();

            if (!user) {
                Swal.fire({
                    icon: "info",
                    title: "Please wait...",
                    text: "Profile is still loading.",
                });
                return;
            }

            const $form = $(this);
            const formData = new FormData(this);
            const allInputs = $form.find("input, select, button, textarea");
            allInputs.prop("disabled", true);
            $form.find(".is-invalid").removeClass("is-invalid");
            $form.find(".invalid-feedback").remove();

            const newEmail = $form.find(".email").val().trim();

            const submitForm = () => {
                Swal.fire({
                    title: "Updating...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: $form.attr("action") || "/data",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                        Accept: "application/json",
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1200,
                        });
                        loadProfile();
                    },
                    error: function (xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            for (const key in errors) {
                                const $input = $form.find(`[name="${key}"]`);
                                if ($input.length) {
                                    $input.addClass("is-invalid");
                                    $input.after(
                                        `<div class="invalid-feedback">${errors[key][0]}</div>`
                                    );
                                }
                            }
                            Swal.fire({
                                icon: "error",
                                title: "Validation Failed",
                                text: "Please check your input fields.",
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    xhr.responseJSON?.message ||
                                    "Something went wrong. Please try again.",
                            });
                        }
                    },
                    complete: function () {
                        allInputs.prop("disabled", false);
                    },
                });
            };

            // Email changed?
            if (user.email.trim() !== newEmail && user.type == "public") {
                Swal.fire({
                    icon: "info",
                    title: "Change your email?",
                    text: "Changing your email may cause your verification to be retracted and require re-approval.",
                    showCancelButton: true,
                    confirmButtonText: "Continue",
                    cancelButtonText: "Cancel",
                }).then((result) => {
                    if (result.isConfirmed) submitForm();
                    else allInputs.prop("disabled", false);
                });
            } else {
                submitForm();
            }
        });
}

function formatTime(timestamp) {
    const utcDate = new Date(timestamp);

    // Malaysia Time (UTC+8)
    const malaysiaOffset = 8 * 60; // minutes
    const localTime = new Date(utcDate.getTime() + malaysiaOffset * 60 * 1000);

    const options = {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    };

    const formatted = new Intl.DateTimeFormat("en-GB", options).format(
        localTime
    );

    return formatted;
}

function changePassword() {
    $("#edit-password-tab-pane")
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();

            if (!user) {
                Swal.fire({
                    icon: "info",
                    title: "Please wait...",
                    text: "Profile is still loading.",
                });
                return;
            }

            const $form = $(this);
            const formData = new FormData(this);

            const allInputs = $form.find("input, select, button, textarea");
            allInputs.prop("disabled", true);
            $form.find(".is-invalid").removeClass("is-invalid");
            $form.find(".invalid-feedback").remove();

            // $(".type").val(type);
            // $(".uuid").val(user.uuid);

            const submitForm = () => {
                Swal.fire({
                    title: "Updating...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: $form.attr("action") || "/password",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                        Accept: "application/json",
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1200,
                        });
                    },
                    error: function (xhr) {
                        Swal.close();
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            for (const key in errors) {
                                const $input = $form.find(`[name="${key}"]`);
                                if ($input.length) {
                                    $input.addClass("is-invalid");
                                    $input.after(
                                        `<div class="invalid-feedback">${errors[key][0]}</div>`
                                    );
                                }
                            }
                            Swal.fire({
                                icon: "error",
                                title: "Validation Failed",
                                text: "Please check your input fields.",
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    xhr.responseJSON?.message ||
                                    "Something went wrong. Please try again.",
                            });
                        }
                    },
                    complete: function () {
                        allInputs.prop("disabled", false);
                    },
                });
            };

            submitForm();
        });
}

export async function publicUserAddUpdate(user) {
    console.log("call public user echo function");
    console.log("public user id", user);
    setTimeout(() => {
        if (!window.Echo) {
            console.error("Echo not found");
            return;
        }

        window.Echo.private(`public-user.${user.uuid}`).listen(
            ".PublicUser",
            (e) => {
                console.log("✅ Public user event:", e.message);
            }
        );
    }, 100);
}

$(document).ready(function () {
    // load profile on page load
    loadProfile();
    editProfile();
    changePassword();
    // publicUserAddUpdate(user);
});
