/* eslint-disable import/no-mutable-exports */
import { executeActionViaClapi, insertFixture } from '../commons';

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

export { executeActionViaClapi, initializeResourceData };
