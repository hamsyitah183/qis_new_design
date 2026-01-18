$(document).ready(function () {

    function type_styling() {
        $(".type-div .icon-box").removeClass("bg-primary");
        $(".type-div .icon-box .icon").removeClass("text-white");
        $(".type-element").removeClass("border-primary");

        const checkedRadio = $('input[name="type"]:checked');

        if (checkedRadio.length > 0) {
            const parentCard = checkedRadio.closest(".type-element");
            const targetDiv = checkedRadio.closest("label").find(".type-div");
            const targetDivIconBox = targetDiv.find(".icon-box");
            const targetDivIcon = targetDivIconBox.find(".icon");

            targetDivIconBox.addClass("bg-primary");
            targetDivIcon.addClass("text-white");
            parentCard.addClass("border-primary");
        }
    }

    $(document).on("change", 'input[name="type"]', function () {
        type_styling();
    });

});
