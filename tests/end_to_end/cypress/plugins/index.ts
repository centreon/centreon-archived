/* eslint-disable global-require */

import webpackPreprocessor from '@cypress/webpack-preprocessor';

module.exports = (on) => {
  const options = {
    webpackOptions: require('../webpack.config.js'),
  };
  on('file:preprocessor', webpackPreprocessor(options));
};
