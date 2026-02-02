import ApexCharts from 'apexcharts'
import $ from "jquery";
import Swal from "sweetalert2";

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
function applicationCount() {
    return $.ajax({
        url: '/application/count',
        type: "GET",
        dataType: "json",
        cache: false
    }).done(response => {
        const data = response.data;

        console.log('data application', data);

        $('#ipCount').text(data.ipCount ?? 0);
        $('#icCount').text(data.icCount ?? 0);
        $('#ccCount').text(data.ccCount ?? 0);

        // ✅ FIXED
        $('#amountRevenue').text(`RM ${data.amount ?? 0}`);
    }).fail(xhr => {
        console.error("Failed to load application count:", xhr.responseText);
        Swal.fire({
            icon: "error",
            title: "Failed to Load Data",
            text: "Please try again or check your connection.",
        });
    });
}


let dailyVolumeChart = null;
let userRegistrationChart = null;

async function loadDailyVolumeChart() {
    try {
        const res = await fetch('/internal/admin/dashboard/daily-volume');
        const data = await res.json();

        // 🔥 remove spinner BEFORE render / update
        $('#dailyVolumeChart .spinner-wrapper').remove();

        if (!dailyVolumeChart) {
            dailyVolumeChart = new ApexCharts(
                document.querySelector("#dailyVolumeChart"),
                {
                    chart: {
                        type: 'line',
                        height: 300,
                    },
                    title: {
                        text: 'Daily Application Volume',
                    },
                    subtitle: {
                        text: 'Total submissions across all modules (Last 7 Days)',
                    },
                    series: data.series,
                    xaxis: {
                        categories: data.days,
                    },
                    colors: ['#5c67f7', '#E354D4', '#FF5D9F', '#9E5CF7'],
                }
            );

            await dailyVolumeChart.render();
        } else {
            // 🔁 Update existing chart (no spinner needed)
            dailyVolumeChart.updateOptions({
                series: data.series,
                xaxis: { categories: data.days },
            });
        }
    } catch (error) {
        console.error('Failed to load daily volume chart', error);

        $('#dailyVolumeChart').html(`
            <div class="text-center text-danger">
                Failed to load chart
            </div>
        `);
    }
}

async function loadUserRegistration() {
    try {
        const res = await fetch('/internal/admin/dashboard/user-registration');
        const data = await res.json();

        console.log("inside the user registration chart", data)
        // 🔥 remove spinner BEFORE render / update
        // $('#userLineChart .spinner-wrapper').remove();

        if (!userRegistrationChart) {
            userRegistrationChart = new ApexCharts(
                document.querySelector("#userLineChart"),
                {
                    chart: {
                        type: 'line',
                        height: 300,
                    },
                    title: {
                        text: 'User registration',
                    },
                    // subtitle: {
                    //     text: 'Total submissions across all modules (Last 7 Days)',
                    // },
                    // series: data.series,
                    xaxis: {
                        categories: data.months,
                    },
                    colors: ['#5c67f7', '#E354D4', '#FF5D9F', '#9E5CF7'],
                }
            );

            await userRegistrationChart.render();
        } else {
            // 🔁 Update existing chart (no spinner needed)
            userRegistrationChart.updateOptions({
                series: data.series,
                xaxis: { categories: data.days },
            });
        }
    } catch (error) {
        console.error('Failed to load daily volume chart', error);

        $('#userRegistrationChart').html(`
            <div class="text-center text-danger">
                Failed to load chart
            </div>
        `);
    }
}


export async function admin_dashboard() {
    try {
        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        // ✅ WAIT until AJAX finishes
        await applicationCount();
        await loadDailyVolumeChart()
        // await loadUserRegistration()

        Swal.close();
    } catch (error) {
        console.error("Error loading dashboard:", error);
        Swal.close();
        // Swal.fire({
        //     icon: "error",
        //     title: "Failed to Load Dashboard",
        // });
    }
}

admin_dashboard();

