/* eslint-disable global-require */
/* eslint-disable @typescript-eslint/no-var-requires */

const webpackPreprocessor = require('@cypress/webpack-preprocessor');

module.exports = (on) => {
  const options = {
    webpackOptions: require('../webpack.config'),
  };
  on('file:preprocessor', webpackPreprocessor(options));

  on('task', {
    checkServicesInDatabase: async (env: string) => {
      const sh = require('shell-exec');

      const req = `SELECT COUNT(service_id) as count_services from services WHERE services.description LIKE '%service_test%' AND services.output LIKE '%submit_status_2%';`;
      const cmd = `docker exec -i ${env} mysql -ucentreon -pcentreon centreon_storage <<< "${req}"`;

      const { stdout } = await sh(cmd);
      return stdout;
    },
  });
};
