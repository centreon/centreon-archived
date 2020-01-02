module.exports = {
  parser: '@typescript-eslint/parser',
  plugins: ["@typescript-eslint"],
  extends:  [
    '@centreon/eslint-config-centreon'
  ],
  settings: {
    'import/resolver': {
      alias: {
        map: [
          ['@centreon/ui', '@centreon/ui/src'],
        ],
        extensions: ['.js', '.jsx',]
      }
    }
  },
  rules: {
    "react/jsx-filename-extension": [2, { "extensions": [".jsx", ".tsx"] }]
  }
};