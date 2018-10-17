import React, { Component } from "react";
import { Route } from "react-router-dom";

class ReactRoute extends Component {
  getRoute = renderProps => {
    const { component: Comp } = this.props;
    return (
      <Comp
        key={
          renderProps.match.params.id
            ? renderProps.match.params.id
            : Math.random()
        }
        {...renderProps}
      />
    );
  };

  render() {
    const { component, ...rest } = this.props;

    return (
      <Route {...rest} render={renderProps => this.getRoute(renderProps)} />
    );
  }
}

export default ReactRoute;
