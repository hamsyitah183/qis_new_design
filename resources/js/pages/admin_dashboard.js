import ApexCharts from 'apexcharts'

console.log('hello admin dashboard')

document.addEventListener("DOMContentLoaded", function () {

    const chartId = window.clerkVolumeChartId;
    if (!chartId) return;

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



