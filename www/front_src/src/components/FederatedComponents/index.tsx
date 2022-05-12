import { useMemo } from 'react';

import { filter, isNil, pathEq } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useMemoComponent } from '@centreon/ui';

import { federatedModulesAtom } from '../../federatedModules/atoms';
import { Remote } from '../../federatedModules/Load';
import { FederatedModule } from '../../federatedModules/models';

interface Props {
  federatedModulesConfigurations: Array<FederatedModule>;
}

const FederatedModules = ({
  federatedModulesConfigurations,
  ...rest
}: Props): JSX.Element | null => {
  return useMemoComponent({
    Component: (
      <>
        {federatedModulesConfigurations.map(
          ({
            remoteEntry,
            moduleFederationName,
            federatedComponentsConfiguration,
            moduleName,
          }) => {
            return federatedComponentsConfiguration.federatedComponents.map(
              (component) => {
                return (
                  <Remote
                    isFederatedModule
                    component={component}
                    key={component}
                    moduleFederationName={moduleFederationName}
                    moduleName={moduleName}
                    remoteEntry={remoteEntry}
                    {...rest}
                  />
                );
              },
            );
          },
        )}
      </>
    ),
    memoProps: [federatedModulesConfigurations],
  });
};

interface LoadableComponentsContainerProps {
  path: string;
}

interface LoadableComponentsProps extends LoadableComponentsContainerProps {
  federatedModules: Array<FederatedModule> | null;
}

const getLoadableComponents = ({
  path,
  federatedModules,
}: LoadableComponentsProps): Array<FederatedModule> | null => {
  if (isNil(federatedModules)) {
    return null;
  }

  const components = path
    ? filter(
        pathEq(['federatedComponentsConfiguration', 'path'], path),
        federatedModules,
      )
    : federatedModules;

  return components;
};

const LoadableComponentsContainer = ({
  path,
  ...props
}: LoadableComponentsContainerProps): JSX.Element | null => {
  const federatedModules = useAtomValue(federatedModulesAtom);

  const federatedModulesToDisplay = useMemo(
    () => getLoadableComponents({ federatedModules, path }),
    [federatedModules, path],
  );

  if (isNil(federatedModulesToDisplay)) {
    return null;
  }

  return (
    <FederatedModules
      federatedModulesConfigurations={federatedModulesToDisplay}
      {...props}
    />
  );
};

export default LoadableComponentsContainer;
