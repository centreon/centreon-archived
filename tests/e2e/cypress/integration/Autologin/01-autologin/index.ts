import { When, Then, Given } from 'cypress-cucumber-preprocessor/steps';

import { removeContact, initializeConfigACLAndGetLoginPage } from '../common';

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
});

Given('an administrator is logged in the platform', () => {
  cy.loginByTypeOfUser({ jsonName: 'admin', preserveToken: true })
    .wait('@getNavigationList')
    .navigateTo({
      page: 'Centreon UI',
      rootItemNumber: 4,
      subMenu: 'Parameters',
    })
    .wait('@getTimeZone')
    .getIframeBody();
});

When('the administrator activates autologin on the platform', () => {
  cy.getIframeBody()
    .find('#Form #enableAutoLogin')
    .check({ force: true })
    .should('be.checked')
    .getIframeBody()
    .find('#submitGeneralOptionsForm')
    .click({ force: true })
    .reload();
});

Then(
  'any user of the plateform should be able to generate an autologin link',
  () => {
    cy.isInProfileMenu('Edit profile')
      .click()
      .visit('/centreon/main.php?p=50104&o=c')
      .wait('@getTimeZone')
      .getIframeBody()
      .find('form #tab1')
      .within(() => {
        cy.get('#generateAutologinKeyButton').should('be.visible');
        cy.get('#aKey').invoke('val').should('not.be.undefined');
      })
      .navigateTo({
        page: 'Contacts / Users',
        rootItemNumber: 3,
        subMenu: 'Users',
      })
      .reload()
      .wait('@getTimeZone')
      .getIframeBody()
      .find('form')
      .contains('td', 'admin')
      .visit('centreon/main.php?p=60301&o=c&contact_id=1')
      .wait('@getTimeZone')
      .getIframeBody()
      .find('form')
      .within(() => {
        cy.contains('Centreon Authentication').click();
        cy.get('#tab2 #generateAutologinKeyButton').should('be.exist');
        cy.get('#aKey').should('be.exist');
      });
  },
);

Given(
  'an authenticated user and autologin configuration menus can be accessed',
  () => {
    cy.logout()
      .reload()
      .loginByTypeOfUser({ jsonName: 'user', preserveToken: true })
      .wait('@getNavigationList')
      .isInProfileMenu('Edit profile')
      .visit('/centreon/main.php?p=50104&o=c')
      .wait('@getTimeZone')
      .getIframeBody()
      .find('form #tab1')
      .within(() => {
        cy.get('#generateAutologinKeyButton').should('be.visible');
        cy.get('#aKey').should('be.visible');
      });
  },
);

When('a user generates his autologin key', () => {
  cy.getIframeBody()
    .find('form #tab1 table tbody tr')
    .within(() => {
      cy.get('#generateAutologinKeyButton').click();
      cy.get('#aKey').invoke('val').should('not.be.undefined');
    });
});

Then('the key is properly generated and displayed', () => {
  cy.getIframeBody()
    .find('form #tab1 table tbody tr')
    .within(() => {
      cy.get('#generateAutologinKeyButton')
        .invoke('val')
        .should('not.be.undefined');
    })
    .getIframeBody()
    .find('form input[name="submitC"]')
    .eq(0)
    .click()
    .reload();
});

Given('a user with autologin key generated', () => {
  cy.isInProfileMenu('Copy autologin link').should('be.exist');
});

When('a user generates an autologin link', () => {
  cy.navigateTo({
    page: 'Templates',
    rootItemNumber: 2,
    subMenu: 'Hosts',
  })
    .wait('@getTimeZone')
    .getIframeBody()
    .find('form')
    .should('be.exist');
  cy.getIframeBody()
    .find('form')
    .isInProfileMenu('Copy autologin link')
    .get('#autologin-input')
    .invoke('text')
    .should('not.be.undefined');
});

Then('the autologin link is copied in the clipboard', () => {
  cy.isInProfileMenu('Copy autologin link')
    .get('#autologin-input')
    .should('not.be.undefined');
});

Given(
  'a platform with autologin enabled and a user with both autologin key and link generated',
  () => {
    cy.isInProfileMenu('Copy autologin link')
      .get('#autologin-input')
      .invoke('text')
      .as('link')
      .should('not.be.undefined')
      .logout()
      .reload()
      .url()
      .should('include', '/centreon/login');
  },
);

When('the user opens the autologin link in a browser', () => {
  cy.get<string>('@link').then((text) => {
    const urlAsLink = text;
    cy.visit(urlAsLink);
  });
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
