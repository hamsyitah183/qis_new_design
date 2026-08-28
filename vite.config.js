import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import { globSync } from "glob";
import path from "path";

// glob requires forward-slash patterns to match correctly on Windows.
// path.resolve() returns backslash-separated paths on Windows, which
// causes globSync() to silently return an empty array with no error —
// so every page-specific JS entry gets skipped during the Vite build.
function globPosix(relativePattern) {
    return path.resolve(__dirname, relativePattern).replace(/\\/g, "/");
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/app.js",
                "resources/css/app.css",
                "resources/css/errors.css",

                ...globSync(globPosix("resources/js/pages/**/*.js")),
                ...globSync(globPosix("resources/js/*.js")),
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: "127.0.0.1",
        port: 5174,
    },
});