import { When, Then } from 'cypress-cucumber-preprocessor/steps';

import {
  stateFilterContainer,
  actionBackgroundColors,
  actions,
  insertResourceFixtures,
  tearDownResource,
} from '../common';

const serviceInAcknowledgementName = 'service_test_ack';
const serviceInDowntimeName = 'service_test_dt';

before(() => {
  insertResourceFixtures();
});

When('I select the acknowledge action on a problematic Resource', () => {
  cy.contains(serviceInAcknowledgementName)
    .parent()
    .parent()
    .parent()
    .parent()
    .find('input[type="checkbox"]:first')
    .click();

  cy.getByLabel({ label: actions.acknowledge }).last().click();

  cy.get('textarea').should('be.visible');
  cy.get('button').contains('Acknowledge').click();
});

Then('the problematic Resource is displayed as acknowledged', () => {
  cy.get(stateFilterContainer).click().get('[data-value="all"]').click();
  cy.waitUntil(() => {
    return cy
      .refreshListing()
      .then(() => cy.contains(serviceInAcknowledgementName))
      .parent()
      .parent()
      .parent()
      .then((val) => {
        return (
          val.css('background-color') === actionBackgroundColors.acknowledge
        );
      });
  });
});

When('I select the downtime action on a problematic Resource', () => {
  cy.contains(serviceInDowntimeName)
    .parent()
    .parent()
    .parent()
    .parent()
    .find('input[type="checkbox"]:first')
    .click();

  cy.getByLabel({ label: actions.setDowntime }).last().click();

  cy.get('textarea').should('be.visible');
  cy.get('button').contains(`${actions.setDowntime}`).click();
});

Then('the problematic Resource is displayed as in downtime', () => {
  cy.waitUntil(() => {
    return cy
      .refreshListing()
      .then(() => cy.contains(serviceInDowntimeName))
      .parent()
      .parent()
      .parent()
      .then((val) => {
        return (
          val.css('background-color') === actionBackgroundColors.inDowntime
        );
      });
  });
});

after(() => {
  tearDownResource().then(() => cy.reload());
});
