import React, { Component } from "react";
import { Route } from "react-router-dom";
import styles from "../../styles/partials/_content.scss";

class ReactRoute extends Component {
  getRoute = renderProps => {
    const { component: Comp } = this.props;

    return (
      <div className={styles["react-page"]}>
        <Comp
          key={
            renderProps.match.params.id
              ? renderProps.match.params.id
              : Math.random()
          }
          {...renderProps}
        />
      </div>
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
