import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/landing.css',
                'resources/css/faq.css',
                'resources/css/register.css',
                'resources/js/app.js',
                'resources/js/landing.js',
                'resources/js/faq.js',
                'resources/js/contact.js',
                'resources/css/dashboard.css',
                'resources/js/dashboard.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});