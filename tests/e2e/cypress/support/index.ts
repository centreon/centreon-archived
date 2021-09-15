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
import './commands';

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

    cy.contains('Connect').then((c) => {
      const element = c?.[0] as HTMLInputElement | undefined;

      const isNotInLoginPage =
        element === undefined || element?.getAttribute('value') !== 'Connect';

      if (isNotInLoginPage) {
        return;
      }

      cy.fixture('users/admin.json').then((userAdmin) => {
        cy.get('input[placeholder="Login"]').type(userAdmin.login);
        cy.get('input[placeholder="Password"]').type(userAdmin.password);
      });

      cy.get('form').submit();
    });
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
