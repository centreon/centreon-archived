import { useEffect } from 'react';

import { useAtom } from 'jotai';
import { useDeepCompare } from 'centreon-frontend/packages/centreon-ui/src/utils/useMemoComponent';

import { getData, useRequest } from '@centreon/ui';

import usePlatformVersions from '../Main/usePlatformVersions';

import { federatedComponentsAtom } from './atoms';
import { FederatedComponent } from './models';

export const getFederatedComponent = (moduleName: string): string =>
  `./modules/${moduleName}/static/moduleFederation.json`;

interface UseFederatedComponentsState {
  federatedComponents: Array<FederatedComponent> | null;
  getFederatedComponents: () => void;
}

const useFederatedComponents = (): UseFederatedComponentsState => {
  const { sendRequest } = useRequest<FederatedComponent>({
    request: getData,
  });
  const [federatedComponents, setFederatedComponents] = useAtom(
    federatedComponentsAtom,
  );
  const { getModules } = usePlatformVersions();

  const modules = getModules();

  const getFederatedComponents = (): void => {
    if (!modules) {
      return;
    }

    Promise.all(
      getModules()?.map((moduleName) =>
        sendRequest({ endpoint: getFederatedComponent(moduleName) }),
      ) || [],
    ).then(setFederatedComponents);
  };

  useEffect(() => {
    getFederatedComponents();
  }, useDeepCompare([modules]));

  return {
    federatedComponents,
    getFederatedComponents,
  };
};

export default useFederatedComponents;
