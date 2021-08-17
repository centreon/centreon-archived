/* eslint-disable global-require */
/* eslint-disable @typescript-eslint/no-var-requires */

const webpackPreprocessor = require('@cypress/webpack-preprocessor');

module.exports = (on) => {
  const options = {
    webpackOptions: require('../webpack.config'),
  };
  on('file:preprocessor', webpackPreprocessor(options));

  on('task', {
    checkServicesInDatabase: async (env: string): Promise<string> => {
      const sh = require('shell-exec');

      const req = `SELECT COUNT(s.service_id) as count_services from services as s WHERE s.description LIKE '%service_test%' AND s.output LIKE '%submit_status_2%' AND s.enabled=1;`;
      const cmd = `docker exec -i ${env} mysql -ucentreon -pcentreon centreon_storage <<< "${req}"`;

      const { stdout } = await sh(cmd);
      return stdout || '';
    },
  });

  on('task', {
    checkConfigurationExport: async (env: string): Promise<boolean> => {
      const sh = require('shell-exec');

      const { stdout } = await sh(
        `docker exec -i ${env} date -r /etc/centreon-engine/services.cfg`,
      );

      const twoMinutes = 10000;
      return new Date().getTime() - new Date(stdout).getTime() < twoMinutes;
    },
  });
};
