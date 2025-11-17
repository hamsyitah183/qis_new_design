import $ from "jquery";
import Swal from "sweetalert2";



    
    var tempDropzoneUrl = `${window.baseUrl}/public/temp_upload`;
    const itemDropzone = new Dropzone("#itemDropzone", {
            url: tempDropzoneUrl,
            paramName: "file",
            maxFilesize: 10,
            acceptedFiles: ".jpg,.jpeg,.png,.pdf",
            addRemoveLinks: true,
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            success: function(file, response) {
                // store temporary file info returned from backend
                tempAttachments.push({
                    id: response.id,
                    original_name: response.original_name,
                    temp_name: response.temp_name,
                    temp_path: response.temp_path,
                    mime_type: response.mime_type,
                    size: response.size
                });

                file.temp_id = response.id; // attach temp ID to the file object
            }
        });

    // itemDropzone.on("success", function (file, response) {
    //         tempAttachments.push({
    //             id: response.file_id,
    //             name: response.file_name,
    //             path: response.file_url,
    //             type: response.file_type
    //         });
    //     });


    document.getElementById('saveBtn').addEventListener('click', function (e) {
        e.preventDefault();
        console.log("savebutton!");
        const itemSelect = document.getElementById('itemSelect');
        const itemValue = document.getElementById('itemValue').value.trim();
        const itemQuantity = document.getElementById('itemQuantity').value.trim();
        const itemMeasure = document.getElementById('itemMeasure').value.trim();
        const itemPurpose = document.getElementById('itemPurpose');
        const itemUses = document.getElementById('itemUses');
        
        
        // const itemDropzone = Dropzone.forElement('#itemDropzone',{
        // const itemDropzone = new Dropzone('#itemDropzone', {
        //     url: "",
        //     paramName: "file",
        //     maxFilesize: 10,
        //     acceptedFiles: ".jpg,.jpeg,.png,.pdf",
        //     addRemoveLinks: true,
        //     headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content }
        // });

        const uploadedFileNames = itemDropzone.getAcceptedFiles().map(file => file.upload?.filename || file.name);
        const uploadedFile = uploadedFileNames.join(', ') || '—';

        // const getDropz = new Dropzone('#itemDropzone');
        // tempAttachments = itemDropzone.files.map(f => ({
        //     name: f.upload?.filename || f.name,
        //     size: f.size,
        //     type: f.type,
        // }));

        // const getFiles = itemDropzone.files;
        // tempAttachments.push(getFiles);

        // console.log("huha: "+getFiles);
        // console.log(getFiles);
        
        
        const newItem = {
            id: crypto.randomUUID(),
            item_id: itemSelect.value,
            item_name: itemSelect.options[itemSelect.selectedIndex].text,
            value: itemValue,
            quantity: itemQuantity,
            measure: itemMeasure,
            purpose: itemPurpose.value,
            uses: itemUses.value,
            temp: tempAttachments,
            attachments: [uploadedFile] // ✅ actual uploaded file info
        };
        
        tempItems.push(newItem);
        console.table(tempItems);

        // Optional: show in table
        const tableBody = document.querySelector('#itemListTbl tbody');
        tableBody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${tableBody.rows.length + 1}</td>
                <td>${newItem.item_name}</td>
                <td>${newItem.quantity} ${newItem.measure}</td>
                <td>${newItem.purpose}</td>
                <td>${newItem.uses}</td>
                <td>RM ${newItem.value}</td>
                <td>${newItem.attachments}</td>
                <td class="text-center"><button class="btn btn-sm btn-danger btn-remove">Remove</button></td>
            </tr>
        `);

        // ✅ Clear modal + Dropzone data for next item
        itemSelect.selectedIndex = 0;
        document.getElementById('itemValue').value = '';
        document.getElementById('itemQuantity').value = '';
        document.getElementById('itemMeasure').value = '';
        itemPurpose.selectedIndex = 0;
        itemUses.selectedIndex = 0;
        itemDropzone.removeAllFiles(true);
        // itemDropzone = []; // clear for next item
        itemDropzone.removeAllFiles(true);

        tempAttachments = [];
        const modal = bootstrap.Modal.getInstance(document.getElementById('addItemModal'));
        modal.hide();
    });


    console.log(tempItems);