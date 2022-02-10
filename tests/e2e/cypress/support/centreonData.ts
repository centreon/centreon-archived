/* eslint-disable import/no-mutable-exports */
import { apiActionV1, executeActionViaClapi, insertFixture } from '../commons';
import { refreshButton } from '../integration/Resources-status/common';

const refreshListing = (): Cypress.Chainable => {
  return cy.get(refreshButton).click();
};

const setUserTokenApiV1 = (): Cypress.Chainable => {
  return cy.fixture('users/admin.json').then((userAdmin) => {
    return cy
      .request({
        body: {
          password: userAdmin.password,
          username: userAdmin.login,
        },
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        method: 'POST',
        url: `${apiActionV1}?action=authenticate`,
      })
      .then(({ body }) =>
        window.localStorage.setItem('userTokenApiV1', body.authToken),
      );
  });
};

const initializeResourceData = (): Cypress.Chainable => {
  const files = [
    'resources/clapi/host1/01-add.json',
    'resources/clapi/service1/01-add.json',
    'resources/clapi/service1/02-set-max-check.json',
    'resources/clapi/service1/03-disable-active-check.json',
    'resources/clapi/service1/04-enable-passive-check.json',
    'resources/clapi/service2/01-add.json',
    'resources/clapi/service2/02-set-max-check.json',
    'resources/clapi/service2/03-disable-active-check.json',
    'resources/clapi/service2/04-enable-passive-check.json',
    'resources/clapi/service3/01-add.json',
    'resources/clapi/service3/02-set-max-check.json',
    'resources/clapi/service3/03-disable-active-check.json',
    'resources/clapi/service3/04-enable-passive-check.json',
  ];

  return cy.wrap(Promise.all(files.map(insertFixture)));
};

const removeResourceData = (): Cypress.Chainable => {
  return executeActionViaClapi({
    action: 'DEL',
    object: 'HOST',
    values: 'test_host',
  });
};

export {
  setUserTokenApiV1,
  executeActionViaClapi,
  initializeResourceData,
  removeResourceData,
  refreshListing,
};
