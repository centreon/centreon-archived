import React from 'react';

import { addMatchImageSnapshotCommand } from 'cypress-image-snapshot/command';
import { mount } from 'cypress/react';

import { ThemeProvider } from '@centreon/ui';

addMatchImageSnapshotCommand({
  capture: 'viewport',
  customDiffConfig: { threshold: 0.1 },
  customSnapshotsDir: './cypress/visual-testing-snapshots',
  failureThreshold: 0.03,
  failureThresholdType: 'percent',
});

Cypress.Commands.add('mount', (component, options = {}) => {
  const wrapped = <ThemeProvider>{component}</ThemeProvider>;

  return mount(wrapped, options);
});
