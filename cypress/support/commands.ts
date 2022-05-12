import { addMatchImageSnapshotCommand } from 'cypress-image-snapshot/command';

addMatchImageSnapshotCommand({
  capture: 'viewport',
  customDiffConfig: { threshold: 0.1 },
  failureThreshold: 0.03,
  failureThresholdType: 'percent',
});
