import $ from "jquery";
import Swal from "sweetalert2";

export function summarySubmit() {
    document.addEventListener("DOMContentLoaded", function () {
        const generateBtn = document.getElementById("generateSummary");

        generateBtn.addEventListener("click", function () {
            const formData = new FormData();

            const sourceTable = document.querySelector("#itemListTbl tbody");
            const targetTable = document.querySelector("#summaryTable3 tbody");

            // get importer's and exporter's detail
            const importerData = {
                id: document.getElementById("impid").value,
                name: document.getElementById("impname").value,
                phone: document.getElementById("impfonno").value,
                address1: document.getElementById("impaddress1").value,
                address2: document.getElementById("impaddress2").value,
                email: document.getElementById("impemail").value,
            };
            const impAddrs = importerData.address2
                ? `${importerData.address1}, ${importerData.address2}`
                : importerData.address1;

            console.log(importerData);

            const exporterData = {
                id: document.getElementById("expid").value,
                name: document.getElementById("expname").value,
                fonno: document.getElementById("expfonno").value,
                address: document.getElementById("expaddress1").value,
                countryCde: document.getElementById("expcountryCode").value,
                country: document.getElementById("expcountry").value,
            };
            console.log(exporterData);

            const permitDetails = {
                applCate: document.getElementById("app_cate").value,
                eta: document.getElementById("eta").value,
                tranType: document.getElementById("trnptType").value,
                entrypoint: document.getElementById("entryPoint").value,
            };
            console.log(permitDetails);

            document.getElementById("importerName").textContent =
                importerData.name;
            document.getElementById("importerPhoneno").textContent =
                importerData.phone;
            document.getElementById("simpAdd").textContent = impAddrs;
            document.getElementById("sexpName").textContent = exporterData.name;
            document.getElementById("sexpfonno").textContent =
                exporterData.fonno;
            document.getElementById("sexpAddress").textContent =
                exporterData.address;
            document.getElementById("sexpCountry").textContent =
                exporterData.country;
            document.getElementById("seta").textContent = permitDetails.eta;
            document.getElementById("strty").textContent =
                permitDetails.tranType;
            document.getElementById("sentryp").textContent =
                permitDetails.entrypoint;

            // ✅ Clear existing rows in summary table
            targetTable.innerHTML = "";

            // ✅ Copy each row from source table
            const rows = sourceTable.querySelectorAll("tr");
            rows.forEach((row, index) => {
                const cols = row.querySelectorAll("td");
                console.log(cols);

                // Extract text content from each column
                const rowData = Array.from(cols).map((td) =>
                    td.textContent.trim()
                );

                // Build new row for summary table (excluding "Action" column)
                targetTable.insertAdjacentHTML(
                    "beforeend",
                    `
            <tr>
            <td>${index + 1}</td>
            <td>${rowData[1] || ""}</td>
            <td>${rowData[2] || ""}</td>
            <td>${rowData[3] || ""}</td>
            <td>${rowData[4] || ""}</td>
            <td>${rowData[5] || ""}</td>
            <td>${rowData[6] || ""}</td>
            </tr>
        `
                );
            });

            // ✅ Optional: Scroll to or highlight summary section
            document
                .querySelector("#summaryTable3")
                .scrollIntoView({ behavior: "smooth" });

            formData.append("exporterData", JSON.stringify(exporterData));
            formData.append("importerData", JSON.stringify(importerData));
            formData.append("permitDetails", JSON.stringify(permitDetails));
            formData.append("items", JSON.stringify(tempItems));
            console.log(" Summary  generated!");

            var saveingurl = `${window.baseUrl}/public/save-application`;
            var redirectUrl = `${window.baseUrl}/public/view_all_application`;
            // Send AJAX request
            function saveapplication() {
                $.ajax({
                    url: saveingurl,
                    type: "POST",
                    data: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    processData: false, // IMPORTANT: do not convert to string
                    contentType: false, // IMPORTANT: allow multipart/form-data
                    success: function (response) {
                        // console.log("SUCCESS RESPONSE:");
                        // console.log(response);
                        Swal.fire({
                            icon: "success",
                            title: "Application submited!",
                            text: "The exporter has been successfully added to the list.",
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true,
                            position: "center",
                        });
                        setTimeout(() => {
                            window.location.href = redirectUrl;
                        }, 1500);
                    },
                    error: function (xhr) {
                        console.error("ERROR RESPONSE:");
                        console.error(xhr.responseText);
                    },
                });
            }

            $(document).on("click", "#submitApps", function () {
                console.log("submit clicked!");
                saveapplication();
            });
        });
    });
}

summarySubmit()