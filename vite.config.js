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
                'resources/css/kader/monitoringbalita.css',
                'resources/js/kader/monitoringbalita.js',
                'resources/css/kader/profil-balita.css',
                'resources/css/kader/create.css',
                'resources/js/kader/create.js',
                'resources/css/kader/dashboard.css',
                'resources/css/kader/jadwal.css',
                'resources/css/kader/edukasi.css',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
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
