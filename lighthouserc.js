module.exports = {
  ci: {
    assert: {
      assertions: {
        // Disable PWA metrics that might fail
        'apple-touch-icon': ['off'],
        'installable-manifest': ['off'],
        'maskable-icon': ['off'],
        'service-worker': ['off'],
        'splash-screen': ['off'],
        'themed-omnibox': ['off'],
      },
      preset: 'lighthouse:recommended',
    },
    collect: {
      isSinglePageApplication: true,
      numberOfRuns: 1,
      puppeteerScript: './puppeteer-script.js',
      settings: {
        onlyCategories: [
          'performance',
          'accessibility',
          'best-practices',
          'seo',
        ],
        preset: 'desktop',
      },
      url: 'http://localhost:4000/centreon/monitoring/resources',
    },
  },
};
