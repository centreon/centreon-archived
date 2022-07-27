import { When, Then, Given } from 'cypress-cucumber-preprocessor/steps';

import { logout, loginAsAdminViaApiV2 } from '../../../commons';
import { insertContactFixture, getIframeBody, clipboardy } from '../common';

const centreonUI = 'main.php?p=50110&o=general'
const userList = '/centreon/main.php?p=60301'
const autologinLink= ''

before(() => {
  insertContactFixture();
  cy.intercept(
    '/centreon/api/internal.php?object=centreon_topcounter&action=user',
  ).as('userTopCounterEndpoint');
  cy.intercept('/centreon/api/internal.php?').as('icon');
});

Given('An administrator is logged in the plateform', () => {
  cy.get('input[aria-label="Alias"]').should('be.visible').type('admin');
  cy.get('input[aria-label="Mot de passe"]').type('Centreon!2021');
  cy.get('button[aria-label="Se connecter"]').click();
  cy.wait('@userTopCounterEndpoint');
  cy.log('Connected as an admin');
});

When('The administrator activates autologin on the plateform', () => {
  cy.visit(`${Cypress.config().baseUrl}/centreon/main.php?p=50110&o=general`);
  
  cy.wait('@userTopCounterEndpoint');
  cy.log('I have access to centreon ui page');
  getIframeBody()
    .find('input[name="enable_autologin[yes]"]')
    .scrollIntoView()
    .check({ force: true });
   cy.log('I cliked successfully on autologin');
});

Then('Autologin is activatd on the plateform', () => {
  getIframeBody()
    .find('input[type="submit"]')
    .contains('Save')
    .scrollIntoView()
    .click();
  cy.log('Autologin is now enabled on this plateform');
});

Given('I am on user page', () => {
    cy.visit(`${Cypress.config().baseUrl}/centreon`);
	cy.get('input[aria-label="Alias"]').type('admin');
    cy.get('input[aria-label="Mot de passe"]').type('Centreon!2021');
    cy.get('button[aria-label="Se connecter"]').click();
    cy.url().should('not.include','/login');
    cy.url().should('include', '/monitoring/resources');
    cy.visit(`${Cypress.config().baseUrl}/centreon/main.php?p=60301`);
    cy.wait(10000);
    cy.visit(`${Cypress.config().baseUrl}/centreon/main.php?p=60301&o=c&contact_id=1`);
    cy.wait(2000);
    getIframeBody()
      .contains('.a','Centreon Authentication')
      .click({ force: true});
});

When('I generate autologin key', () => {
    getIframeBody()
      .find('input[name="contact_gen_akey"]')
      .should('be.visible')
      .click('center');
});

Then('The autologin key is properly generated', () => {
	getIframeBody()
      .find('input[name="contact_autologin_key"]')
      .should('be.visible')
      .invoke('val')
      .should('not.be.empty');
    getIframeBody()
      .find('input[type="submit"]')
      .should('be.visible')
      .first()
      .click();
});

When('I generate an autologin link', ()=> {
  cy.visit(`${Cypress.config().baseUrl}/centreon/`);
  cy.get('input[aria-label="Alias"]').should('be.visible').type('admin');
  cy.get('input[aria-label="Mot de passe"]').type('Centreon!2021');
  cy.get('button[aria-label="Se connecter"]').click();
  cy.url().should('include', '/monitoring/resources');
  cy.wait('@userTopCounterEndpoint');
  cy.get('[data-testid="AccountCircleIcon"]')
      .click();
  cy.get('[data-testid="FileCopyIcon"]')
      .click()
      .task('getClipboard').invoke('val',autologinLink);
});

Then('I can use it in the browser', ()=> {
  cy.visit(autologinLink);
  cy.url().contains('/centreon/monitoring/resources');
});
