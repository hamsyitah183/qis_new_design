import $ from "jquery";
import Swal from "sweetalert2";

let user = null;

async function loadProfile() {
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
    const fullAddress =
        user.office ??
        user.address_1 + (user.address_2 ? `, ${user.address_2}` : "");
    let badgeVerification = "";
    $(".type").val(type);
    $(".uuid").val(user.uuid);
    $(".fullname").val(user.fullname).text(user.fullname);
    $(".address").val(fullAddress).text(fullAddress);
    $(".phone_number")
        .val(user.phone ?? user.phone_number)
        .text(user.phone ?? user.phone_number);
    $(".email").val(user.email).text(user.email);
    $(".ic")
        .val(user.no_ic ?? user.ic)
        .text(user.ic ?? user.no_ic);
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
        if (user.approved?.doa_verified) {
            badgeVerification += ` <span class="badge bg-success-transparent ms-1" title="Verified by DOA">
            Verified by DOA
        </span>`;
        } else {
            badgeVerification = ` <span class="badge bg-dark-transparent ms-1" title="Not verified by DOA">
                Not Verified by DOA
            </span>`;
        }

        $("#imgLink").attr("src", user.approved.verification_attachment);
        $(".submittedVerification").text(formatTime(user.approved?.updated_at) ?? "");
        // Approver name
        $(".approvedBy").text(user.approved?.approver?.fullname ?? "");
        $(".approvedDate").text(
            user.approved?.doa_approved_time ? `on (${formatTime(user.approved.doa_approved_time)})` : ""
        );


        let statusText = '';

        if (user.approved?.status?.includes('waiting')) {
            statusText = `<div class="alert alert-warning" role="alert">
                <i class="ti ti-alert-circle me-2 fs-16"></i>
                ${user.approved.status}
            </div>`;
        }

        $('.status').html(statusText)


    }

    $(".mainFullName").html(badgeVerification);
}

function editProfile() {
    $("#edit-profile-tab-pane").on("submit", function (e) {
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
                title: "Registering...",
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

    const formatted = new Intl.DateTimeFormat("en-GB", options).format(localTime);

    return formatted;
}


function changePassword() {
    $("#edit-password-tab-pane").on("submit", function (e) {
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

$(document).ready(function () {
    // load profile on page load
    loadProfile();
    editProfile();
    changePassword();
});
