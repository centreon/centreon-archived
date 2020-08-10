const { merge } = require('webpack-merge');
const path = require('path');
const os = require('os');

const devConfig = require('@centreon/frontend-core/webpack/patch/dev');
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
  output: {
    publicPath: `http://${devServerAddress}:${devServerPort}/static/`,
  },
  resolve: {
    alias: {
      react: path.resolve('./node_modules/react'),
      'react-dom': '@hot-loader/react-dom',
      'react-router-dom': path.resolve('./node_modules/react-router-dom'),
      '@material-ui/core': path.resolve('./node_modules/@material-ui/core'),
    },
  },
  devServer: {
    contentBase: path.resolve(`${__dirname}/www/modules/`),
    compress: true,
    host: '0.0.0.0',
    port: devServerPort,
    hot: true,
    watchContentBase: true,
    headers: { 'Access-Control-Allow-Origin': '*' },
  },
});
