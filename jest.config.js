const merge = require('lodash/merge');

module.exports = merge(require('@centreon/frontend-core/jest'), {
  roots: ['<rootDir>/www/front_src/src/'],
});
