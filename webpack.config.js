const path = require('path');

const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackHarddiskPlugin = require('html-webpack-harddisk-plugin');
const { merge } = require('webpack-merge');
const getBaseConfiguration = require('centreon-frontend/packages/frontend-config/webpack/base');

module.exports = (jscTransformConfiguration) =>
  merge(
    getBaseConfiguration({ jscTransformConfiguration, moduleName: 'centreon' }),
    {
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
      ],
    },
  );
