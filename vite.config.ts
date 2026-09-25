/// <reference types="node" />
import { defineConfig, loadEnv } from 'vite'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ command, mode }) => {
    // Read the dev port from .env so it always matches the script URL PHP outputs in the footer
    const env = loadEnv(mode, process.cwd(), '')
    const devPort = Number(env.VITE_DEV_PORT) || 5178

    return {
        // Built files are served by Apache from /dist/, so asset URLs (e.g. fonts) must include it
        base: command === 'build' ? '/dist/' : '/',
        // public/ is served by Apache, so don't copy it (including user uploads) into dist
        publicDir: false,
        server: {
            port: devPort,
            // Pages are served by PHP on another port, so asset URLs in dev must point at the Vite server
            origin: `http://localhost:${devPort}`,
            // Fail rather than silently switching port
            strictPort: true,
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
    }
})
