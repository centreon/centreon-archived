import * as React from 'react';

import { useRequest } from '@centreon/ui';

import { getWebSSOConfiguration } from '../api';
import { webSSOConfigurationDecoder } from '../api/decoders';

import { WebSSOConfiguration } from './models';

interface UseWebSSOState {
  initialWebSSOConfiguration: WebSSOConfiguration | null;
  loadWebSSOonfiguration: () => void;
  sendingGetWebSSOConfiguration: boolean;
}

const useWebSSO = (): UseWebSSOState => {
  const [initialWebSSOConfiguration, setInitialWebSSOConfiguration] =
    React.useState<WebSSOConfiguration | null>(null);
  const { sendRequest, sending } = useRequest<WebSSOConfiguration>({
    decoder: webSSOConfigurationDecoder,
    request: getWebSSOConfiguration,
  });

  const loadWebSSOonfiguration = (): void => {
    sendRequest()
      .then(setInitialWebSSOConfiguration)
      .catch(() => undefined);
  };

  return {
    initialWebSSOConfiguration,
    loadWebSSOonfiguration,
    sendingGetWebSSOConfiguration: sending,
  };
};

export default useWebSSO;
