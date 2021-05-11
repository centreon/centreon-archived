import { defineConfig } from 'vite';
import reactRefresh from '@vitejs/plugin-react-refresh';
import svgr from 'vite-plugin-svgr';

const fs = require('fs');
const path = require('path');
const { resolve } = require('path');

const generateHtmlFile = () => {
  const devScript = `
  <script type="module" src="http://localhost:9090/centreon/@vite/client"></script>
  <script type="module" src="http://localhost:9090/centreon/index.jsx"></script>`;

  const prodScript = `
  <!-- if production -->
  <link rel="stylesheet" href="/centreon/static/assets/main.ed3145b1.css" />
  <script type="module" src="/centreon/static/assets/main.3d211f74.js"></script>
  `;

  const loadScript =
    process.env.APP_ENV === 'development' ? devScript : prodScript;

  const baseHtml = `
  
  <!doctype html>
  <html lang="en" style="margin:0;padding:0;width:100%;height:100%;">
  
  <head>
      <meta charset="utf-8">
      <link rel="shortcut icon" href="./img/favicon.ico">
      <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
      <meta name="theme-color" content="#000000">
      <base href="/centreon/">
      <title>Centreon - IT & Network Monitoring</title>
  
      <script type="module">
          import RefreshRuntime from 'http://localhost:9090/centreon/@react-refresh'
          RefreshRuntime.injectIntoGlobalHook(window)
          window.$RefreshReg$ = () => { }
          window.$RefreshSig$ = () => (type) => type
          window.__vite_plugin_react_preamble_installed__ = true
      </script>
  
      ${loadScript}
  
  </head>
  
  <body style="margin:0;padding:0;width:100%;height:100%;">
  
      <noscript>You need to enable JavaScript to run this
          app.</noscript>
  
      <div id="root"></div>
  </body>
  
  </html>
  
  `;

  fs.writeFileSync(path.join(__dirname, 'www/index.html'), baseHtml);
};

generateHtmlFile();

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

  resolve: {},

  root: './www',
  server: {
    cors: true,
    hmr: true,
    port: 9090,
    strictPort: true,
  },
});
