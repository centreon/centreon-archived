const isServing = process.env.WEBPACK_ENV === 'serve';

const plugins = isServing ? ['react-refresh/babel'] : [];

module.exports = {
  extends:
    '/centreon-frontend/packages/frontend-config/babel/typescript',
  plugins,
};
