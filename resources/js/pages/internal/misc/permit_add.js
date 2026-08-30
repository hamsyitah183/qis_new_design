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
    loading: { en: 'Loading...', bm: 'Memuatkan...' },
    fetchingData: { en: 'Fetching permit condition data', bm: 'Mendapatkan data syarat permit' },
    error: { en: 'Error', bm: 'Ralat' },
    failedToFetch: { en: 'Failed to fetch permit condition data.', bm: 'Gagal mendapatkan data syarat permit.' },
    areYouSure: { en: 'Are you sure?', bm: 'Adakah anda pasti?' },
    deleteWarning: { en: 'This permit condition will be permanently deleted.', bm: 'Syarat permit ini akan dipadamkan secara kekal.' },
    deleted: { en: 'Deleted!', bm: 'Dipadam!' },
    deletedSuccess: { en: 'Permit condition has been deleted.', bm: 'Syarat permit telah dipadam.' },
    failedToDelete: { en: 'Failed to delete permit condition.', bm: 'Gagal memadam syarat permit.' },
    failedToLoadCountries: { en: 'Failed to Load Countries', bm: 'Gagal Memuatkan Negara' },
    checkConnection: { en: 'Please try again or check your connection.', bm: 'Sila cuba lagi atau periksa sambungan anda.' },
    failedToLoadUsage: { en: 'Failed to Load Usage List', bm: 'Gagal Memuatkan Senarai Kegunaan' },
    submitPermitCondition: { en: 'Submit Permit Condition', bm: 'Hantar Syarat Permit' },
    chooseAction: { en: 'Choose an action:', bm: 'Pilih tindakan:' },
    saving: { en: 'Saving...', bm: 'Menyimpan...' },
    savingWait: { en: 'Please wait while the condition is being saved.', bm: 'Sila tunggu sementara syarat sedang disimpan.' },
    saved: { en: 'Saved!', bm: 'Telah Disimpan!' },
    savedSuccess: { en: 'Permit condition saved successfully.', bm: 'Syarat permit berjaya disimpan.' },
    sent: { en: 'Sent!', bm: 'Dihantar!' },
    sharedSuccess: { en: 'The permit condition has been shared to all users.', bm: 'Syarat permit telah dikongsi dengan semua pengguna.' },
    shareError: { en: 'Something went wrong while sharing.', bm: 'Sesuatu yang tidak kena berlaku semasa berkongsi.' },
    savedNotShared: { en: 'Saved, but not shared', bm: 'Disimpan, tetapi tidak dikongsi' },
    savedNotSharedMsg: { en: 'The condition was saved but its ID couldn\'t be read, so the email/share step did not run.', bm: 'Syarat telah disimpan tetapi ID-nya tidak dapat dibaca, jadi langkah e-mel/kongsi tidak dijalankan.' },
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

// Import Select2 module
import select2 from "select2";
import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';


// Make Tagify available globally so your blade script can use it
window.Tagify = Tagify;
window.Quill = Quill;

// Force Select2 to attach to THIS jQuery:
select2($);

import "select2/dist/css/select2.min.css";
function fetchCountryList() {
    const $select = $("#countrySelect");
    const url = $select.data("route");

    return $.ajax({
        url,
        type: "GET",
        dataType: "json",
        cache: false,
        success: (response) => {
            const data = response.data || [];

            // Clear previous options
            $select.empty();

            // Optional: placeholder option
            $select.append('<option value="">-- Select Countries --</option>');

            // Add options dynamically
            data.forEach((country) => {
                // country.value = code, country.name = full name
                $select.append(
                    `<option value="${country.value}">${country.name} (${country.value})</option>`
                );
            });

            // Initialize Select2
            $select.select2({
                width: "100%",
                placeholder: "-- Select Countries --",
                allowClear: true,
                multiple: true,
                matcher: function (params, data) {
                    // If no search term, return all data
                    if ($.trim(params.term) === "") {
                        return data;
                    }

                    // Search by both name or value
                    const term = params.term.toLowerCase();
                    if (
                        data.text.toLowerCase().includes(term) ||
                        data.id.toLowerCase().includes(term)
                    ) {
                        return data;
                    }

                    // Return null if not matched
                    return null;
                },
            });
        },
        error: (xhr) => {
            console.error("Failed to load countries:", xhr.responseText);
            Swal.fire({
                icon: "error",
                title: getText("failedToLoadCountries"),
                text: getText("checkConnection"),
            });
        },
    });
}

function fetchUsageList() {
    const $select = $("#usageSelect");
    const url = $select.data("route");

    return $.ajax({
        url,
        type: "GET",
        dataType: "json",
        cache: false,
        success: (response) => {
            const data = response.data || [];

            // Clear existing options
            $select.empty();

            // Optional placeholder
            $select.append('<option value="">-- Select Usage --</option>');

            // Add options dynamically
            data.forEach((item) => {
                // value and text are the same
                $select.append(
                    `<option value="${item.description}">${item.description}</option>`
                );
            });

            // Initialize Select2
            $select.select2({
                width: "100%",
                placeholder: "-- Select Usage --",
                allowClear: true,
                multiple: true,
                matcher: function (params, data) {
                    if ($.trim(params.term) === "") return data;

                    const term = params.term.toLowerCase();
                    if (data.text.toLowerCase().includes(term)) {
                        return data;
                    }

                    return null;
                },
            });
        },
        error: (xhr) => {
            console.error("Failed to load usage list:", xhr.responseText);
            Swal.fire({
                icon: "error",
                title: getText("failedToLoadUsage"),
                text: getText("checkConnection"),
            });
        },
    });
}
export function one() {
    // --- 2. Get usage list ---
    fetchUsageList();
    fetchCountryList();
}
one();

