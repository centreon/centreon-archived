import { useAtom } from 'jotai';

import { getData, useRequest } from '@centreon/ui';

import { federatedComponentsAtom } from './atoms';
import { FederatedComponent } from './models';

export const externalComponentsEndpoint = 'http://10.25.8.83:3010/';

interface UseFederatedComponentsState {
  federatedComponents: Array<FederatedComponent> | null;
  getFederatedComponents: () => void;
}

const useFederatedComponents = (): UseFederatedComponentsState => {
  const { sendRequest } = useRequest<Array<FederatedComponent>>({
    request: getData,
  });

  const [federatedComponents, setFederatedComponents] = useAtom(
    federatedComponentsAtom,
  );

  const getFederatedComponents = (): void => {
    sendRequest({
      endpoint: externalComponentsEndpoint,
    }).then(setFederatedComponents);
  };

  return {
    federatedComponents,
    getFederatedComponents,
  };
};

export default useFederatedComponents;
