import { When, Then, Before } from 'cypress-cucumber-preprocessor/steps';

import {
  stateFilterContainer,
  refreshButton,
  serviceName,
  serviceNameDowntime,
  resourceMonitoringApi,
  bgCssColors,
  actions,
} from '../common';

const refreshListing = (timeout = 5000) => {
  // "wait" here, it's necessary to allow time for the action
  // to be taken into account before launching a call to the API.
  cy.wait(timeout);
  cy.get(refreshButton).click();
};

Before(() => {
  cy.get(stateFilterContainer).click().get('[data-value="all"]').click();

  cy.intercept({
    url: resourceMonitoringApi,
    method: 'GET',
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
  refreshListing();

  cy.wait('@getResources');
  cy.contains(serviceName)
    .parents('div[role="cell"]:first')
    .should('have.css', 'background-color', bgCssColors.acknowledge);
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
  refreshListing();

  cy.wait('@getResources');
  cy.contains(serviceNameDowntime)
    .parents('div[role="cell"]:first')
    .should('have.css', 'background-color', bgCssColors.inDowntime);
});
