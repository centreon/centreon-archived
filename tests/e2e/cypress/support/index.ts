import 'cypress-wait-until';

import { setUserTokenApiV1 } from './centreonData';

before(() => {
  return cy
    .exec(`npx wait-on ${Cypress.config().baseUrl}`)
    .then(setUserTokenApiV1);
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
