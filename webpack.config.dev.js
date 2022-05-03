const path = require('path');
const os = require('os');

const ReactRefreshWebpackPlugin = require('@pmmmwh/react-refresh-webpack-plugin');
const { merge } = require('webpack-merge');
const devConfig = require('centreon-frontend/packages/frontend-config/webpack/patch/dev');

const baseConfig = require('./webpack.config');

const devServerPort = 9090;

const interfaces = os.networkInterfaces();
const externalInterface = Object.keys(interfaces).find(
  (interfaceName) =>
    !interfaceName.includes('docker') &&
    interfaces[interfaceName][0].family === 'IPv4' &&
    interfaces[interfaceName][0].internal === false &&
    !process.env.IS_STATIC_PORT_FORWARDED,
);

const devServerAddress = externalInterface
  ? interfaces[externalInterface][0].address
  : 'localhost';

const publicPath = `http://${devServerAddress}:${devServerPort}/static/`;

const isServeMode = process.env.WEBPACK_ENV === 'serve';
const isDevelopmentMode = process.env.WEBPACK_ENV === 'development';

const plugins = isServeMode ? [new ReactRefreshWebpackPlugin()] : [];

const output =
  isServeMode || isDevelopmentMode
    ? {
        publicPath,
      }
    : {};

const getStaticDirectoryPath = (moduleName) =>
  `${__dirname}/www/modules/${moduleName}/static`;

const modules = [
  {
    getDirectoryPath: getStaticDirectoryPath,
    name: 'centreon-license-manager',
  },
  {
    getDirectoryPath: getStaticDirectoryPath,
    name: 'centreon-autodiscovery-server',
  },
  { getDirectoryPath: getStaticDirectoryPath, name: 'centreon-bam-server' },
  {
    getDirectoryPath: getStaticDirectoryPath,
    name: 'centreon-augmented-services',
  },
  {
    getDirectoryPath: () => `${__dirname}/www/modules/centreon-map4-web-client`,
    name: 'centreon-map4-web-client',
  },
];

module.exports = merge(baseConfig, devConfig, {
  devServer: {
    compress: true,
    headers: { 'Access-Control-Allow-Origin': '*' },
    host: '0.0.0.0',
    hot: true,
    port: devServerPort,
    static: modules.map(({ name, getDirectoryPath }) => ({
      directory: path.resolve(getDirectoryPath(name)),
      publicPath,
      watch: true,
    })),
  },
  output,
  plugins,
  resolve: {
    alias: {
      '@mui/material': path.resolve('./node_modules/@mui/material'),
      dayjs: path.resolve('./node_modules/dayjs'),
      'react-router-dom': path.resolve('./node_modules/react-router-dom'),
    },
  },
});
