import $ from "jquery";
import Swal from "sweetalert2";

window.$ = window.jQuery = $;

$(document).ready(function () {
    $(document)
        .off("submit", "#loginForm")
        .on("submit", "#loginForm", function (e) {
            e.preventDefault();
            const form = $(this);
            const formData = form.serialize();

            // Clear previous errors
            form.find(".is-invalid").removeClass("is-invalid");
            form.find(".invalid-feedback").remove(); // remove previous error messages

            Swal.fire({
                title: "Logging in...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                url: form.attr("action"),
                method: "POST",
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: response.message,
                        showConfirmButton: false,
                        timer: 1000,
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                },
                error: function (xhr) {
                    Swal.close();

                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;

                        for (let field in errors) {
                            const input = form.find(`[name="${field}"]`);
                            input.addClass("is-invalid");

                            // Append error message below input
                            if (input.next(".invalid-feedback").length === 0) {
                                input.after(
                                    `<div class="invalid-feedback">${errors[field][0]}</div>`
                                );
                            }
                        }
                    } else {
                        // Other errors
                        let errorMsg = xhr.responseJSON?.message || "Something went wrong.";
                        Swal.fire({
                            icon: "error",
                            title: "Failed!",
                            html: errorMsg,
                        });
                    }
                },
            });
        });
});
