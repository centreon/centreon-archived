import { addMatchImageSnapshotCommand } from 'cypress-image-snapshot/command';

addMatchImageSnapshotCommand({
  capture: 'viewport',
  customDiffConfig: { threshold: 0.1 },
  customSnapshotsDir: './cypress/visual-testing-snapshots',
  failureThreshold: 0.03,
  failureThresholdType: 'percent',
});
