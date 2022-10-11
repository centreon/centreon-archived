/* eslint-disable @typescript-eslint/no-namespace */
import { MountReturn } from '@cypress/react';
import { addMatchImageSnapshotCommand } from 'cypress-image-snapshot/command';

addMatchImageSnapshotCommand({
  capture: 'viewport',
  customDiffConfig: { threshold: 0.1 },
  customSnapshotsDir: './cypress/visual-testing-snapshots',
  failureThreshold: 0.03,
  failureThresholdType: 'percent',
});

Cypress.Commands.add('displayFilterMenu', () => {
  cy.get('[aria-label="Filter options"]').click();
  cy.contains('Type').should('be.visible').click();
});

Cypress.Commands.add('clickOutside', () => cy.get('body').click(0, 0));

declare global {
  namespace Cypress {
    interface Chainable {
      clickOutside: () => Cypress.Chainable<JQuery<HTMLBodyElement>>;
      displayFilterMenu: () => Cypress.Chainable<MountReturn>;
      mount: (
        component: JSX.Element,
        options?: object,
      ) => Cypress.Chainable<MountReturn>;
    }
  }
}
