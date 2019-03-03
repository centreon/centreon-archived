import React, { Component } from "react";
import { Redirect } from "react-router";
import routeMap from "../../route-maps/route-map";

class HomeRoute extends Component {
  render() {
    return (
      <div>
        <Redirect to={routeMap.module.replace(":id", 1)} />
      </div>
    );
  }
}

export default HomeRoute;
