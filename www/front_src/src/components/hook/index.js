import React, { Component, Suspense } from "react";
import { connect } from "react-redux";
import { withRouter } from "react-router-dom";
import { dynamicImport } from "../../utils/dynamicImport";
import centreonAxios from "../../axios";

// class to dynamically import component from modules
class Hook extends Component {

  state = {
    LoadableComponents: []
  };

  // get hooks from redux and convert these in react components
  getLoadableComponents = () => {
    const { history, hooks, path } = this.props;
    const basename = history.createHref({pathname: '/', search: '', hash: ''});

    let LoadableComponents = [];
    for (const [hook, parameters] of Object.entries(hooks)) {
      if (hook === path) {
        for (const parameter of parameters) {
          LoadableComponents.push(
            React.lazy(
              () => dynamicImport(basename, parameter)
            )
          );
        }
      }
    }

    return LoadableComponents;
  }

  render() {
    const { path, hooks, ...props } = this.props;
    const LoadableComponents = this.getLoadableComponents();

    return (
      <Suspense fallback="">
        {LoadableComponents.map(LoadableComponent => (
          <LoadableComponent
            centreonAxios={centreonAxios}
            {...props}
          />
        ))}
      </Suspense>
    );
  };

}

const mapStateToProps = ({ externalComponents }) => ({
  hooks: externalComponents.hooks
});

export default connect(mapStateToProps)(withRouter(Hook));