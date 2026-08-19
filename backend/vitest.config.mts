import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    environment: 'node',
    testTimeout: 20_000,
    hookTimeout: 20_000,
    fileParallelism: false, // los tests de correlativos comparten la misma tabla — deben correr en serie
    include: ['src/**/*.test.ts'], // excluye dist/ (artefactos compilados de `npm run build`)
  },
});
