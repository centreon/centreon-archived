const { merge } = require('webpack-merge');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');

const baseConfig = require('./webpack.config');

module.exports = merge(baseConfig, {
  performance: {
    assetFilter: (assetFilename) => assetFilename.endsWith('.js'),
    maxAssetSize: 1250000,
    maxEntrypointSize: 1500000,
    hints: 'error',
  },
  plugins: [new BundleAnalyzerPlugin()],
});
