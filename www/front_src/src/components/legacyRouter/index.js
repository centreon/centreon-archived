import React, { Component } from "react";
import { Switch, Route } from "react-router-dom";
import { history } from "../../store";
import { classicRoutes } from "../../route-maps";
import ClassicRoute from '../router/classicRoute';

// class to manage legacy pages in an iframe
class LegacyRouter extends Component {

  render() {
    return (
      <>
        {classicRoutes.map(({path, comp, ...rest}, i) => (
          <ClassicRoute
            key={i}
            history={history}
            path={path}
            component={comp}
            {...rest}
          />
        ))}
      </>
    );
  };
}

export default LegacyRouter;
