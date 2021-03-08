const { merge } = require('webpack-merge');

const baseConfig = require('./webpack.config');

module.exports = merge(baseConfig, {
  performance: {
    assetFilter: (assetFilename) => assetFilename.endsWith('.js'),
    maxAssetSize: 1300000,
    maxEntrypointSize: 1500000,
    hints: 'error',
  },
});
