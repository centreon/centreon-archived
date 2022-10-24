const path = require('path');

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
  getBaseConfiguration(
    isServeMode
      ? devRefreshJscTransformConfiguration
      : devJscTransformConfiguration,
  ),
  getDevConfiguration(),
  {
    ...webpackDevServerConfig,
    resolve: {
      alias: {
        '@mui/material': path.resolve('./node_modules/@mui/material'),
        dayjs: path.resolve('./node_modules/dayjs'),
        'react-router-dom': path.resolve('./node_modules/react-router-dom'),
      },
    },
  },
);
