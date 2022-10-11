import { When, Then, Given } from 'cypress-cucumber-preprocessor/steps';

import { logout, loginAsAdminViaApiV2 } from '../../../commons';
import { insertContactFixture, removeContact } from '../common';

let link = '';

before(() => {
  insertContactFixture();
  loginAsAdminViaApiV2();
  cy.visit('/centreon/monitoring/resources');
});
beforeEach(() => {
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
    return cy
      .get('header')
      .get('svg[aria-label="Profile"]')
      .click()
      .get('div[role="tooltip"]')
      .contains('Edit profile');
  },
);
Given('an Administrator is logged in the platform', () => {
  return cy
    .navigateTo({
      page: 'Centreon UI',
      rootItemNumber: 4,
      subMenu: 'Parameters',
    })
    .wait('@getTimeZone')
    .getFormFieldByIndex(30)
    .contains('Enable Autologin');
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
    return cy
      .get('header')
      .get('svg[aria-label="Profile"]')
      .click()
      .get('div[role="tooltip"]')
      .contains('Edit profile')
      .visit('/centreon/main.php?p=50104&o=c')
      .wait('@getTimeZone')
      .wait('@getNavigationList')
      .getIframeBody()
      .find('form')
      .scrollIntoView()
      .find('#tab1 table tbody tr input[name="contact_autologin_key"]')
      .should('be.visible');
  },
);

When('a user generate his autologin key', () => {
  return cy
    .getIframeBody()
    .find('form')
    .scrollIntoView()
    .find('#tab1 table tbody tr')
    .within(() => {
      cy.get('input[name="contact_gen_akey"]').click();
      cy.log('Key generated !');
    });
});

Then('the key is properly generated and displayed', () => {
  return cy
    .getIframeBody()
    .find('form')
    .scrollIntoView()
    .find('#tab1 table tbody tr')
    .within(() => {
      cy.get('input[name="contact_autologin_key"]')
        .invoke('val')
        .should('not.be.undefined');
    });
});

Given('a User with autologin key generated', () => {
  cy.getIframeBody()
    .find('form')
    .scrollIntoView()
    .within(() => {
      cy.get('#tab1 table tbody tr input[name="contact_autologin_key"]')
        .should('not.be.undefined')
        .invoke('val')
        .then((text) => cy.log('Key autologin => ', text));
    });
});

When('a User generates an autologin link', () => {
  return cy
    .getIframeBody()
    .find('input[name="submitC"]')
    .eq(0)
    .click({ force: true })
    .reload();
});
Then('the autologin link is copied in the clipboard', () => {
  cy.get('header')
    .get('svg[aria-label="Profile"]')
    .click()
    .get('div[role="tooltip"]')
    .get('textarea#autologin-input')

    .invoke('text')
    .then((text) => {
      expect(text.trim());
      link = text;
    });
});

Given(
  'a plateform with autologin enabled and a user with autologin key generated and a user with autologin link generated',
  () => {
    cy.log(link);
  },
);

When('the user opens the autologin link in a browser', () => {
  cy.visit(link);
});

Then('the page is accessed without manual login', () => {
  cy.getIframeBody();
});

after(() => removeContact());
