/* eslint-disable @typescript-eslint/no-namespace */
declare namespace Cypress {
  interface Chainable {
    visitCentreon(url: string): void;
    dockerStart(): boolean;
    loginForm(): void;
    logout(): void;
  }
}

Cypress.Commands.add('visitCentreon', (url = '') => {
  cy.visit(`${Cypress.config().baseUrl}${url}`, {
    failOnStatusCode: false,
  });
});

Cypress.Commands.add('loginForm', () => {
  cy.visitCentreon('/');

  cy.fixture('users/admin.json')
    .as('user')
    .then((user) => {
      cy.get('input[name="useralias"]').type(user.login);
      cy.get('input[name="password"]').type(user.password);
    });

  return cy.get('form').submit();
});
