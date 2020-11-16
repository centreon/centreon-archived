import { Given, When, And, Then } from "cypress-cucumber-preprocessor/steps";

Given('I am on the login page', () => {
  cy.visit('http://localhost:3400')
})

When('I fill in inputs credentials files user data', () => {
  cy.fixture('users/admin.json')
    .as('user')
    .then((user) => {
      cy.get('input[name=useralias]').type(user.login)
      cy.get('input[name=password]').type(user.password)
    })
})

And('I press "Connect"', () => {
  cy.get('form').submit()
})

Then('I should see "Header content"', () => {
  cy.get('header[class^="header"]')
    .should('be.visible')
})