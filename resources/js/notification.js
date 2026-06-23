import $ from "jquery";
import Swal from "sweetalert2";

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

                const url = notification.data.url || "#";

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
        );
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

                unreadItems.forEach((item) => {
                    item.classList.remove("fw-medium");
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

// ✅ Time-based filtering for notifications
export function notificationContent(hours = null) {
    const notificationList = document.getElementById("notificationList");

    // Show loading
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
    });

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
                notificationList.innerHTML = `
                    <li class="list-group-item border-bottom-0 text-center">
                        <span class="fw-medium text-muted">No notifications found</span>
                    </li>
                `;
                Swal.close();
                return;
            }

            // Update read count
            const unread = data.filter((n) => !n.read_at);
            const readCount = document.getElementById("readCount");
            if (readCount) {
                const total = data.length;
                const unreadCount = unread.length;
                readCount.textContent = `Notifications (${total} total, ${unreadCount} unread)`;
            }

            // ✅ Add "Mark All as Read" button if there are unread notifications
            if (unread.length > 0) {
                const markAllRow = document.createElement("li");
                markAllRow.className = "list-group-item border-bottom-0 text-center bg-primary bg-opacity-10";
                markAllRow.innerHTML = `
                    <button id="markAllReadBtn" class="btn btn-sm btn-primary">
                        <i class="ri-check-double-line me-1"></i> Mark All as Read
                    </button>
                `;
                notificationList.appendChild(markAllRow);
            }

            data.forEach((notification) => {
                const listItem = document.createElement("a");
                listItem.className =
                    "list-group-item border-bottom-0 d-flex gap-2 align-items-start pb-2 border-bottom text-decoration-none";
                listItem.dataset.notificationId = notification.id;
                
                // Add class for unread notifications
                if (!notification.read_at) {
                    listItem.classList.add("bg-light");
                }

                const url = notification.data.url || "#";

                listItem.href = url;
                listItem.style.cursor = "pointer";

                // Get icon based on notification type
                let iconHtml = `<i class="ri-notification-3-line"></i>`;
                if (notification.data.icon) {
                    iconHtml = `<i class="${notification.data.icon}"></i>`;
                }

                listItem.innerHTML = `
                    <div class="pe-2">
                        <span class="avatar avatar-md bg-primary avatar-rounded">
                            ${iconHtml}
                        </span>
                    </div>

                    <div class="text-wrap flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="fw-medium">${notification.data.user || 'System'}</span>
                            ${!notification.read_at ? '<span class="badge bg-primary rounded-pill fs-10">New</span>' : ''}
                        </div>
                        <p class="text-muted mb-0 fs-12 w-100 text-wrap">
                            ${notification.data.message || 'No message'}
                        </p>
                        <small class="text-muted fs-10">
                            ${formatTime(notification.created_at)}
                        </small>
                    </div>
                `;

                notificationList.appendChild(listItem);
            });

            Swal.close();

            // ✅ Event listener for "Mark All as Read" button
            const markAllBtn = document.getElementById("markAllReadBtn");
            if (markAllBtn) {
                markAllBtn.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    markAllNotificationsRead();
                });
            }
        })
        .catch((error) => {
            console.error('Error fetching notifications:', error);
            notificationList.innerHTML = `
                <li class="list-group-item border-bottom-0 text-center text-danger">
                    <span class="fw-medium">Failed to load notifications. Please try again.</span>
                </li>
            `;
            Swal.close();
        });
}

// ✅ Function to mark all notifications as read
function markAllNotificationsRead() {
    Swal.fire({
        title: 'Mark All as Read?',
        text: 'This will mark all notifications as read.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, mark all as read',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Marking as read...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch("/notifications/mark-read-all", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            })
            .then((response) => {
                if (!response.ok) throw new Error("Network response was not ok");
                return response.json();
            })
            .then((data) => {
                Swal.close();
                
                // Update pulse and count
                const pulse = document.getElementById("pulse");
                const notificationCount = document.getElementById("notifiation-data");
                if (pulse) pulse.className = "";
                if (notificationCount) notificationCount.textContent = "0 Unread";
                
                // Update read count
                const readCount = document.getElementById("readCount");
                if (readCount) {
                    const currentText = readCount.textContent;
                    // Update to show 0 unread
                    readCount.textContent = currentText.replace(/\d+ unread/, '0 unread');
                }

                // Remove "New" badges and light background from all notifications
                document.querySelectorAll('#notificationList .list-group-item').forEach(item => {
                    item.classList.remove('bg-light');
                    const badge = item.querySelector('.badge.bg-primary');
                    if (badge) badge.remove();
                });

                // Remove the "Mark All as Read" button
                const markAllRow = document.querySelector('#notificationList .list-group-item.bg-primary');
                if (markAllRow) markAllRow.remove();

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'All notifications marked as read.',
                    timer: 2000,
                    showConfirmButton: false
                });
            })
            .catch((error) => {
                console.error('Error marking all notifications as read:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to mark notifications as read. Please try again.'
                });
            });
        }
    });
}

// ✅ Event listeners for time filter
document.addEventListener('DOMContentLoaded', function() {
    // Initial load
    notificationContent();

    // Time filter click handlers
    document.querySelectorAll('.dropdown-item-notification').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            const hours = this.dataset.time;
            
            // Update active state
            document.querySelectorAll('.dropdown-item-notification').forEach(el => {
                el.classList.remove('active');
            });
            this.classList.add('active');
            
            // Reload with filter
            notificationContent(hours);
        });
    });
});

// ✅ Mark single notification as read when clicked
document.addEventListener('click', function(e) {
    const notificationLink = e.target.closest('.list-group-item');
    if (notificationLink && notificationLink.classList.contains('list-group-item') && notificationLink.id !== 'markAllReadBtn') {
        // Find the notification data
        const notificationId = notificationLink.dataset.notificationId;
        if (notificationId) {
            fetch('/notifications/mark-read-single', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the UI
                    notificationLink.classList.remove('bg-light');
                    const badge = notificationLink.querySelector('.badge.bg-primary');
                    if (badge) badge.remove();
                    
                    // Update counts
                    updateNotificationCounts();
                }
            })
            .catch(err => console.error('Failed to mark notification read:', err));
        }
    }
});

// ✅ Function to update notification counts
function updateNotificationCounts() {
    const unreadItems = document.querySelectorAll('#notificationList .list-group-item.bg-light');
    const unreadCount = unreadItems.length;
    
    const pulse = document.getElementById("pulse");
    const notificationCount = document.getElementById("notifiation-data");
    const readCount = document.getElementById("readCount");
    
    if (unreadCount > 0) {
        if (pulse) pulse.className = "header-icon-pulse bg-primary2 rounded pulse pulse-secondary";
        if (notificationCount) notificationCount.textContent = `${unreadCount} Unread`;
    } else {
        if (pulse) pulse.className = "";
        if (notificationCount) notificationCount.textContent = "0 Unread";
        
        // Remove "Mark All as Read" button if no unread
        const markAllRow = document.querySelector('#notificationList .list-group-item.bg-primary');
        if (markAllRow) markAllRow.remove();
    }
    
    if (readCount) {
        const total = document.querySelectorAll('#notificationList .list-group-item:not(.bg-primary)').length;
        readCount.textContent = `Notifications (${total} total, ${unreadCount} unread)`;
    }
}

// Call the notification function when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', notification);
} else {
    notification();
}