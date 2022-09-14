/* eslint-disable @typescript-eslint/explicit-function-return-type */
/* eslint-disable global-require */
/* eslint-disable @typescript-eslint/no-var-requires */
const {
    addMatchImageSnapshotPlugin,
  } = require('cypress-image-snapshot/plugin');
  
  module.exports = (on, config) => {
    if (config.testingType === 'component') {
      addMatchImageSnapshotPlugin(on, config);
  
      const { startDevServer } = require('@cypress/webpack-dev-server');
  
      const webpackConfig = require('../../webpack.config.dev');
  
      on('dev-server:start', (options) =>
        startDevServer({ options, webpackConfig }),
      );
    }
  };
  