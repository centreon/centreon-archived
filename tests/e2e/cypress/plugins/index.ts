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

  on('task', {
    checkConfigurationExport: async (env: string): Promise<boolean> => {
      const pd = await sh(`docker exec -i ${env} ls /etc/`);

      console.log(pd.stdout);

      const { stdout } = await sh(
        `docker exec -i ${env} date -r /etc/centreon-broker/central-broker.json`,
      );

      console.log(stdout);
      console.log(new Date());

      const twoMinutes = 5000;
      return new Date().getTime() - new Date(stdout).getTime() < twoMinutes;
    },
  });
};
