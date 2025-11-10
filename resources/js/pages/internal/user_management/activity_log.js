import axios from "axios";
import Swal from "sweetalert2";
import flatpickr from "flatpickr";

let userTypeVal = 0; // ✅ Define globally so all functions can access it

// 🗓️ Initialize flatpickr date range picker
const picker = flatpickr("#daterange", {
    mode: "range",
    dateFormat: "Y-m-d",
    onChange(selectedDates, dateStr, instance) {
        if (selectedDates.length === 2) {
            const start = selectedDates[0].toISOString().split("T")[0];
            const end = selectedDates[1].toISOString().split("T")[0];
            loadActivityTimeline(start, end);
            instance.close();
        }
    },
});

// ✅ Load all activity logs when page loads
loadActivityTimeline();

// 🚀 Load activity timeline (with optional date filter)
async function loadActivityTimeline(startDate = null, endDate = null) {
    const allInputs = document.querySelectorAll("input, select, button, textarea");
    allInputs.forEach((el) => (el.disabled = true));

    try {
        Swal.fire({
            title: "Loading...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        const params = {};
        if (startDate && endDate) {
            params.start_date = startDate;
            params.end_date = endDate;
        }

        // 🧍 Filter by user type if selected
        if (userTypeVal === "public") {
            params.causer_type = "App\\Models\\PublicUser";
        } else if (userTypeVal === "internal") {
            params.causer_type = "App\\Models\\InternalUser";
        }

        const { data: activities } = await axios.get("/internal/activity_log/data", { params });
        const groupedActivities = groupActivitiesByDate(activities);
        renderTimeline(groupedActivities);

        Swal.close();
    } catch (error) {
        console.error("Error loading timeline:", error);
        Swal.close();
        Swal.fire({
            icon: "error",
            title: "Failed to Load",
            text: "Unable to fetch activity timeline. Please try again.",
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

// 🕒 Render timeline
function renderTimeline(groupedActivities) {
    const container = document.querySelector(".timeline-container");
    container.innerHTML = "";

    if (!Object.keys(groupedActivities).length) {
        container.innerHTML = `<div class="text-center text-muted p-4">No activities found.</div>`;
        return;
    }

    Object.entries(groupedActivities).forEach(([date, activities]) => {
        activities.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        const dateSection = document.createElement("div");
        dateSection.classList.add("timeline-date-section");
        dateSection.innerHTML = `
            <div class="timeline-end">
                <span class="p-1 fs-11 bg-primary2 text-fixed-white rounded-1 fw-medium">${date}</span>
            </div>
            <div class="timeline-continue">
                ${activities
                    .map(
                        (activity) => `
                    <div class="timeline-right">
                        <div class="timeline-content">
                            <p class="timeline-date text-muted mb-2">
                                ${new Date(activity.created_at).toLocaleTimeString([], {
                                    hour: "2-digit",
                                    minute: "2-digit",
                                })}
                            </p>
                            <div class="timeline-box">
                                <p class="mb-2">${activity.description}</p>
                                ${
                                    activity.properties?.attributes
                                        ? `<p class="text-muted mb-0 ms-2">
                                            Changed <b>${Object.keys(activity.properties.attributes).join(", ")}</b>
                                            to <b>${Object.values(activity.properties.attributes).join(", ")}</b>
                                        </p>`
                                        : ""
                                }
                            </div>
                        </div>
                    </div>`
                    )
                    .join("")}
            </div>`;
        container.appendChild(dateSection);
    });
}

// 🔄 Handle user type change
$("#userType").on("change", function (e) {
    e.preventDefault();
    userTypeVal = $(this).val();
    console.log("Selected user type:", userTypeVal);

    const modalTitle = $("#accountUserModalLabel");
    const dropdownMenu = $(".dropdown-menu");
    const dropdownButton = $("#categoryDropdown");

    // 🧭 Update modal + dropdown items
    if (userTypeVal === "public") {
        modalTitle.text("Choose Public User Account");
        dropdownButton.text("Category");
        dropdownMenu.html(`
            <li><a class="dropdown-item" href="#">All</a></li>
            <li><a class="dropdown-item" href="#">Individual</a></li>
            <li><a class="dropdown-item" href="#">Company</a></li>
        `);
    } else if (userTypeVal === "internal") {
        modalTitle.text("Choose Internal User Account");
        dropdownButton.text("User Role");
        dropdownMenu.html(`
            <li><a class="dropdown-item" href="#">All</a></li>
            <li><a class="dropdown-item" href="#">Admin</a></li>
            <li><a class="dropdown-item" href="#">Manager</a></li>
            <li><a class="dropdown-item" href="#">Staff</a></li>
        `);
    }

    // ✅ Rebind dropdown click events safely
    dropdownMenu.find(".dropdown-item").off("click").on("click", function () {
        const selectedText = $(this).text().trim();
        dropdownButton.text(selectedText);
    });
});

// 🧩 Open modal after selecting user type
$("#userAccountBtn").on("click", function (e) {
    e.preventDefault();
    if (userTypeVal === 0) {
        Swal.fire("Error", "Please choose user type first!", "error");
        return;
    }
    const modal = new bootstrap.Modal("#accountUserModal");
    modal.show();
});
