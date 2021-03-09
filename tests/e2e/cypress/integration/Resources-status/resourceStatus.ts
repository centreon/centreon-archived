import { Given, And, When, Then } from 'cypress-cucumber-preprocessor/steps';

import {
  containerStateFilter,
  inputSearch,
  searchValue,
  selectFilterOnInput,
  labelClearFilter,
  pageAuthorizedToUser,
  toggleCriteras,
  isServiceDisplayOnTable,
  isInputContainServiceName,
} from './resourceCommon';

Given('a valid centreon user account', () =>
  cy.getCookie('PHPSESSID').should('exist'),
);
And('there is a page with a list of resources under monitoring', () =>
  cy.get('table').should('be.visible'),
);
And('the user can access this page', () => pageAuthorizedToUser());
And('there is a filters menu on this page', () =>
  cy.get('button').contains('Search').should('be.visible'),
);

// Scenario: User first access to the page
When('the user accesses the page for the first time', () =>
  pageAuthorizedToUser(),
);
Then('a default filter is applied', () =>
  selectFilterOnInput('unhandled_problems'),
);

// Scenario: User can choose from predefined filters
When('the user clicks on the predefined filters selection', () =>
  cy.get(containerStateFilter).click(),
);
Then('the predefined filters should be listed', () => {
  cy.get('ul')
    .children('li')
    .should('contain', 'Resource problems')
    .and('contain', 'All')
    .and('contain', 'Unhandled problems');
});

// Scenario: User resets applied filters
Given('filters already applied', () => cy.get('[data-value="all"]').click());
When('user clicks on Clear button', () => {
  toggleCriteras();
  cy.get('button').contains(labelClearFilter).should('be.visible').click();
});
Then('all selected filters should be reset to their default value', () => {
  cy.get(containerStateFilter).should('be.visible').and('contain', 'All');
});
And('search filter should be emptied', () =>
  cy.get(inputSearch).should('be.empty'),
);

// Scenario: User applies filter(s)
Given('the user has selected filters', () =>
  cy.get(containerStateFilter).contains('All'),
);
And('the user has input a search pattern', () =>
  cy.get(inputSearch).type(searchValue),
);
When('the user clicks on the SEARCH button', () => {
  cy.intercept('GET', /\/api\/.+\/monitoring\/resources?/).as('searchForTerms');
  cy.saveLocalStorage();
  cy.get('button').contains('Search').click();
});

Then(
  'only resources matching the user selected filters should be shown in the result',
  () => isServiceDisplayOnTable(),
);

// Scenario: Selected filters are retained when leaving the page
Given('a set of filters applied to the resources list', () =>
  isInputContainServiceName(),
);
When('the user leaves the page', () => {
  cy.visitCentreon('/centreon/main.php?p=50104&o=c'); // User account page
  cy.contains('My Account');
});
Then('the set of filters should be retained on his next visit', () => {
  cy.restoreLocalStorage();
  cy.visitCentreon('/centreon/monitoring/resources');

  isInputContainServiceName();
  isServiceDisplayOnTable();
});
