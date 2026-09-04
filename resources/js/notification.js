import $ from "jquery";
import Swal from "sweetalert2";
import { applyTranslations } from "./app";

// Helper to get the current language from localStorage (same as used in app.js)
function getCurrentLanguage() {
    return localStorage.getItem('qis_lang') || 'en';
}

// Helper function to fetch with retry logic
async function fetchWithRetry(url, options = {}, maxRetries = 3, delay = 1000) {
    let lastError;
    
    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) throw new Error("Network response was not ok");
            return await response.json();
        } catch (error) {
            lastError = error;
            console.warn(`Notification fetch attempt ${attempt}/${maxRetries} failed:`, error);
            
            if (attempt < maxRetries) {
                // Exponential backoff: wait before retrying
                await new Promise(resolve => setTimeout(resolve, delay * attempt));
            }
        }
    }
    
    // If all retries failed, throw the last error
    throw lastError;
}

// Helper to extract the message in the correct language
const notifTranslations = {
    'Inspection application has been approved by clerk.': 'Permohonan pemeriksaan telah diluluskan oleh kerani.',
    'Consignment application has been approved by clerk.': 'Permohonan konsainan telah diluluskan oleh kerani.',
    'Inspection application has been rejected by clerk.': 'Permohonan pemeriksaan telah ditolak oleh kerani.',
    'Consignment application has been rejected by clerk.': 'Permohonan konsainan telah ditolak oleh kerani.',
    'New inspection application has been submitted.': 'Permohonan pemeriksaan baharu telah dihantar.',
    'New consignment application has been submitted.': 'Permohonan konsainan baharu telah dihantar.',
    'New import permit draft created': 'Draf permit import baharu dibuat',
    'Import permit draft updated': 'Draf permit import dikemas kini',
    'New import permit application submitted': 'Permohonan permit import baharu dihantar',
    'Import permit application updated': 'Permohonan permit import dikemas kini',
    'An application needs your approval.': 'Satu permohonan memerlukan kelulusan anda.',
    'Your application has been successfully submitted and is waiting for approval.': 'Permohonan anda telah berjaya dihantar dan menunggu kelulusan.',
    'Your application has been successfully submitted.': 'Permohonan anda telah berjaya dihantar.',
    'has been approved by clerk.': 'telah diluluskan oleh kerani.',
    'has been rejected by clerk.': 'telah ditolak oleh kerani.',
    'Inspection application': 'Permohonan pemeriksaan',
    'Consignment application': 'Permohonan konsainan',
    'Import permit application': 'Permohonan permit import',
    'has been submitted.': 'telah dihantar.',
};

function translateBm(text) {
    let bmText = text;
    for (const [en, bm] of Object.entries(notifTranslations)) {
        bmText = bmText.replace(new RegExp(en, 'gi'), bm);
    }
    return bmText;
}

function getLocalizedMessage(notificationData) {
    const lang = getCurrentLanguage();
    const message = notificationData.message;

    if (typeof message === 'object' && message !== null) {
        return message[lang] || message['en'] || 'Notification';
    }
    
    return lang === 'bm' ? translateBm(message || 'Notification') : (message || 'Notification');
}

