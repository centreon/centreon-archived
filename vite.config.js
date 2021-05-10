import { defineConfig } from 'vite';
import reactRefresh from '@vitejs/plugin-react-refresh';
import svgr from 'vite-plugin-svgr';

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
    outDir: './static',
    polyfillDynamicImport: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'www/index.jsx'),
      },
    },

    target: 'es2018',
  },

  logLevel: 'info',

  plugins: [svgr(), reactRefresh()],

  resolve: {
    alias: [
      {
        find: /^@material-ui\/core\/(.+)/,
        replacement: '@material-ui/core/es/$1',
      },
      {
        find: /^@material-ui\/core$/,
        replacement: '@material-ui/core/es',
      },
    ],
  },

  root: './www',
  server: {
    cors: true,
    hmr: true,
    port: 9090,
    proxy: {
      '^/centreon/(api|authentification)': {
        target: 'http://172.17.0.1:4000',
      },
    },
    strictPort: true,
  },
});
