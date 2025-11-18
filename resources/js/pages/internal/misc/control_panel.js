import $ from "jquery";
import Swal from "sweetalert2";


// var tempDropzoneUrl = `${window.baseUrl}/public/temp_upload`;

$(document).ready(function() {
    // console.log("Control Panel JS loaded");
    
    // Initialize Bootstrap modal
    var modalElement = document.getElementById("editItemModal");
    var modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
    });

    function loadPBData(cate) {
        var tableId;
        switch (cate) {
            case "district_entry":
                tableId = "#tabletab1";
                break;

            case "condition_category":
                tableId = "#tabletab2";
                break;
            
            case "consignment_purpose":
                tableId = "#tabletab3";
                break;

            case "unit_measurement":
                tableId = "#tabletab4";
                break;

            case "reject_purpose":
                tableId = "#tabletab5";
                break;

            default:
                break;
        }
        $.ajax({
            url: `${window.baseUrl}/internal/get_pbdata/${cate}`,   
            type: 'GET',
            success: function(response) {   
                let tbody  = $(tableId).find("tbody");
                tbody.empty(); // clear old rows
                
                if (response.status === 'success' && response.data.length > 0) {     

                    response.data.forEach((item, index) => {

                        let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.cate_code ?? '-'}</td>
                                <td>${item.description ?? '-'}</td>
                                <td>
                                    <div class="hstack gap-2 flex-wrap">
                                        <a href="javascript:void(0);" class="text-info fs-14 lh-1" onclick="getspecificPBData(${item.id})">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="text-danger fs-14 lh-1" onclick="deletePBData(${item.id})">
                                            <i class="ri-delete-bin-5-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        `;

                        tbody.append(row);
                    });
                } else {
                    // No record found
                    tbody.append(`
                        <tr>
                            <td colspan="4" class="text-center text-danger">No record found</td>
                        </tr>
                    `);
                }
                
            },
            error: function(xhr) {
                console.error('Error fetching PB data:', xhr);
            }
        });
    }

    function loadPBDataForAllCategories() {
        const categories = [
            "district_entry",
            "condition_category",
            "consignment_purpose",
            "unit_measurement",
            "reject_purpose"
        ];
        categories.forEach(cate => {
            loadPBData(cate);
        });
    } loadPBDataForAllCategories();


    // fetch Public Code to modal
    function getspecificPBData(id) {
        modal.show();
        
        $.ajax({
            url: `${window.baseUrl}/internal/getspecificpbdata/${id}`,   
            type: 'GET',
            success: function(response) {   
                if (response.status === 'success' && response.data) {     
                    document.getElementById("editItemId").value = response.data.id;
                    document.getElementById("editICOde").value = response.data.cate_code;
                    document.getElementById("editDesc").value = response.data.description;
                } 
            },
            error: function(xhr) {
                console.error('Error fetching specific PB data:', xhr);
            }
        });
    } window.getspecificPBData = getspecificPBData;

    // handle edit item form submission
    document.getElementById("saveEditBtn").addEventListener("click", function () {
        const id = document.getElementById("editItemId").value;
        const code = document.getElementById("editICOde").value;
        const desc = document.getElementById("editDesc").value;
        
        const fd = new FormData();
            fd.append("id", id);
            fd.append("item_code", code);
            fd.append("item_desc", desc);

        $.ajax({
            url: `${window.baseUrl}/internal/updatepbdata`,   
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {   
                if (response.status === 'success') {     
                    Swal.fire({ 
                        icon: 'success',
                        title: 'Success',
                        text: 'Item updated successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadPBDataForAllCategories();
                } else {
                    Swal.fire({ 
                        icon: 'error',  
                        title: 'Error',
                        text: response.message || 'Failed to update item.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }       
            },
            error: function(xhr) {
                console.error('Error updating PB data:', xhr);
                Swal.fire({ 
                    icon: 'error',  
                    title: 'Error',
                    text: 'An error occurred while updating the item.',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
        // Close modal
        modal.hide();
    });

    function deletePBData(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'    
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${window.baseUrl}/internal/deletepbdata/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({ 
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'The item has been deleted.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadPBDataForAllCategories();
                        } else {
                            Swal.fire({ 
                                icon: 'error',  
                                title: 'Error',
                                text: response.message || 'Failed to delete the item.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error deleting PB data:', xhr);
                        Swal.fire({ 
                            icon: 'error',  
                            title: 'Error', 
                            text: 'An error occurred while deleting the item.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });
    } window.deletePBData = deletePBData;

    function addmodal(cate) {
        var categoryTitle;
        var cateName;
        switch (cate) {
            case "entry":
                categoryTitle = "District Entry";
                cateName = "district_entry";
                break;
            case "condition":
                categoryTitle = "Condition Category";
                cateName = "condition_category";
                break;
            case "purpose":
                categoryTitle = "Consignment Purpose";
                cateName = "consignment_purpose";
                break;
            case "measurement":
                categoryTitle = "Unit Measurement";
                cateName = "unit_measurement";
                break;  
            case "reject":
                categoryTitle = "Rejection Notes";
                cateName = "reject_purpose";
                break;
            default:
                break;
        }
    }
});

