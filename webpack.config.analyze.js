const { merge } = require('webpack-merge');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');

const getBaseConfiguration = require('./webpack.config');

module.exports = merge(getBaseConfiguration(), {
  plugins: [new BundleAnalyzerPlugin()],
});
