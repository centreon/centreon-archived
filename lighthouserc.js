const baseUrl = 'http://localhost:4000/';

module.exports = {
  baseUrl,
  ci: {
    assert: {
      preset: 'lighthouse:no-pwa',
    },
    collect: {
      isSinglePageApplication: true,
      numberOfRuns: 1,
      puppeteerScript: './puppeteer-script.js',
      settings: {
        configPath:
          './node_modules/lighthouse/lighthouse-core/config/lr-desktop-config.js',
        onlyCategories: [
          'performance',
          'accessibility',
          'best-practices',
          'seo',
        ],
      },
      url: `${baseUrl}centreon/monitoring/resources`,
    },
    upload: {
      reportFilenamePattern: '.lighthouseci/lighthouseci-index.%%EXTENSION%%',
      target: 'filesystem',
    },
  },
};
