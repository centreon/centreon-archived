import * as React from 'react';

import { BrowserRouter as Router } from 'react-router-dom';
import { Provider as JotaiProvider } from 'jotai';
import { startsWith, tail } from 'ramda';

import { ThemeProvider } from '@centreon/ui';

interface Props {
  children: React.ReactNode;
}

const Provider = ({ children }: Props): JSX.Element => {
  const basename =
    (document
      .getElementsByTagName('base')[0]
      ?.getAttribute('href') as string) || '';

  React.useEffect(() => {
    const pathStartWithBasename = startsWith(
      basename,
      window.location.pathname,
    );

    if (pathStartWithBasename) {
      return;
    }

    const path = tail(window.location.pathname);
    window.location.href = `${basename}${path}`;
  }, []);

  return (
    <Router basename={basename}>
      <ThemeProvider>
        <JotaiProvider scope="ui-context">{children}</JotaiProvider>
      </ThemeProvider>
    </Router>
  );
};

export default Provider;
