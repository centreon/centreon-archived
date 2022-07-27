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

const insertContactFixture = (): Cypress.Chainable => {
  return initializeContactData()
    .then(applyConfigurationViaClapi)
    .then(() => cy.visit(`${Cypress.config().baseUrl}`))
    .then(() => cy.fixture('users/admin.json'));
};

const getIframeDocument = () => {
  return cy.get('iframe').its('0.contentDocument').should('exist');
};

const getIframeBody = () => {
  return getIframeDocument()
    .its('body')
    .should('not.be.undefined')
    .then(cy.wrap);
};

const clipboardy = require('clipboardy');
module.exports = (
  on: Cypress.PluginEvents,
  config: Cypress.PluginConfigOptions,
) => {
  on('task', {
    getClipboard: () => {
      const clipboard: string = clipboardy.readSync()
      return clipboard
    },
  })
};

const enableAutologin = (): Cypress.Chainable => {
  return setUserTokenApiV1().then(() => {
    executeActionViaClapi({
      action: 'SETPARAM',
      object: 'enable_autologin',
      values: '1',
    });
  });
};

export { insertContactFixture, getIframeBody, enableAutologin, clipboardy };
