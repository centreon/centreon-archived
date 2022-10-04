import { When, Then, Given } from 'cypress-cucumber-preprocessor/steps';

import { logout, loginAsAdminViaApiV2 } from '../../../commons';
import { insertContactFixture, removeContact } from '../common';

before(() => {
  insertContactFixture();
  cy.intercept({
    method: 'GET',
    url: '/centreon/api/internal.php?object=centreon_topology&action=navigationList',
  }).as('getNavigationList');
  cy.intercept({
    method: 'GET',
    url: '/centreon/include/common/userTimezone.php',
  }).as('getTimeZone');
});

Given(
  'an authenticated user and autologin configuration menus can be accessed',
  () => {
    cy.fixture('users/admin.json')
      .then((userAdmin) =>
        cy.loginContactWithAdminCredentials({
          password: userAdmin.password,
          username: userAdmin.login,
        }),
      )
      .url()
      .should('include', '/monitoring/resources')
      .wait('@getNavigationList')
      .navigateTo({
        page: 'Centreon UI',
        rootItemNumber: 4,
        subMenu: 'Parameters',
      })
      .wait('@getTimeZone')
      .getIframeBody();
  },
);
Given('an Administrator is logged in the platform', () => {
  return cy.getFormFieldByIndex(30).contains('Enable Autologin');
});

When('the administrator activates autologin on the platform', () => {
  return cy
    .getFormFieldByIndex(30)
    .find('[type="checkbox"]')
    .check({ force: true })
    .should('be.checked')
    .getIframeBody()
    .find('input[name="submitC"]')
    .click({ force: true });
});

Then(
  'any user of the plateform should be able to generate an autologin link',
  () => {
    return (
      cy
        .get('header')
        .get('svg[aria-label="Profile"]')
        .click()
        .get('div[role="tooltip"]')
        .contains('Edit profile')
        .click()
        .visit('/centreon/main.php?p=50104&o=c')
        .wait('@getNavigationList')
        .wait('@getTimeZone')
        // .reload()
        .getIframeBody()
        .find('form')
        // .find('#tab1')
        .find('td.FormRowField')
        .eq(16)
        .then(($slected) => {
          cy.log('test', $slected);
        })
        .find('td.FormRowValue')
        .find('input')
        .eq(1)

        // .getFormFieldByIndex(16)
        // .find('td.FormRowValue')
        // .find('input[name="contact_gen_passwd"]')
        .then(($selected) => {
          cy.log('find button 1', $selected);
        })
        .find('#aKey')
        .then(($selected) => {
          cy.log('find button 2', $selected);
        })
        .click()
        .find('td')
        .eq(0)
        .then(($value) => {
          cy.log('value', $value);
        })
        .should('not.be.empty')
    );
  },
);

// Then('I am redirected to the default page', () => {
//   cy.url().should('include', '/monitoring/resources');
//   cy.wait('@userTopCounterEndpoint');
//   logout().then(() => cy.reload());
// });

// Given('I am logged in', () => {
//   loginAsAdminViaApiV2();
//   cy.visit(`${Cypress.config().baseUrl}/centreon/monitoring/resources`);
//   cy.url().should('include', '/monitoring/resources');
// });

// When('I click on the logout action', () => {
//   cy.contains('Rows per page');
//   cy.getByLabel({ label: 'Profile' }).click();
//   cy.contains('Logout').click();
// });

// Then('I am logged out and redirected to the login page', () => {
//   cy.url().should('include', '/login');
//   cy.getByLabel({ label: 'Alias', tag: 'input' }).should('exist');
// });

after(removeContact);
