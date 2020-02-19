import React, { Suspense } from 'react';

import { connect } from 'react-redux';
import { withRouter } from 'react-router-dom';
import isEqual from 'lodash/isEqual';

import { dynamicImport } from '../../helpers/dynamicImport';
import centreonAxios from '../../axios';

interface Props {
  history;
  hooks;
  path;
}

const LoadableHooks = ({
  history,
  hooks,
  path,
  ...rest
}: Props): JSX.Element => {
  const basename = history.createHref({
    pathname: '/',
    search: '',
    hash: '',
  });

  return (
    <>
      {Object.entries(hooks)
        .filter(([hook]) => hook === path)
        .map(([_, parameters]) => {
          const HookComponent = React.lazy(() =>
            dynamicImport(basename, parameters),
          );

          return (
            <HookComponent key={path} centreonAxios={centreonAxios} {...rest} />
          );
        })}
    </>
  );
};

const Hook = React.memo(
  (props: Props) => {
    return (
      <Suspense fallback={null}>
        <LoadableHooks {...props} />
      </Suspense>
    );
  },
  ({ hooks: previousHooks }, { hooks: nextHooks }) =>
    isEqual(previousHooks, nextHooks),
);

const mapStateToProps = ({ externalComponents }): {} => ({
  hooks: externalComponents.hooks,
});

export default connect(mapStateToProps)(withRouter(Hook));
