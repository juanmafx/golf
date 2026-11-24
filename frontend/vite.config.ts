import { defineConfig } from 'vitest/config';

export default defineConfig({
  base: '/build/',
  build: {
    outDir: '../backend/public/build',
    emptyOutDir: true,
    manifest: 'manifest.json',
    rollupOptions: { input: 'src/main.ts' },
  },
  test: {
    environment: 'jsdom',
    include: ['tests/**/*.test.ts'],
  },
});
