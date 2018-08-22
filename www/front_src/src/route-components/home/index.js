import React, { Component } from "react";
import { Redirect } from "react-router";
import { connect } from "react-redux";
import routeMap from "../../route-maps";

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
