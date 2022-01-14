import { When, Then, Given } from 'cypress-cucumber-preprocessor/steps';

import { logout, loginAsAdminViaApiV2 } from '../../../commons';
import { insertContactFixture, removeContact } from '../common';

before(() => {
  insertContactFixture();
});

When('I enter my credentials on the login page', () => {
  cy.get('input[aria-label="Alias"]').type('user1');
  cy.get('input[aria-label="Password"]').type('user1');
  cy.get('button[aria-label="Connect"]').click();
});

Then('I am redirected to the default page', () => {
  cy.url().should('include', '/monitoring/resources');
  logout().then(() => cy.reload());
});

Given('I am logged in', () => {
  loginAsAdminViaApiV2();
  cy.visit(`${Cypress.config().baseUrl}/centreon/monitoring/resources`);
  cy.url().should('include', '/monitoring/resources');
});

When('I click on the logout action', () => {
  cy.contains('Rows per page');
  cy.get('[aria-label="Profile"]').click();
  cy.get('button').contains('Logout').click();
});

Then('I am logged out and redirected to the login page', () => {
  cy.url().should('include', '/login');
  cy.get('input[aria-label="Alias"]').should('exist');
});

after(removeContact);
