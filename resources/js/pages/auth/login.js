import $ from "jquery";
import Swal from "sweetalert2";

window.$ = window.jQuery = $;

console.log("hello login page");

$(document).ready(function () {
    $(document)
        .off("submit", "#loginForm")
        .on("submit", "#loginForm", function (e) {
            e.preventDefault();

            const form = $(this);
            const formData = form.serialize();

            // Clear old validation states
            form.find(".is-invalid").removeClass("is-invalid");
            form.find(".invalid-feedback").remove();

            Swal.fire({
                title: "Logging in...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                url: "/login",
                type: "POST",
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: response?.message || "Login successful!",
                        showConfirmButton: false,
                        timer: 1000,
                    }).then(() => {
                        window.location.href = "/";
                    });
                },
                error: function (xhr) {
                    Swal.close();

                    let errorMsg =
                        "Something went wrong. Please try again later.";

                    /* 🔸 403 — Unverified email */
                    if (
                        xhr.status === 403 &&
                        xhr.responseJSON?.status === "unverified"
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

                    /* 🔸 422 — Validation errors */
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;

                        const combinedMessages = Object.values(errors)
                            .flat()
                            .join("\n");

                        // Highlight inputs
                        Object.keys(errors).forEach((field) => {
                            form.find(`[name="${field}"]`).addClass(
                                "is-invalid",
                            );
                        });

                        Swal.fire({
                            icon: "error",
                            title: "Validation Error",
                            text: combinedMessages,
                        });
                        return;
                    }

                    /* 🔸 401 / 400 — Invalid credentials */
                    if (xhr.status === 401 || xhr.status === 400) {
                        Swal.fire({
                            icon: "error",
                            title: "Authentication Failed",
                            text:
                                xhr.responseJSON?.message ||
                                "Invalid credentials or password.",
                        });
                        return;
                    }

                    /* 🔸 Catch-all */
                    if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Login Failed",
                        text: errorMsg,
                    });
                },
            });
        });

    (function () {
        var STORAGE_KEY = "qis_lang";
        var buttons = document.querySelectorAll(".lang-btn");
        var elements = document.querySelectorAll("[data-en]");

        function setLang(lang) {
            elements.forEach(function (el) {
                var text = el.getAttribute("data-" + lang);
                if (text === null) return;
                if (el.getAttribute("data-i18n-attr") === "placeholder") {
                    el.setAttribute("placeholder", text);
                } else {
                    el.textContent = text;
                }
            });

            buttons.forEach(function (btn) {
                btn.classList.toggle(
                    "active",
                    btn.getAttribute("data-lang") === lang,
                );
            });

            document.documentElement.setAttribute(
                "lang",
                lang === "bm" ? "ms" : "en",
            );

            try {
                localStorage.setItem(STORAGE_KEY, lang);
            } catch (e) {
                /* storage unavailable, ignore */
            }
        }

        buttons.forEach(function (btn) {
            btn.addEventListener("click", function () {
                setLang(btn.getAttribute("data-lang"));
            });
        });

        var savedLang = "en";
        try {
            savedLang = localStorage.getItem(STORAGE_KEY) || "en";
        } catch (e) {
            /* storage unavailable, default to en */
        }

        setLang(savedLang);
    })();
});
