import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                main: 'assets/js/main.js',
                style: 'assets/css/style.css',
            },
        },
    },
});
