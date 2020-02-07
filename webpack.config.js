const HtmlWebpackPlugin = require('html-webpack-plugin');
const merge = require('webpack-merge');
const path = require('path');

const baseConfig = require('@centreon/frontend-core/webpack/base/typescript');

module.exports = merge(baseConfig, {
  entry: ['@babel/polyfill', './www/front_src/src/index.js'],
  output: {
    path: path.resolve(`${__dirname}/www/static`),
    publicPath: './static/',
  },
  optimization: {
    splitChunks: {
      chunks: 'all',
    },
    runtimeChunk: true,
  },
  plugins: [
    new HtmlWebpackPlugin({
      template: './www/front_src/public/index.html',
      filename: '../index.html',
    }),
  ],
  module: {
    rules: [
      { parser: { system: false } },
      {
        test: /fonts\/.+\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[hash:8].[ext]',
              publicPath: './',
            },
          },
        ],
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
});
