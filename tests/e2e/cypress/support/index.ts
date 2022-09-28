import 'cypress-wait-until';
import './Commands';

before(() => {
  return cy
    .exec(`npx wait-on ${Cypress.config().baseUrl}`)
    .then(cy.setUserTokenApiV1);
});

Cypress.on('uncaught:exception', (err) => {
  if (
    err.message.includes('Request failed with status code 401') ||
    err.message.includes('undefined')
  ) {
    return false;
  }

  return true;
});
