import axios from "axios";
import Swal from "sweetalert2";
import flatpickr from "flatpickr";
import { autoInitFilterSelect2 } from "../../../utils/select2Utils";
import { applyTranslations } from "../../../app";

function getLang() {
    try {
        return localStorage.getItem('qis_lang') || 'en';
    } catch {
        return 'en';
    }
}

const t = {
    loading: { en: 'Loading...', bm: 'Memuat...' },
    error: { en: 'Error', bm: 'Ralat' },
    failedToLoad: { en: 'Failed to Load', bm: 'Gagal Memuatkan' },
    unableToFetchTimeline: { en: 'Unable to fetch activity timeline. Please try again.', bm: 'Tidak dapat memuatkan garis masa aktiviti. Sila cuba lagi.' },
    chooseUserType: { en: 'Choose User Type first!', bm: 'Pilih Jenis Pengguna dahulu!' },
    noData: { en: 'No Data', bm: 'Tiada Data' },
    noUsersFound: { en: 'No users found for this type.', bm: 'Tiada pengguna dijumpai untuk jenis ini.' },
    unableToLoadUser: { en: 'Unable to load user details', bm: 'Tidak dapat memuatkan butiran pengguna' },
};

function getText(key) {
    const lang = getLang();
    const entry = t[key];
    if (!entry) return key;
    return entry[lang] || entry.en;
}



let userTypeVal = 0; // ✅ Define globally so all functions can access it
let start = null;
let end = null;
let userIds = [];
let startTime = null;
let endTime = null;

window.allUsers = [];
window.selectedUserIds = new Set();

// ✅ Start DateTime Picker (12-hour format)
const startDateTimePicker = flatpickr("#startDateTime", {
    enableTime: true,
    dateFormat: "Y-m-d h:i K", // 12-hour format with AM/PM
    time_24hr: false,
    defaultHour: 0,
    defaultMinute: 0,
    // appendTo removed to let it append to body instead, fixing position offsets
    onChange(selectedDates, dateStr) {
        if (selectedDates.length > 0) {
            const selected = selectedDates[0];

            // Format the date in local time
            const yyyy = selected.getFullYear();
            const mm = String(selected.getMonth() + 1).padStart(2, "0");
            const dd = String(selected.getDate()).padStart(2, "0");
            start = `${yyyy}-${mm}-${dd}`;

            // Get time part from dateStr (12-hour format)
            startTime = dateStr.split(" ")[1] + " " + dateStr.split(" ")[2];

            console.log(
                "selected start",
                "start",
                start,
                "startTime",
                startTime
            );
        }
    },
});

// ✅ End DateTime Picker (12-hour format)
const endDateTimePicker = flatpickr("#endDateTime", {
    enableTime: true,
    dateFormat: "Y-m-d h:i K", // 12-hour format with AM/PM
    time_24hr: false,
    defaultHour: 11, // 11 PM
    defaultMinute: 59,
    // appendTo removed to let it append to body instead, fixing position offsets
    onChange(selectedDates, dateStr) {
        if (selectedDates.length > 0) {
            const selected = selectedDates[0];

            // Format the date in local time
            const yyyy = selected.getFullYear();
            const mm = String(selected.getMonth() + 1).padStart(2, "0");
            const dd = String(selected.getDate()).padStart(2, "0");
            end = `${yyyy}-${mm}-${dd}`;

            // Get time part from dateStr (12-hour format)
            endTime = dateStr.split(" ")[1] + " " + dateStr.split(" ")[2];

            console.log("selected end", "end", end, "endTime", endTime);
        }
    },
});

// ✅ Prevent Bootstrap Dropdown from closing when clicking inside Flatpickr calendar
document.addEventListener("hide.bs.dropdown", function (e) {
    if (e.clickEvent && e.clickEvent.target.closest(".flatpickr-calendar")) {
        e.preventDefault();
    }
});

// ✅ Load all activity logs when page loads
loadActivityTimeline();

// ✅ Initialize select2 on static filters
autoInitFilterSelect2();

// Listen to language changes
window.addEventListener('lang-changed', function() {
    if (window.currentActivities) {
        renderTimeline(window.currentActivities);
    }
});

