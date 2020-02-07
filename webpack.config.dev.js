const merge = require('webpack-merge');
const LiveReloadPlugin = require('webpack-livereload-plugin');
const HtmlWebpackTagsPlugin = require('html-webpack-tags-plugin');
const path = require('path');

const baseConfig = require('./webpack.config');
const devConfig = require('@centreon/frontend-core/webpack/patch/dev');

module.exports = merge(baseConfig, devConfig, {
  resolve: {
    alias: {
      react: path.resolve('./node_modules/react'),
      'react-router-dom': path.resolve('./node_modules/react-router-dom'),
    },
  },
  plugins: [
    new LiveReloadPlugin(),
    new HtmlWebpackTagsPlugin({
      tags: ['http://localhost:35729/livereload.js'],
      append: true,
      usePublicPath: false,
    }),
  ],
});
