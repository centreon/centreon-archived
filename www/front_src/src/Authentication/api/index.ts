import { CancelToken } from 'axios';

import { getData, putData } from '@centreon/ui';

import {
  PasswordSecurityPolicy,
  PasswordSecurityPolicyFromAPI,
  PasswordSecurityPolicyToAPI,
} from '../Local/models';
import { Provider } from '../models';

import { authenticationProvidersEndpoint } from './endpoints';
import { adaptPasswordSecurityPolicyToAPI } from './adapters';

export const getPasswordPasswordSecurityPolicy =
  (cancelToken: CancelToken) => (): Promise<PasswordSecurityPolicy> =>
    getData<PasswordSecurityPolicyFromAPI>(cancelToken)({
      endpoint: authenticationProvidersEndpoint(Provider.Local),
    }).then(
      (securityPolicy): PasswordSecurityPolicy =>
        securityPolicy.password_security_policy,
    );

export const putPasswordPasswordSecurityPolicy =
  (cancelToken: CancelToken) =>
  (securityPolicy: PasswordSecurityPolicy): Promise<unknown> =>
    putData<PasswordSecurityPolicyToAPI, unknown>(cancelToken)({
      data: adaptPasswordSecurityPolicyToAPI(securityPolicy),
      endpoint: authenticationProvidersEndpoint(Provider.Local),
    });
