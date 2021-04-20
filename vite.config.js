import { defineConfig } from 'vite';
import reactRefresh from '@vitejs/plugin-react-refresh';

const { resolve } = require('path');

/**
 * @type {import('vite').UserConfig}
 */
export default defineConfig({
  base: '/centreon/',

  build: {
    emptyOutDir: true,
    manifest: true,
    minify: 'esbuild',
    outDir: './www/static',
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'www/front_src/src/index.jsx'),
      },
    },

    target: 'es2018',
  },

  logLevel: 'info',

  plugins: [reactRefresh()],

  root: './www/front_src/src',

  server: {
    cors: true,
    hmr: true,
    port: 9090,
    strictPort: true,
  },
});
