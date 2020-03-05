const merge = require('lodash/merge');

module.exports = merge(require('@centreon/frontend-core/jest'), {
  roots: ['<rootDir>/www/front_src/src/'],
  setupFilesAfterEnv: [
    '@testing-library/jest-dom/extend-expect',
    '<rootDir>/setupTest.js',
  ],
});
