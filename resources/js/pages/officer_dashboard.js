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
            $('#ipCount').text(data.ipOfficer)
            $('#icCount').text(data.icOfficer)
            $('#ccCount').text(data.ccOfficer)
            $("#amount").text('RM ', data.mount)
            $("#pendingCount").text(data.totalReview)
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