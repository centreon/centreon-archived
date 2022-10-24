import { useState } from 'react';

import { useRequest } from '@centreon/ui';

import { getProviderConfiguration } from '../api';
import { openidConfigurationDecoder } from '../api/decoders';
import { Provider } from '../models';

import { OpenidConfiguration } from './models';

interface UseOpenidState {
  initialOpenidConfiguration: OpenidConfiguration | null;
  loadOpenidConfiguration: () => void;
  sendingGetOpenidConfiguration: boolean;
}

const useOpenid = (): UseOpenidState => {
  const [initialOpenidConfiguration, setInitialOpenidConfiguration] =
    useState<OpenidConfiguration | null>(null);
  const { sendRequest, sending } = useRequest<OpenidConfiguration>({
    decoder: openidConfigurationDecoder,
    request: getProviderConfiguration<OpenidConfiguration>(Provider.Openid)
  });

  const loadOpenidConfiguration = (): void => {
    sendRequest()
      .then(setInitialOpenidConfiguration)
      .catch(() => undefined);
  };

  return {
    initialOpenidConfiguration,
    loadOpenidConfiguration,
    sendingGetOpenidConfiguration: sending
  };
};

export default useOpenid;
