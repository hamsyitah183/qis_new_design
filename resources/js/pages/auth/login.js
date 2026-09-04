import $ from "jquery";
import Swal from "sweetalert2";

window.$ = window.jQuery = $;

console.log("hello login page");

$(document).ready(function () {
    let lang = null;
    // Helper to get current language
    function getCurrentLanguage() {
        return localStorage.getItem('qis_lang') || 'en';
    }

    // Translation object (for frontend‑only strings)
    const translations = {
        en: {
            loggingIn: 'Logging in...',
            loginSuccess: 'Login successful!',
            somethingWentWrong: 'Something went wrong. Please try again later.',
            emailNotVerified: 'Email Not Verified',
            goToVerify: 'Go to Verify Page',
            validationError: 'Validation Error',
            authFailed: 'Authentication Failed',
            invalidCredentials: 'Invalid credentials or password.',
            loginFailed: 'Login Failed',
            passwordNotSet: 'Password Not Set',
            goToEmail: 'Go to Email',
        },
        bm: {
            loggingIn: 'Sedang log masuk...',
            loginSuccess: 'Log masuk berjaya!',
            somethingWentWrong: 'Terdapat masalah. Sila cuba lagi nanti.',
            emailNotVerified: 'E-mel Belum Disahkan',
            goToVerify: 'Pergi ke Halaman Pengesahan',
            validationError: 'Ralat Pengesahan',
            authFailed: 'Pengesahan Gagal',
            invalidCredentials: 'Kelayakan atau kata laluan tidak sah.',
            loginFailed: 'Log Masuk Gagal',
            passwordNotSet: 'Kata Laluan Belum Ditetapkan',
            goToEmail: 'Pergi ke E-mel',
        }
    };

    function t(key) {
        lang = getCurrentLanguage();
        return translations[lang]?.[key] ?? translations['en'][key] ?? key;
    }

    $(document)
        .off("submit", "#loginForm")
        .on("submit", "#loginForm", function (e) {
            e.preventDefault();

            const form = $(this);
            const lang = getCurrentLanguage();
            // Append lang to form data so backend can translate
            const formData = form.serialize() + '&lang=' + encodeURIComponent(lang);

            // Clear old validation states
            form.find(".is-invalid").removeClass("is-invalid");
            form.find(".invalid-feedback").remove();

            Swal.fire({
                title: t('loggingIn'),
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                url: "/login",
                type: "POST",
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: t('loginSuccess'),
                        showConfirmButton: false,
                        timer: 1000,
                    }).then(() => {
                        window.location.href = "/";
                    });
                },
                error: function (xhr) {
                    Swal.close();

                    let errorMsg = t('somethingWentWrong');

                    /* 🔸 422 — Password not set */
                    if (xhr.status === 422 && xhr.responseJSON?.status === 'password_not_set') {
                        Swal.fire({
                            icon: 'info',
                            title: t('passwordNotSet'),
                            text: xhr.responseJSON.message,
                            confirmButtonText: t('goToEmail'),
                        }).then(() => {
                            window.location.href = xhr.responseJSON.redirect;
                        });
                        return;
                    }

                    /* 🔸 403 — Unverified email (server message is already translated) */
                    if (
                        xhr.status === 403 &&
                        xhr.responseJSON?.status === "unverified"
                    ) {
                        Swal.fire({
                            icon: "warning",
                            title: t('emailNotVerified'),
                            text: xhr.responseJSON.message, // already translated by backend
                            confirmButtonText: t('goToVerify'),
                        }).then(() => {
                            window.location.href = xhr.responseJSON.redirect;
                        });
                        return;
                    }

                    /* 🔸 422 — Validation errors (server messages already translated) */
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;
                        const combinedMessages = Object.values(errors)
                            .flat()
                            .join("\n");

                        // Highlight inputs
                        Object.keys(errors).forEach((field) => {
                            form.find(`[name="${field}"]`).addClass("is-invalid");
                        });

                        Swal.fire({
                            icon: "error",
                            title: t('validationError'),
                            text: combinedMessages, // already translated
                        });
                        return;
                    }

                    /* 🔸 401 / 400 — Invalid credentials (server sends translated message) */
                    if (xhr.status === 401 || xhr.status === 400) {
                        Swal.fire({
                            icon: "error",
                            title: t('authFailed'),
                            text: xhr.responseJSON?.message || t('invalidCredentials'),
                        });
                        return;
                    }

                    /* 🔸 Catch-all — use server message if available */
                    if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: "error",
                        title: t('loginFailed'),
                        text: errorMsg,
                    });
                },
            });
        });
});