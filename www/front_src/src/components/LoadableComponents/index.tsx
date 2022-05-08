import { useMemo } from 'react';

import { filter, isNil, propEq, reject } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useMemoComponent } from '@centreon/ui';

import { federatedComponentsAtom } from '../../federatedComponents/atoms';
import { Remote } from '../../federatedComponents/load';
import { FederatedComponent } from '../../federatedComponents/models';

interface Props {
  federatedComponents: Array<FederatedComponent>;
}

const LoadableComponents = ({
  federatedComponents,
  ...rest
}: Props): JSX.Element | null => {
  return useMemoComponent({
    Component: (
      <>
        {federatedComponents.map(({ remoteEntry, name, hooks, moduleName }) => {
          return hooks.map((component) => {
            return (
              <Remote
                isHook
                component={component}
                key={component}
                moduleName={moduleName}
                name={name}
                remoteEntry={remoteEntry}
                {...rest}
              />
            );
          });
        })}
      </>
    ),
    memoProps: [federatedComponents],
  });
};

interface LoadableComponentsContainerProps {
  exclude?: string;
  include?: string;
}

interface LoadableComponentsProps extends LoadableComponentsContainerProps {
  federatedComponents: Array<FederatedComponent> | null;
}

const getLoadableComponents = ({
  include,
  exclude,
  federatedComponents,
}: LoadableComponentsProps): Array<FederatedComponent> | null => {
  if (isNil(federatedComponents)) {
    return null;
  }

  const excludedComponents = exclude
    ? reject(propEq('moduleName', exclude), federatedComponents)
    : federatedComponents;

  const components = include
    ? filter(propEq('moduleName', include), excludedComponents)
    : excludedComponents;

  return components;
};

const LoadableComponentsContainer = ({
  include,
  exclude,
  ...props
}: LoadableComponentsContainerProps): JSX.Element | null => {
  const federatedComponents = useAtomValue(federatedComponentsAtom);

  const components = useMemo(
    () => getLoadableComponents({ exclude, federatedComponents, include }),
    [federatedComponents, include, exclude],
  );

  if (isNil(components)) {
    return null;
  }

  return <LoadableComponents federatedComponents={components} {...props} />;
};

export default LoadableComponentsContainer;
