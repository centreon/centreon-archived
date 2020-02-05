const merge = require('webpack-merge');
const LiveReloadPlugin = require('webpack-livereload-plugin');
const HtmlWebpackTagsPlugin = require('html-webpack-tags-plugin');
const baseConfig = require('./webpack.config');

module.exports = merge(baseConfig, {
  devtool: 'inline-source-map',

  plugins: [
    new LiveReloadPlugin(),
    new HtmlWebpackTagsPlugin({
      tags: ['http://localhost:35729/livereload.js'],
      append: true,
      usePublicPath: false,
    }),
  ],
});
