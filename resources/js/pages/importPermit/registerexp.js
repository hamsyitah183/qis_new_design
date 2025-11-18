import $ from "jquery";
import Swal from "sweetalert2";

let exporterListArray = [];
let url = null;

function exporterList()
{
    const select = $("#selectexp");
    url = select.data("route");

    loadExporterList();

    // 2️⃣ When user changes selection
    select.on("change", function () {
        const selectedId = $(this).val();
        // console.log(selectedId);
        // Clear fields if no selection
        if (!selectedId) {
            $(
                "#expid, #addexpName, #addexpfonno, #addexpaddress1, #addexpaddress2"
            ).val("");
            $("#addexpcountry").val("").trigger("change");
            return;
        }

        // Find exporter from stored list
        const exporter = exporterListArray.find((e) => e.id == selectedId);
        console.log(exporter);
        if (exporter) {
            $("#expid").val(exporter.id || "");
            $("#expname").val(exporter.name || "");
            $("#expfonno").val(exporter.phone_no || "");
            $("#expaddress1").val(exporter.address1 || exporter.address || "");
            $("#expcountryCode").val(exporter.ccode || "");
            // $('#addexpaddress2').val(exporter.address2 || '');
            $("#expcountry").val(exporter.country || "");
        }

        loadConsignmentSelection();
    });
}

function loadExporterList() {
    $.ajax({
        url: url, // same route from data-route
        type: "GET", // equivalent to $.get()
        dataType: "json", // expect JSON response
        cache: false, // prevent caching
        success: function (data) {
            exporterListArray = [];
            exporterListArray = data; // store full list in memory
            // console.log(exporterList);

            // optional: update dropdown immediately
            const select = $("#selectexp");
            select
                .empty()
                .append('<option value="">-- Select Exporter --</option>');
            data.forEach((exp) => {
                select.append(`<option value="${exp.id}">${exp.name}</option>`);
            });

            // if you're using select2
            if (select.hasClass("xintra-select2")) {
                select.trigger("change.select2");
            }
        },
        error: function (xhr, status, error) {
            console.error(
                "❌ Failed to reload exporter list:",
                xhr.responseText
            );
            Swal.fire({
                icon: "error",
                title: "Failed to Load Exporters",
                text: "Please try again or check your connection.",
            });
        },
    });
}

    function rebuildExporterSelect() {
        const select = $("#selectexp");
        const url = select.data("route");

        $.ajax({
            url: url,
            type: "GET",
            success: function (data) {
                // Clear old options
                select.empty();

                // Add the default option
                select.append(
                    '<option value="">-- Select Exporter --</option>'
                );

                // Populate new options from response
                $.each(data, function (index, exporter) {
                    select.append(
                        `<option value="${exporter.id}">${exporter.name}</option>`
                    );
                });

                // If using select2, reinitialize
                if (select.hasClass("xintra-select2")) {
                    select.trigger("change.select2");
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("❌ Failed to reload exporter list.");
            },
        });
    }

$(document).ready(function () {
    const modalEl = document.getElementById("addExporterModal");
    const modal = new bootstrap.Modal(modalEl);

    $("#openExporterModalBtn").on("click", function (e) {
        e.preventDefault();
        modal.show();
    });

    $("#addExporterbtn").on("click", function (e) {
        e.preventDefault();
        // console.log("show this - ");
        const routeUrl = $(this).data("route");
        // console.log(routeUrl);
        // Collect input values
        const name = $("#addexpName").val();
        const phone_no = $("#addexpfonno").val();
        const address1 = $("#addexpaddress1").val();
        const address2 = $("#addexpaddress2").val();
        const full_address = address1 + " " + address2;
        const country = $("#addexpcountry").val();

        // Optional: validation check before sending
        if (!name || !phone_no || !country) {
            alert("⚠️ Please fill in all required fields.");
            return;
        }

        $.ajax({
            url: routeUrl, // Laravel route
            type: "POST",
            data: {
                name: name,
                phone_no: phone_no,
                address: full_address,
                country: country,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                rebuildExporterSelect();
                // Example success alert
                Swal.fire({
                    icon: "success",
                    title: "Exporter Saved!",
                    text: "The exporter has been successfully added to the list.",
                    showConfirmButton: false,
                    timer: 1800,
                    timerProgressBar: true,
                    position: "center",
                });
                $("#addExporterModal").modal("hide");

                // Optionally clear form
                $(
                    "#addexpName, #addexpfonno, #addexpaddress1, #addexpaddress2, #addexpcountry"
                ).val("");

                loadExporterList();
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert(
                    "❌ Failed to save exporter. Please check your input or try again."
                );
            },
        });
    });

    $("#btnFindImp").on("click", function (e) {
        const findtheimp = document.getElementById("findImporter").value.trim();

        if (!findtheimp) {
            alert("⚠️ Identity number is empty!");
            return;
        }

        fetch(`${window.baseUrl}/public/get_importers/` + findtheimp)
            .then((response) => response.json())
            .then((data) => {
                // NOT FOUND (status = not_found)
                if (data.status === "not_found") {
                    document.getElementById("searchresult").style.display =
                        "block";
                    document.getElementById("doanotver").style.display = "none";
                    document.getElementById("emailnotver").style.display =
                        "none";
                    document.getElementById("impname").value = ""; // clear input
                    document.getElementById("impid").value = "";
                    document.getElementById("impfonno").value = "";
                    document.getElementById("impaddress1").value = "";
                    document.getElementById("impaddress2").value = "";
                    document.getElementById("imp_id").value = "";
                    document.getElementById("impemail").value = "";
                    return;
                }

                if (data.status === "not_verified_email") {
                    document.getElementById("emailnotver").style.display =
                        "block";
                    document.getElementById("searchresult").style.display =
                        "none";
                    document.getElementById("doanotver").style.display = "none";
                    document.getElementById("impname").value = ""; // clear input
                    document.getElementById("impid").value = "";
                    document.getElementById("impfonno").value = "";
                    document.getElementById("impaddress1").value = "";
                    document.getElementById("impaddress2").value = "";
                    document.getElementById("imp_id").value = "";
                    document.getElementById("impemail").value = "";
                    return;
                }

                if (data.status === "not_verified_doa") {
                    document.getElementById("doanotver").style.display =
                        "block";
                    document.getElementById("searchresult").style.display =
                        "none";
                    document.getElementById("emailnotver").style.display =
                        "none";
                    document.getElementById("impname").value = ""; // clear input
                    document.getElementById("impid").value = "";
                    document.getElementById("impfonno").value = "";
                    document.getElementById("impaddress1").value = "";
                    document.getElementById("impaddress2").value = "";
                    document.getElementById("imp_id").value = "";
                    document.getElementById("impemail").value = "";
                    return;
                }

                // SUCCESS
                console.log("SUCCESS:", data);

                // Hide warning message
                document.getElementById("searchresult").style.display = "none";
                document.getElementById("doanotver").style.display = "none";
                document.getElementById("emailnotver").style.display = "none";

                // Fill importer name
                document.getElementById("impname").value = data.data.fullname;
                document.getElementById("impid").value = data.data.id;
                document.getElementById("impfonno").value =
                    data.data.phone_number;
                document.getElementById("impaddress1").value =
                    data.data.address_1;
                document.getElementById("impaddress2").value =
                    data.data.address_2;
                document.getElementById("imp_id").value = data.data.id;
                document.getElementById("impemail").value = data.data.email;
            })
            .catch((error) => {
                console.error("ERROR:", error);
            });
    });



    rebuildExporterSelect();
    exporterList();
});
