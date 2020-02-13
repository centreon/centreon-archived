/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-restricted-syntax */
/* eslint-disable react/prop-types */

import React, { Suspense } from 'react';
import { connect } from 'react-redux';
import { withRouter } from 'react-router-dom';
import { dynamicImport } from '../../helpers/dynamicImport';
import centreonAxios from '../../axios';

const LoadableHooks = ({ history, hooks, path, ...rest }) => {
  const basename = history.createHref({
    pathname: '/',
    search: '',
    hash: '',
  });

  return (
    <>
      {Object.entries(hooks)
        .filter(([hook]) => hook === path)
        // eslint-disable-next-line no-unused-vars
        .map(([_, parameters]) => {
          const LoadableHook = React.lazy(() =>
            dynamicImport(basename, parameters),
          );

          return (
            <LoadableHook
              key={`hook_${parameters.js}`}
              centreonAxios={centreonAxios}
              {...rest}
            />
          );
        })}
    </>
  );
};

// class to dynamically import component from modules
const Hook = React.memo(
  (props) => {
    return (
      <Suspense fallback={null}>
        <LoadableHooks {...props} />
      </Suspense>
    );
  },
  ({ hooks: previousHooks }, { hooks: nextHooks }) =>
    JSON.stringify(previousHooks) === JSON.stringify(nextHooks),
);

const mapStateToProps = ({ externalComponents }) => ({
  hooks: externalComponents.hooks,
});

export default connect(mapStateToProps)(withRouter(Hook));
