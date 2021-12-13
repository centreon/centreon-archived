import { useAtom } from 'jotai';

import { getData, useRequest } from '@centreon/ui';

import { externalComponentsAtom } from './atoms';
import ExternalComponents from './models';

const externalComponentsEndpoint =
  './api/internal.php?object=centreon_frontend_component&action=components';

interface UseExternalComponentsState {
  externalComponents: ExternalComponents | null;
  getExternalComponents: () => void;
}

const useExternalComponents = (): UseExternalComponentsState => {
  const { sendRequest } = useRequest<ExternalComponents>({
    request: getData,
  });

  const [externalComponents, setExternalComponents] = useAtom(
    externalComponentsAtom,
  );

  const getExternalComponents = (): void => {
    sendRequest({
      endpoint: externalComponentsEndpoint,
    }).then(setExternalComponents);
  };

  return {
    externalComponents,
    getExternalComponents,
  };
};

export default useExternalComponents;
