import { Given, When, Then } from 'cypress-cucumber-preprocessor/steps';

import {
  insertResourceFixtures,
  searchInput,
  stateFilterContainer,
  setUserFilter,
  deleteUserFilter,
} from '../common';

before(() => {
  insertResourceFixtures().then(() =>
    cy
      .fixture('resources/filters.json')
      .then((filters) => setUserFilter(filters)),
  );
});

Then('the unhandled problems filter is selected', (): void => {
  cy.contains('Unhandled problems');
});

Then('only non-ok resources are displayed', () => {
  cy.contains('service_test_dt');
  cy.contains('service_test');
  cy.contains('service_test_ok').should('not.exist');
  cy.contains('CRITICAL');
  cy.contains('OK').should('not.exist');
  cy.contains('UP').should('not.exist');
});

When('I put in some criterias', () => {
  const searchValue = `type:service s.description:(ok|dt)$`;

  cy.get(searchInput).clear().type(searchValue).type('{enter}');
});

Then(
  'only the Resources matching the selected criterias are displayed in the result',
  () => {
    cy.contains('1-2 of 2');
    cy.contains('service_test_dt');
    cy.contains('service_test_ok');
  },
);

Given('a saved custom filter', () => {
  cy.reload();
  cy.get(stateFilterContainer)
    .click()
    .then(() => cy.contains('OK services').should('exist'));
});

When('I select the custom filter', () => {
  cy.contains('OK services').click();
});

Then(
  'only Resources matching the selected filter are displayed in the result',
  () => {
    cy.contains('1-1 of 1');
    cy.contains('service_test_ok');
  },
);

after(() => deleteUserFilter());
