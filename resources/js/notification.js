export function notification() {
    console.log("Notification script loaded.");

    const pulse = document.getElementById("pulse");
    const messageDropdown = document.getElementById("messageDropdown");
    const notificationContent = document.getElementById("notificationContent");
    const notificationCount = document.getElementById("notifiation-data");

    fetch("/notifications")
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
                notificationContent.innerHTML = `
                    <li class="dropdown-item text-muted text-center">
                        No notifications
                    </li>
                `;
                return;
            }

            data.forEach((notification) => {
                const listItem = document.createElement("li");
                listItem.className = "dropdown-item";

                const url = notification.data.url ? notification.data.url : "#";

                listItem.innerHTML = `
                    <a href="${url}" class="d-flex align-items-center">
                        <div class="pe-2">
                            <span class="avatar avatar-md bg-primary avatar-rounded">
                                <i class="ri-notification-3-line"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0 fw-medium">${
                                notification.data.user
                            }</p>
                            <div class="text-muted fs-12">${
                                notification.data.message
                            }</div>
                            <div class="fw-normal fs-10 text-muted">${formatTime(
                                notification.created_at
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
            "#notificationContent li a"
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
                console.error("Failed to mark notifications read:", err)
            );
    });
}

function formatTime(dateString) {
    const now = new Date();
    const time = new Date(dateString);
    const seconds = Math.floor((now - time) / 1000);

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
