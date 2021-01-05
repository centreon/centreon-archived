const isDevelopment = process.env.NODE_ENV !== 'production';

const plugins = isDevelopment ? ['react-refresh/babel'] : [];

module.exports = {
  extends: '@centreon/frontend-core/babel/typescript',
  plugins,
};
