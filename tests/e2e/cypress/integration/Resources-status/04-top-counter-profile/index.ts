import { When, Then } from 'cypress-cucumber-preprocessor/steps';

When('I click to edit profile link', () => {
  cy.get('[aria-label="User"]').click();
  cy.get('[aria-label="Edit profile"]').click();
});

Then('I see my profile edit form', () => {
  cy.location('search').should('equal', '?p=50104&o=c');

  cy.get('iframe').iframe().should('contain', 'General Information');
});

When('I click to logout link', () => {
  cy.go('back');
  cy.get('[aria-label="User"]').click();
  cy.get('[aria-label="Logout"]').click();
});

Then('I see the login page', () => {
  cy.get('input[name="password"]').should('exist');
  cy.get('input[name="useralias"]').should('exist');
  cy.get('input[name="submitLogin"]').should('contain', 'Connect');
});
