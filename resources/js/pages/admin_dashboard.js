import ApexCharts from 'apexcharts'

console.log('hello admin dashboard')

document.addEventListener("DOMContentLoaded", function () {

    const chartId = window.clerkVolumeChartId;
    if (!chartId) return;

    console.log('chartId', chartId)

    // Wait until ApexCharts instance exists
    const interval = setInterval(() => {
        if (!window.ApexCharts || !ApexCharts.instances) return;

        const chart = ApexCharts.instances.find(c => c.id === chartId);

        if (chart) {
            chart.hideSeries("Import Permit");
            chart.hideSeries("Inspection");
            chart.hideSeries("Consignment");
            clearInterval(interval);
        }
    }, 200);
});


// ip count
function applicationCount()
{
    return $.ajax({
        url: '/application/count',
        type: "GET",
        dataType: "json",
        cache: false,
        success: (response) => {
            let data= response.data
            console.log('data application', data)
            $('#ipCount').text(data.ipCount)
            $('#icCount').text(data.icCount)
            $('#ccCount').text(data.ccCount)
            $("#amount").text('RM ', data.mount)
        },
        error: (xhr) => {
            console.error("Failed to load exporters:", xhr.responseText);
            Swal.fire({
                icon: "error",
                title: "Failed to Load Exporters",
                text: "Please try again or check your connection.",
            });
        },
    });
}

applicationCount()

