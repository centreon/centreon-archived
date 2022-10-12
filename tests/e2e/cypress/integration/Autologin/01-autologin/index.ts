import { When, Then, Given } from 'cypress-cucumber-preprocessor/steps';

import { removeContact, initializeConfigACLAndGetLoginPage } from '../common';

let link = '';

before(() => {
  initializeConfigACLAndGetLoginPage();
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
  cy.intercept({
    method: 'GET',
    url: 'http://0.0.0.0:4000/centreon/api/latest/users/filters/events-view?page=1&limit=100',
  }).as('getfilterData');
});

Given('an Administrator is logged in the platform', () => {
  return cy
    .loginByTypeOfUser({ jsonName: 'admin', preserveToken: true })
    .wait('@getNavigationList')
    .wait('@getfilterData')
    .navigateTo({
      page: 'Centreon UI',
      rootItemNumber: 4,
      subMenu: 'Parameters',
    })
    .wait('@getTimeZone')
    .getIframeBody();
});

When('the administrator activates autologin on the platform', () => {
  return cy
    .getFormFieldByIndex(30)
    .find('[type="checkbox"]')
    .check({ force: true })
    .should('be.checked')
    .getIframeBody()
    .find('input[name="submitC"]')
    .click({ force: true })
    .reload();
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
      .getIframeBody()
      .find('form')
      .find('#tab1')
      .within(() => {
        cy.get('input[name="contact_gen_akey"]').should('be.visible');
        cy.get('#aKey').invoke('val').should('not.be.undefined');
      });
  },
);
Given(
  'an authenticated user and autologin configuration menus can be accessed',
  () => {
    return cy
      .logout()
      .reload()
      .loginByTypeOfUser({ jsonName: 'user', preserveToken: true })
      .wait('@getNavigationList')
      .get('header')
      .get('svg[aria-label="Profile"]')
      .click()
      .get('div[role="tooltip"]')
      .contains('Edit profile')
      .visit('/centreon/main.php?p=50104&o=c')
      .wait('@getTimeZone')
      .getIframeBody()
      .find('form')
      .find('#tab1')
      .within(() => {
        cy.get('input[name="contact_gen_akey"]').should('be.visible');
        cy.get('#aKey').should('be.visible');
      });
  },
);

When('a user generate his autologin key', () => {
  return cy
    .getIframeBody()
    .find('form')
    .find('#tab1 table tbody tr')
    .within(() => {
      cy.get('input[name="contact_gen_akey"]').click();
      cy.get('#aKey').invoke('val').should('not.be.undefined');
    });
});

Then('the key is properly generated and displayed', () => {
  return cy
    .getIframeBody()
    .find('form')
    .find('#tab1 table tbody tr')
    .within(() => {
      cy.get('input[name="contact_autologin_key"]')
        .invoke('val')
        .should('not.be.undefined');
    })
    .getIframeBody()
    .find('form')
    .find('input[name="submitC"]')
    .eq(0)
    .click()
    .reload();
});

Given('a User with autologin key generated', () => {
  cy.get('header')
    .get('svg[aria-label="Profile"]')
    .click()
    .get('div[role="tooltip"]')
    .get('textarea#autologin-input')
    .should('be.exist');
});

When('a User generates an autologin link', () => {
  return cy
    .navigateTo({
      page: 'Templates',
      rootItemNumber: 2,
      subMenu: 'Hosts',
    })
    .wait('@getTimeZone')
    .getIframeBody()
    .find('form')
    .should('be.exist')
    .get('header')
    .get('svg[aria-label="Profile"]')
    .click()
    .get('div[role="tooltip"]')
    .get('textarea#autologin-input')
    .invoke('text')
    .should('not.be.undefined');
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
    cy.get('header')
      .get('svg[aria-label="Profile"]')
      .click()
      .get('div[role="tooltip"]')
      .get('textarea#autologin-input')
      .should('not.be.undefined')
      .logout()
      .reload()
      .url()
      .should('include', '/centreon/login');
  },
);

When('the user opens the autologin link in a browser', () => {
  cy.visit(link);
});

Then('the page is accessed without manual login', () => {
  cy.url()
    .should('include', '/main.php?p=60103')
    .wait('@getTimeZone')
    .getIframeBody()
    .find('form')
    .should('be.exist');
});

after(() => {
  cy.removeACL();
  removeContact();
});
