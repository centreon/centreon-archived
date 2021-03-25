import { Given, When, Then } from 'cypress-cucumber-preprocessor/steps';

import {
  inputSearch,
  btnToogleCriterias,
  searchValue,
  containerStateFilter,
} from '../common';

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

const fixtureClapiPath = 'cypress/fixtures/clapi';

const resourcesMatching = (): Cypress.Chainable => {
  return cy.get<Array<Resource>>('@resources').then((resources) => {
    resources.forEach(({ name }) => {
      cy.contains(name).should('exist');
      cy.contains('CRITICAL');
    });
  });
};

// Background
Given('There are available resources', () => {
  cy.readFile(`${fixtureClapiPath}/resources.txt`).then((data) => {
    const linesResources = data.split('\n').filter((d) => d.includes('ADD'));

    const resources = linesResources.map((line: string) => {
      const [name, description] = line
        .split(';')
        .filter((_, index: number) => index === 2 || index === 3);
      return { name, description };
    });
    cy.wrap(resources).as('resources');
  });
});

// Scenario: I first access to the page
Then('the unhandled problems are displayed', () => {
  cy.get<Array<Resource>>('@resources').then((resources) => {
    resources.forEach(({ name }) => {
      cy.contains(name, { timeout: 10000 }).should('exist');
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
  'only Resources matching I selected criterias should be displayed in the result',
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
  'only Resources matching I selected filter should be displayed in the result',
  () => resourcesMatching(),
);
