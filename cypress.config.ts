/* eslint-disable @typescript-eslint/no-var-requires */

const getDefineCypressConfig = require('centreon-frontend/packages/frontend-config/cypress/component/cypress.config');

const webpackConfig = require('./webpack.config.cypress');

module.exports = getDefineCypressConfig({
  specPattern: './www/front_src/src/**/*.cypress.spec.tsx',
  webpackConfig,
});
