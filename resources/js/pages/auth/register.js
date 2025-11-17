import $ from "jquery";
import Swal from "sweetalert2";

$(document).ready(function () {
    $("#publicRegisterForm").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const formData = new FormData(this);

        $form.find(".is-invalid").removeClass("is-invalid");
        $form.find(".invalid-feedback").remove();

        Swal.fire({
            title: "Registering...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: $form.attr("action") || "/register",
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
                    window.location.href = response.redirect;
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
