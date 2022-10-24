const { merge } = require('webpack-merge');
const {
  getDevConfiguration,
  devJscTransformConfiguration,
  devRefreshJscTransformConfiguration,
} = require('centreon-frontend/packages/frontend-config/webpack/patch/dev');

const getBaseConfiguration = require('./webpack.config');
const webpackDevServerConfig = require('./webpack.config.devServer');

const isServeMode = process.env.WEBPACK_ENV === 'serve';

module.exports = merge(
  getBaseConfiguration({
    isE2E: true,
    jscTransformConfiguration: isServeMode
      ? devRefreshJscTransformConfiguration
      : devJscTransformConfiguration,
  }),
  getDevConfiguration(),
  webpackDevServerConfig,
);
