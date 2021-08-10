import {
  initializeResourceData,
  setUserTokenApiV1,
  setUserTokenApiV2,
  submitResultApiClapi,
  removeResourceData,
  applyCfgApi,
} from './centreonData';
import { countServicesDB } from './database';

before(() => {
  setUserTokenApiV1();
  setUserTokenApiV2();

  initializeResourceData().then(() => applyCfgApi());

  cy.exec(`npx wait-on ${Cypress.config().baseUrl}`).then(() => {
    cy.visit(`${Cypress.config().baseUrl}`);

    cy.fixture('users/admin.json').then((userAdmin) => {
      cy.get('input[placeholder="Login"]').type(userAdmin.login);
      cy.get('input[placeholder="Password"]').type(userAdmin.password);
    });

    cy.get('form')
      .submit()
      .then(() => {
        submitResultApiClapi().then(() => countServicesDB());
      });
  });

  Cypress.Cookies.defaults({
    preserve: 'PHPSESSID',
  });
});

after(() => {
  setUserTokenApiV1().then(() => {
    removeResourceData().then(() => applyCfgApi());
  });
});
