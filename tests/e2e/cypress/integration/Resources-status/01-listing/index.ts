import {
  Before,
  After,
  Given,
  When,
  Then,
} from 'cypress-cucumber-preprocessor/steps';

import { searchInput, searchValue, stateFilterContainer } from '../common';
import {
  setUserFilter,
  deleteUserFilter,
  setUserTokenApiV2,
  fixtureResourcesShouldBeDisplayed,
} from '../../../support/centreonData';

Before(() => {
  setUserTokenApiV2();

  cy.fixture('resources/filters.json').then((filters) =>
    setUserFilter(filters),
  );
});

When('I filter on unhandled problems', () => cy.contains('Unhandled problems'));
Then('Only non-ok resources are displayed', () =>
  fixtureResourcesShouldBeDisplayed(),
);

When('I put in some criterias', () => {
  cy.get(searchInput).type(searchValue).type('{enter}');
});
Then(
  'only the Resources matching the selected criterias are displayed in the result',
  () => fixtureResourcesShouldBeDisplayed(),
);

Given('a saved custom filter', () => {
  cy.reload();
  cy.get(stateFilterContainer)
    .click()
    .then(() => cy.contains('E2E').should('exist'));
});
When('I select the custom filter', () => cy.contains('E2E').click());

Then(
  'only Resources matching the selected filter are displayed in the result',
  () => fixtureResourcesShouldBeDisplayed(),
);

After(() => deleteUserFilter());
