import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    server: {
        port: 5178,
    },
    build: {
        emptyOutDir: true,
        rollupOptions: {
            input: {
                'main': './src/main.ts',
                'style': './src/style.css'
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name][extname]',
            },
        },
    },
    plugins: [
        tailwindcss(),
        /* Custom HMR plugin - reload browser on save */
        {
            name: 'php-reloader',
            handleHotUpdate({ file, server }) {
                if (file.endsWith('.php')) {
                    server.ws.send({
                        type: 'full-reload',
                    })
                }
            },
        }
    ],
})