/* eslint-disable cypress/no-unnecessary-waiting */

import { submitResultsViaClapi } from './centreonData';

const stepWaitingTime = 500;
const pollingCheckTimeout = 100000;
const maxSteps = pollingCheckTimeout / stepWaitingTime;

let servicesFoundStepCount = 0;

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

    servicesFoundStepCount += 1;

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

    const configurationExported = now - new Date(stdout).getTime() < 1000;

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

      return cy.wrap(null).then(() => checkThatConfigurationIsExported());
    }

    throw new Error(`No configuration export after ${pollingCheckTimeout}ms`);
  });
};

export {
  checkThatFixtureServicesExistInDatabase,
  checkThatConfigurationIsExported,
};
