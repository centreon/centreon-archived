import {
  initDataResources,
  setUserTokenApiV1,
  setUserTokenApiV2,
  submitResultApiClapi,
  removeDataResources,
  applyCfgApi,
} from './centreonData';

before(() => {
  setUserTokenApiV1();
  setUserTokenApiV2();

  initDataResources().then(() => {
    applyCfgApi().then(() => {
      // Necessary to wait checks on the engine
      // eslint-disable-next-line cypress/no-unnecessary-waiting
      cy.wait(5000);
      submitResultApiClapi();
    });
  });

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

after(() => setUserTokenApiV1().then(() => removeDataResources()));
