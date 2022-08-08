/* eslint-disable cypress/no-unnecessary-waiting */

interface ActionClapi {
  action: string;
  object?: string;
  values: string;
}

const stepWaitingTime = 250;
const pollingCheckTimeout = 100000;
const maxSteps = pollingCheckTimeout / stepWaitingTime;

const apiBase = `${Cypress.config().baseUrl}/centreon/api`;
const apiActionV1 = `${apiBase}/index.php`;
const versionApi = 'latest';
const apiLoginV2 = '/centreon/authentication/providers/configurations/local';
const apiLogout = '/centreon/api/latest/authentication/logout';

const executeActionViaClapi = (
  bodyContent: ActionClapi,
  method?: string,
): Cypress.Chainable => {
  return cy.request({
    body: bodyContent,
    headers: {
      'Content-Type': 'application/json',
      'centreon-auth-token': window.localStorage.getItem('userTokenApiV1'),
    },
    method: method || 'POST',
    url: `${apiActionV1}?action=action&object=centreon_clapi`,
  });
};

let servicesFoundStepCount = 0;

const checkThatFixtureServicesExistInDatabase = (): void => {
  cy.log('Checking services in database');

  const query = `SELECT COUNT(s.service_id) as count_services from services as s WHERE s.description LIKE '%service_test%' AND s.output LIKE '%submit_status_2%' AND s.enabled=1;`;
  const command = `docker exec -i ${Cypress.env(
    'dockerName',
  )} mysql -ucentreon -pcentreon centreon_storage <<< "${query}"`;

  cy.exec(command).then(({ stdout }): Cypress.Chainable<null> | null => {
    servicesFoundStepCount += 1;

    const output = stdout || '0';
    const foundServiceCount = parseInt(output.split('\n')[1], 10);

    cy.log('Service count in database', foundServiceCount);
    cy.log('Service database check step count', servicesFoundStepCount);

    if (foundServiceCount > 0) {
      return null;
    }

    if (servicesFoundStepCount < maxSteps) {
      cy.wait(stepWaitingTime);

      return cy
        .wrap(null)
        .then(() => submitResultsViaClapi())
        .then(() => checkThatFixtureServicesExistInDatabase());
    }

    throw new Error(
      `No service found in the database after ${pollingCheckTimeout}ms`,
    );
  });
};

let configurationExportedCheckStepCount = 0;

const checkThatConfigurationIsExported = (): void => {
  const now = new Date().getTime();

  cy.log('Checking that configuration is exported');

  cy.exec(
    `docker exec -i ${Cypress.env(
      'dockerName',
    )} date -r /etc/centreon-engine/hosts.cfg`,
  ).then(({ stdout }): Cypress.Chainable<null> | null => {
    configurationExportedCheckStepCount += 1;

    const configurationExported = now - new Date(stdout).getTime() < 500;

    cy.log('Configuration exported', configurationExported);
    cy.log(
      'Configuration export check step count',
      configurationExportedCheckStepCount,
    );

    if (configurationExported) {
      return null;
    }

    if (configurationExportedCheckStepCount < maxSteps) {
      cy.wait(stepWaitingTime);

      return cy
        .wrap(null)
        .then(() => applyConfigurationViaClapi())
        .then(() => checkThatConfigurationIsExported());
    }

    throw new Error(`No configuration export after ${pollingCheckTimeout}ms`);
  });
};

const applyConfigurationViaClapi = (): Cypress.Chainable => {
  return executeActionViaClapi({
    action: 'APPLYCFG',
    values: '1',
  });
};

const updateFixturesResult = (): Cypress.Chainable => {
  return cy
    .fixture('resources/clapi/submit-results.json')
    .then(({ results }) => {
      const timestampNow = Math.floor(Date.now() / 1000) - 15;

      const submitResults = results.map((submittedResult) => {
        return { ...submittedResult, updatetime: timestampNow.toString() };
      });

      return submitResults;
    });
};

const submitResultsViaClapi = (): Cypress.Chainable => {
  return updateFixturesResult().then((submitResults) => {
    return cy.request({
      body: { results: submitResults },
      headers: {
        'Content-Type': 'application/json',
        'centreon-auth-token': window.localStorage.getItem('userTokenApiV1'),
      },
      method: 'POST',
      url: `${apiActionV1}?action=submit&object=centreon_submit_results`,
    });
  });
};

const loginAsAdminViaApiV2 = (): Cypress.Chainable => {
  return cy
    .fixture('users/admin.json')
    .then((userAdmin) => {
      return cy.request({
        body: {
          login: userAdmin.login,
          password: userAdmin.password,
        },
        method: 'POST',
        url: apiLoginV2,
      });
    })
    .then(() => {
      Cypress.Cookies.defaults({
        preserve: 'PHPSESSID',
      });
    });
};

const insertFixture = (file: string): Cypress.Chainable => {
  return cy.fixture(file).then(executeActionViaClapi);
};

const logout = () =>
  cy.request({
    body: {},
    method: 'POST',
    url: apiLogout,
  });

export {
  checkThatConfigurationIsExported,
  checkThatFixtureServicesExistInDatabase,
  executeActionViaClapi,
  submitResultsViaClapi,
  updateFixturesResult,
  apiBase,
  apiActionV1,
  applyConfigurationViaClapi,
  versionApi,
  loginAsAdminViaApiV2,
  insertFixture,
  logout,
};
