import {
  initDataResources,
  setUserTokenApiV1,
  setUserTokenApiV2,
  submitResultApiClapi,
  removeDataResources,
  applyCfgApi,
  resourcesMatching,
  refreshListing,
} from './centreonData';

before(() => {
  setUserTokenApiV1();
  setUserTokenApiV2();

  initDataResources().then(() => applyCfgApi());

  cy.exec(`npx wait-on ${Cypress.config().baseUrl}`).then(() => {
    cy.visit(`${Cypress.config().baseUrl}`);

    cy.fixture('users/admin.json').then((userAdmin) => {
      cy.get('input[placeholder="Login"]').type(userAdmin.login);
      cy.get('input[placeholder="Password"]').type(userAdmin.password);
    });

    cy.get('form')
      .submit()
      .then(() => {
        submitResultApiClapi().then(() => {
          refreshListing().then(() => resourcesMatching());
        });
      });
  });

  Cypress.Cookies.defaults({
    preserve: 'PHPSESSID',
  });
});

after(() => {
  setUserTokenApiV1().then(() => {
    removeDataResources();
    applyCfgApi();
  });
});
