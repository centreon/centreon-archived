const merge = require('webpack-merge');
const path = require('path');

const devConfig = require('@centreon/frontend-core/webpack/patch/dev');
const baseConfig = require('./webpack.config');

const devServerPort = 9090;

module.exports = merge(baseConfig, devConfig, {
  output: {
    publicPath: `http://localhost:${devServerPort}/static/`,
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
    port: devServerPort,
    hot: true,
    watchContentBase: true,
    headers: { 'Access-Control-Allow-Origin': '*' },
  },
});
