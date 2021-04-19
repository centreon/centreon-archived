import { defineConfig } from 'vite';
import reactRefresh from '@vitejs/plugin-react-refresh';

const { resolve } = require('path');

/**
 * @type {import('vite').UserConfig}
 */
export default defineConfig({
  build: {
    manifest: true,
    minify: 'esbuild',
    outDir: './www/static',
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'www/front_src/src/index.html'),
      },
    },
    target: 'es2015',
  },

  plugins: [reactRefresh()],
});
