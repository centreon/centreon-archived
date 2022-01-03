import * as React from 'react';

import { connect } from 'react-redux';
import { equals } from 'ramda';
import { useHref } from 'react-router';

import { MenuSkeleton } from '@centreon/ui';

import { dynamicImport } from '../../helpers/dynamicImport';

interface Props {
  hooks;
  path;
}

const LoadableHooks = ({ hooks, path, ...rest }: Props): JSX.Element => {
  const basename = useHref('/');

  return (
    <>
      {Object.entries(hooks)
        .filter(([hook]) => hook.includes(path))
        .map(([, parameters]) => {
          const HookComponent = React.lazy(() =>
            dynamicImport(basename, parameters),
          );

          return (
            <React.Suspense fallback={<MenuSkeleton width={29} />} key={path}>
              <HookComponent {...rest} />
            </React.Suspense>
          );
        })}
    </>
  );
};

const Hook = React.memo(
  (props: Props) => {
    return <LoadableHooks {...props} />;
  },
  ({ hooks: previousHooks }, { hooks: nextHooks }) =>
    equals(previousHooks, nextHooks),
);

const mapStateToProps = ({ externalComponents }): Record<string, unknown> => ({
  hooks: externalComponents.hooks,
});

export default connect(mapStateToProps)(Hook);
