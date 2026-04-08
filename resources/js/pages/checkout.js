import $ from "jquery";
import Swal from "sweetalert2";


function returnToApplication() {

    $(document).on('click', '#returnToApplication', function (e) {
        e.preventDefault();
        console.log('cancel payment')
        const applicationId = $(this).data('app-id');
        const applicationType = $(this).data('app-type');

        let baseUrl = '/view_application/';
        if (applicationType === 'Inspection Certificate' || applicationType === 'Inspection') {
            baseUrl = '/view_inspection/';
        } else if (applicationType === 'Consignment Certificate' || applicationType === 'Consignment') {
            baseUrl = '/view_consignment/';
        }


        // Extract ONLY permit IDs
        const permitIds = window.PERMITS.map(p => p.id);
        const order = window.ORDER;

        Swal.fire({
            title: "Are you sure?",
            text: "The payment will be cancelled!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, go back!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                // fetch('/payment/cancel', {
                //     method: 'POST',
                //     headers: {
                //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                //         'Content-Type': 'application/json'
                //     },
                //     body: JSON.stringify({
                //         application_id: applicationId,
                //         permit_ids: permitIds,
                //         order: order
                //     })
                // })
                // .then(res => res.json())
                // .then(() => {
                //     window.location.href =
                //         '/view_application/' + applicationId + '#pending';
                // });
                window.location.href = baseUrl + applicationId + '#pending';
            }
        });
    });
}


// function payMent() {
//     $(document).on('click', '#payNow', function (e) {
//         e.preventDefault();

//         let name = window.authUser.fullname
//         // let importerAddress = $('#importerAddress').text().trim();
//         let no_phone = window.authUser.phone_number;
//         let email = window.authUser.email;

//         // 

//         let orderNo = $('#orderNo').text().trim();

//         let amount = $('#amount').text().trim();


//        var paymentMethod = $('input[name="payment"]:checked').val();

//        var application_type = $('#application_type').val();

//         if (!paymentMethod) {
//             Swal.fire('Error', 'Choose a payment method!', 'error');
//             return;
//         }

//         Swal.fire({
//             title: "Are you sure?",
//             icon: "warning",
//             showCancelButton: true,
//             confirmButtonText: "Yes, proceed payment!",
//             cancelButtonText: "Cancel",
//         }).then((result) => {
//             if (result.isConfirmed) {
//                 $.ajax({
//                     url: '/payment',
//                     type: 'POST',
//                     data: {
//                         _token: $('meta[name="csrf-token"]').attr('content'),
//                         name: name,
//                         // importerAddress: importerAddress,
//                         no_phone: no_phone,
//                         email: email,
//                         application_type: application_type,
                      

//                         orderNo: orderNo,

//                         amount: amount,

//                         paymentMethod: paymentMethod

//                     },
//                     // success: function (response) {
//                     //     Swal.fire('Success', 'Payment processed!', 'success');
//                     // },
//                     error: function (xhr) {
//                         Swal.fire('Error', 'Payment failed!', 'error');
//                     }
//                 });
//             }
//         });
//     });
// }


function payMent() {
    $(document).on('submit', '#paymentForm', function (e) {
    e.preventDefault();

    const paymentMethod = $('input[name="paymentMethod"]:checked').val();

    if (!paymentMethod) {
        Swal.fire('Error', 'Choose a payment method!', 'error');
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, proceed payment!",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            e.target.submit(); // 🔥 REAL submit
        }
    });
});

}

$(document).on('change', 'input[name="paymentMethod"]', function() {
    $('input[name="paymentMethod"]').each(function() {
        $(this).parent().removeClass('selected-payment');
        $(this).next('label').find('img').removeClass('selected');
    });
    
    $(this).parent().addClass('selected-payment');
    $(this).next('label').find('img').addClass('selected');
});

// Trigger change on page load to highlight the default payment method
$(document).ready(function() {
    $('input[name="paymentMethod"]:checked').trigger('change');
});


payMent();
returnToApplication();