// 🚀 Load activity timeline (with optional filters)
async function loadActivityTimeline(
    startDate = null,
    endDate = null,
    startTime = null,
    endTime = null,
    userTypeVal,
    userId = null
) {
    const allInputs = document.querySelectorAll(
        "input, select, button, textarea"
    );
    allInputs.forEach((el) => (el.disabled = true));

    try {
        Swal.fire({
            title: getText("loading"),
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                applyTranslations(Swal.getHtmlContainer());
            },
        });

        const params = {};

        // ✅ Combine date & time filters
        if (startDate) params.start_date = startDate;
        if (endDate) params.end_date = endDate;
        if (startTime) params.start_time = startTime;
        if (endTime) params.end_time = endTime;

        if (userTypeVal === "public") {
            params.causer_type = "public";
        } else if (userTypeVal === "internal") {
            params.causer_type = "internal";
        }

        params.causer_id = userId;

        const { data: activities } = await axios.get(
            "/internal/activity-logs/data",
            { params }
        );

        const groupedActivities = groupActivitiesByDate(activities);
        window.currentActivities = groupedActivities;
        renderTimeline(groupedActivities);

        Swal.close();
    } catch (error) {
        console.error("Error loading timeline:", error);
        Swal.close();
        Swal.fire({
            icon: "error",
            title: getText("failedToLoad"),
            text: getText("unableToFetchTimeline"),
        });
    } finally {
        allInputs.forEach((el) => (el.disabled = false));
    }
}

// 🧮 Group activities by date
function groupActivitiesByDate(activities) {
    return activities.reduce((groups, activity) => {
        const date = new Date(activity.created_at).toLocaleDateString("en-GB", {
            day: "numeric",
            month: "long",
            year: "numeric",
        });
        if (!groups[date]) groups[date] = [];
        groups[date].push(activity);
        return groups;
    }, {});
}

function renderTimeline(groupedActivities) {
    const container = document.querySelector(".timeline-container");
    container.innerHTML = "";

    console.log("grouped activities", groupedActivities);

    const lang = localStorage.getItem("qis_lang") || "en";
    const translations = {
        'logged in to the system': 'telah log masuk ke sistem',
        'logged out from the system': 'telah log keluar dari sistem',
        'internal user': 'pengguna dalaman',
        'public user': 'pengguna awam',
        'is new user for boundary officer': 'pengguna baharu untuk pegawai sempadan',
        'has created a new import permit application draft': 'telah mencipta draf permohonan permit import baharu',
        'has created a new inspection certificate application draft': 'telah mencipta draf permohonan sijil pemeriksaan baharu',
        'has created a new consignment application draft': 'telah mencipta draf permohonan konsainan baharu',
        'has submitted an import permit application': 'telah menghantar permohonan permit import',
        'has submitted an inspection certificate application': 'telah menghantar permohonan sijil pemeriksaan',
        'has submitted a consignment application': 'telah menghantar permohonan konsainan',
        'has updated an import permit application draft': 'telah mengemas kini draf permohonan permit import',
        'has updated an inspection certificate application draft': 'telah mengemas kini draf permohonan sijil pemeriksaan',
        'has updated a consignment application draft': 'telah mengemas kini draf permohonan konsainan',
        'verification is in-progress by': 'pengesahan sedang dijalankan oleh',
        'was verified by': 'telah disahkan oleh',
        'verification is rejected by': 'pengesahan ditolak oleh',
        'is not approved by': 'tidak diluluskan oleh'
    };

    if (!Object.keys(groupedActivities).length) {
        const emptyMsg = lang === 'bm' ? "Tiada aktiviti dijumpai." : "No activities found.";
        container.innerHTML = `<div class="text-center text-muted p-4">${emptyMsg}</div>`;
        return;
    }

    Object.entries(groupedActivities).forEach(([date, activities]) => {
        // activities.sort(
        //     (a, b) => new Date(a.created_at) - new Date(b.created_at)
        // );
        const dateSection = document.createElement("div");
        dateSection.classList.add("timeline-date-section");
        dateSection.innerHTML = `
            <div class="timeline-end">
                <span class="p-1 fs-11 bg-primary2 text-fixed-white rounded-1 fw-medium">${date}</span>
            </div>
            <div class="timeline-continue">
                ${activities
                .map(
                    (activity) => {
                        let desc = activity.description;
                        if (lang === 'bm') {
                            for (const [en, bm] of Object.entries(translations)) {
                                if (desc.includes(en)) {
                                    desc = desc.replaceAll(en, bm);
                                }
                            }
                        }

                        let propsHtml = '';
                        if (activity.properties?.attributes) {
                            const changedLabel = lang === 'bm' ? 'Menukar' : 'Changed';
                            const toLabel = lang === 'bm' ? 'kepada' : 'to';
                            propsHtml = `<p class="text-muted mb-0 ms-2">
                                            ${changedLabel} <b>${Object.keys(
                                activity.properties.attributes
                            ).join(", ")}</b>
                                            ${toLabel} <b>${Object.values(
                                activity.properties.attributes
                            ).join(", ")}</b>
                                        </p>`;
                        }

                        return `
                    <div class="timeline-right">
                        <div class="timeline-content">
                            <p class="timeline-date text-muted mb-2">
                                ${new Date(
                        activity.created_at
                    ).toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: true,
                    })}
                            </p>
                            <div class="timeline-box">
                                <p class="mb-2">${desc}</p>
                                ${propsHtml}
                            </div>
                        </div>
                    </div>`;
                    }
                )
                .join("")}
            </div>`;
        container.appendChild(dateSection);
    });
}

