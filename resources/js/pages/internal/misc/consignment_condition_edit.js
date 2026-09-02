import $ from "jquery";
import Swal from "sweetalert2";
import { applyTranslations } from "../../../app";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    failedToLoadCountries: { en: 'Failed to Load Countries', bm: 'Gagal Memuatkan Negara' },
    checkConnection: { en: 'Please try again or check your connection.', bm: 'Sila cuba lagi atau periksa sambungan anda.' },
    failedToLoadUsage: { en: 'Failed to Load Usage List', bm: 'Gagal Memuatkan Senarai Kegunaan' },
    submitConsignmentCondition: { en: 'Submit Consignment Condition', bm: 'Hantar Syarat Konsainan' },
    chooseAction: { en: 'Choose an action:', bm: 'Pilih tindakan:' },
    saving: { en: 'Saving...', bm: 'Menyimpan...' },
    savingWait: { en: 'Please wait while the condition is being saved.', bm: 'Sila tunggu sementara syarat sedang disimpan.' },
    saved: { en: 'Saved!', bm: 'Telah Disimpan!' },
    savedSuccess: { en: 'Consignment condition saved successfully.', bm: 'Syarat konsainan berjaya disimpan.' },
    sent: { en: 'Sent!', bm: 'Dihantar!' },
    sharedSuccess: { en: 'The consignment condition has been shared to all users.', bm: 'Syarat konsainan telah dikongsi dengan semua pengguna.' },
    shareError: { en: 'Something went wrong while sharing.', bm: 'Sesuatu yang tidak kena berlaku semasa berkongsi.' },
    savedNotShared: { en: 'Saved, but not shared', bm: 'Disimpan, tetapi tidak dikongsi' },
    savedNotSharedMsg: { en: 'The condition was saved but its ID couldn\'t be read, so the email/share step did not run.', bm: 'Syarat telah disimpan tetapi ID-nya tidak dapat dibaca, jadi langkah e-mel/kongsi tidak dijalankan.' },
    somethingWentWrong: { en: 'Something went wrong. Please try again.', bm: 'Sesuatu yang tidak kena berlaku. Sila cuba lagi.' },
    error: { en: 'Error', bm: 'Ralat' },
    saveAndShare: { en: 'Save & Share with Public', bm: 'Simpan & Kongsi dengan Awam' },
    saveOnly: { en: 'Save Only', bm: 'Simpan Sahaja' },
    cancel: { en: 'Cancel', bm: 'Batal' }
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}


import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';


// Make Tagify available globally so your blade script can use it
window.Tagify = Tagify;
window.Quill = Quill;

document.addEventListener("DOMContentLoaded", function () {

    document.getElementById("submitConditionBtn").addEventListener("click", function () {

        // Sync Quill content
        const conditionHtml = quill.root.innerHTML;
        document.getElementById("permit-condition-input").value = conditionHtml;

        const formData = new FormData();
        formData.append("item_name", document.getElementById("itemName").value);
        formData.append("scientific_name", document.getElementById("scientificName").value);
        formData.append("category", document.getElementById("itemCategory").value);
        formData.append("quantity_limit", document.getElementById("quanLimit").value);
        formData.append("quanmunit", document.getElementById("quanmunit").value);
        formData.append("id", document.getElementById("id").value);
        formData.append("start_date", document.getElementById("start_date").value);
        formData.append("end_date", document.getElementById("end_date").value);

        formData.append("country", JSON.stringify(countryTagify ? countryTagify.value : []));
        // formData.append("usage", JSON.stringify(usageTagify ? usageTagify.value : []));
        formData.append("addional_condition", conditionHtml);

        // ✅ Ask user first (3 buttons)
        Swal.fire({
            title: getText("submitConsignmentCondition"),
            text: getText("chooseAction"),
            icon: "question",
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: getText("saveAndShare"),
            denyButtonText: getText("saveOnly"),
            cancelButtonText: "Cancel"
        }).then((result) => {

            if (result.isDismissed) {
                return; // Cancel clicked
            }

            // Show loading
            Swal.fire({
                title: getText("saving"),
                text: getText("savingWait"),
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); applyTranslations(Swal.getHtmlContainer()); }
            });

            // ✅ Save AJAX
            $.ajax({
                url: "/internal/consignment_condition/save",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function (res) {

                    // If Save & Share
                    if (result.isConfirmed) {

                            $.ajax({
                                url: "/internal/news/",
                                method: "POST",
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr("content"),
                                    condition_id: res.id || document.getElementById("id").value,
                                    type: "Consignment",
                                    action: document.getElementById("id").value ? "edit" : "add"
                                },
                            success: function () {
                                Swal.fire({
                                    icon: "success",
                                    title: "Saved & Shared!",
                                    text: getText("sharedSuccess")
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: "error",
                                    title: "Saved but Share Failed",
                                    text: xhr.responseJSON?.message || "Condition saved but sharing failed."
                                });
                            }
                        });

                    } else {
                        // Save Only
                        Swal.fire({
                            icon: "success",
                            title: getText("saved"),
                            text: res.message || getText("savedSuccess")
                        }).then(() => {
                            window.location.href = "/internal/consignment_condition";
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: getText("error"),
                        text: xhr.responseJSON?.message || "Failed to save consignment condition."
                    });
                }
            });

        });

    });

});

