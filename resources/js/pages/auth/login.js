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

            // 🔹 Clear old validation feedback
            form.find(".is-invalid").removeClass("is-invalid");
            form.find(".invalid-feedback").remove();

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
                        title: response.message || "Login successful!",
                        showConfirmButton: false,
                        timer: 1000,
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                },
                error: function (xhr) {
                    Swal.close();

                    if (xhr.status === 422) {
                        // 🔸 Validation errors
                        const errors = xhr.responseJSON.errors;
                        for (let field in errors) {
                            const input = form.find(`[name="${field}"]`);
                            input.addClass("is-invalid");

                            if (input.next(".invalid-feedback").length === 0) {
                                input.after(
                                    `<div class="invalid-feedback">${errors[field][0]}</div>`
                                );
                            }
                        }
                    } else if (xhr.status === 401 || xhr.status === 400) {
                        // 🔸 Authentication or general login failure
                        const errorMsg = xhr.responseJSON?.message || "Invalid credentials.";
                        const emailInput = form.find(`[name="email"]`);
                        const passwordInput = form.find(`[name="password"]`);

                        // Highlight both fields
                        emailInput.addClass("is-invalid");
                        passwordInput.addClass("is-invalid");

                        // Show feedback below password
                        if (passwordInput.next(".invalid-feedback").length === 0) {
                            passwordInput.after(
                                `<div class="invalid-feedback">${errorMsg}</div>`
                            );
                        }
                    } else {
                        // 🔸 Unexpected server error
                        let errorMsg =
                            xhr.responseJSON?.message ||
                            "Something went wrong. Please try again later.";
                        Swal.fire({
                            icon: "error",
                            title: "Login Failed",
                            text: errorMsg,
                        });
                    }
                },
            });
        });
});
