console.log("hellooo");

import $ from "jquery";
import Swal from "sweetalert2";

window.$ = window.jQuery = $;

$(document).ready(function () {
    $(document)
        .off("submit", "#verifyEmailForm")
        .on("submit", "#verifyEmailForm", function (e) {
            e.preventDefault();
            const form = $(this);
            const formData = form.serialize();

            // 🔹 Clear old validation feedback
            form.find(".is-invalid").removeClass("is-invalid");
            form.find(".invalid-feedback").remove();

            Swal.fire({
                title: "Sending...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                url: form.attr("action"),
                method: "POST",
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Email Verification is send!",
                        showConfirmButton: false,
                        timer: 1000,
                    })

                    $('#emailSent').text('Email verification resent. Please check your inbox.').css('display', 'block')
                },
                error: function (xhr) {
                    Swal.close();


                    Swal.fire({
                        icon: "error",
                        title: "Email not send",
                    });
                },
            });
        });
});
