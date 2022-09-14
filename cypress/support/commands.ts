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

declare global {
  namespace Cypress {
    interface Chainable {
      mount: (
        component: JSX.Element,
        options?: object,
      ) => Cypress.Chainable<MountReturn>;
    }
  }
}
