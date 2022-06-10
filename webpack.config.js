const path = require('path');
const zlib = require('zlib');

const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackHarddiskPlugin = require('html-webpack-harddisk-plugin');
const { merge } = require('webpack-merge');
const CompressionPlugin = require('compression-webpack-plugin');
const getBaseConfiguration = require('centreon-frontend/packages/frontend-config/webpack/base');

module.exports = (jscTransformConfiguration) =>
  merge(getBaseConfiguration(jscTransformConfiguration), {
    entry: ['./www/front_src/src/index.tsx'],
    module: {
      rules: [
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
      new CompressionPlugin({
        algorithm: 'brotliCompress',
        compressionOptions: {
          params: {
            [zlib.constants.BROTLI_PARAM_QUALITY]: 11,
          },
        },
        deleteOriginalAssets: false,
        filename: '[path][base].br',
        minRatio: 0.8,
        test: /\.(js|css|html|svg)$/,
        threshold: 10240,
      }),
    ],
  });
