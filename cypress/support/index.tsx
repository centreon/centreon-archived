import React from 'react';
import './commands';

import { mount as cypressMount, MountReturn } from '@cypress/react';

import { ThemeProvider } from '@centreon/ui';

window.React = React;

const mount = (
  children: React.ReactElement,
): Cypress.Chainable<MountReturn> => {
  return cypressMount(<ThemeProvider>{children}</ThemeProvider>);
};

export { mount };
