import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Membuka akses Vite ke semua perangkat di jaringan lokal
        port: 5173,
        hmr: {
            host: '10.88.45.178', // Ganti dengan IP lokal laptop Anda
        },
        cors: true, // Mengizinkan browser HP mengambil aset lintas port (8000 ke 5173)
    },
});
