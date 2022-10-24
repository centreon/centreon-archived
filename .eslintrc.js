module.exports = {
  extends:
    './node_modules/centreon-frontend/packages/frontend-config/eslint/react/typescript.eslintrc.js',
  rules: {
    'prettier/prettier': [
      'error',
      {
        trailingComma: 'none'
      }
    ]
  }
};
