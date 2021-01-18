/* eslint-disable global-require */
/* eslint-disable @typescript-eslint/no-var-requires */
const webpackPreprocessor = require('cypress-webpack-preprocessor-v5');

module.exports = (on) => {
  const options = {
    webpackOptions: require('../webpack.config.js'),
  };
  on('file:preprocessor', webpackPreprocessor(options));
};
