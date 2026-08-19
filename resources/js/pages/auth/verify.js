console.log("hellooo");

import $ from "jquery";
import Swal from "sweetalert2";

window.$ = window.jQuery = $;

// ---- Language helper ----
function getCurrentLang() {
    // 1. Check localStorage (set by the language toggle on the page)
    try {
        const stored = localStorage.getItem('qis_lang');
        if (stored === 'bm' || stored === 'en') return stored;
    } catch (e) { /* ignore */ }

    // 2. Fallback to <html lang> attribute
    const htmlLang = document.documentElement.getAttribute('lang');
    if (htmlLang === 'ms' || htmlLang === 'bm') return 'bm';
    if (htmlLang === 'en') return 'en';

    // 3. Default to English
    return 'en';
}

// ---- Localised messages ----
const messages = {
    en: {
        sending: 'Sending...',
        successTitle: 'Email Verification Sent!',
        successText: 'Email verification resent. Please check your inbox.',
        errorTitle: 'Email Not Sent',
        errorText: 'Something went wrong. Please try again.',
    },
    bm: {
        sending: 'Menghantar...',
        successTitle: 'Pengesahan E-mel Dihantar!',
        successText: 'E-mel pengesahan dihantar semula. Sila semak peti masuk anda.',
        errorTitle: 'E-mel Tidak Dihantar',
        errorText: 'Berlaku ralat. Sila cuba lagi.',
    }
};

// ---- Document ready ----
$(document).ready(function () {
    $(document)
        .off("submit", "#verifyEmailForm")
        .on("submit", "#verifyEmailForm", function (e) {
            e.preventDefault();
            const form = $(this);
            const formData = form.serialize();
            const lang = getCurrentLang();
            const msg = messages[lang] || messages.en;

            // Clear old validation feedback
            form.find(".is-invalid").removeClass("is-invalid");
            form.find(".invalid-feedback").remove();

            Swal.fire({
                title: msg.sending,
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
                        title: msg.successTitle,
                        showConfirmButton: false,
                        timer: 1500,
                    });

                    $('#emailSent')
                        .text(msg.successText)
                        .css('display', 'block');
                },
                error: function (xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: "error",
                        title: msg.errorTitle,
                        text: msg.errorText,
                    });
                },
            });
        });
});