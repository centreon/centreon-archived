import { submitResultsViaClapi } from './centreonData';

const stepWaitingTime = 500;
const timeout = 100000;
const maxSteps = timeout / stepWaitingTime;

let stepCount = 0;

const checkThatFixtureServicesExistInDatabase = (): void => {
  cy.log('Checking services in database');

  const query = `SELECT COUNT(s.service_id) as count_services from services as s WHERE s.description LIKE '%service_test%' AND s.output LIKE '%submit_status_2%' AND s.enabled=1;`;
  const command = `docker exec -i ${Cypress.env(
    'dockerName',
  )} mysql -ucentreon -pcentreon centreon_storage <<< "${query}"`;

  cy.exec(command).then(({ stdout }): Cypress.Chainable<null> | null => {
    let foundServiceCount = 0;

    if (stdout !== '') {
      foundServiceCount = parseInt(stdout.split('\n')[1], 10);
    }

    stepCount += 1;

    cy.log('Service count in database', foundServiceCount);
    cy.log('Service database check step count', stepCount);

    if (foundServiceCount > 0) {
      return null;
    }

    if (stepCount < maxSteps) {
      // eslint-disable-next-line cypress/no-unnecessary-waiting
      cy.wait(stepWaitingTime, { log: false });

      return cy
        .wrap(null)
        .then(() => submitResultsViaClapi())
        .then(() => checkThatFixtureServicesExistInDatabase());
    }

    throw new Error(`No service found in the database after ${timeout}ms`);
  });
};

let configCheckStepCount = 0;

const checkThatConfigurationIsExported = (): void => {
  const now = new Date().getTime();

  cy.log('Checking that configuration is exported');

  cy.exec(
    `docker exec -i ${Cypress.env(
      'dockerName',
    )} date -r /etc/centreon-engine/hosts.cfg`,
  ).then(({ stdout }): Cypress.Chainable<null> | null => {
    configCheckStepCount += 1;

    const exported = now - new Date(stdout).getTime() < 500;

    cy.log('Configuration exported', exported);
    cy.log('Configuration export check step count', configCheckStepCount);

    if (exported) {
      return null;
    }

    if (configCheckStepCount < maxSteps) {
      // eslint-disable-next-line cypress/no-unnecessary-waiting
      cy.wait(stepWaitingTime, { log: false });

      return cy.wrap(null).then(() => checkThatConfigurationIsExported());
    }

    throw new Error(`No configuration export after ${timeout}ms`);
  });
};

export {
  checkThatFixtureServicesExistInDatabase,
  checkThatConfigurationIsExported,
};
