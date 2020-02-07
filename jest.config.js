module.exports = {
  setupFilesAfterEnv: [
    '@testing-library/react/cleanup-after-each',
    '@testing-library/jest-dom/extend-expect',
  ],
  roots: ['<rootDir>/www/front_src/src/'],
  transform: {
    '^.+\\.[jt]sx?$': 'babel-jest',
  },
  transformIgnorePatterns: ['/node_modules/(?!@centreon/ui).+\\.jsx?$'],
  moduleNameMapper: {
    '\\.(s?css|png|svg)$': 'identity-obj-proxy',
  },
  testPathIgnorePatterns: ['/node_modules/'],
};
