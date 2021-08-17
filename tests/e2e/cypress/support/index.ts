import sh from 'shell-exec';

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

before(() => {
  setUserTokenApiV1();
  setUserTokenApiV2();

  initializeResourceData()
    .then(() => applyConfigurationViaClapi())
    .then(() => checkThatConfigurationIsExported())
    .then(() => submitResultsViaClapi())
    .then(() => checkThatFixtureServicesExistInDatabase());

  cy.exec(`npx wait-on ${Cypress.config().baseUrl}`).then(() => {
    cy.visit(`${Cypress.config().baseUrl}`);

    cy.fixture('users/admin.json').then((userAdmin) => {
      cy.get('input[placeholder="Login"]').type(userAdmin.login);
      cy.get('input[placeholder="Password"]').type(userAdmin.password);
    });

    cy.get('form').submit();
  });

  Cypress.Cookies.defaults({
    preserve: 'PHPSESSID',
  });
});

after(() => {
  setUserTokenApiV1().then(() => {
    removeResourceData().then(() => applyConfigurationViaClapi());
  });
});
