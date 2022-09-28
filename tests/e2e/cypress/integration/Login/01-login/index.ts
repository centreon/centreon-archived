import { When, Then, Given } from 'cypress-cucumber-preprocessor/steps';

import { logout, loginAsAdminViaApiV2 } from '../../../commons';
import { insertContactFixture, removeContact } from '../common';

before(() => {
  insertContactFixture();
  cy.intercept(
    '/centreon/api/internal.php?object=centreon_topcounter&action=user',
  ).as('userTopCounterEndpoint');
});

When('I enter my credentials on the login page', () => {
  cy.getByLabel({ label: 'Alias', tag: 'input' }).type('user1');
  cy.getByLabel({ label: 'Password', tag: 'input' }).type('Centreon!2021User1');
  cy.getByLabel({ label: 'Connect', tag: 'button' }).click();
});

Then('I am redirected to the default page', () => {
  cy.url().should('include', '/monitoring/resources');
  cy.wait('@userTopCounterEndpoint');
  logout().then(() => cy.reload());
});

Given('I am logged in', () => {
  loginAsAdminViaApiV2();
  cy.visit(`${Cypress.config().baseUrl}/centreon/monitoring/resources`);
  cy.url().should('include', '/monitoring/resources');
});

When('I click on the logout action', () => {
  cy.contains('Rows per page');
  cy.getByLabel({ label: 'Profile' }).click();
  cy.contains('Logout').click();
});

Then('I am logged out and redirected to the login page', () => {
  cy.url().should('include', '/login');
  cy.getByLabel({ label: 'Alias', tag: 'input' }).should('exist');
});

after(removeContact);
