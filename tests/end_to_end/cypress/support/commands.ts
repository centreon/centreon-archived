import 'cypress-localstorage-commands';

Cypress.Commands.add('dockerStart', () => {
  return cy
    .exec(`npx wait-on ${Cypress.env('DOCKER_URL')}`)
    .then(() =>
      cy.log(`Docker Centreon started on : ${Cypress.env('DOCKER_URL')}`),
    );
});

Cypress.Commands.add('visitCentreon', (url = '') => {
  cy.visit(`${Cypress.env('DOCKER_URL')}${url}`, {
    failOnStatusCode: false,
  });
});

Cypress.Commands.add('loginForm', () => {
  cy.visitCentreon();

  cy.fixture('users/admin.json')
    .as('user')
    .then((user) => {
      cy.get('input[name="useralias"]').type(user.login);
      cy.get('input[name="password"]').type(user.password);
    });

  return cy.get('form').submit();
});

Cypress.Commands.add('logout', () => {
  cy.get('div[class^="wrap-right-user"] span[class^="iconmoon"]')
    .should('be.visible')
    .click();
  cy.get('div[class^="button-wrap"] button[class*="logout"]')
    .should('be.visible')
    .click();

  return cy.get('input[name="useralias"]').should('be.visible');
});
