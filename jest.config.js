const { mergeDeepRight } = require('ramda');

module.exports = mergeDeepRight(
  require('centreon-frontend/packages/frontend-config/jest'),
  {
    moduleNameMapper: {
      'd3-array': '<rootDir>/node_modules/d3-array/dist/d3-array.min.js',
    },
    roots: ['<rootDir>/www/front_src/src/'],
    setupFilesAfterEnv: [
      '@testing-library/jest-dom/extend-expect',
      '<rootDir>/setupTest.js',
    ],
    testEnvironmentOptions: {
      url: 'http://localhost/',
    },
    testMatch: ['**/__tests__/**/*.[jt]s?(x)', '**/?(*.)+(test).[jt]s?(x)'],
    testResultsProcessor: 'jest-junit',
  },
);
