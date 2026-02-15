import $ from "jquery";
import Swal from "sweetalert2";

// $(document).ready(function () {
//     let type;

//     function type_styling() {
//         $(".type-div .icon-box").removeClass("bg-primary");
//         $(".type-div .icon-box .icon").removeClass("text-white");
//         $(".type-element").removeClass("border-primary");

//         const checkedRadio = $('input[name="type"]:checked');

//         if (checkedRadio.length > 0) {
//             const parentCard = checkedRadio.closest(".type-element");
//             const targetDiv = checkedRadio.closest("label").find(".type-div");
//             const targetDivIconBox = targetDiv.find(".icon-box");
//             const targetDivIcon = targetDivIconBox.find(".icon");

//             targetDivIconBox.addClass("bg-primary");
//             targetDivIcon.addClass("text-white");
//             parentCard.addClass("border-primary");

//             type = checkedRadio.data("type");

//         }
//     }

//    $(document).on("change", 'input[name="type"]', function () {
//         type_styling();

//         // get type safely
//         const type = $(this).data('type');

//         console.log('type of the application:', type);

//         // move to next tab
//         setTimeout(() => {
//             switchTo("#shipped-tab");
//         }, 300);

//         const selfApply  = $('#selfApply');
//         const otherApply = $('#othersApply');

//         if (!selfApply.length || !otherApply.length) {
//             console.warn('Apply links not found in DOM yet');
//             return;
//         }

//         switch (type) {
//             case 'import-permit':
//                 selfApply.attr('href', '/public/import_permit_application');
//                 otherApply.attr('href', '/public/import_assign_application');
//                 break;

//             case 'inspection_certificate':
//                 selfApply.attr('href', '/public/inspection_certificates_application_self');
//                 otherApply.attr('href', '/public/inspection_certificates_application_others');
//                 break;

//             case 'consignment':
//                 selfApply.attr('href', '/public/consignment_certificate_application_self');
//                 otherApply.attr('href', '/public/consignment_certificates_application_other');
//                 break;

//             default:
//                 console.warn('Unknown application type:', type);
//         }
//     });



//     function switchTo(tabButtonId) {
//         const tabTrigger = document.querySelector(tabButtonId);
//         if (tabTrigger) {
//             const tab = new bootstrap.Tab(tabTrigger);
//             tab.show();
//         }
//     }

//     $("#nextToPersonalTab").on("click", function (e) {
//         e.preventDefault();

//         if (!type) {
//             Swal.fire({
//                 icon: "error",
//                 title: "No type selected",
//                 text: "Please choose an application type first!",
//             });
//             return;
//         }

//         // ✅ Move to Verification Attachment tab
//         switchTo("#shipped-tab");
//     });

//     $("#backToAccountTab").on("click", (e) => {
//         e.preventDefault();
//         switchTo("#order-tab");
//     });
//     $("#nextToSummaryTab").on("click", (e) => {
//         e.preventDefault();
//         switchTo("#shipped-tab");
//     });
//     $("#backToDetailsTab").on("click", (e) => {
//         e.preventDefault();
//         switchTo("#confirmed-tab");
//     });

// });



$(document).ready(function () {

    let type = null;

    function type_styling(selectedElement) {
        $(".type-element").removeClass("border-primary active");
        $(".type-div .icon-box").removeClass("bg-primary");
        $(".type-div .icon").removeClass("text-white");

        selectedElement.addClass("border-primary active");

        const iconBox = selectedElement.find(".icon-box");
        const icon = selectedElement.find(".icon");

        iconBox.addClass("bg-primary");
        icon.removeClass("text-primary").addClass("text-white");
    }

    // CLICK EVENT INSTEAD OF RADIO CHANGE
    $(document).on("click", ".type-element", function () {

        type = $(this).data("type");

        type_styling($(this));

        console.log("Selected type:", type);

        setTimeout(() => {
            switchTo("#shipped-tab");
        }, 300);

        const selfApply = $("#selfApply");
        const otherApply = $("#othersApply");

        if (!selfApply.length || !otherApply.length) return;

        switch (type) {
            case "import-permit":
                selfApply.attr("href", "/public/import_permit_application");
                otherApply.attr("href", "/public/import_assign_application");
                break;

            case "inspection_certificate":
                selfApply.attr("href", "/public/inspection_certificates_application_self");
                otherApply.attr("href", "/public/inspection_certificates_application_others");
                break;

            case "consignment":
                selfApply.attr("href", "/public/consignment_certificate_application_self");
                otherApply.attr("href", "/public/consignment_certificates_application_other");
                break;

            default:
                console.warn("Unknown type:", type);
        }
    });

    function switchTo(tabButtonId) {
        const tabTrigger = document.querySelector(tabButtonId);
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }

    $("#nextToPersonalTab").on("click", function (e) {
        e.preventDefault();

        if (!type) {
            Swal.fire({
                icon: "error",
                title: "No type selected",
                text: "Please choose an application type first!",
            });
            return;
        }

        switchTo("#shipped-tab");
    });

    $("#backToAccountTab").on("click", (e) => {
        e.preventDefault();
        switchTo("#order-tab");
    });
    $("#nextToSummaryTab").on("click", (e) => {
        e.preventDefault();
        switchTo("#shipped-tab");
    });
    $("#backToDetailsTab").on("click", (e) => {
        e.preventDefault();
        switchTo("#confirmed-tab");
    });

});
