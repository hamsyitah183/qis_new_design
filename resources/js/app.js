import "./bootstrap";
import { IconHome, IconUser } from "tabler-icons";
import "@fortawesome/fontawesome-free/css/all.min.css";
// import './feather-icons';

// If you are using JavaScript/ECMAScript modules:

// // If you are using an older version than Dropzone 6.0.0,
// // then you need to disabled the autoDiscover behaviour here:
// Dropzone.autoDiscover = false;

// let myDropzone = new Dropzone("#my-form");
// myDropzone.on("addedfile", file => {
//   console.log(`File added: ${file.name}`);
// });

import $ from "jquery";
window.$ = window.jQuery = $; // make it global
import "select2";

$("#redirectProfile").on("click", function (e) {
    e.preventDefault();

    console.log("redirect ");

    window.location.href = "/profile";
});

export async function getAuthUser() {
    if (window.authUserPromise) return window.authUserPromise;

    window.authUserPromise = fetch("/api/auth-user")
        .then((res) => {
            if (!res.ok) throw new Error("Failed to get auth user");
            return res.json();
        })
        .then((user) => {
            window.authUser = user; // store globally
            return user;
        })
        .catch((err) => {
            console.error(err);
            return null;
        });

    return window.authUserPromise;
}

getAuthUser().then((user) => {
    window.authUser = user;
});

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
    return await res.json();  // return the actual country data
}

export async function getEntryPoint(id) {
    const res = await fetch(`/entry_point/${id}`);
    return await res.json();  // return the actual country data
}
