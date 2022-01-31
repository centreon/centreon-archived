import * as React from 'react';

import { BrowserRouter as Router } from 'react-router-dom';
import { Provider as JotaiProvider } from 'jotai';
import { not, startsWith, tail } from 'ramda';

import { ThemeProvider, Module } from '@centreon/ui';

interface Props {
  children: React.ReactNode;
}

const Provider = ({ children }: Props): JSX.Element | null => {
  const basename =
    (document
      .getElementsByTagName('base')[0]
      ?.getAttribute('href') as string) || '';

  const pathStartsWithBasename = startsWith(basename, window.location.pathname);

  React.useEffect(() => {
    if (pathStartsWithBasename) {
      return;
    }

    const path = tail(window.location.pathname);
    window.location.href = `${basename}${path}`;
  }, []);

  if (not(pathStartsWithBasename)) {
    return null;
  }

  return (
    <Router basename={basename}>
      <ThemeProvider>
        <Module maxSnackbars={2} seedName="centreon">
          <JotaiProvider scope="ui-context">{children}</JotaiProvider>
        </Module>
      </ThemeProvider>
    </Router>
  );
};

export default Provider;
