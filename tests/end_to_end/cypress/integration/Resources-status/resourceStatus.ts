import { Given, And, When, Then } from 'cypress-cucumber-preprocessor/steps';

const containerStateFilter = 'div[aria-label="State filter"]';
const serviceName = 'Ping';
const resourcesRegexApiUrl = /\/api\/.+\/monitoring\/resources?/;

Given('a valid centreon user account', () => true);
And('there is a page with a list of resources under monitoring', () => true);
And('the user can access this page', () => true);
And('there is a filters menu on this page', () => true);
And(
  `the resources contains a service named "${serviceName}" in the list`,
  () => true,
);

// Scenario: User first access to the page
When('the user accesses the page for the first time', () => {
  cy.get('nav[aria-label="Breadcrumb"]').should('be.visible');

  cy.get('ol[class="MuiBreadcrumbs-ol"] li')
    .should('have.length', 3)
    .each(($li) => $li.text() === 'Resources Status');
});
Then('a default filter is applied', () => {
  const inputFilter = 'input[aria-label="MuiSelect-nativeInput"]';

  cy.get(containerStateFilter).should('be.visible');
  cy.get(inputFilter).should('not.be.empty');
});

// Scenario: User can choose from predefined filters
When('the user clicks on the predefined filters selection', () => {
  cy.get(containerStateFilter).click();
  cy.get('li[data-value="resource_problems"]').click();

  cy.get('svg[aria-label="Show criterias filters"]')
    .click()
    .then(() => cy.get('div.MuiAccordionDetails-root').should('be.visible'));
});
Then('the predefined filters should be listed', () => {
  cy.get('div.MuiTextField-root > label')
    .contains('Status')
    .then(($containerChips) =>
      cy
        .get($containerChips)
        .next()
        .children('.MuiChip-deletable')
        .should('have.length', 2),
    );
});

// Scenario: User resets applied filters
Given('filters already applied', () => {
  cy.get('.MuiButton-label').contains('Clear all').should('be.visible');
});
When('user clicks on Clear button', () => {
  cy.get('.MuiButton-label').contains('Clear all').click();
});
Then('all selected filters should be reset to their default value', () => {
  const inputFilter = 'input[aria-label="MuiSelect-nativeInput"]';

  cy.get(containerStateFilter).should('be.visible');
  cy.get(inputFilter).should('not.be.empty');
});
And('search filter should be emptied', () => {
  cy.get('div.MuiTextField-root > label')
    .contains('Status')
    .then(($containerChips) =>
      cy
        .get($containerChips)
        .next()
        .children('.MuiChip-deletable')
        .should('have.length', 0),
    );
});

// Scenario: User applies filter(s)
Given('the user has selected filters', () => true);
And('the user has input a search pattern', () => {
  cy.get('.MuiAccordionSummary-content input.MuiInputBase-input')
    .should('have.attr', 'aria-invalid', 'false')
    .then(($inputSearch) => {
      cy.get($inputSearch).type(`s.description:${serviceName}`);
    });
});
When('the user clicks on the SEARCH button', () => {
  cy.intercept('GET', resourcesRegexApiUrl).as('searchForTerms');
  cy.get('.MuiButton-label').contains('Search').click();
});

Then(
  'only resources matching the user selected filters should be shown in the result',
  () => {
    cy.wait('@searchForTerms');
    cy.saveLocalStorage();
    cy.get('p.MuiTypography-root').contains(serviceName);
  },
);

// Scenario: Selected filters are retained when leaving the page
Given('a set of filters applied to the resources list', () => {
  return true;
});
When('the user leaves the page', () => {
  cy.visitCentreon('/centreon/main.php?p=103');
  cy.get('div[class^="loader-inner"]');
  cy.get('iframe#main-content').should('be.visible');
});
Then('the set of filters should be retained on his next visit', () => {
  cy.visitCentreon('/centreon/monitoring/resources');

  cy.restoreLocalStorage();
  cy.intercept('GET', resourcesRegexApiUrl).as('filtersSavedRequest');

  cy.wait('@filtersSavedRequest');

  cy.get(containerStateFilter).should('be.visible');

  cy.get('p.MuiTypography-root').contains(serviceName);
});
