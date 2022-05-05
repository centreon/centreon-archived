const path = require('path');

const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackHarddiskPlugin = require('html-webpack-harddisk-plugin');
const { merge } = require('webpack-merge');
const baseConfig = require('centreon-frontend/packages/frontend-config/webpack/base');
const { ModuleFederationPlugin } = require('webpack').container;

module.exports = merge(baseConfig, {
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
    new ModuleFederationPlugin({
      name: 'centreon',
      shared: [
        {
          '@centreon/ui-context': {
            requiredVersion: '22.4.0',
            singleton: true,
          },
        },
        {
          jotai: {
            requiredVersion: '1.x',
            singleton: true,
          },
        },
        {
          react: {
            requiredVersion: '18.x',
            singleton: true,
          },
        },
        {
          'react-dom': {
            requiredVersion: '18.x',
            singleton: true,
          },
        },
        {
          'react-i18next': {
            requiredVersion: '11.x',
            singleton: true,
          },
        },
        {
          'react-router-dom': {
            requiredVersion: '6.x',
            singleton: true,
          },
        },
      ],
    }),
  ],
});
