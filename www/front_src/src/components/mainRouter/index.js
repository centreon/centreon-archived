/* eslint-disable react/jsx-no-undef */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';
import { Switch, Route, withRouter } from 'react-router-dom';
import ReactRouter from '../reactRouter';
import LegacyRoute from '../../route-components/legacyRoute';

// main router to handle switch between legacy routes and react pages
// legacy route has a key to make it fully uncontrolled
// (https://reactjs.org/blog/2018/06/07/you-probably-dont-need-derived-state.html#recommendation-fully-uncontrolled-component-with-a-key)
// it allows to reconstruct the component to display loading animation
const MainRouter = ({
  history: {
    location: { key },
  },
}) => (
  <Switch>
    <Route key={`path-${key}`} path="/main.php" exact component={LegacyRoute} />
    <Route path="/" exact render={() => <Redirect to="/main.php" />} />
    <Route path="/" component={ReactRouter} />
  </Switch>
);

export default withRouter(MainRouter);
