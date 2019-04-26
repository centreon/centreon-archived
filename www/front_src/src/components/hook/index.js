import React, { Component, Suspense } from "react";
import { connect } from "react-redux";
import { dynamicImport } from "../../utils/dynamicImport";
import centreonAxios from "../../axios";
import centreonConfig from "../../config";

// class to dynamically import component from modules
class Hook extends Component {

  getLoadableHooks = () => {
    const { hooks, path, ...rest } = this.props;

    let LoadableHooks = [];
    for (const [hook, parameters] of Object.entries(hooks)) {
      if (hook === path) {
        for (const parameter of parameters) {
          const LoadableHook = React.lazy(() => dynamicImport(parameter));
          LoadableHooks.push(
            <LoadableHook
              key={`hook_${parameter.js}`}
              centreonAxios={centreonAxios}
              centreonConfig={centreonConfig}
              {...rest}
            />
          );
        }
      }
    }

    return LoadableHooks;
  }

  render() {
    const LoadableHooks = this.getLoadableHooks();

    return (
      <Suspense fallback="">
        {LoadableHooks}
      </Suspense>
    );
  };

}

const mapStateToProps = ({ externalComponents }) => ({
  hooks: externalComponents.hooks
});

export default connect(mapStateToProps)(Hook);