import '@percy/cypress';

import React from 'react';

import { mount as cypressMount, MountReturn } from '@cypress/react';

import { ThemeProvider } from '@centreon/ui';

const mount = (
  children: React.ReactElement,
): Cypress.Chainable<MountReturn> => {
  return cypressMount(<ThemeProvider>{children}</ThemeProvider>);
};

export { mount };
