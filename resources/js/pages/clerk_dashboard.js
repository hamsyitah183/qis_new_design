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
            $("#verified").text(data.verified)
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


export function clerk_dashboard()
{
    applicationCount()
}