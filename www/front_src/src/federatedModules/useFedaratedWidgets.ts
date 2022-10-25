import { useCallback, useEffect } from 'react';

import { useAtom } from 'jotai';
import { useDeepCompare } from 'centreon-frontend/packages/centreon-ui/src/utils/useMemoComponent';

import { getData, useRequest } from '@centreon/ui';

import usePlatformVersions from '../Main/usePlatformVersions';

import { federatedWidgetsAtom } from './atoms';
import { FederatedModule } from './models';

export const getFederatedWidget = (moduleName: string): string =>
  `./widgets/${moduleName}/static/moduleFederation.json`;

interface UseFederatedModulesState {
  federatedWidgets: Array<FederatedModule> | null;
  getFederatedModulesConfigurations: () => void;
}

const useFederatedWidgets = (): UseFederatedModulesState => {
  const { sendRequest } = useRequest<FederatedModule>({
    request: getData,
  });
  const [federatedWidgets, setFederatedWidgets] = useAtom(federatedWidgetsAtom);
  const { getWidgets } = usePlatformVersions();

  const widgets = getWidgets();

  const getFederatedModulesConfigurations = useCallback((): void => {
    if (!widgets) {
      return;
    }

    Promise.all(
      widgets?.map((moduleName) =>
        sendRequest({ endpoint: getFederatedWidget(moduleName) }),
      ) || [],
    ).then(setFederatedWidgets);
  }, [widgets]);

  useEffect(() => {
    getFederatedModulesConfigurations();
  }, useDeepCompare([widgets]));

  return {
    federatedWidgets,
    getFederatedModulesConfigurations,
  };
};

export default useFederatedWidgets;
