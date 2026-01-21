import "./bootstrap";
import { IconHome, IconUser } from "tabler-icons";
import "@fortawesome/fontawesome-free/css/all.min.css";


import $ from "jquery";
window.$ = window.jQuery = $; // make it global
import "select2";
import { internalUserEcho, publicUserEcho } from "./broadcast_user";
import { notification, notificationContent } from "./notification";

$("#redirectProfile").on("click", function (e) {
    e.preventDefault();

    console.log("redirect ");

    window.location.href = "/profile";
});

export function getAuthUser() {
    return window.authUser ?? null;
}


const user = getAuthUser();
console.log('user', user);

if (user) {
    if (user.type === "internal") {
        internalUserEcho();
    } else {
        publicUserEcho(user.uuid);
    }
}

export function formatTime(timestamp) {
    const utcDate = new Date(timestamp);

    // Malaysia Time (UTC+8)
    const malaysiaOffset = 8 * 60; // minutes
    const localTime = new Date(utcDate.getTime() + malaysiaOffset * 60 * 1000);

    const options = {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    };

    const formatted = new Intl.DateTimeFormat("en-GB", options).format(
        localTime
    );

    return formatted;
}

export function initTooltips() {
    // remove old tooltip instances to avoid duplicates
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        if (bootstrap.Tooltip.getInstance(el)) {
            bootstrap.Tooltip.getInstance(el).dispose();
        }
        new bootstrap.Tooltip(el);
    });
}

export async function getCountry(code) {
    const res = await fetch(`/country/${code}`);
    return await res.json(); // return the actual country data
}

export async function getEntryPoint(id) {
    const res = await fetch(`/entry_point/${id}`);
    return await res.json(); // return the actual country data
}

export function showToast(message) {
    const toastEl = document.getElementById("editToast");
    const toastMessage = document.getElementById("toastMessage");
    const toastTime = document.getElementById("toastTime");

    toastMessage.innerText = message;
    toastTime.innerText = "just now";

    const toast = new bootstrap.Toast(toastEl, {
        delay: 7000,
    });

    toast.show();
}

export function notifyUser(message, editor = null) {
    // 🔔 PLAY SOUND
    const sound = document.getElementById("notificationSound");

    // Required for browser autoplay policy
    sound.currentTime = 0;
    sound.play().catch(() => {
        console.warn("Sound blocked until user interacts with page");
    });

    // 🍞 SHOW TOAST
    showToast(editor ? `${message} (by ${editor})` : message);
    notificationContent();
}


// notification();


let notificationInterval = null;

export function startNotificationPolling() {
    if (notificationInterval) return; // prevent duplicates

    notification(); // run immediately
    notificationInterval = setInterval(notification, 10000);
}

export function stopNotificationPolling() {
    if (!notificationInterval) return;

    clearInterval(notificationInterval);
    notificationInterval = null;
}

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopNotificationPolling();
    } else {
        startNotificationPolling();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    startNotificationPolling();
});

