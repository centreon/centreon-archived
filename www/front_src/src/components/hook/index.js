/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-restricted-syntax */
/* eslint-disable react/prop-types */

import React, { Component, Suspense } from 'react';
import { connect } from 'react-redux';
import { withRouter } from 'react-router-dom';
import { dynamicImport } from '../../helpers/dynamicImport';
import centreonAxios from '../../axios';

// class to dynamically import component from modules
class Hook extends Component {
  getLoadableHooks = () => {
    const { history, hooks, path, ...rest } = this.props;
    const basename = history.createHref({
      pathname: '/',
      search: '',
      hash: '',
    });

    const LoadableHooks = [];
    for (const [hook, parameters] of Object.entries(hooks)) {
      if (hook === path) {
        for (const parameter of parameters) {
          const LoadableHook = React.lazy(() =>
            dynamicImport(basename, parameter),
          );
          LoadableHooks.push(
            <LoadableHook
              key={`hook_${parameter.js}`}
              centreonAxios={centreonAxios}
              {...rest}
            />,
          );
        }
      }
    }

    return LoadableHooks;
  };

  render() {
    const LoadableHooks = this.getLoadableHooks();

    return <Suspense fallback={null}>{LoadableHooks}</Suspense>;
  }
}

const mapStateToProps = ({ externalComponents }) => ({
  hooks: externalComponents.hooks,
});

export default connect(mapStateToProps)(withRouter(Hook));
