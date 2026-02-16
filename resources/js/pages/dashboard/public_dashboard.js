import ApexCharts from 'apexcharts'
import $ from "jquery";
import Swal from "sweetalert2";
function getReviewCount()
{
    // /application/review/list/data
    return $.ajax({
        url: '/application/review/list/data',
        type: "GET",
        dataType: "json",
        cache: false
    }).done(response => {
        console.log('getreviewcount', response)
        let recordCount = response.recordsTotal;

        if(recordCount > 0) {
            $('#toReviewCount').html(`To Review <span class="badge ms-3 bg-success p-1">${recordCount}</span>`)
        } else {
            $('#toReviewCount').html(`To Review`)
        }
       

    }).fail(xhr => {
        console.error("Failed to load application count:", xhr.responseText);
        // Swal.fire({
        //     icon: "error",
        //     title: "Failed to Load Data",
        //     text: "Please try again or check your connection.",
        // });
    });
}

export function public_dashboard() {
    getReviewCount()
}
