import $ from "jquery";
import Swal from "sweetalert2";


$(document).ready(function() {
    const trnptType = document.getElementById('trnptType');
    const detailsSelect = document.getElementById('transportDetails');
    
    if (!trnptType) return;

    trnptType.addEventListener('change', function () {
        const value = this.value;                         // Air / Sea / Land
        const route = this.dataset.route;                 // /public/transport/options

        if (!value || route === '#') {
            detailsSelect.innerHTML = '<option value="">-- Select Option --</option>';
            return;
        }

        // build URL with the selected value as query param
        const url = `${route}?type=${encodeURIComponent(value)}`;
        console.log(url);
        $.ajax({
            url: url,            // the same URL you built earlier: route + ?type=value
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                console.log("something here");
                console.log(data);

                // rebuild next dropdown
                const detailsSelect = $('#entryPoint'); // if using jQuery
                // detailsSelect.empty().append('<option value="">-- Select Entry Point --</option>');
                // $.each(data, function (i, item) {
                //     detailsSelect.append(
                //         $('<option value=>', { value: item.id, text: item.entry_display })
                //     );
                // });
                let options = '<option value="">-- Select Entry Point --</option>';
                data.forEach(function (item) {
                    options += `<option value="${item.id}">${item.entry_display}</option>`;
                });
                detailsSelect.html(options);
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.log(xhr.responseText); // helpful for Laravel debug messages
            }
        });
    });

});

