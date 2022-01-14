import 'cypress-wait-until';
import { applyConfigurationViaClapi } from '../commons';

import { setUserTokenApiV1, removeResourceData, logout } from './centreonData';

before(() => {
  return cy
    .exec(`npx wait-on ${Cypress.config().baseUrl}`)
    .then(setUserTokenApiV1);
});

after(() => {
  return setUserTokenApiV1()
    .then(removeResourceData)
    .then(applyConfigurationViaClapi)
    .then(logout);
});
