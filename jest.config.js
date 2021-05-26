const merge = require('lodash/merge');

module.exports = merge(
  require('@centreon/centreon-frontend/packages/frontend-config/jest'),
  {
    roots: ['<rootDir>/www/front_src/src/'],
    setupFilesAfterEnv: [
      '@testing-library/jest-dom/extend-expect',
      '<rootDir>/setupTest.js',
    ],
    testEnvironment: 'jest-environment-jsdom-sixteen',
  },
);
