import $ from "jquery";
import Swal from "sweetalert2";


$(document).ready(function() {

    // console.log("BASE URL =", window.baseUrl);
    // fetch(`${window.baseUrl}/public/get_consignment/smy`)
    // .then(response => response.json())
    // .then(data => console.log(data));

    function loadUses(id){
        fetch(`${window.baseUrl}/public/consignment_uses/` + id)
            .then(response => response.json())
            .then(data => {
                const itemselection = document.getElementById('itemUses');

                itemselection.innerHTML = '<option value="">-- Select Item --</option>';
                console.log(data);
                data.data.forEach(row => {
                    const opt = document.createElement('option');
                    opt.value = row;
                    opt.textContent = row;
                    itemselection.appendChild(opt);
                });
        });
    }

    function loadConsignmentSelection(){
        var countryCoded = document.getElementById('expcountryCode').value;
        
        fetch(`${window.baseUrl}/public/get_consignment/` + countryCoded)
            .then(response => response.json())
            .then(data => {
                const itemselection = document.getElementById('itemSelect');

                itemselection.innerHTML = '<option value="">-- Select Item --</option>';

                data.forEach(row => {
                    const opt = document.createElement('option');
                    opt.value = row.id;
                    opt.textContent = row.entry_display;
                    itemselection.appendChild(opt);
                });
        });
    }// Make the function globally accessible:
    window.loadConsignmentSelection = loadConsignmentSelection;


    const select = $('#itemSelect');
    select.on('change', function() {
        const selectedId = $(this).val();
        console.log(selectedId);
        loadUses(selectedId);
    });
});