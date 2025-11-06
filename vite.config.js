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
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',
                // Automatically include all page-specific JS files
                ...globSync(path.resolve(__dirname, 'resources/js/pages/**/*.js')),
                ...globSync(path.resolve(__dirname, 'resources/js/internal/**/*.js')),
                ...globSync(path.resolve(__dirname, 'resources/js/public/**/*.js')),
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
