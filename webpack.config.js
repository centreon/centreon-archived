const path = require('path');

const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackHarddiskPlugin = require('html-webpack-harddisk-plugin');
const webpack = require('webpack');
const { merge } = require('webpack-merge');
const baseConfig = require('centreon-frontend/packages/frontend-config/webpack/base');

module.exports = merge(baseConfig, {
  entry: ['@babel/polyfill', './www/front_src/src/index.js'],

  externals: {
    bufferutil: 'bufferutil',
    net: 'net',
    tls: 'tls',
    'utf-8-validate': 'utf-8-validate',
  },
  module: {
    rules: [
      {
        parser: { system: false },
        resolve: {
          fullySpecified: false,
        },
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
    new webpack.ProvidePlugin({
      process: 'process/browser',
    }),
    new HtmlWebpackPlugin({
      alwaysWriteToDisk: true,
      filename: path.resolve(`${__dirname}`, 'www', 'index.html'),
      template: './www/front_src/public/index.html',
    }),
    new HtmlWebpackHarddiskPlugin(),
  ],
  resolve: {
    fallback: {
      assert: require.resolve('assert'),
      crypto: require.resolve('crypto-browserify'),
      http: require.resolve('stream-http'),
      https: require.resolve('https-browserify'),
      stream: require.resolve('stream-browserify'),
      url: require.resolve('url'),
      zlib: require.resolve('browserify-zlib'),
    },
  },
});
