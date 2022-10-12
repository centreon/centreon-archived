import {
  applyConfigurationViaClapi,
  executeActionViaClapi,
} from '../../commons';

const initializeConfigACLAndGetLoginPage = (): Cypress.Chainable => {
  return cy
    .executeCommandsViaClapi(
      'resources/clapi/config-ACL/autologin-configuration-acl-user.json',
    )
    .then(applyConfigurationViaClapi)
    .then(() => cy.visit(`${Cypress.config().baseUrl}`))
    .then(() => cy.fixture('users/admin.json'));
};

const removeContact = (): Cypress.Chainable => {
  return cy.setUserTokenApiV1().then(() => {
    executeActionViaClapi({
      action: 'DEL',
      object: 'CONTACT',
      values: 'user1',
    });
  });
};

export { removeContact, initializeConfigACLAndGetLoginPage };
