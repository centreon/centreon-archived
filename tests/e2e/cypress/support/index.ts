import './commands';
import 'cypress-localstorage-commands';

before(() => {
  cy.log(`-----------------Start of Scenario-----------------`);
  cy.clearLocalStorage();
  cy.clearCookies();
  cy.dockerStart().then(() => cy.loginForm());
});

beforeEach(() => {
  Cypress.Cookies.preserveOnce('PHPSESSID');
});

after(() => {
  cy.logout();
  cy.log('"-----------------End of Scenario-----------------"');
});
