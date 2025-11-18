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

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { globSync } from 'glob';
import path from 'path';

export default defineConfig({
    server: {
        host: true, // or '0.0.0.0'
        port: 5173,
        hmr: {
            host:  process.env.VITE_HOST_IP,
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',

                // 🔹 Automatically include all JS under `resources/js/pages/**`
                ...globSync(path.resolve(__dirname, 'resources/js/pages/**/*.js')),
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});