export function notification() {
    console.log("Notification script loaded.");

    const pulse = document.getElementById("pulse");
    const messageDropdown = document.getElementById("messageDropdown");
    const notificationContent = document.getElementById("notificationContent");
    const notificationCount = document.getElementById("notifiation-data");

    fetchWithRetry("/notifications/data")
        .then((data) => {
            notificationContent.innerHTML = "";
            const lang = getCurrentLanguage();

            console.log("Fetched notifications:", data);

            // Count unread notifications
            const unread = data.filter((n) => !n.read_at);
            if (unread.length > 0) {
                pulse.className =
                    "header-icon-pulse bg-primary2 rounded pulse pulse-secondary";
                notificationCount.setAttribute('data-en', `${unread.length} Unread`);
                notificationCount.setAttribute('data-bm', `${unread.length} Belum Dibaca`);
                notificationCount.textContent = lang === 'bm' ? `${unread.length} Belum Dibaca` : `${unread.length} Unread`;
            } else {
                pulse.className = "";
                notificationCount.setAttribute('data-en', `0 Unread`);
                notificationCount.setAttribute('data-bm', `0 Belum Dibaca`);
                notificationCount.textContent = lang === 'bm' ? `0 Belum Dibaca` : `0 Unread`;
            }

            if (!data.length) {
                const noMsgEn = 'No notifications';
                const noMsgBm = 'Tiada Notifikasi';
                notificationContent.innerHTML = `
                    <li class="dropdown-item text-muted text-center" data-en="${noMsgEn}" data-bm="${noMsgBm}">
                        ${lang === 'bm' ? noMsgBm : noMsgEn}
                    </li>
                `;
                return;
            }

            data.forEach((notification) => {
                const listItem = document.createElement("li");
                listItem.className = "dropdown-item";

                const url = notification.data.url ? notification.data.url : "#";
                const user = notification.data.user || 'System';
                const messageObj = typeof notification.data.message === 'object' ? notification.data.message : { en: notification.data.message, bm: translateBm(notification.data.message) };
                const msgEn = messageObj.en ? messageObj.en.replace(/"/g, '&quot;') : 'Notification';
                const msgBm = messageObj.bm ? messageObj.bm.replace(/"/g, '&quot;') : 'Notifikasi';
                const message = getLocalizedMessage(notification.data);
                const timeFmt = formatTime(notification.created_at);

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
                            <div class="fw-normal fs-10 text-muted" data-en="${timeFmt.en}" data-bm="${timeFmt.bm}">${lang === 'bm' ? timeFmt.bm : timeFmt.en}</div>
                        </div>
                    </a>
                `;

                notificationContent.appendChild(listItem);
            });

            applyTranslations(notificationContent);
        })
        .catch((error) => {
            console.error("Notification error after all retries:", error);
            const lang = getCurrentLanguage();
            const noMsgEn = 'Failed to load notifications';
            const noMsgBm = 'Gagal memuatkan notifikasi';
            notificationContent.innerHTML = `
                <li class="dropdown-item text-danger text-center" data-en="${noMsgEn}" data-bm="${noMsgBm}">
                    ${lang === 'bm' ? noMsgBm : noMsgEn}
                </li>
            `;
        });

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
                const lang = getCurrentLanguage();
                pulse.className = "";
                notificationCount.setAttribute('data-en', `0 Unread`);
                notificationCount.setAttribute('data-bm', `0 Belum Dibaca`);
                notificationCount.textContent = lang === 'bm' ? `0 Belum Dibaca` : `0 Unread`;

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

    if (seconds < 10) return { en: "Just now", bm: "Baru sahaja" };
    if (seconds < 60) return { en: `${seconds} seconds ago`, bm: `${seconds} saat lepas` };

    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return { en: `${minutes} minute${minutes > 1 ? "s" : ""} ago`, bm: `${minutes} minit lepas` };

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return { en: `${hours} hour${hours > 1 ? "s" : ""} ago`, bm: `${hours} jam lepas` };

    const days = Math.floor(hours / 24);
    if (days < 7) return { en: `${days} day${days > 1 ? "s" : ""} ago`, bm: `${days} hari lepas` };

    const dateFmt = time.toLocaleDateString("en-MY", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
    return { en: dateFmt, bm: dateFmt };
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

    fetchWithRetry(url)
        .then((data) => {
            notificationList.innerHTML = "";
            const lang = getCurrentLanguage(); // ← declare once, top of scope

            if (!data.length) {
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
                const messageObj = typeof notification.data.message === 'object' ? notification.data.message : { en: notification.data.message, bm: translateBm(notification.data.message) };
                const msgEn = messageObj.en ? messageObj.en.replace(/"/g, '&quot;') : 'Notification';
                const msgBm = messageObj.bm ? messageObj.bm.replace(/"/g, '&quot;') : 'Notifikasi';
                const message = getLocalizedMessage(notification.data);
                const timeFmt = formatTime(notification.created_at);

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

                    <span class="text-muted ms-auto fs-12" data-en="${timeFmt.en}" data-bm="${timeFmt.bm}">
                        ${lang === 'bm' ? timeFmt.bm : timeFmt.en}
                    </span>
                `;

                notificationList.appendChild(listItem);
            });

            applyTranslations(notificationList);
        })
        .catch((error) => {
            console.error("Notification error after all retries:", error);
            const lang = getCurrentLanguage();
            const failMsg = lang === 'bm' ? 'Gagal memuatkan notifikasi' : 'Failed to load notifications';
            notificationList.innerHTML = `
                <li class="list-group-item border-bottom-0 text-center text-danger">
                    <span class="fw-medium">${failMsg}</span>
                </li>
            `;
        })
        .finally(() => Swal.close());
}
// Call on load
notificationContent();