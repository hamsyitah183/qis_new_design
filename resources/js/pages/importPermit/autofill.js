import $ from "jquery";
import Swal from "sweetalert2";
import { summarySubmit } from "./summarysubmit";

$(document).ready(function() {
    const select = $('#selectexp');
    const url = select.data('route');
    let exporterList = [];

    // get all exporters when the page loads
    $.ajax({
        url: url,           // same route from data-route
        type: 'GET',        // equivalent to $.get()
        dataType: 'json',   // expect JSON response
        cache: false,       // prevent caching
        success: function(data) {
            exporterList = data; // store full list in memory
            // console.log(exporterList);

            // optional: update dropdown immediately
            const select = $('#selectexp');
            select.empty().append('<option value="">-- Select Exporter --</option>');
            data.forEach(exp => {
                select.append(`<option value="${exp.id}">${exp.name}</option>`);
            });

            // if you're using select2
            if (select.hasClass('xintra-select2')) {
                select.trigger('change.select2');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Failed to reload exporter list:', xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Failed to Load Exporters',
                text: 'Please try again or check your connection.'
            });
        }
    });

    // 2️⃣ When user changes selection
    select.on('change', function() {
        const selectedId = $(this).val();
        // console.log(selectedId);
        // Clear fields if no selection
        if (!selectedId) {
            $('#expid, #addexpName, #addexpfonno, #addexpaddress1, #addexpaddress2').val('');
            $('#addexpcountry').val('').trigger('change');
            return;
        }

        // Find exporter from stored list
        const exporter = exporterList.find(e => e.id == selectedId);
        console.log(exporter);
        if (exporter) {
            $('#expid').val(exporter.id || '');
            $('#expname').val(exporter.name || '');
            $('#expfonno').val(exporter.phone_no || '');
            $('#expaddress1').val(exporter.address1 || exporter.address || '');
            $('#expcountryCode').val(exporter.ccode || '');
            // $('#addexpaddress2').val(exporter.address2 || '');
            $('#expcountry').val(exporter.country || '');
        }

        loadConsignmentSelection();
    });
});

