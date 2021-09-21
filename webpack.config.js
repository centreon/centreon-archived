const path = require('path');

const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackHarddiskPlugin = require('html-webpack-harddisk-plugin');
const { merge } = require('webpack-merge');

const baseConfig = require('@centreon/centreon-frontend/packages/frontend-config/webpack/base');
const extractCssConfig = require('@centreon/centreon-frontend/packages/frontend-config/webpack/patch/extractCss');

module.exports = merge(baseConfig, extractCssConfig, {
  entry: ['@babel/polyfill', './www/front_src/src/index.js'],
  module: {
    rules: [
      {
        parser: { system: false },
        test: /\.[cm]?(j|t)sx?$/,
      },
      {
        test: /\.icon.svg$/,
        use: ['@svgr/webpack'],
      },
      {
        test: /\.(bmp|png|jpg|jpeg|gif|svg)$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              limit: 10000,
              name: '[name].[hash:8].[ext]',
            },
          },
        ],
      },
    ],
  },
  output: {
    crossOriginLoading: 'anonymous',
    library: ['name'],
    path: path.resolve(`${__dirname}/www/static`),
    publicPath: './static/',
  },
  plugins: [
    new HtmlWebpackPlugin({
      alwaysWriteToDisk: true,
      filename: path.resolve(`${__dirname}`, 'www', 'index.html'),
      template: './www/front_src/public/index.html',
    }),
    new HtmlWebpackHarddiskPlugin(),
  ],
});
