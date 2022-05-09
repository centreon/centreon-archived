import { useMemo } from 'react';

import { filter, isNil, pathEq } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useMemoComponent } from '@centreon/ui';

import { federatedComponentsAtom } from '../../federatedModules/atoms';
import { Remote } from '../../federatedModules/Load';
import { FederatedComponent } from '../../federatedModules/models';

interface Props {
  federatedComponents: Array<FederatedComponent>;
}

const FederatedComponents = ({
  federatedComponents,
  ...rest
}: Props): JSX.Element | null => {
  return useMemoComponent({
    Component: (
      <>
        {federatedComponents.map(
          ({ remoteEntry, name, hooksConfiguration, moduleName }) => {
            return hooksConfiguration.hooks.map((component) => {
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
          },
        )}
      </>
    ),
    memoProps: [federatedComponents],
  });
};

interface LoadableComponentsContainerProps {
  path: string;
}

interface LoadableComponentsProps extends LoadableComponentsContainerProps {
  federatedComponents: Array<FederatedComponent> | null;
}

const getLoadableComponents = ({
  path,
  federatedComponents,
}: LoadableComponentsProps): Array<FederatedComponent> | null => {
  if (isNil(federatedComponents)) {
    return null;
  }

  const components = path
    ? filter(pathEq(['hooksConfiguration', 'path'], path), federatedComponents)
    : federatedComponents;

  return components;
};

const LoadableComponentsContainer = ({
  path,
  ...props
}: LoadableComponentsContainerProps): JSX.Element | null => {
  const federatedComponents = useAtomValue(federatedComponentsAtom);

  const components = useMemo(
    () => getLoadableComponents({ federatedComponents, path }),
    [federatedComponents, path],
  );

  if (isNil(components)) {
    return null;
  }

  return <FederatedComponents federatedComponents={components} {...props} />;
};

export default LoadableComponentsContainer;
