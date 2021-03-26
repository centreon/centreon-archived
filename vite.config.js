import { defineConfig } from 'vite';
import reactRefresh from '@vitejs/plugin-react-refresh';
const { resolve } = require('path')

/**
 * @type {import('vite').UserConfig}
 */
export default defineConfig({
  plugins: [reactRefresh()],

  build: {
    manifest: true,
    minify: 'esbuild',
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'www/front_src/src/index.html')
      },
    },
    target: 'es2015',
    outDir: './www/static',
  },
});
