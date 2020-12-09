import './commands';

before(() => {
  cy.log(`-----------------Start of Scenario-----------------`);
  cy.dockerStart().then(() => cy.loginForm());
});

beforeEach(() => {
  Cypress.Cookies.preserveOnce('PHPSESSID');
});

after(() => {
  cy.logout();
  cy.log('"-----------------End of Scenario-----------------"');
});
