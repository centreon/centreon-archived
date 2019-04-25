import React, { Component, Suspense } from "react";
import { connect } from "react-redux";
import { dynamicImport } from "../../utils/dynamicImport";
import centreonAxios from "../../axios";
import centreonConfig from "../../config";

// class to dynamically import component from modules
class Hook extends Component {

  state = {
    LoadableComponents: []
  };

  // get hooks from redux and convert these in react components
  getLoadableComponents = () => {
    const { hooks, path } = this.props;

    let LoadableComponents = [];
    for (const [hook, parameters] of Object.entries(hooks)) {
      if (hook === path) {
        for (const parameter of parameters) {
          LoadableComponents.push({
            key: parameter.js,
            component: React.lazy(() => dynamicImport(parameter))
          });
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
        {LoadableComponents.map(({ key, component: LoadableComponent}) => (
          <LoadableComponent
            key={key}
            centreonConfig={centreonConfig}
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

export default connect(mapStateToProps)(Hook);