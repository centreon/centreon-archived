/* eslint-disable @typescript-eslint/explicit-function-return-type */
/* eslint-disable global-require */
/* eslint-disable @typescript-eslint/no-var-requires */

module.exports = (on, config) => {
  if (config.testingType === 'component') {
    const { startDevServer } = require('@cypress/webpack-dev-server');

    const webpackConfig = require('../../webpack.config.dev');

    on('dev-server:start', (options) =>
      startDevServer({ options, webpackConfig }),
    );
  }
};
