import $ from "jquery";
import Swal from "sweetalert2";

console.log('reset password')

$(document).ready(function () {
    // Grab query parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email');
    const type = urlParams.get('type');

    // Grab token from the path
    const pathSegments = window.location.pathname.split('/');
    const token = pathSegments[pathSegments.length - 1]; // last segment is the token

    // Set hidden fields
    $('#email').val(email);
    $('#token').val(token);
    $('#type').val(type);

    $("#resetPasswordForm").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const formData = new FormData(this);

        Swal.fire({
            title: "Reset Password...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: $form.attr("action") || "/reset-password",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                Accept: "application/json",
            },
            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1200,
                }).then(() => {
                    window.location.href = '/login'; // redirect after success
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
                            $input.after(`<div class="invalid-feedback">${errors[key][0]}</div>`);
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
                        text: xhr.responseJSON?.message || "Something went wrong. Please try again.",
                    });
                }
            },
        });
    });
});
