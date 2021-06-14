const path = require('path');
const os = require('os');

const ReactRefreshWebpackPlugin = require('@pmmmwh/react-refresh-webpack-plugin');
const { merge } = require('webpack-merge');

const devConfig = require('@centreon/centreon-frontend/packages/frontend-config/webpack/patch/dev');

const baseConfig = require('./webpack.config');

const devServerPort = 9090;

const interfaces = os.networkInterfaces();
const externalInterface = Object.keys(interfaces).find(
  (interfaceName) =>
    !interfaceName.includes('docker') &&
    interfaces[interfaceName][0].family === 'IPv4' &&
    interfaces[interfaceName][0].internal === false,
);

const devServerAddress = externalInterface
  ? interfaces[externalInterface][0].address
  : 'localhost';

const publicPath = `http://${devServerAddress}:${devServerPort}/static/`;

const isServing = process.env.WEBPACK_ENV === 'serve';

const plugins = isServing ? [new ReactRefreshWebpackPlugin()] : [];

const output = isServing
  ? {
      publicPath,
    }
  : {};

module.exports = merge(baseConfig, devConfig, {
  output,
  resolve: {
    alias: {
      'react-router-dom': path.resolve('./node_modules/react-router-dom'),
      '@material-ui/core': path.resolve('./node_modules/@material-ui/core'),
      dayjs: path.resolve('./node_modules/dayjs'),
    },
  },
  devServer: {
    contentBase: [
      path.resolve(`${__dirname}/www/modules/centreon-license-manager/static`),
      path.resolve(
        `${__dirname}/www/modules/centreon-autodiscovery-server/static`,
      ),
      path.resolve(`${__dirname}/www/modules/centreon-bam-server/static`),
    ],
    compress: true,
    host: '0.0.0.0',
    port: devServerPort,
    hot: true,
    watchContentBase: true,
    headers: { 'Access-Control-Allow-Origin': '*' },
    publicPath,
  },
  plugins,
});
