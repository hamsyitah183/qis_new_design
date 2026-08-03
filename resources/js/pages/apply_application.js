import $ from "jquery";
import Swal from "sweetalert2";

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
            // Get current language from localStorage
            const lang = localStorage.getItem('qis_lang') || 'en';
            
            // Bilingual Swal messages
            const title = lang === 'bm' ? 'Tiada jenis dipilih' : 'No type selected';
            const text = lang === 'bm' ? 'Sila pilih jenis permohonan terlebih dahulu!' : 'Please choose an application type first!';
            
            Swal.fire({
                icon: "error",
                title: title,
                text: text,
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