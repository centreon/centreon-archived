/* eslint-disable react/jsx-no-undef */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import * as React from 'react';

import { Switch, Route, withRouter } from 'react-router-dom';

import { PageSkeleton } from '@centreon/ui';

import LegacyRoute from '../../route-components/legacyRoute';

const ReactRouter = React.lazy(() => import('../ReactRouter'));

// main router to handle switch between legacy routes and react pages
// legacy route has a key to make it fully uncontrolled
// (https://reactjs.org/blog/2018/06/07/you-probably-dont-need-derived-state.html#recommendation-fully-uncontrolled-component-with-a-key)
// it allows to reconstruct the component to display loading animation
const MainRouter = ({
  history: {
    location: { key },
  },
}) => (
  <React.Suspense fallback={<PageSkeleton />}>
    <Switch>
      <Route
        exact
        component={LegacyRoute}
        key={`path-${key}`}
        path="/main.php"
      />
      <Route exact path="/" render={() => <Redirect to="/main.php" />} />
      <Route component={ReactRouter} path="/" />
    </Switch>
  </React.Suspense>
);

export default withRouter(MainRouter);
