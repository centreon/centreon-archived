import { When, Then, Given } from 'cypress-cucumber-preprocessor/steps';

import {
  removeContact,
  initializeConfigACLAndGetLoginPage,
  checkDefaultsValueForm,
} from '../common';

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
    url: '/centreon/main.php?p=50104',
  }).as('getIframeReload');
  cy.intercept({
    method: 'GET',
    url: '/centreon/include/common/userTimezone.php',
  }).as('getTimeZone');
});

Given('an administrator deploying a new Centreon platform', () =>
  cy
    .loginByTypeOfUser({ jsonName: 'admin', preserveToken: true })
    .wait('@getNavigationList'),
);

When('the administrator first open authentication configuration menu', () => {
  cy.navigateTo({
    page: 'Authentication',
    rootItemNumber: 4,
  })
    .get('div[role="tablist"] button')
    .eq(0)
    .contains('Password security policy');
});

Then(
  'a default password policy and default excluded users must be preset',
  () => {
    checkDefaultsValueForm.forEach(({ selector, value, custom }) => {
      cy.get(selector).should('exist').and('have.value', value);
      if (custom) {
        custom();
      }
    });
    cy.logout().reload();
  },
);

Given(
  'an administrator configuring a Centreon platform and an existing user account',
  () => {
    cy.loginByTypeOfUser({ jsonName: 'user', preserveToken: false })
      .wait('@getNavigationList')
      .isInProfileMenu('Edit profile')
      .logout()
      .reload();
  },
);

When(
  'the administrator sets a valid password length and a sets all the letter cases',
  () => {
    cy.loginByTypeOfUser({ jsonName: 'admin', preserveToken: false })
      .wait('@getNavigationList')
      .navigateTo({
        page: 'Authentication',
        rootItemNumber: 4,
      })
      .get('div[role="tablist"] button')
      .eq(0)
      .contains('Password security policy');
    cy.get('#Minimumpasswordlength').should('exist').and('have.value', '12');
    cy.get('#Passwordmustcontainlowercase').should(
      'have.class',
      'MuiButton-containedPrimary',
    );
    cy.get('#Passwordmustcontainuppercase').should(
      'have.class',
      'MuiButton-containedPrimary',
    );
    cy.get('#Passwordmustcontainnumbers').should(
      'have.class',
      'MuiButton-containedPrimary',
    );
    cy.get('#Passwordmustcontainspecialcharacters').should(
      'have.class',
      'MuiButton-containedPrimary',
    );
    cy.get('#Save').click({ force: true });
    cy.logout().reload();
  },
);

Then(
  'the existing user can not define a password that does not match the password case policy defined by the administrator and is notified about it',
  () => {
    cy.loginByTypeOfUser({ jsonName: 'user', preserveToken: false })
      .wait('@getNavigationList')
      .isInProfileMenu('Edit profile')
      .should('be.visible')
      .visit('/centreon/main.php?p=50104&o=c')
      .wait('@getTimeZone')
      .getIframeBody()
      .find('form')
      .within(() => {
        cy.get('#passwd1').should('be.visible').type('azerty');
        cy.get('#passwd2').should('be.visible').type('azerty');
      })
      .find('#validForm input[name="submitC"]')
      .click();
    cy.wait(3000);
    cy.getIframeBody()
      .find('#Form')
      .find('#tab1 #passwd1')
      .parent()
      .contains(
        "Your password must be 12 characters long and must contain : uppercase characters, lowercase characters, numbers, special characters among '@$!%*?&'.",
      );
  },
);

after(() => {
  cy.removeACL();
  removeContact();
});
