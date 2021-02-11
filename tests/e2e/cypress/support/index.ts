import './commands';
import 'cypress-localstorage-commands';

before(() => {
  cy.log('-----------------Start-----------------');
  cy.clearLocalStorage();
  cy.clearCookies();
  cy.exec(`npx wait-on ${Cypress.config().baseUrl}`).then(() => cy.loginForm());
});

beforeEach(() => {
  Cypress.Cookies.preserveOnce('PHPSESSID');
});

after(() => {
  cy.log('-----------------End-----------------');
});
