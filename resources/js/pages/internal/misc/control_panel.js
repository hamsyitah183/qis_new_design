import $ from "jquery";
import Swal from "sweetalert2";

// var tempDropzoneUrl = `${window.baseUrl}/public/temp_upload`;

let cateName = "";

$(document).ready(function () {
    // console.log("Control Panel JS loaded");

    // Initialize Bootstrap modal
    var modalElement = document.getElementById("editItemModal");
    var modal = new bootstrap.Modal(modalElement, {
        backdrop: "static",
        keyboard: false,
    });

    const thismodal = new bootstrap.Modal(
        document.getElementById("addGenericModal"),
        {
            backdrop: "static",
            keyboard: false,
        }
    );

    // load PB data into tables
    function loadPBData(cate) {
        let tableId;

        // 1️⃣ Show loading Swal
        Swal.fire({
            title: "Loading data...",
            text: "Please wait",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        switch (cate) {
            case "district_entry":
                tableId = "#tabletab1";
                document.getElementById("editICOde").disabled = true;
                break;

            case "condition_category":
                tableId = "#tabletab4";
                document.getElementById("editICOde").disabled = true;
                break;

            case "consignment_purpose":
                tableId = "#tabletab2";
                document.getElementById("editICOde").disabled = true;
                break;

            case "unit_measurement":
                tableId = "#tabletab3";
                console.log('unit measurement clicked');
                document.getElementById("editICOde").disabled = false;
                $('#editItemModal #editICOde').attr('disabled', false)
                break;

            case "reject_purpose":
                tableId = "#tabletab5";
                document.getElementById("editICOde").disabled = true;
                break;

            case "consignment_category":
                tableId = "#tabletab_category";
                break;
                

            default:
                Swal.close();
                return;
        }

        $.ajax({
            url: `/internal/get_pbdata/${cate}`,
            type: "GET",
            success: function (response) {
                let tbody = $(tableId).find("tbody");
                tbody.empty();

                if (response.status === "success" && response.data.length > 0) {
                    response.data.forEach((item) => {
                        if (cate == "district_entry") {
                            entryPlaces(item);
                        }
                        const countTd =
                            cate === "district_entry"
                                ? `
                            <td>
                                <button class="btn btn-sm btn-outline-info open-entry-modal"
                                    data-id="${item.id}"
                                    data-places='${JSON.stringify(
                                        item.places ?? []
                                    )}'>
                                    ${item.places.length} Places
                                </button>
                            </td>`
                                : "";

                        let row = `
                        <tr>
                            <td>${item.description ?? "-"}  </td>
                            ${countTd}
                            <td>
                                <div class="hstack gap-2 flex-wrap">
                                    <a href="javascript:void(0);" class="text-info fs-14 lh-1"
                                       onclick="getspecificPBData(${item.id})">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="text-danger fs-14 lh-1"
                                       onclick="deletePBData(${item.id})">
                                        <i class="ri-delete-bin-5-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append(`
                    <tr>
                        <td colspan="2" class="text-center text-danger">
                            No record found
                        </td>
                    </tr>
                `);
                }

                // 2️⃣ Close loading Swal after render
                Swal.close();
            },
            error: function (xhr) {
                console.error("Error fetching PB data:", xhr);

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to load data. Please try again.",
                });
            },
        });
    }

    function openEntryPointModal() {
        $(document).on("click", ".open-entry-modal", function () {
            const districtId = $(this).data("id");
            const places = $(this).data("places");

            $("#districtId").val(districtId);
            $("#placeList").empty();

            if (places.length) {
                places.forEach((p) => {
                    // console.log('the item', p)
                    appendPlaceRow(p.entry_name ?? p, p.transport_type);
                });
            } else {
                appendPlaceRow("");
            }

            $("#entryPointModal").modal("show");
        });
    }

    openEntryPointModal();

    function appendPlaceRow(value = "", transportType = "") {
        const transportOptions = ["Air", "Land", "Sea"];
        let optionsHtml = transportOptions
            .map(
                (type) =>
                    `<option value="${type.toLowerCase()}" ${
                        transportType.toLowerCase() === type.toLowerCase()
                            ? "selected"
                            : ""
                    }>${type}</option>`
            )
            .join("");

        $("#placeList").append(`
        <div class="d-flex gap-2 place-row mb-2">
            <input type="text"
                   name="places[]"
                   class="form-control"
                   placeholder="Enter place name"
                   value="${value}">
            <select name="transport_types[]" class="form-select w-auto">
                ${optionsHtml}
            </select>
            <button type="button"
                    class="btn btn-outline-danger remove-place">
                ✕
            </button>
        </div>
    `);
    }

    $(document).on("click", "#addPlaceBtn", function () {
        appendPlaceRow();
    });

    $(document).on("click", ".remove-place", function () {
        $(this).closest(".place-row").remove();
    });

    $(document).on("click", "#submitEntryPoint", function (e) {
        e.preventDefault();

        // 1️⃣ Show loading Swal
        Swal.fire({
            title: "Saving...",
            text: "Please wait",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            url: "/internal/district/entry-point/update",
            type: "POST",
            data: $("#entryPointForm").serialize(),
            success: function () {
                // Close loading Swal first
                Swal.close();

                // Then show success
                Swal.fire("Saved!", "Entry points updated.", "success");

                $("#entryPointModal").modal("hide");
                loadPBData("district_entry");
            },
            error: function (xhr) {
                // Close loading Swal first
                Swal.close();

                Swal.fire(
                    "Error",
                    xhr.responseJSON?.message ?? "Failed to save entry points.",
                    "error"
                );
            },
        });
    });

    function entryPlaces(district) {
        console.log(district);
    }

    function loadPBDataForAllCategories() {
        const categories = [
            "district_entry",
            "condition_category",
            "consignment_purpose",
            "unit_measurement",
            "reject_purpose",
        ];
        categories.forEach((cate) => {
            loadPBData(cate);
        });
    }
    loadPBDataForAllCategories();


    function loadBranches() {
        $.ajax({
            url: `/internal/branches`,
            type: "GET",
            success: function (response) {
                let tbody = $("#tabletab_branch").find("tbody");
                tbody.empty();

                if (response.status === "success" && response.data.length > 0) {
                    response.data.forEach((item) => {
                        let row = `
                        <tr>
                            <td>${item.name}</td>
                            <td>
                                <div class="hstack gap-2 flex-wrap justify-content-center">
                                    <a href="javascript:void(0);" class="text-info fs-14 lh-1"
                                       onclick="editBranch(${item.id}, '${item.name.replace(/'/g, "\\'")}')"> 
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="text-danger fs-14 lh-1"
                                       onclick="deleteBranch(${item.id})">
                                        <i class="ri-delete-bin-5-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append(`
                    <tr>
                        <td colspan="2" class="text-center text-danger">
                            No branches found
                        </td>
                    </tr>
                    `);
                }
            },
            error: function (xhr) {
                console.error("Error fetching branches:", xhr);
            },
        });
    }

    loadBranches();

    function editBranch(id, name) {
        document.getElementById("editItemId").value = id;
        document.getElementById("editICOde").value = "";
        document.getElementById("editDesc").value = name;
        document.getElementById("editICOde").disabled = true;
        $("#editItemModal .modal-title").html('<i class="ri-edit-line me-1"></i> Edit Branch');
        cateName = "branch_entry";
        modal.show();
    }
    window.editBranch = editBranch;

    function deleteBranch(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "This branch will be permanently deleted!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/internal/branch/delete/${id}`,
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: "The branch has been deleted.",
                                timer: 2000,
                                showConfirmButton: false,
                            });
                            loadBranches();
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: response.message || "Failed to delete the branch.",
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: xhr.responseJSON?.message || "An error occurred while deleting.",
                        });
                    },
                });
            }
        });
    }
    window.deleteBranch = deleteBranch;

    // fetch Public Code to modal
    // fetch Public Code to modal
    function getspecificPBData(id) {

        Swal.fire({
            title: "Loading data...",
            text: "Please wait",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $.ajax({
            url: `/internal/getspecificpbdata/${id}`,
            type: "GET",
            success: function (response) {

                if (response.status === "success" && response.data) {

                    const data = response.data;

                    // Populate basic fields
                    $("#editItemId").val(data.id);
                    $("#editICOde").val(data.cate_code);
                    $("#editDesc").val(data.description);

                    // 🔥 Always clear conversion container first
                    $("#conversionContainer").html("");

                    // ✅ Only show conversion if unit_measurement
                    if (data.cate_name === "unit_measurement") {

                        const conversionValue = data.conversion
                            ? data.conversion.conversion
                            : "";

                        const conversionInput = `
                            <div class="mb-3" id="conversionWrapper">
                                <label class="form-label">
                                    Conversion (1 ${data.cate_code} = ? KG)
                                </label>
                                <input 
                                    type="number" 
                                    step="0.000001"
                                    name="conversion"
                                    value="${conversionValue}"
                                    id="conversion"
                                    class="form-control">
                            </div>
                        `;

                        $("#conversionContainer").html(conversionInput);
                    }

                    Swal.close();
                    modal.show();

                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "No Data",
                        text: "Unable to load item data.",
                    });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to load data. Please try again.",
                });
            }
        });
    }

    window.getspecificPBData = getspecificPBData;

    // handle edit item form submission
    document
        .getElementById("saveEditBtn")
        .addEventListener("click", function () {
            const id = document.getElementById("editItemId").value;
            const code = document.getElementById("editICOde").value;
            const desc = document.getElementById("editDesc").value;

            // Handle optional conversion for unit_measurement
            const conversionInput = document.getElementById("editConversion");
            const newConversionInput = document.getElementById("conversion");
            const conversion = conversionInput ? conversionInput.value : newConversionInput ? newConversionInput.value : "";

            const fd = new FormData();
            fd.append("id", id);
            fd.append("item_code", code);
            fd.append("item_desc", desc);
            fd.append('conversion',conversion )

            $.ajax({
                url: `/internal/updatepbdata`,
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "Item updated successfully.",
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        loadPBDataForAllCategories();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: response.message || "Failed to update item.",
                            timer: 3000,
                            showConfirmButton: false,
                        });
                    }
                },
                error: function (xhr) {
                    console.error("Error updating PB data:", xhr);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "An error occurred while updating the item.",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                },
            });
            // Close modal
            modal.hide();
        });

    function deletePBData(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/internal/deletepbdata/${id}`,
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: "The item has been deleted.",
                                timer: 2000,
                                showConfirmButton: false,
                            });
                            loadPBDataForAllCategories();
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    response.message ||
                                    "Failed to delete the item.",
                                timer: 3000,
                                showConfirmButton: false,
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error("Error deleting PB data:", xhr);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "An error occurred while deleting the item.",
                            timer: 3000,
                            showConfirmButton: false,
                        });
                    },
                });
            }
        });
    }
    window.deletePBData = deletePBData;

    // Show modal and set values
    function addmodal(cate) {
        const modalEl = document.getElementById("addGenericModal");
        const thismodal = new bootstrap.Modal(modalEl);
        thismodal.show();

        let categoryTitle;
        switch (cate) {
            case "entry":
                categoryTitle = "District Entry";
                cateName = "district_entry";
                $("#addCodev").prop("disabled", true);
                break;
            case "condition":
                categoryTitle = "Description Form";
                cateName = "condition_category";
                $("#addCodev").prop("disabled", true);
                break;
            case "purpose":
                categoryTitle = "Purpose of Import";
                cateName = "consignment_purpose";
                $("#addCodev").prop("disabled", true);
                break;
            case "measurement":
                categoryTitle = "Unit Measurement";
                cateName = "unit_measurement";
                $("#addCodev").prop("disabled", false);
                const conversionInput = `
                    <div class="mb-3" id="conversionWrapper">
                        <label class="form-label">
                            Conversion to KG (1 unit = ? KG)
                        </label>
                        <input 
                            type="number" 
                            step="0.000001"
                            name="conversion"
                            
                            id="addConversion"
                            class="form-control">
                    </div>
                `;
                $('#addGenericModal #addGenericForm .modal-body').append(conversionInput)
                break;
            case "reject":
                categoryTitle = "Rejection Notes";
                cateName = "reject_purpose";
                $("#addCodev").prop("disabled", true);
                break;
            case "branch":
                categoryTitle = "Branch";
                cateName = "branch_entry";
                $("#addCodev").prop("disabled", true);
                break;
        }

        $("#addItemType").val(cateName);

        // Set modal title safely
        const titleEl = modalEl.querySelector(".modal-title");
        if (titleEl) titleEl.innerText = `Add ${categoryTitle}`;
    }

    window.addmodal = addmodal;

    // Trigger when the Save button is clicked
    $(document).on("click", "#saveGenericBtn", function (e) {
        e.preventDefault(); // prevent any default behavior

        console.log("Add generic button clicked!");

        const cate = cateName;
        const code = $("#addCodev").val();
        const desc = $("#addDescv").val();

        const conversionInput = document.getElementById("addConversion");

        // Branch add uses a different endpoint
        if (cate === "branch_entry") {
            const fd = new FormData();
            fd.append("name", desc);

            $.ajax({
                url: `/internal/branch/add`,
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    $("#addGenericModal").modal("hide");
                    $("#addDescv").val("");

                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "Branch added successfully.",
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        loadBranches();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: response.message || "Failed to add branch.",
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: xhr.responseJSON?.message || "An error occurred while adding the branch.",
                    });
                },
            });
            return;
        }

        const fd = new FormData();
        fd.append("category", cate);
        fd.append("item_code", code);
        fd.append("item_desc", desc);
        fd.append('conversion', conversionInput ? conversionInput.value : "")

        $.ajax({
            url: `/internal/addpbdata`,
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#addGenericModal").modal("hide");

                // Clear form inputs
                $("#addItemType").val("");
                $("#addCodev").val("");
                $("#addDescv").val("");

                if (response.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: "Item added successfully.",
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    loadPBDataForAllCategories();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message || "Failed to add item.",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (xhr) {
                console.error("Error adding PB data:", xhr);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "An error occurred while adding the item.",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
        });
    });
});
                     