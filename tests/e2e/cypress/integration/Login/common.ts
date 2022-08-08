import { setUserTokenApiV1 } from '../../support/centreonData';
import {
  applyConfigurationViaClapi,
  executeActionViaClapi,
  insertFixture,
} from '../../commons';

const initializeContactData = (): Cypress.Chainable => {
  const files = ['resources/clapi/contact1/01-add.json'];

  return cy.wrap(Promise.all(files.map(insertFixture)));
};

const insertContactFixture = () => {
  return initializeContactData()
    .then(applyConfigurationViaClapi)
    .then(() => cy.visit(`${Cypress.config().baseUrl}`))
    .then(() => cy.fixture('users/admin.json'));
};

const removeContact = (): Cypress.Chainable => {
  return setUserTokenApiV1().then(() => {
    executeActionViaClapi({
      action: 'DEL',
      object: 'CONTACT',
      values: 'user1',
    });
  });
};

export { insertContactFixture, removeContact };
