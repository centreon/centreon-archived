module.exports = {
  resolve: {
    extensions: ['.ts', '.js'],
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
