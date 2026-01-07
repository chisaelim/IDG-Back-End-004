import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      '@com': fileURLToPath(new URL('./src/components', import.meta.url)),
      '@func': fileURLToPath(new URL('./src/functions', import.meta.url)),
    },
  },
  server: {
    host: '0.0.0.0',
    port: process.env.VITE_HMR_PORT,
    hmr: {
      protocol: 'ws',
      port: process.env.VITE_HMR_PORT,
    },
    watch: {
      usePolling: true,
      useFsEvents: true,
      interval: 1000,
    }
  }
})
