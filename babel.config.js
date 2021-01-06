const isServing = process.env.WEBPACK_ENV === 'serve';

const plugins = isServing ? ['react-refresh/babel'] : [];

module.exports = {
  extends: '@centreon/frontend-core/babel/typescript',
  plugins,
};
