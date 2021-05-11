import {
  Before,
  After,
  Given,
  When,
  Then,
} from 'cypress-cucumber-preprocessor/steps';

import {
  inputSearch,
  btnToogleCriterias,
  searchValue,
  containerStateFilter,
} from '../common';
import {
  setUserFilter,
  deleteUserFilter,
  setUserTokenApiV2,
} from '../../../support/centreonData';

interface Status {
  severity_code: number;
  name: string;
}

interface Resource {
  type: 'host' | 'service';
  name: string;
  status: Status;
  acknowledged: boolean;
  in_downtime: boolean;
}

const resourcesMatching = (): Cypress.Chainable => {
  return cy.get<Array<Resource>>('@resources').then((resources) => {
    resources.forEach(({ name }) => {
      cy.contains(name).should('exist');
      cy.contains('CRITICAL');
    });
  });
};

Before(() => {
  setUserTokenApiV2();

  cy.fixture('resources/filters.json').then((filters) =>
    setUserFilter(filters),
  );
});

// Scenario: I first access to the page
Then('the unhandled problems are displayed', () => {
  cy.get<Array<Resource>>('@resources').then((resources) => {
    resources.forEach(({ name }) => {
      cy.contains(name).should('exist');
      cy.contains('CRITICAL').should('exist');
    });
  });
});

// Scenario: I can filter Resources
When('I put in some criterias', () => {
  cy.get(btnToogleCriterias).click();
  cy.get(inputSearch).type(searchValue);
  cy.contains('Search').should('exist').click();

  cy.get('[aria-label="Resource"]')
    .click()
    .then(() =>
      cy
        .contains(/^Service$/)
        .should('exist')
        .click(),
    );
});
Then(
  'only the Resources matching the selected criterias are displayed in the result',
  () => resourcesMatching,
);

// Scenario: I can select filters
Given('a saved custom filter', () => {
  cy.get(containerStateFilter)
    .click()
    .then(() => cy.contains('E2E').should('exist'));
});
When('I select the custom filter', () => cy.contains('E2E').click());

// REFACTO to a step reusing
Then(
  'only Resources matching the selected filter are displayed in the result',
  () => resourcesMatching(),
);

After(() => deleteUserFilter());
