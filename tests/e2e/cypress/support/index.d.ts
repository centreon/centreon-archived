/// <reference types="cypress" />

declare namespace Cypress {
  interface Chainable {
    iframe(): Chainable;
  }
}
