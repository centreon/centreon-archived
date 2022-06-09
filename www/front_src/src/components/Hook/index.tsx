import React, { Suspense } from 'react';

import { connect } from 'react-redux';
import { useHistory } from 'react-router';
import isEqual from 'lodash/isEqual';

import { dynamicImport } from '../../helpers/dynamicImport';
import centreonAxios from '../../axios';

interface Props {
  hooks;
  path;
}

const LoadableHooks = ({ hooks, path, ...rest }: Props): JSX.Element => {
  const history = useHistory();
  const basename = history.createHref({
    hash: '',
    pathname: '/',
    search: '',
  });

  return (
    <>
      {Object.entries(hooks)
        .filter(([hook]) => hook.includes(path))
        .map(([, parameters]) => {
          const HookComponent = React.lazy(() =>
            dynamicImport(basename, parameters),
          );

          return (
            <HookComponent centreonAxios={centreonAxios} key={path} {...rest} />
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

const mapStateToProps = ({ externalComponents }): Record<string, unknown> => ({
  hooks: externalComponents.hooks,
});

export default connect(mapStateToProps)(Hook);
