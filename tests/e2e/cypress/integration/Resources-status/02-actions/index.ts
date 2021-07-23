import { When, Then, Before } from 'cypress-cucumber-preprocessor/steps';

import {
  stateFilterContainer,
  serviceName,
  serviceNameDowntime,
  resourceMonitoringApi,
  actionBackgroundColors,
  actions,
} from '../common';
import { refreshListing } from '../../../support/centreonData';

Before(() => {
  cy.get(stateFilterContainer).click().get('[data-value="all"]').click();

  cy.intercept({
    method: 'GET',
    url: resourceMonitoringApi,
  }).as('getResources');
});

When('I select the acknowledge action on a problematic Resource', () => {
  cy.contains(serviceName)
    .parents('div[role="row"]:first')
    .find('input[type="checkbox"]:first')
    .click();

  cy.get(`[title="${actions.acknowledge}"]`)
    .children('button')
    .first()
    .should('be.enabled')
    .click();

  cy.get('textarea').should('be.visible');
  cy.get('button').contains('Acknowledge').click();
});

Then('The problematic Resource is displayed as acknowledged', () => {
  refreshListing(5000);

  cy.wait('@getResources');
  cy.contains(serviceName)
    .parents('div[role="cell"]:first')
    .should('have.css', 'background-color', actionBackgroundColors.acknowledge);
});

When('I select the downtime action on a problematic Resource', () => {
  cy.contains(serviceNameDowntime)
    .parents('div[role="row"]:first')
    .find('input[type="checkbox"]:first')
    .click();

  cy.get(`[title="${actions.setDowntime}"]`)
    .children('button')
    .first()
    .should('be.enabled')
    .click();

  cy.get('textarea').should('be.visible');
  cy.get('button').contains(`${actions.setDowntime}`).click();
});

Then('The problematic Resource is displayed as in downtime', () => {
  refreshListing(5000);

  cy.wait('@getResources');
  cy.contains(serviceNameDowntime)
    .parents('div[role="cell"]:first')
    .should('have.css', 'background-color', actionBackgroundColors.inDowntime);
});
