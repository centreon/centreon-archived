import React from 'react';

import { addMatchImageSnapshotCommand } from 'cypress-image-snapshot/command';
import { mount as cypressMount, MountReturn } from '@cypress/react';

import { ThemeProvider } from '@centreon/ui';

addMatchImageSnapshotCommand({
  capture: 'viewport',
  customDiffConfig: { threshold: 0.1 },
  failureThreshold: 0.03,
  failureThresholdType: 'percent',
});

const mount = (
  children: React.ReactElement,
): Cypress.Chainable<MountReturn> => {
  return cypressMount(<ThemeProvider>{children}</ThemeProvider>);
};

export { mount };
