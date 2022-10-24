import { useState } from 'react';

import { useRequest } from '@centreon/ui';

import { getProviderConfiguration } from '../api';
import { webSSOConfigurationDecoder } from '../api/decoders';
import { Provider } from '../models';

import { WebSSOConfiguration } from './models';

interface UseWebSSOState {
  initialWebSSOConfiguration: WebSSOConfiguration | null;
  loadWebSSOonfiguration: () => void;
  sendingGetWebSSOConfiguration: boolean;
}

const useWebSSO = (): UseWebSSOState => {
  const [initialWebSSOConfiguration, setInitialWebSSOConfiguration] =
    useState<WebSSOConfiguration | null>(null);
  const { sendRequest, sending } = useRequest<WebSSOConfiguration>({
    decoder: webSSOConfigurationDecoder,
    request: getProviderConfiguration<WebSSOConfiguration>(Provider.WebSSO)
  });

  const loadWebSSOonfiguration = (): void => {
    sendRequest()
      .then(setInitialWebSSOConfiguration)
      .catch(() => undefined);
  };

  return {
    initialWebSSOConfiguration,
    loadWebSSOonfiguration,
    sendingGetWebSSOConfiguration: sending
  };
};

export default useWebSSO;
