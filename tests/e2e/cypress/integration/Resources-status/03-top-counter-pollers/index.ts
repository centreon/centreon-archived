import { When, Then } from 'cypress-cucumber-preprocessor/steps';

When(
  'I click on the pollers icon and I click on the configuration button',
  () => {
    cy.contains('Unhandled problems');
    cy.get('[aria-label="Pollers"]').click();
    cy.get('button').contains('Configure pollers').click();
  },
);

Then('I see the list of pollers configuration', () => {
  cy.location('search').should('equal', '?p=60901');
});
