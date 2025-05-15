import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  server: {
    cors: {
      origin: "*",
    },
    port: 80,
  },
  build: {
    rollupOptions: {

      emptyOutDir: true,
      output: {
        entryFileNames: `assets/index.js`,
        chunkFileNames: `assets/chunk.js`,
        assetFileNames: `assets/index.[ext]`
      }
    }
  }
})
