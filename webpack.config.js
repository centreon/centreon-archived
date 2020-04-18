const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackHarddiskPlugin = require('html-webpack-harddisk-plugin');
const merge = require('webpack-merge');
const path = require('path');

const baseConfig = require('@centreon/frontend-core/webpack/base');
const extractCssConfig = require('@centreon/frontend-core/webpack/patch/extractCss');

module.exports = merge(baseConfig, extractCssConfig, {
  entry: [
    'react-hot-loader/patch',
    '@babel/polyfill',
    './www/front_src/src/index.js',
  ],
  output: {
    path: path.resolve(`${__dirname}/www/static`),
    publicPath: './static/',
    library: ['name'],
  },
  optimization: {
    splitChunks: {
      chunks: 'all',
    },
    runtimeChunk: true,
  },
  plugins: [
    new HtmlWebpackPlugin({
      alwaysWriteToDisk: true,
      template: './www/front_src/public/index.html',
      filename: '../index.html',
    }),
    new HtmlWebpackHarddiskPlugin(),
  ],
  module: {
    rules: [
      { parser: { system: false } },
      {
        test: /fonts(\\|\/).+\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?|\.(bmp|png|jpg|jpeg|gif|svg)$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              name: '[name].[hash:8].[ext]',
              limit: 10000,
            },
          },
        ],
      },
      {
        test: /\.icon.svg$/,
        use: ['@svgr/webpack'],
      },
    ],
  },
});
