const path = require('path');
const os = require('os');

const { merge } = require('webpack-merge');

const devConfig = require('@centreon/centreon-frontend/packages/frontend-config/webpack/patch/dev');

const baseConfig = require('./webpack.config');

const devServerPort = 9090;

const interfaces = os.networkInterfaces();
const externalInterface = Object.keys(interfaces).find((interfaceName) => {
  return (
    !interfaceName.includes('docker') &&
    interfaces[interfaceName][0].family === 'IPv4' &&
    interfaces[interfaceName][0].internal === false
  );
});

const devServerAddress = externalInterface
  ? interfaces[externalInterface][0].address
  : 'localhost';

module.exports = merge(baseConfig, devConfig, {
  devServer: {
    compress: true,
    contentBase: path.resolve(`${__dirname}/www/modules/`),
    headers: { 'Access-Control-Allow-Origin': '*' },
    host: '0.0.0.0',
    hot: true,
    port: devServerPort,
    watchContentBase: true,
  },
  output: {
    publicPath: `http://${devServerAddress}:${devServerPort}/static/`,
  },
  resolve: {
    alias: {
      '@material-ui/core': path.resolve('./node_modules/@material-ui/core'),
      react: path.resolve('./node_modules/react'),
      'react-dom': '@hot-loader/react-dom',
      'react-router-dom': path.resolve('./node_modules/react-router-dom'),
    },
  },
});
