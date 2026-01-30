import $ from "jquery";
import Swal from "sweetalert2";

$(document).ready(function () {
    // Fetch States on page load
    fetchStates();

    // Handle State change
    $(".state-register").on("change", function () {
        const stateId = $(this).val();
        $(".district-register").html('<option value="">Select District</option>');
        $(".postcode-register").html('<option value="">Select Postcode</option>');

        if (stateId) {
            fetchDistricts(stateId);
        }
    });

    // Handle District change
    $(".district-register").on("change", function () {
        const districtId = $(this).val();
        $(".postcode-register").html('<option value="">Select Postcode</option>');

        if (districtId) {
            fetchPostcodes(districtId);
        }
    });

    // Fetch States
    function fetchStates() {
        $.ajax({
            url: "/get_states",
            type: "GET",
            success: function (data) {
                $(".state-register").empty().append('<option value="">Select State</option>');
                data.forEach(state => {
                    $(".state-register").append(`<option value="${state.id}">${state.name}</option>`);
                });
            },
            error: function (err) {
                console.error("Error fetching states", err);
            }
        });
    }

    // Fetch Districts
    function fetchDistricts(stateId) {
        $(".district-register").html('<option value="">Loading...</option>');

        $.ajax({
            url: `/get_districts/${stateId}`,
            type: "GET",
            success: function (data) {
                $(".district-register").empty().append('<option value="">Select District</option>');
                data.forEach(district => {
                    $(".district-register").append(`<option value="${district.id}">${district.name}</option>`);
                });
            },
            error: function (err) {
                console.error("Error fetching districts", err);
                $(".district-register").html('<option value="">Error loading districts</option>');
            }
        });
    }

    // Fetch Postcodes
    function fetchPostcodes(districtId) {
        $(".postcode-register").html('<option value="">Loading...</option>');

        $.ajax({
            url: `/get_postcodes/${districtId}`,
            type: "GET",
            success: function (data) {
                $(".postcode-register").empty().append('<option value="">Select Postcode</option>');
                data.forEach(postcode => {
                    $(".postcode-register").append(`<option value="${postcode.value}">${postcode.value}</option>`);
                });
            },
            error: function (err) {
                console.error("Error fetching postcodes", err);
                $(".postcode-register").html('<option value="">Error loading postcodes</option>');
            }
        });
    }

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
