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
            ".ApplicationCreatedInternalUser",
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

        window.Echo.private("internal-admins").listen(
            ".InternalAdmins",
            (e) => {
                console.log("Admin User:", e.message);

                notifyUser(e.message, e.editor);
            }
        );

        window.Echo.private("internal-clerks").listen(
            ".InternalClerks",
            (e) => {
                console.log("Admin User:", e.message);

                notifyUser(e.message, e.editor);
            }
        );

        window.Echo.private("internal-officers").listen(
            ".InternalOfficers",
            (e) => {
                console.log("Admin User:", e.message);

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

export function publicUserEcho(uuid) {
    console.log('public user uuid', uuid);
    setTimeout(() => {
        console.log("Setting up Public User Echo for UUID:", uuid);
        if (!window.Echo) {
            console.error("Echo not found");
            return;
        }

        // window.Echo.private("public-user").listen(".PublicUserEvent", (e) => {
        //     console.log("✅ Public user event:", e.message);
        // });

        window.Echo.private(`public-user.${uuid}`).listen(
            ".PublicUserEvent",
            (e) => {
                console.log("✅ Application Public user event:", e.message);
                notifyUser(e.message);
            }
        );
    }, 100);
}
