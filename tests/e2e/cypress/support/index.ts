import 'cypress-wait-until';

import { setUserTokenApiV1 } from './centreonData';

before(() => {
  return cy
    .exec(`npx wait-on ${Cypress.config().baseUrl}`)
    .then(setUserTokenApiV1);
});
