import { defineConfig } from 'vite'

export default defineConfig({
  root: '.',
  base: '/build/',
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: 'assets/script.js',
        style: 'assets/style.css'
      }
    }
  },
  server: {
    middlewareMode: false,
    host: '0.0.0.0',
    port: 5173,
    hmr: {
      host: 'localhost',
      port: 5173,
      protocol: 'ws'
    }
  },
  preview: {
    host: '0.0.0.0',
    port: 4173
  }
})
