import { useCallback, useEffect } from 'react';

import { useAtom } from 'jotai';
import { useDeepCompare } from 'centreon-frontend/packages/centreon-ui/src/utils/useMemoComponent';

import { getData, useRequest } from '@centreon/ui';

import usePlatformVersions from '../Main/usePlatformVersions';

import { federatedModulesAtom } from './atoms';
import { FederatedModule } from './models';

export const getFederatedModule = (moduleName: string): string =>
  `./modules/${moduleName}/static/moduleFederation.json`;

interface UseFederatedModulesState {
  federatedModules: Array<FederatedModule> | null;
  getFederatedModulesConfigurations: () => void;
}

const useFederatedModules = (): UseFederatedModulesState => {
  const { sendRequest } = useRequest<FederatedModule>({
    request: getData,
  });
  const [federatedModules, setFederatedModules] = useAtom(federatedModulesAtom);
  const { getModules } = usePlatformVersions();

  const modules = getModules();

  const getFederatedModulesConfigurations = useCallback((): void => {
    if (!modules) {
      return;
    }

    Promise.all(
      modules?.map((moduleName) =>
        sendRequest({ endpoint: getFederatedModule(moduleName) }),
      ) || [],
    ).then(setFederatedModules);
  }, [modules]);

  useEffect(() => {
    getFederatedModulesConfigurations();
  }, useDeepCompare([modules]));

  return {
    federatedModules,
    getFederatedModulesConfigurations,
  };
};

export default useFederatedModules;
