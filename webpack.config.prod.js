const { merge } = require('webpack-merge');

const baseConfig = require('./webpack.config');

module.exports = merge(baseConfig, {
  optimization: {
    runtimeChunk: true,
  },
  performance: {
    assetFilter: (assetFilename) => assetFilename.endsWith('.js'),
    hints: 'error',
    maxAssetSize: 1250000,
    maxEntrypointSize: 1500000,
  },
});