export function three() {
    let quill = new Quill("#permit-condition-editor", {
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ["bold", "italic", "underline", "strike"],
                [{ list: "ordered" }, { list: "bullet" }],
                ["link", "blockquote", "code-block"],
                [{ align: [] }],
                ["clean"],
            ],
        },
        placeholder: "Write permit conditions here...",
        theme: "snow",
    });

    const submitBtn = document.getElementById("submitConditionBtn");
    if (submitBtn) {
        submitBtn.addEventListener("click", function (e) {
            e.preventDefault();

            // Make sure quill syncs to hidden input
            const conditionHtml = quill.root.innerHTML;
            document.getElementById("permit-condition-input").value = conditionHtml;

            const formData = new FormData();
            formData.append("itemName", document.getElementById("itemName").value);
            formData.append("scientificName", document.getElementById("scientificName").value);
            formData.append("itemCategory", document.getElementById("itemCategory").value);
            formData.append("quanLimit", document.getElementById("quanLimit").value);
            formData.append("quanmunit", document.getElementById("quanmunit").value);
            formData.append("start_date", document.getElementById("start_date").value);
            formData.append("end_date", document.getElementById("end_date").value);

            // Select2 values -> JSON strings containing array of objects with "value" key
            const selectedCountries = $("#countrySelect").val() || [];
            const countryTagData = selectedCountries.map((val) => ({ value: val }));
            formData.append("countryTag", JSON.stringify(countryTagData));

            const selectedUsages = $("#usageSelect").val() || [];
            const usageTagData = selectedUsages.map((val) => ({ value: val }));
            formData.append("usageTags", JSON.stringify(usageTagData));

            // Quill HTML
            formData.append("permit_condition", conditionHtml);

            // Ask user which action to take (same UI as edit screen)
            Swal.fire({
                title: getText("submitPermitCondition"),
                text: getText("chooseAction"),
                icon: "question",
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: getText("saveAndShare"),
                denyButtonText: getText("saveOnly"),
                cancelButtonText: getText("cancel"),
            }).then((result) => {
                if (result.isDismissed) {
                    return; // User clicked Cancel or closed the dialog
                }

                // Show loading while saving
                Swal.fire({
                    title: getText("saving"),
                    text: getText("savingWait"),
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); applyTranslations(Swal.getHtmlContainer()); },
                });

                $.ajax({
                    url: `/internal/save_condition`,
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (res) {
                        Swal.close(); // close loading

                        Swal.fire({
                            icon: "success",
                            title: getText("saved"),
                            text:
                                res.message ||
                                getText("savedSuccess"),
                            timer: 2000,
                            showConfirmButton: false,
                        }).then(() => {
                            // If user chose "Save & Share with Public"
                            const newConditionId =
                                res.condition_id || res.data?.id;

                            if (result.isConfirmed && newConditionId) {
                                $.ajax({
                                    url: "/internal/news/",
                                    method: "POST",
                                    dataType: "json",
                                    data: {
                                        _token: $(
                                            'meta[name="csrf-token"]'
                                        ).attr("content"),
                                        condition_id: newConditionId,
                                        type: "Import Permit",
                                        action: "released",
                                    },
                                    success: function () {
                                        Swal.fire(
                                            getText("sent"),
                                            getText("sharedSuccess"),
                                            "success"
                                        ).then(() => {
                                            window.location.href = `${window.baseUrl}/internal/permit_condition`;
                                        });
                                    },
                                    error: function (xhr) {
                                        Swal.fire({
                                            icon: "error",
                                            title: getText("error"),
                                            text:
                                                xhr.responseJSON?.message ||
                                                getText("shareError"),
                                        }).then(() => {
                                            window.location.href = `${window.baseUrl}/internal/permit_condition`;
                                        });
                                    },
                                });
                            } else if (result.isConfirmed && !newConditionId) {
                                Swal.fire({
                                    icon: "warning",
                                    title: getText("savedNotShared"),
                                    text: getText("savedNotSharedMsg"),
                                }).then(() => {
                                    window.location.href = `${window.baseUrl}/internal/permit_condition`;
                                });
                            } else {
                                // Just redirect back to list
                                window.location.href = `${window.baseUrl}/internal/permit_condition`;
                            }
                        });
                    },
                    error: function (xhr) {
                        Swal.close(); // close loading
                        console.error("Save Error:", xhr.responseText);
                        let errMsg = "Failed to save permit condition.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        Swal.fire(getText("error"), errMsg, "error");
                    },
                });
            });
        });
    }
}

// Check if document is already loaded, otherwise wait for it
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', three);
} else {
    three();
}
