// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';
// import tailwindcss from '@tailwindcss/vite';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//         tailwindcss(),
//     ],
// });

import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import { globSync } from "glob";
import path from "path";

export default defineConfig({

    plugins: [
        laravel({
            input: [
                "resources/js/app.js",
                "resources/css/app.css",
                "resources/css/errors.css",

                // 🔹 Automatically include all JS under `resources/js/pages/**`
                ...globSync(
                    path.resolve(__dirname, "resources/js/pages/**/*.js"),
                    path.resolve(__dirname, "resources/js/*.js")
                ),
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // server: {
    //     host: '127.0.0.1',
    //     port: 5174,
    // }
});
