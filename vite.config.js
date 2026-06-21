import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/ar.css',
                'resources/js/ar.js',
                'resources/js/ar-compiler.js',
            ],
            refresh: true,
            fonts: [
                bunny('Sora', { weights: [400, 500, 600, 700] }),
                bunny('DM Sans', { weights: [400, 500, 600] }),
                bunny('Funnel Sans', { weights: [400, 600, 700] }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
