import { useUpdateAtom } from 'jotai/utils';

import { getData, useRequest } from '@centreon/ui';

import { externalComponentsAtom } from './atoms';
import ExternalComponents from './models';

const externalComponentsEndpoint =
  './api/internal.php?object=centreon_frontend_component&action=components';

interface UseExternalComponentsState {
  getExternalComponents: () => void;
}

const useExternalComponents = (): UseExternalComponentsState => {
  const { sendRequest } = useRequest<ExternalComponents>({
    request: getData,
  });

  const setExternalComponents = useUpdateAtom(externalComponentsAtom);

  const getExternalComponents = (): void => {
    sendRequest(externalComponentsEndpoint).then(setExternalComponents);
  };

  return {
    getExternalComponents,
  };
};

export default useExternalComponents;
