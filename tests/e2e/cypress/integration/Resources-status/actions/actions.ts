import { Given, And, When, Then } from 'cypress-cucumber-preprocessor/steps';

import { canAccessPage, validUserAccount } from '../common';

// Background
Given('a valid centreon user account', () => validUserAccount());
And('I can access this page', () => canAccessPage());
And('there are available resources', () =>
  cy.get('table').should('be.visible'),
);

// Scenario: I can acknowledge a problematic Resource
When('I select the acknowledge action on a problematic Resource', () => true);
Then('The problematic Resource is displayed as acknowledged', () => true);
