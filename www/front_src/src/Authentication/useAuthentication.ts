import * as React from 'react';

import { useRequest } from '@centreon/ui';

import { SecurityPolicy } from './models';
import { getSecurityPolicy } from './api';
import { securityPolicyDecoder } from './api/decoders';
import { adaptSecurityPolicyFromAPI } from './api/adapters';

interface UseAuthenticationState {
  initialSecurityPolicy: SecurityPolicy | null;
  sendingGetSecurityPolicy: boolean;
}

const useAuthentication = (): UseAuthenticationState => {
  const [initialSecurityPolicy, setInitialSecurityPolicy] =
    React.useState<SecurityPolicy | null>(null);
  const { sendRequest, sending } = useRequest<SecurityPolicy>({
    decoder: securityPolicyDecoder,
    request: getSecurityPolicy,
  });

  React.useEffect(() => {
    sendRequest()
      .then((securityPolicy) =>
        setInitialSecurityPolicy(adaptSecurityPolicyFromAPI(securityPolicy)),
      )
      .catch(() => undefined);
  }, []);

  return {
    initialSecurityPolicy,
    sendingGetSecurityPolicy: sending,
  };
};

export default useAuthentication;
