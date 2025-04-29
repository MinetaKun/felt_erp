import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./tests/setup.js'],
    include: [
      'tests/**/*.test.js', 
      'tests/**/*.spec.js',
      'tests/Feature/**/*.js'
    ],
    coverage: {
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'tests/setup.js',
      ],
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
}) 