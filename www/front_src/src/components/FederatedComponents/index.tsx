import { useMemo } from 'react';

import { concat, filter, isNil, pathEq } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useMemoComponent } from '@centreon/ui';

import {
  federatedModulesAtom,
  federatedWidgetsAtom,
} from '../../federatedModules/atoms';
import { Remote } from '../../federatedModules/Load';
import { FederatedModule } from '../../federatedModules/models';

interface Props extends Record<string, unknown> {
  federatedModulesConfigurations: Array<FederatedModule>;
  isFederatedWidget?: boolean;
  memoProps: Array<unknown>;
}

const FederatedModules = ({
  federatedModulesConfigurations,
  isFederatedWidget,
  memoProps,
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
                    isFederatedWidget={isFederatedWidget}
                    key={component}
                    memoProps={memoProps}
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
    memoProps: [federatedModulesConfigurations, memoProps],
  });
};

interface LoadableComponentsContainerProps extends Record<string, unknown> {
  isFederatedWidget?: boolean;
  memoProps?: Array<unknown>;
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
  isFederatedWidget,
  memoProps = [],
  ...props
}: LoadableComponentsContainerProps): JSX.Element | null => {
  const federatedModules = useAtomValue(federatedModulesAtom);
  const federatedWidgets = useAtomValue(federatedWidgetsAtom);

  const federatedModulesToDisplay = useMemo(
    () =>
      getLoadableComponents({
        federatedModules: concat(
          federatedModules || [],
          federatedWidgets || [],
        ),
        path,
      }),
    [federatedModules, path],
  );

  if (isNil(federatedModulesToDisplay)) {
    return null;
  }

  return (
    <FederatedModules
      federatedModulesConfigurations={federatedModulesToDisplay}
      isFederatedWidget={isFederatedWidget}
      memoProps={memoProps}
      {...props}
    />
  );
};

export default LoadableComponentsContainer;
