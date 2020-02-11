const commonPresets = ['@babel/preset-typescript', '@babel/preset-react'];
const configuration = {
  presets: [
    ...commonPresets,
    [
      '@babel/preset-env',
      {
        modules: false,
      },
    ],
  ],
  plugins: ['@babel/proposal-class-properties'],
};

module.exports = {
  env: {
    production: configuration,
    development: configuration,
    test: {
      ...configuration,
      presets: [
        ...commonPresets,
        [
          '@babel/preset-env',
          {
            targets: {
              node: 'current',
            },
          },
        ],
      ],
    },
  },
};
