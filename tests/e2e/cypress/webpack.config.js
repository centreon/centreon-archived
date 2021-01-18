module.exports = {
  resolve: {
    extensions: ['.ts', '.js'],
    fallback: { path: false },
  },
  module: {
    rules: [
      {
        test: /\\.ts?$/,
        exclude: /node_modules/,
        use: 'ts-loader',
      },
      {
        test: /\.feature$/,
        use: 'cypress-cucumber-preprocessor/loader',
      },
    ],
  },
};
