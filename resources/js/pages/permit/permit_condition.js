// resources/js/pages/permit/permit_condition.js
import $ from "jquery";
import Swal from "sweetalert2";
import { applyTranslations } from "../../app";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    submitPermitCondition: { en: 'Submit Permit Condition', bm: 'Hantar Syarat Permit' },
    chooseAction: { en: 'Choose an action:', bm: 'Pilih tindakan:' },
    saving: { en: 'Saving...', bm: 'Menyimpan...' },
    savingWait: { en: 'Please wait while the condition is being saved.', bm: 'Sila tunggu sementara syarat sedang disimpan.' },
    saved: { en: 'Saved!', bm: 'Telah Disimpan!' },
    savedSuccess: { en: 'Permit condition saved successfully.', bm: 'Syarat permit berjaya disimpan.' },
    sent: { en: 'Sent!', bm: 'Dihantar!' },
    sharedSuccess: { en: 'The permit condition has been shared to all users.', bm: 'Syarat permit telah dikongsi dengan semua pengguna.' },
    error: { en: 'Error', bm: 'Ralat' },
    shareError: { en: 'Something went wrong.', bm: 'Sesuatu yang tidak kena berlaku.' },
    failedToSave: { en: 'Failed to save permit condition.', bm: 'Gagal menyimpan syarat permit.' },
    deleteTitle: { en: 'Delete this permit condition?', bm: 'Padam syarat permit ini?' },
    deleteWarning: { en: 'This action cannot be undone.', bm: 'Tindakan ini tidak boleh dibatalkan.' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    deletedSuccess: { en: 'The permit condition has been deleted.', bm: 'Syarat permit telah dipadam.' },
    failedToDelete: { en: 'Failed to delete.', bm: 'Gagal memadam.' },
    somethingWentWrong: { en: 'Something went wrong. Please try again.', bm: 'Sesuatu yang tidak kena berlaku. Sila cuba lagi.' },
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

        // Make sure quill syncs to hidden input
        const conditionHtml = quill.root.innerHTML;
        document.getElementById("permit-condition-input").value = conditionHtml;

        const formData = new FormData();
        formData.append("itemName", document.getElementById("itemName").value);
        formData.append("itemCategory", document.getElementById("itemCategory").value);
        formData.append("quanLimit", document.getElementById("quanLimit").value);
        formData.append("quanmunit", document.getElementById("quanmunit").value);
        // formData.append('measurement', document.getElementById("quanmunit"))
        formData.append('id', document.getElementById('id').value);
        formData.append('start_date', document.getElementById('start_date').value);
        formData.append('end_date', document.getElementById('end_date').value);
        formData.append('type', 'Import Permit');
    
        // Tagify values → JSON strings
        formData.append("countryTag", JSON.stringify(countryTagify ? countryTagify.value : []));
        formData.append("usageTags", JSON.stringify(usageTagify ? usageTagify.value : []));
        // ─── NEW: Another Name Tags ──────────────────────────────────────
        formData.append("anotherNameTags", JSON.stringify(window.anotherNameTagify ? window.anotherNameTagify.value : []));

        // Quill HTML
        formData.append("permit_condition", conditionHtml);

        // Ask user which action to take
        Swal.fire({
            title: getText("submitPermitCondition"),
            text: getText("chooseAction"),
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: getText("saveAndShare"),
            denyButtonText: getText("saveOnly"),
            cancelButtonText: getText("cancel")
        }).then((result) => {
            if (result.isDismissed) {
                // User clicked Cancel
                return;
            }

            // Show loading while saving
            Swal.fire({
                title: getText("saving"),
                text: getText("savingWait"),
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); applyTranslations(Swal.getHtmlContainer()); }
            });

            // Save via AJAX
            $.ajax({
                url: `/internal/save_condition`,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function (res) {
                    Swal.close(); // close loading

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: getText("saved"),
                        text: res.message || getText("savedSuccess"),
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // If user chose "Save & Share"
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/internal/news/',
                                method: "POST",
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr("content"),
                                    condition_id: document.getElementById('id').value,
                                    type: 'Import Permit',
                                    action: 'updated'
                                },
                                success: function (response) {
                                    Swal.fire(getText("sent"), getText("sharedSuccess"), 'success');
                                },
                                error: function (xhr) {
                                    Swal.fire({
                                        icon: "error",
                                        title: getText("error"),
                                        text: xhr.responseJSON?.message || getText("shareError"),
                                    });
                                },
                            });
                        }
                    });
                },
                error: function (xhr) {
                    Swal.close(); // close loading
                    Swal.fire({
                        icon: 'error',
                        title: getText("error"),
                        text: xhr.responseJSON?.message || getText("failedToSave"),
                    });
                }
            });

        });

    });

    const deleteBtn = document.getElementById('deleteConditionBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            const conditionId = document.getElementById('id').value;
            Swal.fire({
                title: getText("deleteTitle"),
                text: getText("deleteWarning"),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: getText("cancel"),
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/internal/permit_condition/${conditionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: getText("deleted"),
                                    text: getText("deletedSuccess"),
                                    timer: 1500,
                                    showConfirmButton: false,
                                }).then(() => {
                                    window.location.href = '/internal/permit_condition';
                                });
                            } else {
                                Swal.fire(getText("error"), data.message ?? getText("failedToDelete"), 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire(getText("error"), getText("somethingWentWrong"), 'error');
                        });
                }
            });
        });
    }

});