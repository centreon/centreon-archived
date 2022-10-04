import { addMatchImageSnapshotCommand } from 'cypress-image-snapshot/command';

addMatchImageSnapshotCommand({
  capture: 'viewport',
  customDiffConfig: { threshold: 0.1 },
  customSnapshotsDir: './cypress/visual-testing-snapshots',
  failureThreshold: 0.03,
  failureThresholdType: 'percent',
});

Cypress.Commands.add('display_filter_Menu', () => {
  cy.get('[aria-label="Filter options"]').click();
  cy.contains('Type').should('be.visible').click();
});
