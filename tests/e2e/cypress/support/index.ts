import 'cypress-wait-until';

import {
  initializeResourceData,
  setUserTokenApiV1,
  setUserTokenApiV2,
  submitResultsViaClapi,
  removeResourceData,
  applyConfigurationViaClapi,
} from './centreonData';
import {
  checkThatConfigurationIsExported,
  checkThatFixtureServicesExistInDatabase,
} from './database';

const login = (adminUser): Cypress.Chainable => {
  cy.get('input[aria-label="Alias"]').type(adminUser.login);
  cy.get('input[aria-label="Password"]').type(adminUser.password);

  cy.get('form').submit();

  Cypress.Cookies.defaults({
    preserve: 'PHPSESSID',
  });

  return cy.wrap({});
};

before(() => {
  return cy
    .exec(`npx wait-on ${Cypress.config().baseUrl}`)
    .then(setUserTokenApiV1)
    .then(setUserTokenApiV2)
    .then(initializeResourceData)
    .then(applyConfigurationViaClapi)
    .then(checkThatConfigurationIsExported)
    .then(submitResultsViaClapi)
    .then(checkThatFixtureServicesExistInDatabase)
    .then(() => cy.visit(`${Cypress.config().baseUrl}`))
    .then(() => cy.fixture('users/admin.json'))
    .then(login);
});

after(() => {
  return setUserTokenApiV1()
    .then(removeResourceData)
    .then(applyConfigurationViaClapi)
    .then(() =>
      cy.visit(`${Cypress.config().baseUrl}/centreon/index.php?disconnect=1`),
    );
});
