const os = require('os');

const ReactRefreshWebpackPlugin = require('@pmmmwh/react-refresh-webpack-plugin');
const { merge } = require('webpack-merge');
const {
  getDevConfiguration,
  devJscTransformConfiguration,
  devRefreshJscTransformConfiguration,
} = require('centreon-frontend/packages/frontend-config/webpack/patch/dev');

const getBaseConfiguration = require('./webpack.config');

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

module.exports = merge(
  getBaseConfiguration({
    isE2E: true,
    jscTransformConfiguration: isServeMode
      ? devRefreshJscTransformConfiguration
      : devJscTransformConfiguration,
  }),
  getDevConfiguration(),
  {
    devServer: {
      compress: true,
      headers: { 'Access-Control-Allow-Origin': '*' },
      host: '0.0.0.0',
      hot: true,
      port: devServerPort,
    },
    output,
    plugins,
  },
);
