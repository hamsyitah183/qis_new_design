import $ from "jquery";
import Swal from "sweetalert2";

window.$ = window.jQuery = $;

$(document).ready(function() {
    $(document)
        .off("submit", "#loginForm")
        .on("submit", "#loginForm", function(e) {
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
                url: '/login',
                method: "POST",
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function(response) {
                    Swal.fire({
                        icon: "success",
                        title: response.message || "Login successful!",
                        showConfirmButton: false,
                        timer: 1000,
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                },
                error: function(xhr) {
                    Swal.close();

                    let errorMsg =
                        "Something went wrong. Please try again later.";
                    let swalIcon = "error";
                    let swalTitle = "Login Failed";
                    let redirectUrl = null;

                    // 🔸 Handle unverified users (403)
                    if (
                        xhr.status === 403 &&
                        xhr.responseJSON ? .status === "unverified"
                    ) {
                        Swal.fire({
                            icon: "warning",
                            title: "Email Not Verified",
                            text: xhr.responseJSON.message,
                            confirmButtonText: "Go to Verify Page",
                        }).then(() => {
                            window.location.href = xhr.responseJSON.redirect;
                        });
                        return;
                    }

                    // 🔸 Validation errors (422)
                    if (xhr.status === 422 && xhr.responseJSON ? .errors) {
                        const errors = xhr.responseJSON.errors;
                        let combinedMessages = Object.values(errors)
                            .map((errArr) => errArr.join(" "))
                            .join("\n");

                        // Highlight inputs
                        for (let field in errors) {
                            const input = form.find(`[name="${field}"]`);
                            input.addClass("is-invalid");
                        }

                        Swal.fire({
                            icon: "error",
                            title: "Validation Error",
                            text: combinedMessages,
                        });
                        return;
                    }

                    // 🔸 Invalid credentials (401 or 400)
                    if (xhr.status === 401 || xhr.status === 400) {
                        errorMsg =
                            xhr.responseJSON ? .message ||
                            "Invalid credentials or password.";
                        swalIcon = "error";
                        swalTitle = "Authentication Failed";

                        Swal.fire({
                            icon: swalIcon,
                            title: swalTitle,
                            text: errorMsg,
                        });
                        return;
                    }

                    // 🔸 Catch-all (any unexpected error)
                    if (xhr.responseJSON ? .message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: swalIcon,
                        title: swalTitle,
                        text: errorMsg,
                    });
                },
            });
        });
});