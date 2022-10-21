const path = require('path');

const HtmlWebpackPlugin = require('html-webpack-plugin');
const HtmlWebpackHarddiskPlugin = require('html-webpack-harddisk-plugin');
const webpack = require('webpack');
const { merge } = require('webpack-merge');
const getBaseConfiguration = require('centreon-frontend/packages/frontend-config/webpack/base');

const excludeNodeModulesExceptCentreonUiAndCypressConfig =
  /node_modules(\\|\/)(?!(centreon-frontend(\\|\/)packages(\\|\/)(ui-context|centreon-ui|frontend-config\/cypress\/component)))/;

module.exports = ({ jscTransformConfiguration, isE2E = false }) =>
  merge(
    getBaseConfiguration({
      excludeJSPattern: isE2E
        ? excludeNodeModulesExceptCentreonUiAndCypressConfig
        : false,
      jscTransformConfiguration,
      moduleName: 'centreon',
    }),
    {
      entry: ['./www/front_src/src/index.tsx'],
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
    },
  );
