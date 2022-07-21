import React from 'react';

import './commands';
import { mount } from 'cypress/react';

import { ThemeProvider } from '@centreon/ui';

window.React = React;

Cypress.Commands.add('mount', (component, options = {}) => {
  const wrapped = <ThemeProvider>{component}</ThemeProvider>;

  return mount(wrapped, options);
});
