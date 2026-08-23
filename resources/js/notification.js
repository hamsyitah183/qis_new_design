import $ from "jquery";
import Swal from "sweetalert2";

// Helper to get the current language from localStorage (same as used in app.js)
function getCurrentLanguage() {
    return localStorage.getItem('qis_lang') || 'en';
}

// Helper to extract the message in the correct language
function getLocalizedMessage(notificationData) {
    const lang = getCurrentLanguage();
    const message = notificationData.message;

    // If message is an object with 'en' and 'bm' keys
    if (typeof message === 'object' && message !== null) {
        return message[lang] || message['en'] || 'Notification';
    }
    // Otherwise it's a plain string (old notifications)
    return message || 'Notification';
}

export function notification() {
    console.log("Notification script loaded.");

    const pulse = document.getElementById("pulse");
    const messageDropdown = document.getElementById("messageDropdown");
    const notificationContent = document.getElementById("notificationContent");
    const notificationCount = document.getElementById("notifiation-data");

    fetch("/notifications/data")
        .then((response) => {
            if (!response.ok) throw new Error("Network response was not ok");
            return response.json();
        })
        .then((data) => {
            notificationContent.innerHTML = "";

            console.log("Fetched notifications:", data);

            // Count unread notifications
            const unread = data.filter((n) => !n.read_at);
            if (unread.length > 0) {
                pulse.className =
                    "header-icon-pulse bg-primary2 rounded pulse pulse-secondary";
                notificationCount.textContent = `${unread.length} Unread`;
            } else {
                pulse.className = "";
                notificationCount.textContent = `0 Unread`;
            }

            if (!data.length) {
                // Use bilingual static message (already in HTML via data attributes, but we set innerHTML)
                const lang = getCurrentLanguage();
                const noMsg = lang === 'bm' ? 'Tiada Notifikasi' : 'No notifications';
                notificationContent.innerHTML = `
                    <li class="dropdown-item text-muted text-center">
                        ${noMsg}
                    </li>
                `;
                return;
            }

            data.forEach((notification) => {
                const listItem = document.createElement("li");
                listItem.className = "dropdown-item";

                const url = notification.data.url ? notification.data.url : "#";
                const user = notification.data.user || 'System';
                const messageObj = typeof notification.data.message === 'object' ? notification.data.message : { en: notification.data.message, bm: notification.data.message };
                const msgEn = messageObj.en ? messageObj.en.replace(/"/g, '&quot;') : 'Notification';
                const msgBm = messageObj.bm ? messageObj.bm.replace(/"/g, '&quot;') : 'Notifikasi';
                const message = getLocalizedMessage(notification.data);

                listItem.innerHTML = `
                    <a href="${url}" class="d-flex align-items-center">
                        <div class="pe-2">
                            <span class="avatar avatar-md bg-primary avatar-rounded">
                                <i class="ri-notification-3-line"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0 fw-medium">${user}</p>
                            <div class="text-muted fs-12" data-en="${msgEn}" data-bm="${msgBm}">${message}</div>
                            <div class="fw-normal fs-10 text-muted">${formatTime(
                                notification.created_at,
                            )}</div>
                        </div>
                    </a>
                `;

                notificationContent.appendChild(listItem);
            });
        })
        .catch((error) => console.error("Notification error:", error));

    // Mark all notifications as read when dropdown is clicked
    messageDropdown.addEventListener("click", () => {
        const unreadItems = document.querySelectorAll(
            "#notificationContent li a",
        ); // only shown notifications
        if (unreadItems.length === 0) return;

        fetch("/notifications/mark-read", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
        })
            .then(() => {
                pulse.className = "";
                notificationCount.textContent = "0 Unread";

                // Mark visually all the items as read (optional)
                unreadItems.forEach((item) => {
                    item.classList.remove("fw-medium"); // example: remove bold
                });
            })
            .catch((err) =>
                console.error("Failed to mark notifications read:", err),
            );
    });
}

function formatTime(dateString) {
    const now = new Date();
    const time = new Date(dateString);
    const seconds = Math.floor((now - time) / 1000);

    // You can optionally translate these time labels using a helper, but I'll keep as is.
    if (seconds < 10) return "Just now";
    if (seconds < 60) return `${seconds} seconds ago`;

    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} minute${minutes > 1 ? "s" : ""} ago`;

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hour${hours > 1 ? "s" : ""} ago`;

    const days = Math.floor(hours / 24);
    if (days < 7) return `${days} day${days > 1 ? "s" : ""} ago`;

    return time.toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

// (Existing event listeners for the dropdown filter – unchanged)
document.querySelectorAll(".dropdown-item-notification").forEach((item) => {
    item.addEventListener("click", function (e) {
        e.preventDefault();
        const hours = this.dataset.time;
        notificationContent(hours);
    });
});

export function notificationContent(hours = null) {
    const notificationList = document.getElementById("notificationList");

    let url = "/notifications/data/get";
    if (hours) {
        url += `?hours=${hours}`;
    }

    fetch(url)
        .then((response) => {
            if (!response.ok) throw new Error("Network response was not ok");
            return response.json();
        })
        .then((data) => {
            notificationList.innerHTML = "";

            if (!data.length) {
                const lang = getCurrentLanguage();
                const noMsg = lang === 'bm' ? 'Tiada notifikasi' : 'No notification';
                notificationList.innerHTML = `
                    <li class="list-group-item border-bottom-0 text-center">
                        <span class="fw-medium">${noMsg}</span>
                    </li>
                `;
                return;
            }

            data.forEach((notification) => {
                const listItem = document.createElement("a");
                listItem.className =
                    "list-group-item border-bottom-0 d-flex gap-2 align-items-start pb-2 border-bottom";

                listItem.href = notification.data.url ?? "#";

                const user = notification.data.user || 'System';
                const messageObj = typeof notification.data.message === 'object' ? notification.data.message : { en: notification.data.message, bm: notification.data.message };
                const msgEn = messageObj.en ? messageObj.en.replace(/"/g, '&quot;') : 'Notification';
                const msgBm = messageObj.bm ? messageObj.bm.replace(/"/g, '&quot;') : 'Notifikasi';
                const message = getLocalizedMessage(notification.data);

                listItem.innerHTML = `
                    <div class="pe-2">
                        <span class="avatar avatar-md bg-primary avatar-rounded">
                            <i class="ri-notification-3-line"></i>
                        </span>
                    </div>

                    <div class="text-wrap">
                        <span class="fw-medium">${user}</span>
                        <p class="text-muted mb-0 fs-12 w-100 text-wrap" data-en="${msgEn}" data-bm="${msgBm}">
                            ${message}
                        </p>
                    </div>

                    <span class="text-muted ms-auto fs-12">
                        ${formatTime(notification.created_at)}
                    </span>
                `;

                notificationList.appendChild(listItem);
            });
        })
        .catch(() => {
            console.log("error fetch notification");
        })
        .finally(() => Swal.close());
}

// Call on load
notificationContent();