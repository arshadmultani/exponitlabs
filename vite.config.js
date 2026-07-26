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
                'resources/css/filament/console/theme.css',
            ],
            refresh: true,
            fonts: [
                // No font preloads: the LCP element is text with font-display:swap,
                // so it paints at FCP with the system fallback and swaps in later.
                // On slow mobile links, preloading fonts only steals bandwidth from
                // the critical CSS and delays LCP.
                bunny('Sora', { weights: [600, 700], display: 'swap', preload: false }),
                bunny('DM Sans', { weights: [400, 500], display: 'swap', preload: false }),
                bunny('Funnel Sans', { weights: [400, 600], display: 'swap', preload: false }),
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
