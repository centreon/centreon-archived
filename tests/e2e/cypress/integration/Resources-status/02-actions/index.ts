import { When, Then, Before } from 'cypress-cucumber-preprocessor/steps';

import {
  stateFilterContainer,
  resourceMonitoringApi,
  actionBackgroundColors,
  actions,
} from '../common';
import { refreshListing } from '../../../support/centreonData';

const serviceName = 'service_test';
const serviceInDowntimeName = 'service_test_dt';

Before(() => {
  cy.get(stateFilterContainer).click().get('[data-value="all"]').click();

  cy.intercept({
    method: 'GET',
    url: resourceMonitoringApi,
  });
});

When('I select the acknowledge action on a problematic Resource', () => {
  cy.contains(serviceName)
    .parents('div[role="row"]:first')
    .find('input[type="checkbox"]:first')
    .click();

  cy.get(`[aria-label="${actions.acknowledge}"]`)
    .parent('button')
    .first()
    .should('be.enabled')
    .click();

  cy.get('textarea').should('be.visible');
  cy.get('button').contains('Acknowledge').click();
});

Then('the problematic Resource is displayed as acknowledged', () => {
  refreshListing(5000);

  cy.contains(serviceName)
    .parent()
    .parent()
    .parent()
    .should('have.css', 'background-color', actionBackgroundColors.acknowledge);
});

When('I select the downtime action on a problematic Resource', () => {
  cy.contains(serviceInDowntimeName)
    .parents('div[role="row"]:first')
    .find('input[type="checkbox"]:first')
    .click();

  cy.get(`[aria-label="${actions.setDowntime}"]`)
    .parent('button')
    .first()
    .should('be.enabled')
    .click();

  cy.get('textarea').should('be.visible');
  cy.get('button').contains(`${actions.setDowntime}`).click();
});

Then('the problematic Resource is displayed as in downtime', () => {
  refreshListing(5000);

  cy.contains(serviceInDowntimeName)
    .parent()
    .parent()
    .parent()
    .should('have.css', 'background-color', actionBackgroundColors.inDowntime);
});
