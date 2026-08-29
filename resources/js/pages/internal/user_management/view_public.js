import $ from "jquery";
import { setupSelect2 } from "../../../utils/select2Utils";

function initActivitySelect2() {
    const lang = localStorage.getItem("qis_lang") || "en";
    const placeholderText = lang === 'bm' ? 'Semua Aktiviti' : 'All Activities';

    setupSelect2('#activitySearch', placeholderText);
}

document.addEventListener("DOMContentLoaded", function () {
    const activitySearch = document.getElementById("activitySearch");
    if (activitySearch) {
        initActivitySelect2();

        // Re-init Select2 when language button is clicked
        $('.lang-btn').on('click', function () {
            setTimeout(() => {
                $('#activitySearch').select2('destroy');
                initActivitySelect2();
            }, 100);
        });

        $('#activitySearch').on("change", function () {
            const selectedVals = $(this).val() || [];
            let keywords = [];
            
            selectedVals.forEach(val => {
                const parts = val.toLowerCase().split(",").map((k) => k.trim());
                keywords = keywords.concat(parts);
            });
            keywords = keywords.filter(k => k);

            const listItems = document.querySelectorAll(
                "#activityListGroup .list-group-item"
            );

            listItems.forEach(function (item) {
                const descSpan = item.querySelector('.activity-desc');
                const text = descSpan ? descSpan.getAttribute('data-en').toLowerCase() : item.textContent.toLowerCase();

                if (keywords.length === 0) {
                    item.classList.remove('d-none');
                    item.classList.add('d-flex');
                    return;
                }

                const matches = keywords.some((keyword) => text.includes(keyword));

                if (matches) {
                    item.classList.remove('d-none');
                    item.classList.add('d-flex');
                } else {
                    item.classList.remove('d-flex');
                    item.classList.add('d-none');
                }
            });
        });
    }
});
