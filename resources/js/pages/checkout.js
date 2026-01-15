import $ from "jquery";
import Swal from "sweetalert2";


function returnToApplication()
{

    $(document).ready(function() {
        $(document).on('click', '#returnToApplication', function(e) {
            e.preventDefault();

            // Get application ID from button attribute
            const applicationId = $(this).data('app-id');

            console.log('Return to application', applicationId);

            Swal.fire({
                title: "Are you sure?",
                text: "The payment will be cancelled!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, go back!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                 if (result.isConfirmed) {
                    fetch('/payment/cancel', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        }
                    }).then(() => {
                        window.location.href = '/view_application/' + applicationId + '#pending';
                    });
                }
            });
        });
    
    });


}


returnToApplication();