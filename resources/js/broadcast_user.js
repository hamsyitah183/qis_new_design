import { notifyUser, showToast } from "./app";
import { notification } from "./notification";

console.log("hello from apply permit page");
console.log("Internal dashboard loaded");

export function internalUserEcho() {
    setTimeout(() => {
        if (!window.Echo) {
            console.error("Echo not found");
            return;
        }

        window.Echo.private("internal-users").listen(
            ".ApplicationCreated",
            (e) => {
                console.log("✅ Application created:", e.message);
                notifyUser(e.message, e.editor);
            }
        );

        window.Echo.private("internal-users").listen(
            ".ApplicationDeleted",
            (e) => {
                console.log("✅ Application deleted:", e.message);

                notifyUser(e.message, e.editor);
            }
        );

        window.Echo.private("internal-users").listen(
            ".PublicUserUpdated",
            (e) => {
                console.log("🔔 Internal notification:", e.message);
                console.log("Public User UUID:", e.public_user_uuid);
                notifyUser(e.message);
            }
        );
    }, 100);
}

export function publicUserEcho() {
    setTimeout(() => {
        if (!window.Echo) {
            console.error("Echo not found");
            return;
        }

        window.Echo.private("public-user").listen(".PublicUser", (e) => {
            console.log("✅ Public user event:", e.message);
        });
    }, 100);
}