// 🔹 When user changes type (public/internal)
$("#userType").on("change", function (e) {
    e.preventDefault();

    $("#searchUserInput").val("");
    $("#userList input:checkbox").prop("checked", false);

    userTypeVal = $(this).val();
    console.log("Selected user type:", userTypeVal);

    if (!userTypeVal || userTypeVal == 0) {
        Swal.fire(getText("error"), getText("chooseUserType"), "error");
        return;
    }

    // 🔸 Setup modal title & dropdowns
    setupUserModal(userTypeVal);

    if (userTypeVal && userTypeVal != "0") {
        $("#userAccountContainer").removeClass("d-none");
    }

    // 🔸 Load user list separately
    loadUserList(userTypeVal);

    // Optional: load timeline
    loadActivityTimeline(start, end, startTime, endTime, userTypeVal, userIds);
});

// 🔹 When user clicks the account button
$("#userAccountBtn").on("click", function (e) {
    e.preventDefault();
    if ($(this).css("opacity") == "0.6") return;
    $("#userAccountContainer").toggleClass("d-none");
});

// 🔹 Setup interface (title + dropdown)
function setupUserModal(userTypeVal) {
    const modalTitle = $("#accountUserModalLabel");
    const dropdownButton = $("#categoryDropdown");
    const dropdownMenu = $("#categoryDropdownMenu");

    dropdownButton.css("display", "block");

    if (userTypeVal === "public") {
        modalTitle.text("Choose Public User Account");
        dropdownButton.text("Category");
        dropdownMenu.html(`
            <li><a class="dropdown-item" href="#">All</a></li>
            <li><a class="dropdown-item" href="#">Individual</a></li>
            <li><a class="dropdown-item" href="#">Company</a></li>
        `);
        $("#userAccountBtn").css({ "pointer-events": "auto", "opacity": "1" });
    } else if (userTypeVal === "internal") {
        modalTitle.text("Choose Internal User Account");
        dropdownButton.text("User Role");
        dropdownMenu.html(`
            <li><a class="dropdown-item" href="#">All</a></li>
            <li><a class="dropdown-item" href="#">Admin</a></li>
            <li><a class="dropdown-item" href="#">Manager</a></li>
            <li><a class="dropdown-item" href="#">Staff</a></li>
        `);
        $("#userAccountBtn").css({ "pointer-events": "auto", "opacity": "1" });
    } else {
        modalTitle.text("User Account");
        dropdownButton.css("display", "none");
        $("#userAccountBtn").css({ "pointer-events": "none", "opacity": "0.6" });
        $("#userAccountContainer").addClass("d-none");
    }

    // 🔹 Handle dropdown click
    dropdownMenu
        .off("click", ".dropdown-item")
        .on("click", ".dropdown-item", function (e) {
            e.preventDefault();
            const selectedText = $(this).text().trim();
            dropdownButton.text(selectedText);
            console.log("Selected dropdown:", selectedText);
        });
}

function loadUserList(userTypeVal) {
    Swal.fire({
        title: getText("loading"),
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            applyTranslations(Swal.getHtmlContainer());
        },
    });

    window.selectedUserIds.clear();

    $.ajax({
        url: `/internal/user_list/${userTypeVal}`,
        type: "GET",
        success: function (response) {
            Swal.close();
            if (response && response.users) {
                window.allUsers = response.users;
                listUser(response.users);
            } else {
                Swal.fire(getText("noData"), getText("noUsersFound"), "info");
            }
        },
        error: function () {
            Swal.fire(getText("error"), getText("unableToLoadUser"), "error");
        },
    });
}

// 🔹 Search filter (real-time)
$("#searchUserInput").on("input", function () {
    const keyword = $(this).val().toLowerCase().trim();
    const filteredUsers = window.allUsers.filter((user) =>
        user.fullname.toLowerCase().includes(keyword)
    );
    listUser(filteredUsers);
});

