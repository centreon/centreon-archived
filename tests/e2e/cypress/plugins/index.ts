/* eslint-disable global-require */
/* eslint-disable @typescript-eslint/no-var-requires */

const webpackPreprocessor = require('@cypress/webpack-preprocessor');
const sh = require('shell-exec');

module.exports = (on) => {
  const options = {
    webpackOptions: require('../webpack.config'),
  };
  on('file:preprocessor', webpackPreprocessor(options));

  on('task', {
    checkServicesInDatabase: async (env: string): Promise<string> => {
      const req = `SELECT COUNT(s.service_id) as count_services from services as s WHERE s.description LIKE '%service_test%' AND s.output LIKE '%submit_status_2%' AND s.enabled=1;`;
      const cmd = `docker exec -i ${env} mysql -ucentreon -pcentreon centreon_storage <<< "${req}"`;

      const { stdout } = await sh(cmd);
      return stdout || '';
    },
  });
};
