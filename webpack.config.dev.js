const merge = require('webpack-merge');
const LiveReloadPlugin = require('webpack-livereload-plugin');
const path = require('path');

const devConfig = require('@centreon/frontend-core/webpack/patch/dev');
const baseConfig = require('./webpack.config');

module.exports = merge(baseConfig, devConfig, {
  resolve: {
    alias: {
      react: path.resolve('./node_modules/react'),
      'react-router-dom': path.resolve('./node_modules/react-router-dom'),
      '@material-ui/core': path.resolve('./node_modules/@material-ui/core'),
    },
  },
  plugins: [new LiveReloadPlugin({ appendScriptTag: true })],
});