// 🔹 Render user list
function listUser(users) {
    console.log("Rendering users:", users);
    let listUserText = "";

    users.forEach((user) => {
        const isChecked = window.selectedUserIds.has(user.id) ? "checked" : "";
        listUserText += `
        <div class="col-6 pb-2"> 
            <div class="form-check">
                <input class="form-check-input me-2 user-checkbox" type="checkbox" value="${user.uuid}" id="user-${user.uuid}" ${isChecked}>
                <label class="form-check-label" for="user-${user.uuid}">
                    ${user.fullname}
                </label>
            </div>
        </div>`;
    });

    $("#userList").html(listUserText);
    $(".user-checkbox")
        .off("change")
        .on("change", function () {
            const userId = $(this).val();
            if ($(this).is(":checked")) {
                window.selectedUserIds.add(userId);
            } else {
                window.selectedUserIds.delete(userId);
            }
            userIds = Array.from(window.selectedUserIds);
            console.log("Selected IDs:", userIds);
        });
}

$("#clearAll").on("click", function (e) {
    e.preventDefault();

    // Reset global variables
    userTypeVal = 0;
    start = null;
    end = null;
    userIds = [];

    window.allUsers = [];
    window.selectedUserIds = new Set();

    // Reset dropdown to default
    if (userTypeVal) {
        $("#userType").val(0).trigger("change");
    }

    // Reset flatpickr date range
    if (startDateTimePicker) startDateTimePicker.clear();
    if (endDateTimePicker) endDateTimePicker.clear();

    // Clear checkboxes and user list
    $("#searchUserInput").val("");
    $("#userList").empty();
    $("#userList input:checkbox").prop("checked", false);

    // Reload timeline without any filters
    loadActivityTimeline();
});

$("#find").on("click", function (e) {
    e.preventDefault();

    loadActivityTimeline(start, end, startTime, endTime, userTypeVal, userIds);
});

// 🔹 Open Export Modal
$("#openExportModal").on("click", function (e) {
    e.preventDefault();
    const modal = new bootstrap.Modal("#exportModal");
    modal.show();
});

// 🔹 Confirm Export Excel
$("#confirmExportExcel").on("click", function (e) {
    e.preventDefault();
    exportActivityLogModal('excel');
});

// 🔹 Confirm Export PDF
$("#confirmExportPdf").on("click", function (e) {
    e.preventDefault();
    exportActivityLogModal('pdf');
});

function exportActivityLog(type) {
    let params = new URLSearchParams();

    if (start) params.append('start_date', start);
    if (end) params.append('end_date', end);
    if (startTime) params.append('start_time', startTime);
    if (endTime) params.append('end_time', endTime);

    if (userTypeVal === "public") {
        params.append('causer_type', 'public');
    } else if (userTypeVal === "internal") {
        params.append('causer_type', 'internal');
    }

    if (userIds && userIds.length > 0) {
        // Handle array of user IDs
        userIds.forEach(id => params.append('causer_id[]', id));
    }

    let url = type === 'excel' ? '/internal/activity-logs/export-excel' : '/internal/activity-logs/export-pdf';

    // Redirect to download
    window.location.href = `${url}?${params.toString()}`;
}

function exportActivityLogModal(type) {
    let params = new URLSearchParams();

    // Get selected Month and Year from modal
    const month = $("#exportMonth").val();
    const year = $("#exportYear").val();

    // Calculate start and end date for the selected month
    const startDate = `${year}-${String(month).padStart(2, '0')}-01`;
    // Get last day of the month
    const lastDay = new Date(year, month, 0).getDate();
    const endDate = `${year}-${String(month).padStart(2, '0')}-${lastDay}`;

    // Set full date range (00:00 to 23:59)
    params.append('start_date', startDate);
    params.append('end_date', endDate);
    params.append('start_time', '12:00 AM');
    params.append('end_time', '11:59 PM');

    if (userTypeVal === "public") {
        params.append('causer_type', 'public');
    } else if (userTypeVal === "internal") {
        params.append('causer_type', 'internal');
    }

    if (userIds && userIds.length > 0) {
        // Handle array of user IDs
        userIds.forEach(id => params.append('causer_id[]', id));
    }

    let url = type === 'excel' ? '/internal/activity-logs/export-excel' : '/internal/activity-logs/export-pdf';

    // Redirect to download
    window.location.href = `${url}?${params.toString()}`;

    // Close modal properly
    const modalEl = document.getElementById("exportModal");
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    }
}
